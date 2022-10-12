<?php
/**
 * Safe SVG block markup
 *
 * @package SafeSvg\Blocks
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 * @var array    $context    Block context.
 */

$attr_key = md5( maybe_serialize( $attributes ) );
$block_id = 'safe-svg-recommended-block-' . $attr_key;
