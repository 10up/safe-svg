<?php
/**
 * Recommended Content block markup
 *
 * @package SafeSvg\Blocks
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 * @var array    $context    Block context.
 */

// Excluded current page/post from the recommended content.
if ( is_singular() ) {
	$attributes['excludeId'] = get_the_ID();
}

$attr_key = md5( maybe_serialize( $attributes ) );
$block_id = 'safe-svg-recommended-block-' . $attr_key;
?>
<div class="safe-svg-recommended-block-wrap" id="<?php echo esc_attr( $block_id ); ?>" data-attr_key="<?php echo esc_attr( $attr_key ); ?>">
	<?php esc_html_e( 'Loading...', 'safe-svg' ); ?>
</div>
<script id="attributes-<?php echo esc_attr( $attr_key ); ?>" type="application/json"><?php echo wp_json_encode( $attributes ); ?></script>
<?php
