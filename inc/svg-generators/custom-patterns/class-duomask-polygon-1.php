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

		$fn_divide_by_100 = fn( float $c ): float => $c / 100.0;
		// semitransparent mask path. Defined in % per cent, and transformed in % per 1.
		$semitranslarent_path = ' L -1.06 3.591 L 13.991 0.119 L 52.265 7.816 L 74.952 1.2 L 82.687 3.075 L 102.672 1.202';
		$semitranslarent_path = SVGPath_Helpers::apply_transform_to_path_coordenates(
			$semitranslarent_path,
			$fn_divide_by_100,
			$fn_divide_by_100
		);
		// we scale to make the max Y coor is 1, and the min Y is 0.
		$semitranslarent_path = SVGPath_Helpers::scale_y_to_unit_interval( $semitranslarent_path );
		if ( '%' === $this->type_pattern ) {
			// we multiply by the factor chosen by the user. No need to do it in px as the height of the container will scale it naturally.
			$semitranslarent_path = SVGPath_Helpers::apply_transform_to_path_coordenates(
				$semitranslarent_path,
				fn( float $c ): float => $c,
				fn( float $c ): float => $c * (float) $this->pattern_height
			);
		}

		$semitranslarent_path = SVGPath_Helpers::close_path( $semitranslarent_path );

		// ===== now the second path, totally opaque mask path. ============

		$mask_path = ' L -0.582 6.674 L 15.93 0.5 L 48.978 10.0 L 72.308 4.903 L 80.235 7.796 L 102.35 2.584';
		$mask_path = SVGPath_Helpers::apply_transform_to_path_coordenates(
			$mask_path,
			$fn_divide_by_100,
			$fn_divide_by_100
		);
		$mask_path = SVGPath_Helpers::scale_y_to_unit_interval( $mask_path );
		if ( '%' === $this->type_pattern ) {
			$mask_path = SVGPath_Helpers::apply_transform_to_path_coordenates(
				$mask_path,
				fn( float $c ): float => $c,
				fn( float $c ): float => $c * (float) $this->pattern_height
			);
		}
		$mask_path = SVGPath_Helpers::close_path( $mask_path );


		$this->svg_string = '<svg width="0" height="0"
			style="position:absolute;overflow:hidden;" class="svg-for-' . $this->pattern_name . '">
	<defs>
		<mask id="' . $this->pattern_id . '"
		 maskUnits="objectBoundingBox" maskContentUnits="objectBoundingBox">
			<path d="' . $semitranslarent_path . '" fill="red" />
			<path d="' . $mask_path . '" fill="white" />
		</mask>
	</defs>
</svg>';

		return $this->svg_string;
	}
}
