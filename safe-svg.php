<?php
/**
 * Plugin Name:       Safe SVG
 * Plugin URI:        https://wordpress.org/plugins/safe-svg/
 * Description:       Enable SVG uploads and sanitize them to stop XML/SVG vulnerabilities in your WordPress website
 * Version:           2.2.4
 * Requires at least: 5.7
 * Requires PHP:      7.4
 * Author:            10up
 * Author URI:        https://10up.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       safe-svg
 * Domain Path:       /languages
 *
 * @package safe-svg
 */

namespace SafeSvg;

use enshrined\svgSanitize\Sanitizer;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'SAFE_SVG_VERSION', '2.2.4' );
define( 'SAFE_SVG_PLUGIN_DIR', __DIR__ );
define( 'SAFE_SVG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Get the minimum version of PHP required by this plugin.
 *
 * @since 2.1.1
 *
 * @return string Minimum version required.
 */
function minimum_php_requirement() {
	return '7.4';
}

/**
 * Whether PHP installation meets the minimum requirements
 *
 * @since 2.1.1
 *
 * @return bool True if meets minimum requirements, false otherwise.
 */
function site_meets_php_requirements() {
	return version_compare( phpversion(), minimum_php_requirement(), '>=' );
}

// Try and include our autoloader, ensuring our PHP version is met first.
if ( ! site_meets_php_requirements() ) {
	add_action(
		'admin_notices',
		function() {
			?>
			<div class="notice notice-error">
				<p>
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: %s: Minimum required PHP version */
							__( 'Safe SVG requires PHP version %s or later. Please upgrade PHP or disable the plugin.', 'safe-svg' ),
							esc_html( minimum_php_requirement() )
						)
					);
					?>
				</p>
			</div>
			<?php
		}
	);
	return;
} elseif ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
} elseif ( ! class_exists( Sanitizer::class ) ) {
	add_action(
		'admin_notices',
		function() {
			?>
			<div class="notice notice-error">
				<p>
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: %1$s is the command that needs to be run. */
							__( 'You appear to be running a development version of Safe SVG. Please run %1$s in order for things to work properly.', 'safe-svg' ),
							'<code>composer install</code>'
						)
					);
					?>
				</p>
			</div>
			<?php
		}
	);
	return;
}

require __DIR__ . '/includes/safe-svg-tags.php';
require __DIR__ . '/includes/safe-svg-attributes.php';
require __DIR__ . '/includes/safe-svg-settings.php';
require __DIR__ . '/includes/blocks.php';
require __DIR__ . '/includes/optimizer.php';

new \SafeSVG\Optimizer();

