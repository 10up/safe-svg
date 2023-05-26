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
			$r = $optimizer->optimize_image( $svg );
			WP_CLI::log( $r );
		}
	}

	protected function get_svg_query( $limit = 10 ): WP_Query {
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