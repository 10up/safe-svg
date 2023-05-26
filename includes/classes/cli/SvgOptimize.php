<?php
/**
 * Implements optimize command.
 *
 * @package SafeSvg
 */

namespace SafeSvg\CLI;

use \WP_CLI;
use \WP_Query;
use \SafeSvg\API\VectorExpress;

/**
 * Optimize command class.
 */
class SvgOptimize {

	public function __invoke( $args, $assoc_args ) {
		$svg_query = $this->get_svg_query();
		$optimizer = new VectorExpress();

		foreach ( $svg_query->posts as $svg ) {
			$path = get_attached_file( $svg );
			$optimizer->optimize_image( $path );
		}
	}

	protected function get_svg_query( $limit = 1 ): WP_Query {
		$args = [
			'post_type'      => 'attachment',
			'post_status'    => 'any',
			'posts_per_page' => $limit,
			'post_mime_type' => 'image/svg+xml',
			'fields'         => 'ids',
		];

		return new WP_Query( $args );
	}
}