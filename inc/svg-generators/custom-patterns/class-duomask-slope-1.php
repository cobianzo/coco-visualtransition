<?php
/**
 * DuoMask Slope 1 SVG Class
 *
 * @package CocoVisualTransition
 */

namespace Coco\VisualTransition;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class DuoMask Slope 1
 * Rest of configuration is in patterns.json.
 */
class DuoMask_Slope_1 extends SVG_Generator {

	public function generate_svg(): string
	{
		$this->svg_string = <<<SVG
<svg width="0" height="0" style="position:absolute;overflow:hidden;">
	<defs>
		<mask id="$this->pattern_id"
		 maskUnits="objectBoundingBox" maskContentUnits="objectBoundingBox">
			<path d="M -0.1 0 L -0.15033 0.07474 C 0.05496 0.03269 0.23904 0.01285 0.44586 0.03603 C 0.54577 0.04723 0.64742 0.10648 0.74471 0.1326 C 0.82802 0.15496 0.93067 0.14696 1.20572 0.16959L 1.1 0L 1.1 1.1L -0.1 1.1Z" fill="red" />
			<path d="M -0.1 0 L -0.10033 0.12474 C 0.10496 0.08269 0.28904 0.06285 0.49586 0.08603 C 0.59577 0.09723 0.69742 0.15648 0.79471 0.1826 C 0.87802 0.20496 0.98067 0.19696 1.25572 0.21959 L 1.1 0 L 1.1 1.1 L -0.1 1.1Z" fill="white" />
		</mask>
	</defs>
</svg>
SVG;

		return $this->svg_string;
	}
}
