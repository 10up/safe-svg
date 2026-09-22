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
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => array( 'safe_svg_large_svg' ),
				'return' => 0,
			)
		);
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
	 * Mock WordPress functions used to load an SVG attachment.
	 *
	 * @param string $fixture File name within tests/unit/files.
	 * @return void
	 */
	protected function mock_svg_attachment( $fixture ) {
		$attachment            = new \stdClass();
		$attachment->post_type = 'attachment';

		\WP_Mock::userFunction(
			'get_post',
			array( 'return' => $attachment )
		);
		\WP_Mock::userFunction(
			'get_post_mime_type',
			array( 'return' => 'image/svg+xml' )
		);
		\WP_Mock::userFunction(
			'get_attached_file',
			array( 'return' => TEST_PLUGIN_DIR . '/tests/unit/files/' . $fixture )
		);
		\WP_Mock::userFunction(
			'is_wp_error',
			array(
				'return' => function ( $thing ) {
					return $thing instanceof \WP_Error;
				},
			)
		);
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
		$this->mock_svg_attachment( $fixture );
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
	 *
	 * @dataProvider data_svg_has_stylesheet
	 *
	 * @param string $svg SVG markup declaring a stylesheet.
	 */
	public function test_svg_has_stylesheet( $svg ) {
		$this->assertTrue( Block\svg_has_stylesheet( $svg ) );
	}

	/**
	 * Data provider for test_svg_has_stylesheet.
	 *
	 * @return array[] Data provider.
	 */
	public static function data_svg_has_stylesheet() {
		return array(
			'style element'              => array( '<svg><style>a{fill:red}</style></svg>' ),
			'style element with a type'  => array( '<svg><style type="text/css">a{fill:red}</style></svg>' ),
			'style element within defs'  => array( '<svg><defs><style>a{fill:red}</style></defs></svg>' ),

			// The HTML parser lower-cases tag names and resolves prefixes, so both of
			// these become a style element once the SVG is inlined into the page.
			'upper case style element'   => array( '<svg><STYLE>a{fill:red}</STYLE></svg>' ),
			'prefixed style element'     => array( '<svg><svg:style>a{fill:red}</svg:style></svg>' ),

			// A self closing element still declares a stylesheet.
			'self closing style element' => array( '<svg><style/></svg>' ),
		);
	}

	/**
	 * Test that SVGs without a stylesheet are left alone.
	 *
	 * @dataProvider data_svg_has_no_stylesheet
	 *
	 * @param string $svg SVG markup declaring no stylesheet.
	 */
	public function test_svg_has_no_stylesheet( $svg ) {
		$this->assertFalse( Block\svg_has_stylesheet( $svg ) );
	}

	/**
	 * Data provider for test_svg_has_no_stylesheet.
	 *
	 * @return array[] Data provider.
	 */
	public static function data_svg_has_no_stylesheet() {
		return array(
			'no stylesheet'          => array( '<svg><rect fill="red"/></svg>' ),

			// A style attribute only ever applies to the element it sits on.
			'style attributes'       => array( '<svg style="fill:red"><rect style="fill:red"/></svg>' ),

			// Escaped markup in a text node is inert.
			'escaped style element'  => array( '<svg><text>&lt;style&gt;</text></svg>' ),

			// Elements that merely start with the same letters are not style elements.
			'element named styles'   => array( '<svg><styles>a{fill:red}</styles></svg>' ),
		);
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
		$contents = \SafeSvg\Svg_Sanitizer::sanitize_markup( $this->fixture( 'svgWithStyle.svg' ) );

		\WP_Mock::onFilter( 'safe_svg_inline_use_shadow_dom' )
			->with( true, $contents, 1, true )
			->reply( false );

		$markup = $this->render( 'svgWithStyle.svg' );

		$this->assertStringNotContainsString( 'shadowrootmode', $markup );
		$this->assertStringNotContainsString( 'contain: paint;', $markup );
	}

	/**
	 * Test that isolation can be forced on for every SVG.
	 */
	public function test_isolation_can_be_filtered_on() {
		$contents = \SafeSvg\Svg_Sanitizer::sanitize_markup( $this->fixture( 'svgCleanOne.svg' ) );

		\WP_Mock::onFilter( 'safe_svg_inline_use_shadow_dom' )
			->with( false, $contents, 1, false )
			->reply( true );

		$markup = $this->render( 'svgCleanOne.svg' );

		$this->assertStringContainsString( 'shadowrootmode', $markup );
		$this->assertStringContainsString( 'contain: paint;', $markup );
	}

	/**
	 * Test that a non SVG attachment renders nothing.
	 */
	public function test_non_svg_renders_nothing() {
		$attachment            = new \stdClass();
		$attachment->post_type = 'attachment';

		\WP_Mock::userFunction(
			'get_post',
			array( 'return' => $attachment )
		);
		\WP_Mock::userFunction(
			'get_post_mime_type',
			array( 'return' => 'image/png' )
		);
		\WP_Mock::userFunction(
			'is_wp_error',
			array(
				'return' => function ( $thing ) {
					return $thing instanceof \WP_Error;
				},
			)
		);

		$this->assertSame( '', Block\render_block_callback( array( 'imageID' => 1 ) ) );
	}

	/**
	 * Test that a missing image ID renders nothing.
	 */
	public function test_missing_image_id_renders_nothing() {
		$this->assertSame( '', Block\render_block_callback( array() ) );
		$this->assertSame( '', Block\render_block_callback( array( 'imageID' => 0 ) ) );
	}

	/**
	 * Test that a script in a file the plugin never sanitized is stripped.
	 */
	public function test_unsanitized_script_is_stripped() {
		$markup = $this->render( 'svgTestOne.svg' );

		$this->assertStringNotContainsString( '<script', strtolower( $markup ) );
		$this->assertStringNotContainsString( 'onload=', strtolower( $markup ) );
		$this->assertStringContainsString( '<svg', $markup );
	}

	/**
	 * Mock what the save-time guard depends on.
	 *
	 * @param int  $user_id  Current user ID; 0 for a save with no user.
	 * @param bool $can_edit Whether the user can edit_posts.
	 * @param bool $can_read Whether the user can read_post for attachment 12.
	 * @return void
	 */
	protected function mock_save_context( $user_id, $can_edit = true, $can_read = false ) {
		\WP_Mock::userFunction( 'get_current_user_id', array( 'return' => $user_id ) );
		\WP_Mock::userFunction(
			'current_user_can',
			array(
				'args'   => array( 'edit_posts' ),
				'return' => $can_edit,
			)
		);
		\WP_Mock::userFunction(
			'current_user_can',
			array(
				'args'   => array( 'read_post', 12 ),
				'return' => $can_read,
			)
		);
		\WP_Mock::userFunction(
			'has_block',
			array(
				'return' => function ( $name, $content ) {
					return false !== strpos( $content, 'wp:' . $name );
				},
			)
		);
		\WP_Mock::passthruFunction( 'wp_slash' );
		\WP_Mock::passthruFunction( 'wp_unslash' );
	}

	/**
	 * Build a parsed SVG block, as parse_blocks() would return it.
	 *
	 * @param int $attachment_id Attachment ID referenced by the block.
	 * @return array A parsed block.
	 */
	protected function parsed_block( $attachment_id ) {
		return array(
			'blockName'    => 'safe-svg/svg-icon',
			'attrs'        => array( 'imageID' => $attachment_id ),
			'innerBlocks'  => array(),
			'innerHTML'    => '',
			'innerContent' => array(),
		);
	}

	/**
	 * Build post data holding one SVG block.
	 *
	 * @param int $attachment_id Attachment ID referenced by the block.
	 * @return array Post data, as wp_insert_post_data receives it.
	 */
	protected function post_data( $attachment_id ) {
		return array(
			'post_content' => '<!-- wp:safe-svg/svg-icon {"imageID":' . $attachment_id . '} /-->',
		);
	}

	/**
	 * Test that an ID the saving user cannot read is dropped.
	 */
	public function test_save_drops_unreadable_attachment_id() {
		$this->mock_save_context( 3, true, false );
		$changed = false;

		$blocks = Block\without_unreadable_attachment_ids( array( $this->parsed_block( 12 ) ), $changed );

		$this->assertTrue( $changed );
		$this->assertSame( 0, $blocks[0]['attrs']['imageID'] );
	}

	/**
	 * Test that an ID the saving user can read is left alone.
	 */
	public function test_save_keeps_readable_attachment_id() {
		$this->mock_save_context( 3, true, true );
		$changed = false;

		$blocks = Block\without_unreadable_attachment_ids( array( $this->parsed_block( 12 ) ), $changed );

		$this->assertFalse( $changed );
		$this->assertSame( 12, $blocks[0]['attrs']['imageID'] );
	}

	/**
	 * Test that an unreadable ID nested inside another block is still dropped.
	 */
	public function test_save_drops_unreadable_id_inside_inner_blocks() {
		$this->mock_save_context( 3, true, false );
		$changed = false;

		$blocks = Block\without_unreadable_attachment_ids(
			array(
				array(
					'blockName'    => 'core/group',
					'attrs'        => array(),
					'innerBlocks'  => array( $this->parsed_block( 12 ) ),
					'innerHTML'    => '',
					'innerContent' => array(),
				),
			),
			$changed
		);

		$this->assertTrue( $changed );
		$this->assertSame( 0, $blocks[0]['innerBlocks'][0]['attrs']['imageID'] );
	}

	/**
	 * Test that other blocks are not touched.
	 */
	public function test_save_leaves_other_blocks_alone() {
		$this->mock_save_context( 3, true, false );
		$changed = false;

		$other  = array(
			'blockName'    => 'core/image',
			'attrs'        => array( 'imageID' => 12 ),
			'innerBlocks'  => array(),
			'innerHTML'    => '',
			'innerContent' => array(),
		);
		$blocks = Block\without_unreadable_attachment_ids( array( $other ), $changed );

		$this->assertFalse( $changed );
		$this->assertSame( 12, $blocks[0]['attrs']['imageID'] );
	}

	/**
	 * Test that a classic-content null blockName is skipped.
	 */
	public function test_save_skips_blocks_without_a_name() {
		$this->mock_save_context( 3, true, false );
		$changed = false;

		$blocks = Block\without_unreadable_attachment_ids(
			array(
				array(
					'blockName'    => null,
					'attrs'        => array(),
					'innerBlocks'  => array(),
					'innerHTML'    => '<p>Classic.</p>',
					'innerContent' => array( '<p>Classic.</p>' ),
				),
			),
			$changed
		);

		$this->assertFalse( $changed );
		$this->assertCount( 1, $blocks );
	}

	/**
	 * Test that a save with no current user is left alone.
	 *
	 * Imports, cron and WP-CLI have no user, and every capability check would
	 * fail for them, stripping references that were perfectly valid.
	 */
	public function test_save_ignores_posts_saved_without_a_user() {
		$this->mock_save_context( 0, false, false );

		$data = Block\drop_unreadable_attachment_ids( $this->post_data( 12 ) );

		$this->assertStringContainsString( '"imageID":12', $data['post_content'] );
	}

	/**
	 * Test that slashed content is unslashed before it is parsed.
	 *
	 * Everything reaching wp_insert_post_data is slashed, and block attributes are
	 * JSON, so parsing it as-is finds no attributes and skips every block.
	 */
	public function test_save_parses_slashed_content() {
		\WP_Mock::userFunction( 'get_current_user_id', array( 'return' => 3 ) );
		\WP_Mock::userFunction(
			'wp_unslash',
			array(
				'return' => function ( $value ) {
					return stripslashes( $value );
				},
			)
		);

		// Returning false stops the run here, so this is the content the parser
		// would have been handed.
		$seen = '';
		\WP_Mock::userFunction(
			'has_block',
			array(
				'return' => function ( $name, $content ) use ( &$seen ) {
					$seen = $content;
					return false;
				},
			)
		);

		Block\drop_unreadable_attachment_ids(
			array( 'post_content' => '<!-- wp:safe-svg/svg-icon {\\"imageID\\":12} /-->' )
		);

		$this->assertStringContainsString( '{"imageID":12}', $seen );
	}

	/**
	 * Test that content without the block is left alone.
	 */
	public function test_save_ignores_content_without_the_block() {
		$this->mock_save_context( 3, true, false );

		$data = Block\drop_unreadable_attachment_ids( array( 'post_content' => '<p>Nothing here.</p>' ) );

		$this->assertSame( '<p>Nothing here.</p>', $data['post_content'] );
	}

	/**
	 * Build a handler array as rest_request_before_callbacks receives it.
	 *
	 * @param bool $is_block_renderer Whether the handler is core's block renderer.
	 * @return array A route handler.
	 */
	protected function handler( $is_block_renderer = true ) {
		if ( ! $is_block_renderer ) {
			return array( 'callback' => '__return_empty_string' );
		}

		return array( 'callback' => array( new \WP_REST_Block_Renderer_Controller(), 'get_item' ) );
	}

	/**
	 * Mock the capabilities the render guard consults.
	 *
	 * @param bool $can_edit Whether the user can edit_posts.
	 * @param bool $can_read Whether the user can read_post on attachment 12.
	 * @return void
	 */
	protected function mock_render_caps( $can_edit, $can_read ) {
		\WP_Mock::userFunction(
			'current_user_can',
			array(
				'args'   => array( 'edit_posts' ),
				'return' => $can_edit,
			)
		);
		\WP_Mock::userFunction(
			'current_user_can',
			array(
				'args'   => array( 'read_post', 12 ),
				'return' => $can_read,
			)
		);
		\WP_Mock::userFunction( 'is_wp_error', array( 'return' => false ) );
	}

	/**
	 * Test that an ID the requesting user cannot read is zeroed before rendering.
	 */
	public function test_render_request_drops_unreadable_attachment_id() {
		$this->mock_render_caps( true, false );
		$request = new SafeSvgFakeRestRequest( 'safe-svg/svg-icon', array( 'imageID' => 12 ) );

		Block\drop_unreadable_rendered_attachment_id( null, $this->handler(), $request );

		$this->assertSame( array( 'imageID' => 0 ), $request['attributes'] );
	}

	/**
	 * Test that a contributor cannot bypass the guard with a string ID.
	 */
	public function test_render_request_drops_unreadable_string_attachment_id() {
		$this->mock_render_caps( true, false );
		$request = new SafeSvgFakeRestRequest( 'safe-svg/svg-icon', array( 'imageID' => '12' ) );

		Block\drop_unreadable_rendered_attachment_id( null, $this->handler(), $request );

		$this->assertSame( array( 'imageID' => 0 ), $request['attributes'] );
	}

	/**
	 * Test that an ID the requesting user can read is left alone.
	 */
	public function test_render_request_keeps_readable_attachment_id() {
		$this->mock_render_caps( true, true );
		$request = new SafeSvgFakeRestRequest( 'safe-svg/svg-icon', array( 'imageID' => 12 ) );

		Block\drop_unreadable_rendered_attachment_id( null, $this->handler(), $request );

		$this->assertSame( array( 'imageID' => 12 ), $request['attributes'] );
		$this->assertFalse( $request->was_set );
	}

	/**
	 * Test that requests for another block are left alone.
	 */
	public function test_render_request_ignores_other_blocks() {
		$this->mock_render_caps( true, false );
		$request = new SafeSvgFakeRestRequest( 'core/archives', array( 'imageID' => 12 ) );

		Block\drop_unreadable_rendered_attachment_id( null, $this->handler(), $request );

		$this->assertSame( array( 'imageID' => 12 ), $request['attributes'] );
		$this->assertFalse( $request->was_set );
	}

	/**
	 * Test that routes other than the block renderer are left alone.
	 *
	 * This is what keeps the frontend, and the REST responses that render post
	 * content for headless sites, rendering saved blocks as normal.
	 */
	public function test_render_request_ignores_other_routes() {
		$this->mock_render_caps( true, false );
		$request = new SafeSvgFakeRestRequest( 'safe-svg/svg-icon', array( 'imageID' => 12 ) );

		Block\drop_unreadable_rendered_attachment_id( null, $this->handler( false ), $request );

		$this->assertSame( array( 'imageID' => 12 ), $request['attributes'] );
		$this->assertFalse( $request->was_set );
	}

	/**
	 * Test that a request another filter already rejected is left alone.
	 */
	public function test_render_request_ignores_an_existing_error() {
		\WP_Mock::userFunction( 'is_wp_error', array( 'return' => true ) );
		$request = new SafeSvgFakeRestRequest( 'safe-svg/svg-icon', array( 'imageID' => 12 ) );

		$response = Block\drop_unreadable_rendered_attachment_id( new \WP_Error( 'nope', 'Nope.' ), $this->handler(), $request );

		$this->assertInstanceOf( \WP_Error::class, $response );
		$this->assertFalse( $request->was_set );
	}

	/**
	 * Test that a request without an imageID is left alone.
	 */
	public function test_render_request_without_an_image_id_is_left_alone() {
		$this->mock_render_caps( true, false );
		$request = new SafeSvgFakeRestRequest( 'safe-svg/svg-icon', array( 'href' => 'https://example.com' ) );

		Block\drop_unreadable_rendered_attachment_id( null, $this->handler(), $request );

		$this->assertFalse( $request->was_set );
	}

	/**
	 * Test that the response is passed through untouched.
	 */
	public function test_render_request_returns_the_response_unchanged() {
		$this->mock_render_caps( true, false );
		$request = new SafeSvgFakeRestRequest( 'safe-svg/svg-icon', array( 'imageID' => 12 ) );

		$this->assertNull( Block\drop_unreadable_rendered_attachment_id( null, $this->handler(), $request ) );
	}
}

