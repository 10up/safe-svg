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

	public function optimize_image( $image ) {
		return $image;
	}

}