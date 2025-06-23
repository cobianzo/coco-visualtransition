<?php
/**
 * Triangle SVG Class
 *
 * @package    VisualTransition
 */

namespace Coco\VisualTransition;

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

	public function generate_SVG(): string {

		/*
		PATTERN SQUARES, created programmatically as a path.
		================================================
				┌───┐   ┌───┐   ┌───┐
				│   │   │   │   │   │
			──┘   └───┘   └───┘   └───
		*/

		// calculate the params to draw the points.

		$svg = parent::generate_SVG();

		return $svg;
	}
}
