<?php
/**
 * Vector Express API
 *
 * @package SafeSvg
 */

namespace SafeSvg\API;

/**
 * Interface for optmization APIs
 */
class VectorExpress implements Optimizer {

	const ENDPOINT = 'https://vector.express/api/v2/public/convert/svg/svgo/svg';

	public function optimize_image( $image_path ) {
		var_dump( $image_path );

		$body = file_get_contents( $image_path );

		var_dump( $body );

		$request = wp_remote_post(
			self::ENDPOINT,
			[
				'body' => $body,
			]
		);

		$response_code = wp_remote_retrieve_response_code( $request );
		$response_body = wp_remote_retrieve_body( $request );

		var_dump( $response_code );
		var_dump( $response_body );

	}

}