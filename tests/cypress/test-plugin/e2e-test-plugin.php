<?php
/**
 * Plugin name: Safe SVG Cypress Test plugin
 */

add_filter( 'svg_allowed_attributes', function ( $attributes ) {
    $attributes[] = 'customTestAttribute'; // This would allow the customTestAttribute="" attribute.
    return $attributes;
} );


add_filter( 'svg_allowed_tags', function ( $tags ) {
    $tags[] = 'customTestTag'; // This would allow the <customTestTag> element.
    return $tags;
} );