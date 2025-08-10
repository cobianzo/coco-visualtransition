<?php
/**
 * DuoMask Slope 1 SVG Class
 *
 * @package CocoVisualTransition
 */

namespace Coco\VisualTransition;

use Coco\VisualTransition\Helpers\SVGPath_Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class DuoMask Slope 1
 * Rest of configuration is in patterns.json.
 */
class DuoMask_Slope_1 extends SVG_Generator {

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

		$semitranslarent_path = ' L -0.15033 ' . $this->pattern_height * ( 0.07474 )
		. ' C 0.05496 ' . $this->pattern_height * ( 0.03269 ) . ' 0.23904 ' . $this->pattern_height * ( 0.01285 ) . ' 0.44586 ' . $this->pattern_height * ( 0.03603 )
		. ' C 0.54577 ' . $this->pattern_height * ( 0.04723 ) . ' 0.64742 ' . $this->pattern_height * ( 0.10648 ) . ' 0.74471 ' . $this->pattern_height * ( 0.1326 )
		. ' C 0.82802 ' . $this->pattern_height * ( 0.15496 ) . ' 0.93067 ' . $this->pattern_height * ( 0.14696 ) . ' 1.20572 ' . $this->pattern_height * 1;

		$semitranslarent_path = SVGPath_Helpers::close_path( $semitranslarent_path );

		$this->svg_string = '<svg width="0" height="0" style="position:absolute;overflow:hidden;">
	<defs>
		<mask id="' . $this->pattern_id . '"
		 maskUnits="objectBoundingBox" maskContentUnits="objectBoundingBox">
			<path d="' . $semitranslarent_path . '" fill="red" />
			<path d="M -0.1 0 L -0.10033 0.12474 C 0.10496 0.08269 0.28904 0.06285 0.49586 0.08603 C 0.59577 0.09723 0.69742 0.15648 0.79471 0.1826 C 0.87802 0.20496 0.98067 0.19696 1.25572 0.21959 L 1.1 0 L 1.1 1.1 L -0.1 1.1Z" fill="white" />
		</mask>
	</defs>
</svg>';

		return $this->svg_string;
	}
}
