<?php
/**
 * SafeSvg Block setup
 *
 * @package SafeSvg\Blocks\RecommendedContentBlock
 */

namespace SafeSvg\Blocks\SafeSvgBlock;

/**
 * Register the block
 */
function register() {
	// Register the block.
	register_block_type_from_metadata(
		SAFE_SVG_PLUGIN_PATH . 'includes/blocks/safe-svg', // this is the directory where the block.json is found.
	);
}
