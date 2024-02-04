<?php
/**
 * Safe SVG plugin settings.
 *
 * @package safe-svg
 */

namespace SafeSvg;

/**
 * SVG settings class.
 */
class safe_svg_settings {

	/**
	 * Set up the class
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'settings_init' ] );
		add_filter( 'pre_update_option_safe_svg_upload_roles', [ $this, 'update_capability' ], 10, 2 );
	}

	/**
	 * Custom option and settings
	 */
	public function settings_init() {
		register_setting( 'media', 'safe_svg_upload_roles', [ $this, 'sanitize_safe_svg_roles' ] );

		add_settings_section(
			'safe_svg_settings',
			__( 'Safe SVG Settings', 'safe-svg' ),
			[ $this, 'safe_svg_settings_callback' ],
			'media'
		);

		add_settings_field(
			'safe_svg_roles',
			__( 'User Roles', 'safe-svg' ),
			[ $this, 'safe_svg_roles_cb' ],
			'media',
			'safe_svg_settings'
		);
	}

	/**
	 * Sanitizes roles before saving.
	 *
	 * @param array $roles The roles that we are attempting to save
	 *
	 * @return array The sanitized roles array.
	 */
	public function sanitize_safe_svg_roles( $roles ) {
		if ( ! is_array( $roles ) ) {
			$roles = [];
		}

		$valid_roles = $this->get_upload_capable_roles();
		$valid_slugs = array_keys( $valid_roles );
		$roles       = array_intersect( $valid_slugs, $roles );

		// Store a non empty/falsy value for easier handling.
		if ( empty( $roles ) ) {
			$roles = 'none';
		}

		return $roles;
	}

	/**
	 * Get roles with upload capabilities.
	 *
	 * @return array An array of roles with the upload_files capability.
	 */
	public function get_upload_capable_roles() {
		$all_roles    = get_editable_roles();
		$upload_roles = array_filter(
			$all_roles,
			function( $_role ) {
				return $_role['capabilities']['upload_files'] ?? false;
			}
		);

		/**
		 * Filter the roles that can upload SVG files.
		 *
		 * @since 2.2.0
		 *
		 * @param array             $upload_roles The roles that can upload SVG files.
		 * @param array             $all_roles All editable roles on the site.
		 * @param safe_svg_settings $this The safe_svg_settings instance.
		 */
		return apply_filters( 'safe_svg_upload_roles', $upload_roles, $all_roles, $this );
	}

	/**
	 * Settings section callback function.
	 *
	 * @param array $args The settings array, defining title, id, callback.
	 */
	public function safe_svg_settings_callback( $args ) {
		?>
		<p id="<?php echo esc_attr( $args['id'] ); ?>">
			<?php esc_html_e( 'Select which user roles can upload SVG files.', 'safe-svg' ); ?>
		</p>
		<?php
	}

	/**
	 * User role field callback function.
	 */
	public function safe_svg_roles_cb() {
		$upload_roles = (array) get_option( 'safe_svg_upload_roles', [] );
		$role_options = $this->get_upload_capable_roles();

		if ( empty( $upload_roles ) ) {
			$upload_roles = array_keys( $role_options );
		}

		foreach ( $role_options as $role => $info ) :
			?>
			<div>
				<label>
					<input type="checkbox" name="safe_svg_upload_roles[]" value="<?php echo esc_attr( $role ); ?>" <?php checked( in_array( $role, $upload_roles, true ), true ); ?> /> <?php echo esc_html( $info['name'] ); ?>
				</label>
			</div>
			<?php
		endforeach;
	}

	/**
	 * Update user role capability based on the settings.
	 *
	 * @param array $new_roles New user roles.
	 * @param array $old_roles Old user roles.
	 *
	 * @return array
	 */
	public function update_capability( $new_roles, $old_roles ) {
		$add_roles    = array_filter( array_diff( (array) $new_roles, (array) $old_roles ) );
		$remove_roles = array_filter( array_diff( (array) $old_roles, (array) $new_roles ) );

		if ( ! empty( $add_roles ) ) {
			foreach ( $add_roles as $role ) {
				$role = get_role( $role );

				if ( $role instanceof \WP_Role ) {
					$role->add_cap( 'safe_svg_upload_svg' );
				}
			}
		}

		if ( ! empty( $remove_roles ) ) {
			foreach ( $remove_roles as $role ) {
				$role = get_role( $role );

				if ( $role instanceof \WP_Role ) {
					$role->remove_cap( 'safe_svg_upload_svg' );
				}
			}
		}

		return $new_roles;
	}

}
