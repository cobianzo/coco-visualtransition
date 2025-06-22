<?php
/**
 * Triangle SVG Class
 *
 * @package    VisualTransition
 */

namespace Coco\VisualTransition;

use SVG_Generator;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Triangle_SVG
 * Handles generation and manipulation of triangle SVG elements
 */
class Waves_SVG extends SVG_Generator {

	// props are set up in the constructor.

	public $id;
	public $pattern_height = '0.6';
	public $number_figures = '10';

	public function generate_SVG( $p = '' ): string {

		if ( $p ) {
			return parent::generate_SVG( $p );
		}

		/*
		PATTERN WAVES, created programmatically as a path.
		================================================

		================================================
		*/

		// calculate the params to draw the points.
		$this->points_string = '';
		$point_x             = $this->start_point_x;
		$point_y             = 0;
		$point_x_step        = 1 / $this->number_figures;

		// create the pattern in points in percentage.
		while ( $point_x < $this->end_point_x ) {
			$this->points_string .= $point_x . ' ' . $point_y . ',';

			// calculate for next iteration
			$point_x = $point_x + $point_x_step;
			$point_y = $point_y === 0 ? $this->pattern_height : 0;
		}

		$this->points_string  = <<<SVG
		M 0,0.5
		C 0.025,0.45 0.075,0.55 0.1,0.5
		C 0.125,0.45 0.175,0.55 0.2,0.5
		C 0.225,0.45 0.275,0.55 0.3,0.5
		C 0.325,0.45 0.375,0.55 0.4,0.5
		C 0.425,0.45 0.475,0.55 0.5,0.5
		C 0.525,0.45 0.575,0.55 0.6,0.5
		C 0.625,0.45 0.675,0.55 0.7,0.5
		C 0.725,0.45 0.775,0.55 0.8,0.5
		C 0.825,0.45 0.875,0.55 0.9,0.5
		C 0.925,0.45 0.975,0.55 1,0.5
		L 1,1
		L 0,1
SVG;
		$this->points_string .= "$this->end_point_x $this->end_point_y, $this->start_point_x $this->end_point_y";
		$this->points_string .= 'Z';
		$svg = parent::generate_SVG();

		return $svg;
	}
}