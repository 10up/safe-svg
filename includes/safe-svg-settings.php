<?php
/**
 * Safe SVG plugin settings.
 *
 * @package safe-svg
 */

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
		register_setting( 'media', 'safe_svg_upload_roles' );

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
	 * Settings section callback function.
	 *
	 * @param array $args The settings array, defining title, id, callback.
	 */
	public function safe_svg_settings_callback( $args ) {
		?>
		<p id="<?php echo esc_attr( $args['id'] ); ?>">
			<?php esc_html_e( 'Select user roles who can upload SVG files.', 'safe-svg' ); ?>
		</p>
		<?php
	}

	/**
	 * User role field callback function.
	 */
	public function safe_svg_roles_cb() {
		$user_roles   = get_editable_roles();
		$upload_roles = (array) get_option( 'safe_svg_upload_roles', [] );

		foreach ( $user_roles as $role => $info ) :
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
				$role->add_cap( 'safe_svg_upload_svg' );
			}
		}

		if ( ! empty( $remove_roles ) ) {
			foreach ( $remove_roles as $role ) {
				$role = get_role( $role );
				$role->remove_cap( 'safe_svg_upload_svg' );
			}
		}

		return $new_roles;
	}

}
