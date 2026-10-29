<?php
/**
 * Abilities API integration.
 *
 * @package safe-svg
 */

namespace SafeSvg;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers abilities that upload and read SVGs.
 *
 * @since x.x.x
 */
class Abilities {

	/**
	 * Ability category slug.
	 *
	 * @since x.x.x
	 * @var string
	 */
	const CATEGORY = 'safe-svg';

	/**
	 * Setup needed hooks.
	 */
	public function __construct() {
		add_action( 'wp_abilities_api_categories_init', array( $this, 'register_category' ) );
		add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ) );
	}

	/**
	 * Register the ability category.
	 *
	 * @since x.x.x
	 */
	public function register_category() {
		wp_register_ability_category(
			self::CATEGORY,
			array(
				'label'       => __( 'SVG', 'safe-svg' ),
				'description' => __( 'Abilities that upload sanitized SVGs to the media library or read their markup.', 'safe-svg' ),
			)
		);
	}

	/**
	 * Register the abilities.
	 *
	 * @since x.x.x
	 */
	public function register_abilities() {
		$attachment_schema = array(
			'type'       => 'object',
			'properties' => array(
				'id'  => array( 'type' => 'integer' ),
				'url' => array( 'type' => 'string' ),
			),
		);

		wp_register_ability(
			'safe-svg/upload-svg',
			array(
				'label'               => __( 'Upload SVG', 'safe-svg' ),
				'description'         => __( 'Adds an SVG to the media library from its markup. The markup is sanitized the same way as an SVG uploaded in wp-admin, and is refused if it cannot be sanitized or is larger than the site\'s maximum upload size. Returns the attachment ID and URL, which can be used in an image or Safe SVG block.', 'safe-svg' ),
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(
						'svg'      => array(
							'type'        => 'string',
							'minLength'   => 1,
							'description' => __( 'SVG markup, starting with <svg or an XML declaration.', 'safe-svg' ),
						),
						'filename' => array(
							'type'        => 'string',
							'default'     => 'image.svg',
							'description' => __( 'File name. Its extension, if any, is replaced with .svg.', 'safe-svg' ),
						),
						'title'    => array(
							'type'        => 'string',
							'description' => __( 'Attachment title. Defaults to the file name.', 'safe-svg' ),
						),
						'post_id'  => array(
							'type'        => 'integer',
							'minimum'     => 0,
							'default'     => 0,
							'description' => __( 'ID of the post to attach the SVG to (not a revision or attachment). 0 leaves it unattached.', 'safe-svg' ),
						),
					),
					'required'             => array( 'svg' ),
					'additionalProperties' => false,
				),
				'output_schema'       => $attachment_schema,
				'execute_callback'    => array( $this, 'upload_svg' ),
				'permission_callback' => array( $this, 'can_upload_svg' ),
				'meta'                => array(
					'annotations'  => array(
						'readonly'    => false,
						'destructive' => false,
						'idempotent'  => false,
					),
					'show_in_rest' => true,
				),
			)
		);

		wp_register_ability(
			'safe-svg/get-svg',
			array(
				'label'               => __( 'Get SVG', 'safe-svg' ),
				'description'         => __( 'Returns the sanitized markup and URL of an SVG in the media library.', 'safe-svg' ),
				'category'            => self::CATEGORY,
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(
						'id' => array(
							'type'        => 'integer',
							'minimum'     => 1,
							'description' => __( 'Attachment ID of the SVG.', 'safe-svg' ),
						),
					),
					'required'             => array( 'id' ),
					'additionalProperties' => false,
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'markup' => array( 'type' => 'string' ),
						'url'    => array( 'type' => 'string' ),
					),
				),
				'execute_callback'    => array( $this, 'get_svg' ),
				'permission_callback' => array( $this, 'can_read_svg' ),
				'meta'                => array(
					'annotations'  => array(
						'readonly'    => true,
						'destructive' => false,
						'idempotent'  => true,
					),
					'show_in_rest' => true,
				),
			)
		);
	}

	/**
	 * Whether the current user can upload an SVG, and attach it to the given post.
	 *
	 * @since x.x.x
	 *
	 * @param array $input Ability input.
	 * @return bool
	 */
	public function can_upload_svg( $input = array() ) {
		// Same base capability as the media REST endpoint, plus the plugin's SVG check.
		if ( ! current_user_can( 'upload_files' ) || ! Svg_Sanitizer::current_user_can_upload() ) {
			return false;
		}

		$post_id = isset( $input['post_id'] ) ? absint( $input['post_id'] ) : 0;

		return ! $post_id || current_user_can( 'edit_post', $post_id );
	}

	/**
	 * Whether the current user can read the requested SVG.
	 *
	 * @since x.x.x
	 *
	 * @param array $input Ability input.
	 * @return bool
	 */
	public function can_read_svg( $input = array() ) {
		return Svg_Sanitizer::current_user_can_read( isset( $input['id'] ) ? absint( $input['id'] ) : 0 );
	}

	/**
	 * Upload SVG markup to the media library.
	 *
	 * The file goes through media_handle_sideload(), so it is checked and sanitized
	 * by the same upload filters as any other SVG.
	 *
	 * @since x.x.x
	 *
	 * @param array $input Ability input.
	 * @return array|\WP_Error
	 */
	public function upload_svg( $input ) {
		$size     = strlen( $input['svg'] );
		$max_size = wp_max_upload_size();

		// Sideloading skips check_upload_size(), so apply the same limits as a direct upload.
		if ( $max_size > 0 && $size > $max_size ) {
			return new \WP_Error( 'safe_svg_file_too_big', __( 'The SVG is larger than the maximum upload size.', 'safe-svg' ), array( 'status' => 400 ) );
		}

		if ( is_multisite() && ! get_site_option( 'upload_space_check_disabled' ) && $size > get_upload_space_available() ) {
			return new \WP_Error( 'safe_svg_upload_limited_space', __( 'Not enough space to upload the SVG.', 'safe-svg' ), array( 'status' => 400 ) );
		}

		$post_id = absint( $input['post_id'] ?? 0 );

		// Same restriction as the media REST endpoint.
		if ( $post_id && in_array( get_post_type( $post_id ), array( 'revision', 'attachment' ), true ) ) {
			return new \WP_Error( 'safe_svg_invalid_post', __( 'An SVG cannot be attached to a revision or another attachment.', 'safe-svg' ), array( 'status' => 400 ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$name = pathinfo( (string) ( $input['filename'] ?? '' ), PATHINFO_FILENAME );
		$name = ( '' === $name ? 'image' : $name ) . '.svg';
		$tmp  = wp_tempnam( $name );

		if ( ! $tmp || false === file_put_contents( $tmp, $input['svg'] ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			return new \WP_Error( 'safe_svg_upload_failed', __( 'The SVG could not be written to a temporary file.', 'safe-svg' ) );
		}

		$post_data = isset( $input['title'] ) && '' !== trim( $input['title'] ) ? array( 'post_title' => $input['title'] ) : array();
		$id        = media_handle_sideload(
			array(
				'name'     => $name,
				'tmp_name' => $tmp,
			),
			$post_id,
			null,
			$post_data
		);

		// The temporary file is only moved on success.
		if ( file_exists( $tmp ) ) {
			wp_delete_file( $tmp );
		}

		if ( is_wp_error( $id ) ) {
			return $id;
		}

		return array(
			'id'  => (int) $id,
			'url' => (string) wp_get_attachment_url( $id ),
		);
	}

	/**
	 * Return sanitized markup for an SVG attachment.
	 *
	 * @since x.x.x
	 *
	 * @param array $input Ability input.
	 * @return array|\WP_Error
	 */
	public function get_svg( $input ) {
		$id     = absint( $input['id'] );
		$markup = Svg_Sanitizer::from_attachment( $id );

		if ( is_wp_error( $markup ) ) {
			return $markup;
		}

		return array(
			'markup' => $markup,
			'url'    => (string) wp_get_attachment_url( $id ),
		);
	}
}
