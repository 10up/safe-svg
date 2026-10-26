<?php
/**
 * Shared SVG sanitization.
 *
 * @package safe-svg
 */

namespace SafeSvg;

use enshrined\svgSanitize\Sanitizer;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Stateless helpers for configuring the sanitizer and reading safe markup.
 *
 * @since 2.5.1
 */
class Svg_Sanitizer {

	/**
	 * Post meta key holding the cached sanitized markup for an attachment.
	 *
	 * @since 2.5.1
	 *
	 * @var string
	 */
	const CACHE_META_KEY = '_safe_svg_sanitized';

	/**
	 * Smallest file worth caching, in bytes.
	 *
	 * @since 2.5.1
	 *
	 * @var int
	 */
	const CACHE_MIN_FILESIZE = 8192;

	/**
	 * Largest default size a gzipped SVG may inflate to, in bytes.
	 *
	 * Increased ten fold when the large SVG option is enabled.
	 *
	 * @since 2.5.2
	 *
	 * @var int
	 */
	const MAX_INFLATED_SIZE = 10485760;

	/**
	 * Whether a string is gzip-compressed.
	 *
	 * @since 2.5.1
	 *
	 * @param string $contents Content to check.
	 * @return bool
	 */
	public static function is_gzipped( $contents ) {
		if ( ! is_string( $contents ) || '' === $contents ) {
			return false;
		}

		// phpcs:ignore Generic.Strings.UnnecessaryStringConcat.Found
		$magic = "\x1f" . "\x8b" . "\x08";

		if ( function_exists( 'mb_strpos' ) ) {
			return 0 === mb_strpos( $contents, $magic );
		}

		return 0 === strpos( $contents, $magic );
	}

	/**
	 * Whether a filename has an SVG extension.
	 *
	 * @since 2.5.2
	 *
	 * @param string $filename File name or path.
	 * @return bool
	 */
	public static function is_svg_filename( $filename ) {
		$ext = strtolower( (string) pathinfo( (string) $filename, PATHINFO_EXTENSION ) );

		return in_array( $ext, array( 'svg', 'svgz' ), true );
	}

	/**
	 * Return a sanitizer configured the same way as the upload path.
	 *
	 * @since 2.5.1
	 *
	 * @param Sanitizer|null $sanitizer Optional sanitizer to configure. A new
	 *                                  instance is created when none is given.
	 * @return Sanitizer
	 */
	public static function configure( $sanitizer = null ) {
		if ( ! $sanitizer instanceof Sanitizer ) {
			$sanitizer = new Sanitizer();
		}

		$sanitizer->minify( true );

		// Allow large SVGs if the setting is on.
		if ( get_option( 'safe_svg_large_svg' ) ) {
			$sanitizer->setAllowHugeFiles( true );
		}

		/**
		 * Strip references to remote resources from the SVG.
		 *
		 * This removes remote `href`/`xlink:href` targets, along with `url()`,
		 * `@import` and `image-set()` references inside `<style>` elements and
		 * `style` attributes.
		 *
		 * It is off by default as some SVGs reference remote fonts and images,
		 * and removing them would silently change how those files render.
		 *
		 * @since 2.5.0
		 *
		 * @param bool $remove_remote_references Whether to strip remote references.
		 *                                       Default false.
		 */
		$sanitizer->removeRemoteReferences(
			(bool) apply_filters( 'safe_svg_remove_remote_references', false )
		);

		// Load extra filters to allow devs to access the safe tags and attrs.
		$sanitizer->setAllowedTags( new SafeSvgTags\safe_svg_tags() );
		$sanitizer->setAllowedAttrs( new SafeSvgAttr\safe_svg_attributes() );

		return $sanitizer;
	}

