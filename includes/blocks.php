<?php
/**
 * SafeSvg Blocks setup
 *
 * @package SafeSvg
 */

namespace SafeSvg\Blocks;

/**
 * Set up blocks
 *
 * @return void
 */
function setup() {
	$n = function ( $function ) {
		return __NAMESPACE__ . "\\$function";
	};

	add_action( 'enqueue_block_assets', $n( 'blocks_styles' ) );
	add_filter( 'block_categories_all', $n( 'blocks_categories' ), 10, 2 );

	register_blocks();
}

/**
 * Registers blocks that are located within the includes/blocks directory.
 *
 * @return void
 */
function register_blocks() {
	// Require custom blocks.
	require_once SAFE_SVG_PLUGIN_DIR . '/includes/blocks/safe-svg/register.php';

	// Call register function for each block.
	SafeSvgBlock\register();
}

/**
 * Enqueue JavaScript/CSS for blocks.
 *
 * @return void
 */
function blocks_styles() {
	wp_enqueue_style(
		'safe-svg-block-frontend',
		SAFE_SVG_PLUGIN_URL . '/dist/safe-svg-block-frontend.css',
		[],
		SAFE_SVG_VERSION
	);

	$fe_file_name          = 'safe-svg-block-frontend';
	$frontend_dependencies = ( include SAFE_SVG_PLUGIN_DIR . "/dist/$fe_file_name.asset.php" );
	wp_enqueue_script(
		'safe-svg-block-script',
		SAFE_SVG_PLUGIN_URL . 'dist/safe-svg-block-frontend.js',
		$frontend_dependencies['dependencies'],
		$frontend_dependencies['version'],
		true
	);

	wp_localize_script(
		'safe-svg-block-script',
		'safe_svg_personalizer_params',
		array(
			'ajax_url'   => esc_url( admin_url( 'admin-ajax.php' ) ),
			'ajax_nonce' => wp_create_nonce( 'safe-svg-block' ),
		)
	);
}

/**
 * Filters the registered block categories.
 *
 * @param array $categories Registered categories.
 *
 * @return array Filtered categories.
 */
function blocks_categories( $categories ) {
	return array_merge(
		$categories,
		array(
			array(
				'slug'  => 'safe-svg-blocks',
				'title' => __( 'SafeSvg', 'safe-svg' ),
			),
		)
	);
}
