<?php
/**
 * Optimizer API Interface
 *
 * @package SafeSvg
 */

namespace SafeSvg\API;

/**
 * Interface for optmization APIs
 */
interface Optimizer {

	public function get_option_fields();

	public function optimize_image( $image );

}