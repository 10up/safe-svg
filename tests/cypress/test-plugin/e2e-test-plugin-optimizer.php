<?php
/**
 * Plugin name: Safe SVG Cypress Optimizer Test plugin
 * Description: Test plugin for Safe SVG to test the optimizer.
 *
 * @package safe-svg
 */

/**
 * Optimizer and allowed attributes can't be mutually enabled, hence the secondary testing plugin.
 */
add_filter('safe_svg_optimizer_enabled', '__return_true');