	/**
	 * Sanitize SVG markup and return the cleaned XML.
	 *
	 * Gzipped input is decoded first. The return value is always plain XML; it
	 * is never re-compressed. Callers that persist `.svgz` files must gzip
	 * again.
	 *
	 * @since 2.5.1
	 *
	 * @param string $dirty Raw SVG markup, optionally gzip-compressed.
	 * @return string|false Clean SVG markup, or false on failure.
	 */
	public static function sanitize_markup( $dirty ) {
		if ( ! is_string( $dirty ) || '' === $dirty ) {
			return false;
		}

		if ( self::is_gzipped( $dirty ) ) {
			$max_size = get_option( 'safe_svg_large_svg' ) ? 10 * self::MAX_INFLATED_SIZE : self::MAX_INFLATED_SIZE;

			/**
			 * Filters the largest size a gzipped SVG may inflate to, in bytes.
			 *
			 * @since 2.5.2
			 *
			 * @param int $max_size Maximum inflated size in bytes. Default 10 MB,
			 *                      or 100 MB when large SVGs are allowed.
			 */
			$max_size = (int) apply_filters( 'safe_svg_max_inflated_size', $max_size );

			// gzdecode() treats 0 as unlimited so we enforce 1 as our minimum.
			$max_size = max( 1, $max_size );

			$dirty = @gzdecode( $dirty, $max_size ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

			if ( false === $dirty ) {
				return false;
			}
		}

		$clean = self::configure()->sanitize( $dirty );

		if ( false === $clean || '' === $clean ) {
			return false;
		}

		return $clean;
	}

	/**
	 * Fingerprint of everything that changes what sanitization produces.
	 *
	 * @since 2.5.1
	 *
	 * @return string
	 */
	public static function cache_signature() {
		$parts = array(
			SAFE_SVG_VERSION,
			get_option( 'safe_svg_large_svg' ) ? '1' : '0',
			apply_filters( 'safe_svg_remove_remote_references', false ) ? '1' : '0',
			SafeSvgTags\safe_svg_tags::getTags(),
			SafeSvgAttr\safe_svg_attributes::getAttributes(),
		);

		$encoded = wp_json_encode( $parts );

		// A filter returned something unencodable; never let every signature hash alike.
		if ( ! is_string( $encoded ) ) {
			return '';
		}

		return md5( $encoded );
	}

	/**
	 * Whether the current user may see the SVG at an attachment ID.
	 *
	 * @since 2.5.1
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return bool
	 */
	public static function current_user_can_read( $attachment_id ) {
		$attachment_id = (int) $attachment_id;

		if ( $attachment_id < 1 ) {
			return false;
		}

		// Always require the user to be able to edit posts.
		if ( ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		/**
		 * Whether to also require that the user can read this specific attachment.
		 *
		 * True by default. `read_post` resolves an attachment's `inherit` status
		 * through its parent post, so this denies, say, an author asking for an SVG
		 * attached to someone else's private post.
		 *
		 * To opt out:
		 *
		 *     add_filter( 'safe_svg_require_read_post', '__return_false' );
		 *
		 * @since 2.5.1
		 *
		 * @param bool $require       Whether to require the `read_post` capability.
		 * @param int  $attachment_id Attachment ID being requested.
		 */
		if ( ! apply_filters( 'safe_svg_require_read_post', true, $attachment_id ) ) {
			return true;
		}

		return current_user_can( 'read_post', $attachment_id );
	}

	/**
	 * Whether the current user may upload, or otherwise write, SVG files.
	 *
	 * @since 2.5.2
	 *
	 * @return bool
	 */
	public static function current_user_can_upload() {
		$upload_roles = get_option( 'safe_svg_upload_roles', [] );
		$can_upload   = false;

		if ( empty( $upload_roles ) ) {
			// Fallback to upload_files check for backwards compatibility.
			$can_upload = current_user_can( 'upload_files' );
		} else {
			// Use our custom capability if some upload roles are set.
			$can_upload = current_user_can( 'safe_svg_upload_svg' );
		}

		/**
		 * Determine if the current user can upload an svg.
		 *
		 * @since 2.2.5
		 *
		 * @param bool $can_upload Can the current user upload an svg?
		 * @return bool
		 */
		return (bool) apply_filters( 'safe_svg_current_user_can_upload', $can_upload );
	}

	/**
	 * Read an attachment from disk and return sanitized SVG markup.
	 *
	 * @since 2.5.1
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return string|\WP_Error Sanitized SVG markup, or an error on failure.
	 */
	public static function from_attachment( $attachment_id ) {
		$attachment_id = (int) $attachment_id;
		$post          = $attachment_id > 0 ? get_post( $attachment_id ) : null;

		if ( ! $post || 'attachment' !== $post->post_type ) {
			return new \WP_Error(
				'safe_svg_invalid_attachment',
				__( 'Invalid SVG attachment.', 'safe-svg' ),
				array( 'status' => 404 )
			);
		}

		if ( 'image/svg+xml' !== get_post_mime_type( $attachment_id ) ) {
			return new \WP_Error(
				'safe_svg_not_svg',
				__( 'The requested attachment is not an SVG.', 'safe-svg' ),
				array( 'status' => 404 )
			);
		}

		$path = get_attached_file( $attachment_id );

		if ( empty( $path ) || ! is_readable( $path ) ) {
			return new \WP_Error(
				'safe_svg_unreadable',
				__( 'The SVG file could not be read.', 'safe-svg' ),
				array( 'status' => 404 )
			);
		}

		// Ensure the stored mime type is the same as the file extension.
		if ( ! self::is_svg_filename( $path ) ) {
			return new \WP_Error(
				'safe_svg_not_svg',
				__( 'The requested attachment is not an SVG.', 'safe-svg' ),
				array( 'status' => 404 )
			);
		}

		$size      = (int) filesize( $path );
		$mtime     = (int) filemtime( $path );
		$signature = '';

		// Both are needed to tell a changed file from an unchanged one.
		$use_cache = $mtime > 0 && $size >= self::CACHE_MIN_FILESIZE;

		if ( $use_cache ) {
			$signature = self::cache_signature();

			// An unhashable signature must never match a stored one.
			$use_cache = '' !== $signature;
		}

		if ( $use_cache ) {
			$cached = get_post_meta( $attachment_id, self::CACHE_META_KEY, true );

			if ( is_array( $cached )
				&& isset( $cached['markup'], $cached['mtime'], $cached['size'], $cached['signature'] )
				&& is_string( $cached['markup'] )
				&& (int) $cached['mtime'] === $mtime
				&& (int) $cached['size'] === $size
				&& $cached['signature'] === $signature
			) {
				return $cached['markup'];
			}
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$dirty = file_get_contents( $path );

		if ( false === $dirty || '' === $dirty ) {
			return new \WP_Error(
				'safe_svg_unreadable',
				__( 'The SVG file could not be read.', 'safe-svg' ),
				array( 'status' => 404 )
			);
		}

		$clean = self::sanitize_markup( $dirty );

		if ( false === $clean ) {
			return new \WP_Error(
				'safe_svg_sanitize_failed',
				__( 'The SVG could not be sanitized.', 'safe-svg' ),
				array( 'status' => 400 )
			);
		}

		if ( $use_cache ) {
			update_post_meta(
				$attachment_id,
				self::CACHE_META_KEY,
				wp_slash(
					array(
						'markup'    => $clean,
						'mtime'     => $mtime,
						'size'      => $size,
						'signature' => $signature,
					)
				)
			);
		}

		return $clean;
	}
}
