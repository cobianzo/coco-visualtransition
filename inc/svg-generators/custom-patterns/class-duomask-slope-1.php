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
 *
 * TODO: We could move everything into patterns.json, and automate the logic here for all
 * patterns without y_size param.
 *
 * In this case there would be the patters:
 * L 0.182 {0.2326*y_size} C 0.182 {0.2326*y_size} 37.461 {0.997*y_size} 56 {0.7622*y_size} C 81.993 {0.4331*y_size} 117.443 {0.9113*y_size} 117.443 {0.9113*y_size}
 * L 0.496 {0.3548*y_size} C 0.496 {0.3548*y_size} 11.687 {0.0347*y_size} 19.028 {0.0598*y_size} C 30.422 {0.0994*y_size} 35.276 {0.7898*y_size} 48.637 {0.8043*y_size} C 54.287 {0.8106*y_size} 63.761 {-0.0541*y_size} 78.384 {0.0599*y_size} C 94.92 {0.1892*y_size} 91.777 {0.8081*y_size} 99.111 {0.8919*y_size} C 108.636 {1*y_size} 118.101 {0.4501*y_size} 118.101 {0.4501*y_size}
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

		// helper. We'll use it later.
		$fn_divide_by_100 = fn( float $c ): float => $c / 100.0;

		// semitransparent mask path. Defined in % per cent, and transformed in % per 1.
		$semitranslarent_path = 'L 0.496 5.179 C 0.496 5.179 11.687 0.507 19.028 0.884 C 30.422 1.47 35.276 11.68 48.637 11.902 C 54.287 11.996 63.761 -0.801 78.384 0.885 C 94.92 2.792 91.777 11.928 99.111 13.123 C 108.636 14.676 118.101 6.601 118.101 6.601';

		// convert in % per 1.
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

		$mask_path = 'L 0.182 2.322 C 0.182 2.322 37.461 9.966 56 7.598 C 81.993 4.278 117.443 9.132 117.443 9.132';
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
