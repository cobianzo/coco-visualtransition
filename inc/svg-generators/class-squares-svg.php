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
class Squares_SVG extends SVG_Generator {

	public $id;
	public $pattern_height = '0.6';
	public $number_figures = '5';

	public function generate_SVG( $p = '' ): string {

		if ( $p ) {
			return parent::generate_SVG( $p );
		}

		/* PATTERN SQUARES, created programmatically as a path.
		================================================
  			┌───┐   ┌───┐   ┌───┐
				│   │   │   │   │   │
			──┘   └───┘   └───┘   └───
		*/

		// calculate the params to draw the points.
		$this->points_string = '';
		$point_x       = $this->start_point_x;
		$point_y      = 0;
		$point_x_step = 1 / $this->number_figures;
		$move_on_x    = true;
		$move_on_y    = false;
		// create the pattern in points in percentage.
		while ( $point_x < $this->end_point_x ) {
			$this->points_string .= $point_x . ' ' . $point_y . ',';


			$point_x = $point_x + ( $move_on_x ? $point_x_step : 0 );
			if ( $move_on_y ) {
				$point_y = $point_y === 0 ? $this->pattern_height : 0;
			}

			$move_on_x = ! $move_on_x;
			$move_on_y = ! $move_on_y;
		}

		// closing the poygon.
		$this->points_string .= "$this->end_point_x $this->end_point_y, $this->start_point_x $this->end_point_y";


		$svg = parent::generate_SVG();

		return $svg;
	}
}
