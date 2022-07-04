<?php 
use \WP_Mock\Tools\TestCase;

/**
 * SafeSvgTest class tests the safe_svg class and functions.
 */
class SafeSvgTest extends TestCase {
    /**
	 * Set up our mocked WP functions. Rather than setting up a database we can mock the returns of core WordPress functions.
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
	 * Test constructor.
	 */
    public function test_constructor(){
        $safe_svg = new safe_svg();
        
        \WP_Mock::expectFilterAdded( 'upload_mimes', array( $safe_svg, 'allow_svg' ) );
        \WP_Mock::expectFilterAdded( 'wp_handle_upload_prefilter', array( $safe_svg, 'check_for_svg' ) );
        \WP_Mock::expectFilterAdded( 'wp_check_filetype_and_ext', array( $safe_svg, 'fix_mime_type_svg' ), 75, 4 );
        \WP_Mock::expectFilterAdded( 'wp_prepare_attachment_for_js', array( $safe_svg, 'fix_admin_preview' ), 10, 3 );
        \WP_Mock::expectFilterAdded( 'wp_get_attachment_image_src', array( $safe_svg, 'one_pixel_fix' ), 10, 4 );
        \WP_Mock::expectFilterAdded( 'admin_post_thumbnail_html', array( $safe_svg, 'featured_image_fix' ), 10, 3 );
        \WP_Mock::expectActionAdded( 'admin_enqueue_scripts', array( $safe_svg, 'load_custom_admin_style' ) );
        \WP_Mock::expectActionAdded( 'get_image_tag', array( $safe_svg, 'get_image_tag_override' ), 10, 6 );
        \WP_Mock::expectFilterAdded( 'wp_generate_attachment_metadata', array( $safe_svg, 'skip_svg_regeneration' ), 10, 2 );
        \WP_Mock::expectFilterAdded( 'wp_get_attachment_metadata', array( $safe_svg, 'metadata_error_fix' ), 10, 2 );
        \WP_Mock::expectFilterAdded( 'wp_calculate_image_srcset_meta', array( $safe_svg, 'disable_srcset' ), 10, 4 );

        $safe_svg->__construct();
		$this->assertConditionsMet();
    }
}