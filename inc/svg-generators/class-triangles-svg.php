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
class Triangles_SVG extends SVG_Generator {

	// props are set up in the constructor.

	public $id;
	public $pattern_height = '0.6';
	public $number_figures = '10';

	public function generate_SVG( $p = '' ): string {

		if ( $p ) {
			return parent::generate_SVG( $p );
		}

		/* PATTERN TRIANGLES, created programmatically as a path.
		================================================
				/\  /\  /\  /\  /\  /\  /\  /\  /\  /\
			 /  \/  \/  \/  \/  \/  \/  \/  \/  \/  \
		================================================
		*/

		// calculate the params to draw the points.
		$this->points_string = '';
		$point_x       = $this->start_point_x;
		$point_y      = 0;
		$point_x_step = 1 / $this->number_figures;

		// create the pattern in points in percentage.
		while ( $point_x < $this->end_point_x ) {
			$this->points_string .= $point_x . ' ' . $point_y . ',';

			// calculate for next iteration
			$point_x = $point_x + $point_x_step;
			$point_y = $point_y === 0 ? $this->pattern_height : 0;
		}

		// closing the poygon.
		$this->points_string .= "$this->end_point_x $this->end_point_y, $this->start_point_x $this->end_point_y";


		$svg = parent::generate_SVG();

		return $svg;
	}
}
