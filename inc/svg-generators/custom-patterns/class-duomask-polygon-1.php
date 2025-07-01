<?php
/**
 * DuoMask Pllygon 1 SVG Class
 *
 * @package CocoVisualTransition
 */

namespace Coco\VisualTransition;

use Coco\VisualTransition\Helpers\SVGPath_Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class
 * Rest of configuration is in patterns.json.
 */
class DuoMask_Polygon_1 extends SVG_Generator {

	/**
	 * Overrides the parent generate_svg() method to create a specific
	 *
	 * @return string The complete SVG markup
	 */
	public function generate_svg(): string {

		// semitransparent mask path
		$semitranslarent_path = ' L -1.06 3.591 L 13.991 0.119 L 52.265 7.816 L 74.952 1.2 L 82.687 3.075 L 102.672 1.202';
		$semitranslarent_path = SVGPath_Helpers::apply_transform_to_path_coordenates(
			$semitranslarent_path,
			fn( float $c ): float => $c / 100.0,
			fn( float $c ): float => $c / 100.0
		);
		$semitranslarent_path = 'M -0.1 0' . $semitranslarent_path . ' L 1.1 1.1 L -0.1 1.1 Z'; // close path

		// opaque mask path
		$mask_path = ' L -0.582 6.674 L 15.93 0.5 L 48.978 10.0 L 72.308 4.903 L 80.235 7.796 L 102.35 2.584';
		$mask_path = SVGPath_Helpers::apply_transform_to_path_coordenates(
			$mask_path,
			fn( float $c ): float => $c / 100.0,
			fn( float $c ): float => $c / 100.0
		);
		$mask_path = 'M -0.1 0 ' . $mask_path . ' L 1.1 1.1 L -0.1 1.1 Z';

		$this->svg_string = <<<SVG
<svg width="0" height="0" style="position:absolute;overflow:hidden;">
	<defs>
		<mask id="$this->pattern_id"
		 maskUnits="objectBoundingBox" maskContentUnits="objectBoundingBox">
			<path d="$semitranslarent_path" fill="red" />
			<path d="$mask_path" fill="white" />
		</mask>
	</defs>
</svg>
SVG;

		return $this->svg_string;
	}
}