if ( ! class_exists( 'SafeSvg\\safe_svg' ) ) {

	/**
	 * Class safe_svg
	 */
	class safe_svg {

		/**
		 * The sanitizer
		 *
		 * @var \enshrined\svgSanitize\Sanitizer
		 */
		protected $sanitizer;

		/**
		 * Set up the class
		 */
		public function __construct() {
			$this->sanitizer = new Sanitizer();
			$this->sanitizer->minify( true );

			add_action( 'init', array( $this, 'setup_blocks' ) );
			add_filter( 'upload_mimes', array( $this, 'allow_svg' ) );
			add_filter( 'wp_handle_upload_prefilter', array( $this, 'check_for_svg' ) );
			add_filter( 'wp_check_filetype_and_ext', array( $this, 'fix_mime_type_svg' ), 75, 4 );
			add_filter( 'wp_prepare_attachment_for_js', array( $this, 'fix_admin_preview' ), 10, 3 );
			add_filter( 'wp_get_attachment_image_src', array( $this, 'one_pixel_fix' ), 10, 4 );
			add_filter( 'admin_post_thumbnail_html', array( $this, 'featured_image_fix' ), 10, 3 );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_custom_admin_style' ) );
			add_action( 'get_image_tag', array( $this, 'get_image_tag_override' ), 10, 6 );
			add_filter( 'wp_generate_attachment_metadata', array( $this, 'skip_svg_regeneration' ), 10, 2 );
			add_filter( 'wp_get_attachment_metadata', array( $this, 'metadata_error_fix' ), 10, 2 );
			add_filter( 'wp_calculate_image_srcset_meta', array( $this, 'disable_srcset' ), 10, 4 );

			new safe_svg_settings();
		}

		/**
		 * Custom function to check if user can upload svg.
		 *
		 * Use core caps if setting hasn't every been updated.
		 *
		 * @return bool
		 */
		public function current_user_can_upload_svg() {
			$upload_roles = get_option( 'safe_svg_upload_roles', [] );

			// Fallback to upload_files check for backwards compatibility.
			if ( empty( $upload_roles ) ) {
				return current_user_can( 'upload_files' );
			}

			return current_user_can( 'safe_svg_upload_svg' );
		}

		/**
		 * Setup the blocks.
		 */
		public function setup_blocks() {
			// Setup blocks.
			Blocks\setup();
		}

		/**
		 * Allow SVG Uploads
		 *
		 * @param array $mimes Mime types keyed by the file extension regex corresponding to those types.
		 *
		 * @return mixed
		 */
		public function allow_svg( $mimes ) {
			if ( $this->current_user_can_upload_svg() ) {
				$mimes['svg']  = 'image/svg+xml';
				$mimes['svgz'] = 'image/svg+xml';
			}

			return $mimes;
		}

		/**
		 * Fixes the issue in WordPress 4.7.1 being unable to correctly identify SVGs
		 *
		 * @thanks @lewiscowles
		 *
		 * @param array    $data     Values for the extension, mime type, and corrected filename.
		 * @param string   $file     Full path to the file.
		 * @param string   $filename The name of the file.
		 * @param string[] $mimes    Array of mime types keyed by their file extension regex.
		 *
		 * @return null
		 */
		public function fix_mime_type_svg( $data = null, $file = null, $filename = null, $mimes = null ) {
			$ext = isset( $data['ext'] ) ? $data['ext'] : '';
			if ( strlen( $ext ) < 1 ) {
				$exploded = explode( '.', $filename );
				$ext      = strtolower( end( $exploded ) );
			}
			if ( 'svg' === $ext ) {
				$data['type'] = 'image/svg+xml';
				$data['ext']  = 'svg';
			} elseif ( 'svgz' === $ext ) {
				$data['type'] = 'image/svg+xml';
				$data['ext']  = 'svgz';
			}

			return $data;
		}

		/**
		 * Check if the file is an SVG, if so handle appropriately
		 *
		 * @param array $file An array of data for a single file.
		 *
		 * @return mixed
		 */
		public function check_for_svg( $file ) {

			// Ensure we have a proper file path before processing
			if ( ! isset( $file['tmp_name'] ) ) {
				return $file;
			}

			$file_name   = isset( $file['name'] ) ? $file['name'] : '';
			$wp_filetype = wp_check_filetype_and_ext( $file['tmp_name'], $file_name );
			$type        = ! empty( $wp_filetype['type'] ) ? $wp_filetype['type'] : '';

			if ( 'image/svg+xml' === $type ) {
				if ( ! $this->current_user_can_upload_svg() ) {
					$file['error'] = __(
						'Sorry, you are not allowed to upload SVG files.',
						'safe-svg'
					);

					return $file;
				}

				if ( ! $this->sanitize( $file['tmp_name'] ) ) {
					$file['error'] = __(
						"Sorry, this file couldn't be sanitized so for security reasons wasn't uploaded",
						'safe-svg'
					);
				}
			}

			return $file;
		}

		/**
		 * Sanitize the SVG
		 *
		 * @param string $file Temp file path.
		 *
		 * @return bool|int
		 */
		protected function sanitize( $file ) {
			$dirty = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			// Is the SVG gzipped? If so we try and decode the string
			$is_zipped = $this->is_gzipped( $dirty );
			if ( $is_zipped ) {
				$dirty = gzdecode( $dirty );

				// If decoding fails, bail as we're not secure
				if ( false === $dirty ) {
					return false;
				}
			}

			/**
			 * Load extra filters to allow devs to access the safe tags and attrs by themselves.
			 */
			$this->sanitizer->setAllowedTags( new SafeSvgTags\safe_svg_tags() );
			$this->sanitizer->setAllowedAttrs( new SafeSvgAttr\safe_svg_attributes() );

			$clean = $this->sanitizer->sanitize( $dirty );

			if ( false === $clean ) {
				return false;
			}

			// If we were gzipped, we need to re-zip
			if ( $is_zipped ) {
				$clean = gzencode( $clean );
			}

			file_put_contents( $file, $clean ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents

			return true;
		}

		/**
		 * Check if the contents are gzipped
		 *
		 * @see http://www.gzip.org/zlib/rfc-gzip.html#member-format
		 *
		 * @param string $contents Content to check.
		 *
		 * @return bool
		 */
		protected function is_gzipped( $contents ) {
			// phpcs:disable Generic.Strings.UnnecessaryStringConcat.Found
			if ( function_exists( 'mb_strpos' ) ) {
				return 0 === mb_strpos( $contents, "\x1f" . "\x8b" . "\x08" );
			} else {
				return 0 === strpos( $contents, "\x1f" . "\x8b" . "\x08" );
			}
			// phpcs:enable
		}

		/**
		 * Filters the attachment data prepared for JavaScript to add the sizes array to the response
		 *
		 * @param array      $response Array of prepared attachment data.
		 * @param int|object $attachment Attachment ID or object.
		 * @param array      $meta Array of attachment meta data.
		 *
		 * @return array
		 */
		public function fix_admin_preview( $response, $attachment, $meta ) {

			if ( 'image/svg+xml' === $response['mime'] ) {
				$dimensions = $this->svg_dimensions( $attachment->ID );

				if ( $dimensions ) {
					$response = array_merge( $response, $dimensions );
				}

				$possible_sizes = apply_filters(
					'image_size_names_choose',
					array(
						'full'      => __( 'Full Size' ),
						'thumbnail' => __( 'Thumbnail' ),
						'medium'    => __( 'Medium' ),
						'large'     => __( 'Large' ),
					)
				);

				$sizes = array();

				foreach ( $possible_sizes as $size => $label ) {
					$default_height = 2000;
					$default_width  = 2000;

					if ( 'full' === $size && $dimensions ) {
						$default_height = $dimensions['height'];
						$default_width  = $dimensions['width'];
					}

					$sizes[ $size ] = array(
						'height'      => get_option( "{$size}_size_w", $default_height ),
						'width'       => get_option( "{$size}_size_h", $default_width ),
						'url'         => $response['url'],
						'orientation' => 'portrait',
					);
				}

				$response['sizes'] = $sizes;
				$response['icon']  = $response['url'];
			}

			return $response;
		}

		/**
		 * Filters the image src result.
		 * If the image size doesn't exist, set a default size of 100 for width and height
		 *
		 * @param array|false  $image Either array with src, width & height, icon src, or false.
		 * @param int          $attachment_id Image attachment ID.
		 * @param string|array $size Size of image. Image size or array of width and height values
		 *                                    (in that order). Default 'thumbnail'.
		 * @param bool         $icon Whether the image should be treated as an icon. Default false.
		 *
		 * @return array
		 */
		public function one_pixel_fix( $image, $attachment_id, $size, $icon ) {
			if ( get_post_mime_type( $attachment_id ) === 'image/svg+xml' ) {
				$dimensions = $this->svg_dimensions( $attachment_id );

				if ( $dimensions ) {
					$image[1] = $dimensions['width'];
					$image[2] = $dimensions['height'];
				} else {
					$image[1] = 100;
					$image[2] = 100;
				}
			}

			return $image;
		}

		/**
		 * If the featured image is an SVG we wrap it in an SVG class so we can apply our CSS fix.
		 *
		 * @param string $content Admin post thumbnail HTML markup.
		 * @param int    $post_id Post ID.
		 * @param int    $thumbnail_id Thumbnail ID.
		 *
		 * @return string
		 */
		public function featured_image_fix( $content, $post_id, $thumbnail_id ) {
			$mime = get_post_mime_type( $thumbnail_id );

			if ( 'image/svg+xml' === $mime ) {
				$content = sprintf( '<span class="svg">%s</span>', $content );
			}

			return $content;
		}

		/**
		 * Load our custom CSS sheet.
		 */
		public function load_custom_admin_style() {
			wp_enqueue_style( 'safe-svg-css', plugins_url( 'assets/safe-svg.css', __FILE__ ), array(), SAFE_SVG_VERSION );
		}

		/**
		 * Override the default height and width string on an SVG
		 *
		 * @param string       $html HTML content for the image.
		 * @param int          $id Attachment ID.
		 * @param string       $alt Alternate text.
		 * @param string       $title Attachment title.
		 * @param string       $align Part of the class name for aligning the image.
		 * @param string|array $size Size of image. Image size or array of width and height values (in that order).
		 *                            Default 'medium'.
		 *
		 * @return mixed
		 */
		public function get_image_tag_override( $html, $id, $alt, $title, $align, $size ) {
			$mime = get_post_mime_type( $id );

			if ( 'image/svg+xml' === $mime ) {
				if ( is_array( $size ) ) {
					$width  = $size[0];
					$height = $size[1];
				// phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.Found, Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
				} elseif ( 'full' === $size && $dimensions = $this->svg_dimensions( $id ) ) {
					$width  = $dimensions['width'];
					$height = $dimensions['height'];
				} else {
					$width  = get_option( "{$size}_size_w", false );
					$height = get_option( "{$size}_size_h", false );
				}

				if ( $height && $width ) {
					$html = str_replace( 'width="1" ', sprintf( 'width="%s" ', $width ), $html );
					$html = str_replace( 'height="1" ', sprintf( 'height="%s" ', $height ), $html );
				} else {
					$html = str_replace( 'width="1" ', '', $html );
					$html = str_replace( 'height="1" ', '', $html );
				}

				$html = str_replace( '/>', ' role="img" />', $html );
			}

			return $html;
		}

		/**
		 * Skip regenerating SVGs
		 *
		 * @param array $metadata      An array of attachment meta data.
		 * @param int   $attachment_id Attachment Id to process.
		 *
		 * @return mixed Metadata for attachment.
		 */
		public function skip_svg_regeneration( $metadata, $attachment_id ) {
			$mime = get_post_mime_type( $attachment_id );
			if ( 'image/svg+xml' === $mime ) {
				$additional_image_sizes = wp_get_additional_image_sizes();
				$svg_path               = get_attached_file( $attachment_id );
				$upload_dir             = wp_upload_dir();
				// get the path relative to /uploads/ - found no better way:
				$relative_path = str_replace( trailingslashit( $upload_dir['basedir'] ), '', $svg_path );
				$filename      = basename( $svg_path );

				$dimensions = $this->svg_dimensions( $attachment_id );

				if ( ! $dimensions ) {
					return $metadata;
				}

				$metadata = array(
					'width'  => intval( $dimensions['width'] ),
					'height' => intval( $dimensions['height'] ),
					'file'   => $relative_path,
				);

				// Might come handy to create the sizes array too - But it's not needed for this workaround! Always links to original svg-file => Hey, it's a vector graphic! ;)
				$sizes = array();
				foreach ( get_intermediate_image_sizes() as $s ) {
					$sizes[ $s ] = array(
						'width'  => '',
						'height' => '',
						'crop'   => false,
					);

					if ( isset( $additional_image_sizes[ $s ]['width'] ) ) {
						// For theme-added sizes
						$sizes[ $s ]['width'] = intval( $additional_image_sizes[ $s ]['width'] );
					} else {
						// For default sizes set in options
						$sizes[ $s ]['width'] = get_option( "{$s}_size_w" );
					}

					if ( isset( $additional_image_sizes[ $s ]['height'] ) ) {
						// For theme-added sizes
						$sizes[ $s ]['height'] = intval( $additional_image_sizes[ $s ]['height'] );
					} else {
						// For default sizes set in options
						$sizes[ $s ]['height'] = get_option( "{$s}_size_h" );
					}

					if ( isset( $additional_image_sizes[ $s ]['crop'] ) ) {
						// For theme-added sizes
						$sizes[ $s ]['crop'] = intval( $additional_image_sizes[ $s ]['crop'] );
					} else {
						// For default sizes set in options
						$sizes[ $s ]['crop'] = get_option( "{$s}_crop" );
					}

					$sizes[ $s ]['file']      = $filename;
					$sizes[ $s ]['mime-type'] = $mime;
				}
				$metadata['sizes'] = $sizes;
			}

			return $metadata;
		}

		/**
		 * Filters the attachment meta data.
		 *
		 * @param array|bool $data Array of meta data for the given attachment, or false
		 *                            if the object does not exist.
		 * @param int        $post_id Attachment ID.
		 */
		public function metadata_error_fix( $data, $post_id ) {

			// If it's a WP_Error regenerate metadata and save it
			if ( is_wp_error( $data ) ) {
				$data = wp_generate_attachment_metadata( $post_id, get_attached_file( $post_id ) );
				wp_update_attachment_metadata( $post_id, $data );
			}

			return $data;
		}

		/**
		 * Get SVG size from the width/height or viewport.
		 *
		 * @param integer $attachment_id The attachment ID of the SVG being processed.
		 *
		 * @return array|bool
		 */
		protected function svg_dimensions( $attachment_id ) {
			/**
			 * Calculate SVG dimensions and orientation.
			 *
			 * This filter allows you to implement your own sizing. By returning a non-false
			 * value, it will short-circuit this function and return your set value.
			 *
			 * @param boolean Default value of the filter.
			 * @param integer $attachment_id The attachment ID of the SVG being processed.
			 *
			 * @return array|false An array of SVG dimensions and orientation or false.
			 */
			$short_circuit = apply_filters( 'safe_svg_pre_dimensions', false, $attachment_id );

			if ( false !== $short_circuit ) {
				return $short_circuit;
			}

			if ( ! function_exists( 'simplexml_load_file' ) ) {
				return false;
			}

			$svg      = get_attached_file( $attachment_id );
			$metadata = wp_get_attachment_metadata( $attachment_id );
			$width    = 0;
			$height   = 0;

			if ( $svg && ! empty( $metadata['width'] ) && ! empty( $metadata['height'] ) ) {
				$width  = floatval( $metadata['width'] );
				$height = floatval( $metadata['height'] );
			} elseif ( $svg ) {
				$svg = @simplexml_load_file( $svg ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

				// Ensure the svg could be loaded.
				if ( ! $svg ) {
					return false;
				}

				$attributes = $svg->attributes();

				if ( isset( $attributes->viewBox ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					$sizes = explode( ' ', $attributes->viewBox ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					if ( isset( $sizes[2], $sizes[3] ) ) {
						$viewbox_width  = floatval( $sizes[2] );
						$viewbox_height = floatval( $sizes[3] );
					}
				}

				if ( isset( $attributes->width, $attributes->height ) && is_numeric( (float) $attributes->width ) && is_numeric( (float) $attributes->height ) && ! $this->str_ends_with( (string) $attributes->width, '%' ) && ! $this->str_ends_with( (string) $attributes->height, '%' ) ) {
					$attr_width  = floatval( $attributes->width );
					$attr_height = floatval( $attributes->height );
				}

				/**
				 * Decide which attributes of the SVG we use first for image tag dimensions.
				 *
				 * We default to using the parameters in the viewbox attribute but
				 * that can be overridden using this filter if you'd prefer to use
				 * the width and height attributes.
				 *
				 * @hook safe_svg_use_width_height_attributes
				 *
				 * @param bool   $use_width_height_attributes If the width & height attributes should be used first. Default false.
				 * @param string $svg                         The file path to the SVG.
				 *
				 * @return bool If we should use the width & height attributes first or not.
				 */
				$use_width_height = (bool) apply_filters( 'safe_svg_use_width_height_attributes', false, $svg );

				if ( $use_width_height ) {
					if ( isset( $attr_width, $attr_height ) ) {
						$width  = $attr_width;
						$height = $attr_height;
					} elseif ( isset( $viewbox_width, $viewbox_height ) ) {
						$width  = $viewbox_width;
						$height = $viewbox_height;
					}
				} else {
					if ( isset( $viewbox_width, $viewbox_height ) ) {
						$width  = $viewbox_width;
						$height = $viewbox_height;
					} elseif ( isset( $attr_width, $attr_height ) ) {
						$width  = $attr_width;
						$height = $attr_height;
					}
				}

				if ( ! $width && ! $height ) {
					return false;
				}
			}

			$dimensions = array(
				'width'       => $width,
				'height'      => $height,
				'orientation' => ( $width > $height ) ? 'landscape' : 'portrait',
			);

			/**
			 * Calculate SVG dimensions and orientation.
			 *
			 * @param array  $dimensions An array containing width, height, and orientation.
			 * @param string $svg        The file path to the SVG.
			 *
			 * @return array An array of SVG dimensions and orientation.
			 */
			return apply_filters( 'safe_svg_dimensions', $dimensions, $svg );
		}

		/**
		 * Disable the creation of srcset on SVG images.
		 *
		 * @param array  $image_meta The image meta data.
		 * @param int[]  $size_array {
		 *     An array of requested width and height values.
		 *
		 *     @type int $0 The width in pixels.
		 *     @type int $1 The height in pixels.
		 * }
		 * @param string $image_src     The 'src' of the image.
		 * @param int    $attachment_id The image attachment ID.
		 */
		public function disable_srcset( $image_meta, $size_array, $image_src, $attachment_id ) {
			if ( $attachment_id && 'image/svg+xml' === get_post_mime_type( $attachment_id ) ) {
				$image_meta['sizes'] = array();
			}

			return $image_meta;
		}

		/**
		 * Polyfill for `str_ends_with()` function added in PHP 8.0.
		 *
		 * Performs a case-sensitive check indicating if
		 * the haystack ends with needle.
		 *
		 * @param string $haystack The string to search in.
		 * @param string $needle   The substring to search for in the `$haystack`.
		 * @return bool True if `$haystack` ends with `$needle`, otherwise false.
		 */
		protected function str_ends_with( $haystack, $needle ) {
			if ( function_exists( 'str_ends_with' ) ) {
				return str_ends_with( $haystack, $needle );
			}

			if ( '' === $haystack && '' !== $needle ) {
				return false;
			}

			$len = strlen( $needle );
			return 0 === substr_compare( $haystack, $needle, -$len, $len );
		}

	}
}

new safe_svg();