/**
 * Stand-in for WP_REST_Request covering the surface the render guard uses.
 */
class SafeSvgFakeRestRequest implements ArrayAccess {
	/**
	 * Whether set_param() was called.
	 *
	 * @var bool
	 */
	public $was_set = false;

	/**
	 * Request parameters.
	 *
	 * @var array
	 */
	private $params;

	/**
	 * Constructor.
	 *
	 * @param string $name       Registered block name.
	 * @param array  $attributes Block attributes.
	 */
	public function __construct( $name, $attributes ) {
		$this->params = array(
			'name'       => $name,
			'attributes' => $attributes,
		);
	}

	/**
	 * Set a request parameter.
	 *
	 * @param string $key   Parameter name.
	 * @param mixed  $value Parameter value.
	 * @return void
	 */
	public function set_param( $key, $value ) {
		$this->was_set        = true;
		$this->params[ $key ] = $value;
	}

	/**
	 * Whether a parameter is set.
	 *
	 * @param mixed $offset Parameter name.
	 * @return bool
	 */
	public function offsetExists( $offset ): bool {
		return isset( $this->params[ $offset ] );
	}

	/**
	 * Read a parameter.
	 *
	 * @param mixed $offset Parameter name.
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return isset( $this->params[ $offset ] ) ? $this->params[ $offset ] : null;
	}

	/**
	 * Set a parameter.
	 *
	 * @param mixed $offset Parameter name.
	 * @param mixed $value  Parameter value.
	 * @return void
	 */
	public function offsetSet( $offset, $value ): void {
		$this->params[ $offset ] = $value;
	}

	/**
	 * Remove a parameter.
	 *
	 * @param mixed $offset Parameter name.
	 * @return void
	 */
	public function offsetUnset( $offset ): void {
		unset( $this->params[ $offset ] );
	}
}
