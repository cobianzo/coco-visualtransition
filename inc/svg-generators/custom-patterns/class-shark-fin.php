<?php
/**
 * Shark_Fin SVG Class
 *
 * @package CocoVisualTransition
 */

namespace Coco\VisualTransition;

use Coco\VisualTransition\Helpers\SVGPath_Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Shark_Fin
 * Rest of configuration is in patterns.json.
 */
class Shark_Fin extends SVG_Generator {

	/**
	 * Generates an SVG mask with a specific slope pattern.
	 *
	 * Creates an SVG mask element containing two paths - one semi-transparent and one solid white,
	 * forming a slope transition pattern. The paths are defined using a series of coordinates and
	 * Bezier curves to create a smooth transition effect.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string The complete SVG markup as a string.
	 */
	public function generate_svg(): string {

		// scaled in %.
		$path = 'M 0 5.397 C 0.324 5.397 8.897 10.914 24.151 12.003 C 39.405 13.092 53.062 9.394 53.207 8.124 C 53.352 6.854 50.597 3.732 54.689 2.146 C 58.781 0.56 70.091 0.505 75.712 0.704 C 81.333 0.903 66.383 1.939 69.609 4.157 C 72.835 6.375 79.142 5.856 83.271 4.329 C 87.4 2.802 89.735 0.993 97.381 0.998 C 105.027 1.003 120.219 4.787 120.219 4.787';

		$path = SVGPath_Helpers::close_path( $path, 100, 10, 10 );

		$path = SVGPath_Helpers::apply_transform_to_path_coordenates(
			$path,
			fn( float $c ): float => $c / 100.0,
			fn( float $c ): float => $c / 100.0
		);

		$this->svg_string = '<svg width="0" height="0" style="position:absolute;overflow:hidden;">
	<defs>
		<mask id="' . $this->pattern_id . '"
		 maskUnits="objectBoundingBox" maskContentUnits="objectBoundingBox">
			<path d="' . $path . '" fill="white" />
		</mask>
	</defs>
</svg>';

		return $this->svg_string;
	}
}
