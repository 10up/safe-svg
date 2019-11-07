<?php
/*
Plugin Name: Safe SVG
Plugin URI:  https://wpsvg.com/
Description: Allows SVG uploads into WordPress and sanitizes the SVG before saving it
Version:     1.9.6
Author:      Daryll Doyle
Author URI:  http://enshrined.co.uk
Text Domain: safe-svg
Domain Path: /languages
 */

defined( 'ABSPATH' ) or die( 'Really?' );

require 'lib/vendor/autoload.php';
require 'includes/safe-svg-tags.php';
require 'includes/safe-svg-attributes.php';

if ( ! class_exists( 'safe_svg' ) ) {

    /**
     * Class safe_svg
     */
    Class safe_svg {

        /**
         * The sanitizer
         *
         * @var \enshrined\svgSanitize\Sanitizer
         */
        protected $sanitizer;

        /**
         * Set up the class
         */
        function __construct() {
            $this->sanitizer = new enshrined\svgSanitize\Sanitizer();
            $this->sanitizer->minify( true );

            add_filter( 'upload_mimes', array( $this, 'allow_svg' ) );
            add_filter( 'wp_handle_upload_prefilter', array( $this, 'check_for_svg' ) );
            add_filter( 'wp_check_filetype_and_ext', array( $this, 'fix_mime_type_svg' ), 75, 4 );
            add_filter( 'wp_prepare_attachment_for_js', array( $this, 'fix_admin_preview' ), 10, 3 );
            add_filter( 'wp_get_attachment_image_src', array( $this, 'one_pixel_fix' ), 10, 4 );
            add_filter( 'admin_post_thumbnail_html', array( $this, 'featured_image_fix' ), 10, 3 );
            add_action( 'admin_enqueue_scripts', array( $this, 'load_custom_admin_style' ) );
            add_action( 'get_image_tag', array( $this, 'get_image_tag_override' ), 10, 6 );
            add_filter( 'wp_generate_attachment_metadata', array( $this, 'skip_svg_regeneration' ), 10, 2 );
            add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_upgrade_link' ) );
            add_filter( 'wp_get_attachment_metadata', array( $this, 'metadata_error_fix' ), 10, 2 );
            add_filter( 'wp_get_attachment_image_attributes', array( $this, 'fix_direct_image_output' ), 10, 3 );
        }

        /**
         * Allow SVG Uploads
         *
         * @param $mimes
         *
         * @return mixed
         */
        public function allow_svg( $mimes ) {
            $mimes['svg']  = 'image/svg+xml';
            $mimes['svgz'] = 'image/svg+xml';

            return $mimes;
        }

        /**
         * Fixes the issue in WordPress 4.7.1 being unable to correctly identify SVGs
         *
         * @thanks @lewiscowles
         *
         * @param null $data
         * @param null $file
         * @param null $filename
         * @param null $mimes
         *
         * @return null
         */
        public function fix_mime_type_svg( $data = null, $file = null, $filename = null, $mimes = null ) {
            $ext = isset( $data['ext'] ) ? $data['ext'] : '';
            if ( strlen( $ext ) < 1 ) {
                $exploded = explode( '.', $filename );
                $ext      = strtolower( end( $exploded ) );
            }
            if ( $ext === 'svg' ) {
                $data['type'] = 'image/svg+xml';
                $data['ext']  = 'svg';
            } elseif ( $ext === 'svgz' ) {
                $data['type'] = 'image/svg+xml';
                $data['ext']  = 'svgz';
            }

            return $data;
        }

        /**
         * Check if the file is an SVG, if so handle appropriately
         *
         * @param $file
         *
         * @return mixed
         */
        public function check_for_svg( $file ) {

            if ( $file['type'] === 'image/svg+xml' ) {
                if ( ! $this->sanitize( $file['tmp_name'] ) ) {
                    $file['error'] = __( "Sorry, this file couldn't be sanitized so for security reasons wasn't uploaded",
                        'safe-svg' );
                }
            }

            return $file;
        }

        /**
         * Sanitize the SVG
         *
         * @param $file
         *
         * @return bool|int
         */
        protected function sanitize( $file ) {
            $dirty = file_get_contents( $file );

            // Is the SVG gzipped? If so we try and decode the string
            if ( $is_zipped = $this->is_gzipped( $dirty ) ) {
                $dirty = gzdecode( $dirty );

                // If decoding fails, bail as we're not secure
                if ( $dirty === false ) {
                    return false;
                }
            }

            /**
             * Load extra filters to allow devs to access the safe tags and attrs by themselves.
             */
            $this->sanitizer->setAllowedTags( new safe_svg_tags() );
            $this->sanitizer->setAllowedAttrs( new safe_svg_attributes() );

            $clean = $this->sanitizer->sanitize( $dirty );

            if ( $clean === false ) {
                return false;
            }

            // If we were gzipped, we need to re-zip
            if ( $is_zipped ) {
                $clean = gzencode( $clean );
            }

            file_put_contents( $file, $clean );

            return true;
        }

        /**
         * Check if the contents are gzipped
         *
         * @see http://www.gzip.org/zlib/rfc-gzip.html#member-format
         *
         * @param $contents
         *
         * @return bool
         */
        protected function is_gzipped( $contents ) {
            if ( function_exists( 'mb_strpos' ) ) {
                return 0 === mb_strpos( $contents, "\x1f" . "\x8b" . "\x08" );
            } else {
                return 0 === strpos( $contents, "\x1f" . "\x8b" . "\x08" );
            }
        }

        /**
         * Filters the attachment data prepared for JavaScript to add the sizes array to the response
         *
         * @param array $response Array of prepared attachment data.
         * @param int|object $attachment Attachment ID or object.
         * @param array $meta Array of attachment meta data.
         *
         * @return array
         */
        public function fix_admin_preview( $response, $attachment, $meta ) {

            if ( $response['mime'] == 'image/svg+xml' ) {
                $dimensions = $this->svg_dimensions( get_attached_file( $attachment->ID ) );

                if ( $dimensions ) {
                    $response = array_merge( $response, $dimensions );
                }

                $possible_sizes = apply_filters( 'image_size_names_choose', array(
                    'full'      => __( 'Full Size' ),
                    'thumbnail' => __( 'Thumbnail' ),
                    'medium'    => __( 'Medium' ),
                    'large'     => __( 'Large' ),
                ) );

                $sizes = array();

                foreach ( $possible_sizes as $size => $label ) {
                    $default_height = 2000;
                    $default_width  = 2000;

                    if ( 'full' === $size && $dimensions ) {
                        $default_height = $dimensions['height'];
                        $default_width  = $dimensions['width'];
                    }

                    $sizes[ $size ] = array(
                        'height'      => get_option( "{$size}_size_w", $default_height ),
                        'width'       => get_option( "{$size}_size_h", $default_width ),
                        'url'         => $response['url'],
                        'orientation' => 'portrait',
                    );
                }

                $response['sizes'] = $sizes;
                $response['icon']  = $response['url'];
            }

            return $response;
        }

        /**
         * Filters the image src result.
         * Here we're gonna spoof the image size and set it to 100 width and height
         *
         * @param array|false $image Either array with src, width & height, icon src, or false.
         * @param int $attachment_id Image attachment ID.
         * @param string|array $size Size of image. Image size or array of width and height values
         *                                    (in that order). Default 'thumbnail'.
         * @param bool $icon Whether the image should be treated as an icon. Default false.
         *
         * @return array
         */
        public function one_pixel_fix( $image, $attachment_id, $size, $icon ) {
            if ( get_post_mime_type( $attachment_id ) == 'image/svg+xml' ) {
                $image['1'] = false;
                $image['2'] = false;
            }

            return $image;
        }

        /**
         * If the featured image is an SVG we wrap it in an SVG class so we can apply our CSS fix.
         *
         * @param string $content Admin post thumbnail HTML markup.
         * @param int $post_id Post ID.
         * @param int $thumbnail_id Thumbnail ID.
         *
         * @return string
         */
        public function featured_image_fix( $content, $post_id, $thumbnail_id ) {
            $mime = get_post_mime_type( $thumbnail_id );

            if ( 'image/svg+xml' === $mime ) {
                $content = sprintf( '<span class="svg">%s</span>', $content );
            }

            return $content;
        }

        /**
         * Load our custom CSS sheet.
         */
        function load_custom_admin_style() {
            wp_enqueue_style( 'safe-svg-css', plugins_url( 'assets/safe-svg.css', __FILE__ ), array() );
        }

        /**
         * Override the default height and width string on an SVG
         *
         * @param string $html HTML content for the image.
         * @param int $id Attachment ID.
         * @param string $alt Alternate text.
         * @param string $title Attachment title.
         * @param string $align Part of the class name for aligning the image.
         * @param string|array $size Size of image. Image size or array of width and height values (in that order).
         *                            Default 'medium'.
         *
         * @return mixed
         */
        function get_image_tag_override( $html, $id, $alt, $title, $align, $size ) {
            $mime = get_post_mime_type( $id );

            if ( 'image/svg+xml' === $mime ) {
                if ( is_array( $size ) ) {
                    $width  = $size[0];
                    $height = $size[1];
                } elseif ( 'full' == $size && $dimensions = $this->svg_dimensions( get_attached_file( $id ) ) ) {
                    $width  = $dimensions['width'];
                    $height = $dimensions['height'];
                } else {
                    $width  = get_option( "{$size}_size_w", false );
                    $height = get_option( "{$size}_size_h", false );
                }

                if ( $height && $width ) {
                    $html = str_replace( 'width="1" ', sprintf( 'width="%s" ', $width ), $html );
                    $html = str_replace( 'height="1" ', sprintf( 'height="%s" ', $height ), $html );
                } else {
                    $html = str_replace( 'width="1" ', '', $html );
                    $html = str_replace( 'height="1" ', '', $html );
                }

                $html = str_replace( '/>', ' role="img" />', $html );
            }

            return $html;
        }

        /**
         * Skip regenerating SVGs
         *
         * @param int $attachment_id Attachment Id to process.
         * @param string $file Filepath of the Attached image.
         *
         * @return mixed Metadata for attachment.
         */
        function skip_svg_regeneration( $metadata, $attachment_id ) {
            $mime = get_post_mime_type( $attachment_id );
            if ( 'image/svg+xml' === $mime ) {
                $additional_image_sizes = wp_get_additional_image_sizes();
                $svg_path               = get_attached_file( $attachment_id );
                $upload_dir             = wp_upload_dir();
                // get the path relative to /uploads/ - found no better way:
                $relative_path = str_replace( $upload_dir['basedir'], '', $svg_path );
                $filename      = basename( $svg_path );

                $dimensions = $this->svg_dimensions( $svg_path );

                if ( ! $dimensions ) {
                    return $metadata;
                }

                $metadata = array(
                    'width'  => intval( $dimensions['width'] ),
                    'height' => intval( $dimensions['height'] ),
                    'file'   => $relative_path
                );

                // Might come handy to create the sizes array too - But it's not needed for this workaround! Always links to original svg-file => Hey, it's a vector graphic! ;)
                $sizes = array();
                foreach ( get_intermediate_image_sizes() as $s ) {
                    $sizes[ $s ] = array( 'width' => '', 'height' => '', 'crop' => false );

                    if ( isset( $additional_image_sizes[ $s ]['width'] ) ) {
                        // For theme-added sizes
                        $sizes[ $s ]['width'] = intval( $additional_image_sizes[ $s ]['width'] );
                    } else {
                        // For default sizes set in options
                        $sizes[ $s ]['width'] = get_option( "{$s}_size_w" );
                    }

                    if ( isset( $additional_image_sizes[ $s ]['height'] ) ) {
                        // For theme-added sizes
                        $sizes[ $s ]['height'] = intval( $additional_image_sizes[ $s ]['height'] );
                    } else {
                        // For default sizes set in options
                        $sizes[ $s ]['height'] = get_option( "{$s}_size_h" );
                    }

                    if ( isset( $additional_image_sizes[ $s ]['crop'] ) ) {
                        // For theme-added sizes
                        $sizes[ $s ]['crop'] = intval( $additional_image_sizes[ $s ]['crop'] );
                    } else {
                        // For default sizes set in options
                        $sizes[ $s ]['crop'] = get_option( "{$s}_crop" );
                    }

                    $sizes[ $s ]['file']      = $filename;
                    $sizes[ $s ]['mime-type'] = $mime;
                }
                $metadata['sizes'] = $sizes;
            }

            return $metadata;
        }

        /**
         * Add in an upgrade link for Safe SVG
         *
         * @param $links
         *
         * @return array
         */
        function add_upgrade_link( $links ) {
            $mylinks = array(
                '<a target="_blank" style="color:#3db634;" href="https://wpsvg.com/?utm_source=plugin-list&utm_medium=upgrade-link&utm_campaign=plugin-list&utm_content=action-link">Upgrade</a>',
            );

            return array_merge( $links, $mylinks );
        }

        /**
         * Filters the attachment meta data.
         *
         * @param array|bool $data Array of meta data for the given attachment, or false
         *                            if the object does not exist.
         * @param int $post_id Attachment ID.
         */
        function metadata_error_fix( $data, $post_id ) {

            // If it's a WP_Error regenerate metadata and save it
            if ( is_wp_error( $data ) ) {
                $data = wp_generate_attachment_metadata( $post_id, get_attached_file( $post_id ) );
                wp_update_attachment_metadata( $post_id, $data );
            }

            return $data;
        }

        /**
         * Get SVG size from the width/height or viewport.
         *
         * @param $svg
         *
         * @return array|bool
         */
        protected function svg_dimensions( $svg ) {
            $svg    = @simplexml_load_file( $svg );
            $width  = 0;
            $height = 0;
            if ( $svg ) {
                $attributes = $svg->attributes();
                if ( isset( $attributes->width, $attributes->height ) ) {
                    $width  = floatval( $attributes->width );
                    $height = floatval( $attributes->height );
                } elseif ( isset( $attributes->viewBox ) ) {
                    $sizes = explode( ' ', $attributes->viewBox );
                    if ( isset( $sizes[2], $sizes[3] ) ) {
                        $width  = floatval( $sizes[2] );
                        $height = floatval( $sizes[3] );
                    }
                } else {
                    return false;
                }
            }

            return array(
                'width'       => $width,
                'height'      => $height,
                'orientation' => ( $width > $height ) ? 'landscape' : 'portrait'
            );
        }

        /**
         * Fix the output of images using wp_get_attachment_image
         *
         * @param array $attr Attributes for the image markup.
         * @param WP_Post $attachment Image attachment post.
         * @param string|array $size Requested size. Image size or array of width and height values
         *                                 (in that order). Default 'thumbnail'.
         */
        public function fix_direct_image_output( $attr, $attachment, $size = 'thumbnail' ) {

            // If we're not getting a WP_Post object, bail early.
            // @see https://wordpress.org/support/topic/notice-trying-to-get-property-id/
            if ( ! $attachment instanceof WP_Post ) {
                return $attr;
            }

            $mime = get_post_mime_type( $attachment->ID );
            if ( 'image/svg+xml' === $mime ) {
                $default_height = 100;
                $default_width  = 100;

                $dimensions = $this->svg_dimensions( get_attached_file( $attachment->ID ) );

                if ( $dimensions ) {
                    $default_height = $dimensions['height'];
                    $default_width  = $dimensions['width'];
                }

                $attr['height'] = $default_height;
                $attr['width']  = $default_width;
            }

            return $attr;
        }

    }
}

$safe_svg = new safe_svg();
