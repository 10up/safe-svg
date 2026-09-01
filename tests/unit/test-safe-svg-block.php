<?php
/**
 * Test the Safe SVG block render callback
 *
 * @package safe-svg
 */

use \WP_Mock\Tools\TestCase;
use SafeSvg\Blocks\SafeSvgBlock as Block;

require_once TEST_PLUGIN_DIR . '/includes/blocks/safe-svg/register.php';

/**
 * SafeSvgBlockTest tests the block's server side rendering.
 */
class SafeSvgBlockTest extends TestCase {
	/**
	 * Set up WP Mock.
	 *
	 * @return void
	 */
	public function setUp(): void {
		\WP_Mock::setUp();
	}

	/**
	 * Tear down WP Mock.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	/**
	 * Read one of the SVG fixtures.
	 *
	 * WP_Mock matches filter arguments by exact value, so a mock for a filter that
	 * receives the SVG contents has to be given the same string.
	 *
	 * @param string $fixture File name within tests/unit/files.
	 *
	 * @return string The file contents.
	 */
	protected function fixture( $fixture ) {
		return file_get_contents( TEST_PLUGIN_DIR . '/tests/unit/files/' . $fixture ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}

	/**
	 * Render the block for one of the SVG fixtures.
	 *
	 * @param string $fixture    File name within tests/unit/files.
	 * @param array  $attributes Block attributes to merge in.
	 *
	 * @return string The rendered block markup.
	 */
	protected function render( $fixture, $attributes = array() ) {
		\WP_Mock::userFunction(
			'get_post_mime_type',
			array( 'return' => 'image/svg+xml' )
		);
		\WP_Mock::userFunction(
			'get_attached_file',
			array( 'return' => TEST_PLUGIN_DIR . '/tests/unit/files/' . $fixture )
		);
		\WP_Mock::passthruFunction( 'esc_attr' );
		\WP_Mock::passthruFunction( 'esc_url' );

		return Block\render_block_callback(
			array_merge(
				array(
					'imageID'         => 1,
					'alignment'       => 'left',
					'dimensionWidth'  => 120,
					'dimensionHeight' => 120,
				),
				$attributes
			)
		);
	}

	/**
	 * Test that SVGs carrying a stylesheet are recognised.
	 */
	public function test_svg_has_stylesheet() {
		$this->assertTrue( Block\svg_has_stylesheet( '<svg><style>a{fill:red}</style></svg>' ) );
		$this->assertTrue( Block\svg_has_stylesheet( '<svg><style type="text/css">a{fill:red}</style></svg>' ) );
		$this->assertTrue( Block\svg_has_stylesheet( '<svg><defs><style>a{fill:red}</style></defs></svg>' ) );

		// The HTML parser lower-cases tag names and resolves prefixes, so both of
		// these become a style element once the SVG is inlined into the page.
		$this->assertTrue( Block\svg_has_stylesheet( '<svg><STYLE>a{fill:red}</STYLE></svg>' ) );
		$this->assertTrue( Block\svg_has_stylesheet( '<svg><svg:style>a{fill:red}</svg:style></svg>' ) );

		// A self closing element still declares a stylesheet.
		$this->assertTrue( Block\svg_has_stylesheet( '<svg><style/></svg>' ) );
	}

	/**
	 * Test that SVGs without a stylesheet are left alone.
	 */
	public function test_svg_has_no_stylesheet() {
		$this->assertFalse( Block\svg_has_stylesheet( '<svg><rect fill="red"/></svg>' ) );

		// A style attribute only ever applies to the element it sits on.
		$this->assertFalse( Block\svg_has_stylesheet( '<svg style="fill:red"><rect style="fill:red"/></svg>' ) );

		// Escaped markup in a text node is inert.
		$this->assertFalse( Block\svg_has_stylesheet( '<svg><text>&lt;style&gt;</text></svg>' ) );

		// Elements that merely start with the same letters are not style elements.
		$this->assertFalse( Block\svg_has_stylesheet( '<svg><styles>a{fill:red}</styles></svg>' ) );
	}

	/**
	 * Test that an SVG with a stylesheet is isolated in a shadow root.
	 */
	public function test_svg_with_stylesheet_is_isolated() {
		$markup = $this->render( 'svgWithStyle.svg' );

		$this->assertStringContainsString( '<span class="safe-svg-shadow-host"', $markup );
		$this->assertStringContainsString( '<template shadowrootmode="open">', $markup );
		$this->assertStringContainsString( '</template>', $markup );

		// The SVG has to sit inside the template to be scoped by it.
		$this->assertMatchesRegularExpression( '#<template shadowrootmode="open">.*<svg#s', $markup );

		// Paint containment on a guard wrapping the host stops CSS in the shadow root
		// repositioning its own host over the rest of the page.
		$this->assertMatchesRegularExpression(
			'#<span class="safe-svg-shadow-guard" style="[^"]*contain: paint;[^"]*"><span class="safe-svg-shadow-host"#',
			$markup
		);
	}

	/**
	 * Test the reported case: an SVG whose CSS targets the page around it.
	 *
	 * The CSS is deliberately kept rather than stripped, so this asserts that every
	 * page-targeting rule ends up inside the template, where the browser scopes it,
	 * and that nothing is left in the light DOM to apply to the document.
	 */
	public function test_page_targeting_css_is_confined_to_the_shadow_root() {
		$markup = $this->render( 'svgHostileStyle.svg' );

		preg_match( '#<template shadowrootmode="open">(.*)</template>#s', $markup, $matches );
		$this->assertNotEmpty( $matches, 'the SVG is rendered inside a template' );

		$in_shadow_root = $matches[1];
		$light_dom      = str_replace( $matches[0], '', $markup );

		foreach ( array( 'body {', 'body *', 'body::before', ':host' ) as $rule ) {
			$this->assertStringContainsString( $rule, $in_shadow_root, "$rule is inside the shadow root" );
			$this->assertStringNotContainsString( $rule, $light_dom, "$rule is not in the light DOM" );
		}

		// Paint containment covers the one thing shadow CSS can still reach: its host.
		$this->assertStringContainsString( 'contain: paint;', $light_dom );
	}

	/**
	 * Test that an SVG without a stylesheet is rendered as it always was.
	 */
	public function test_svg_without_stylesheet_is_not_isolated() {
		$markup = $this->render( 'svgCleanOne.svg' );

		$this->assertStringNotContainsString( 'safe-svg-shadow-host', $markup );
		$this->assertStringNotContainsString( 'shadowrootmode', $markup );
		$this->assertStringNotContainsString( 'contain: paint;', $markup );
		$this->assertStringContainsString( '<svg', $markup );
	}

	/**
	 * Test that the shadow root carries the styles the block stylesheet can't reach in with.
	 */
	public function test_shadow_root_includes_block_styles() {
		$markup = $this->render( 'svgWithStyle.svg' );

		$this->assertMatchesRegularExpression(
			'#<template shadowrootmode="open"><style>svg\{[^}]*fill:currentColor[^}]*\}</style>#',
			$markup
		);
	}

	/**
	 * Test that the link stays in the light DOM, outside the shadow root.
	 *
	 * Markup inside a shadow root is not visible to every crawler, and the link is
	 * generated by the block rather than supplied by the SVG, so it gains nothing
	 * from being isolated.
	 */
	public function test_link_is_outside_the_shadow_root() {
		$markup = $this->render(
			'svgWithStyle.svg',
			array(
				'href'      => 'https://example.com',
				'linkLabel' => 'Example',
			)
		);

		$this->assertMatchesRegularExpression(
			'#<a href="https://example.com"[^>]*><span class="safe-svg-shadow-guard"#',
			$markup
		);
		$this->assertStringNotContainsString( '<template shadowrootmode="open"><a', $markup );
	}

	/**
	 * Test that a closing template tag in the SVG can't end the shadow root early.
	 *
	 * The sanitizer escapes text on save so this can't happen to a file it has
	 * processed, but files predating the plugin, or changed on disk since, have not
	 * necessarily been through it.
	 */
	public function test_closing_template_tag_is_neutralised() {
		$svg = '<svg xmlns="http://www.w3.org/2000/svg"><style>/* </template><style>body{display:none}</style> */</style></svg>';

		$markup = Block\wrap_in_shadow_root( $svg, 1 );

		// Exactly one closing tag: the one that ends the shadow root.
		$this->assertSame( 1, substr_count( strtolower( $markup ), '</template>' ) );
		$this->assertStringContainsString( '&lt;/template>', $markup );
	}

	/**
	 * Test that the shadow styles can't break out of their own style element.
	 */
	public function test_shadow_styles_cannot_close_their_element() {
		\WP_Mock::onFilter( 'safe_svg_inline_shadow_styles' )
			->with( 'svg{fill:currentColor;width:100%;height:100%;max-width:100%;max-height:100%}', 1 )
			->reply( 'svg{fill:red}</style><style>body{display:none}' );

		$markup = Block\wrap_in_shadow_root( '<svg xmlns="http://www.w3.org/2000/svg"></svg>', 1 );

		// One style element, the one holding the filtered CSS. What the filter tried
		// to smuggle in survives as inert text rather than markup.
		$this->assertSame( 1, substr_count( $markup, '<style>' ) );
		$this->assertSame( 1, substr_count( $markup, '</style>' ) );

		preg_match( '#<style>(.*?)</style>#s', $markup, $matches );
		$this->assertStringNotContainsString( '<', $matches[1] );
	}

	/**
	 * Test that isolation can be turned off for a site that styles SVGs from its theme.
	 */
	public function test_isolation_can_be_filtered_off() {
		\WP_Mock::onFilter( 'safe_svg_inline_use_shadow_dom' )
			->with( true, $this->fixture( 'svgWithStyle.svg' ), 1 )
			->reply( false );

		$markup = $this->render( 'svgWithStyle.svg' );

		$this->assertStringNotContainsString( 'shadowrootmode', $markup );
		$this->assertStringNotContainsString( 'contain: paint;', $markup );
	}

	/**
	 * Test that isolation can be forced on for every SVG.
	 */
	public function test_isolation_can_be_filtered_on() {
		\WP_Mock::onFilter( 'safe_svg_inline_use_shadow_dom' )
			->with( false, $this->fixture( 'svgCleanOne.svg' ), 1 )
			->reply( true );

		$markup = $this->render( 'svgCleanOne.svg' );

		$this->assertStringContainsString( 'shadowrootmode', $markup );
		$this->assertStringContainsString( 'contain: paint;', $markup );
	}

	/**
	 * Test that a non SVG attachment renders nothing.
	 */
	public function test_non_svg_renders_nothing() {
		\WP_Mock::userFunction(
			'get_post_mime_type',
			array( 'return' => 'image/png' )
		);

		$this->assertSame( '', Block\render_block_callback( array( 'imageID' => 1 ) ) );
	}
}
