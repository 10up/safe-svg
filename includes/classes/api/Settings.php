<?php
/**
 * API settings.
 *
 * @package SafeSvg
 */

namespace SafeSvg\API;

use SafeSvg\Module;

/**
 * Class for handling API settings.
 */
class Settings extends Module {

	/**
	 * Checks whether the Module should run within the current context.
	 *
	 * @return bool
	 */
	public function can_register() {
        return is_admin() && current_user_can( 'manage_options' );
    }

	/**
	 * Connects the Module with WordPress using Hooks and/or Filters.
	 *
	 * @return void
	 */
	public function register() {
        add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
    }

    /**
     * Adds the API settings menu page.
     *
     * @return void
     */
    public function add_menu_page() {
        add_submenu_page(
            'options-general.php',
            'Safe SVG API Settings',
            'Safe SVG',
            'manage_options',
            'safe-svg-api',
            [ $this, 'print_settings_page' ]
        );
    }

    /**
     *
     */
    public function print_settings_page() {

    }

}