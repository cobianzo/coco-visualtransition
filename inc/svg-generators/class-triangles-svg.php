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
class Triangles_SVG extends SVG_Generator {

	// props are set up in the constructor.

	public $id;
	public $pattern_height = '0.6';
	public $number_figures = '10';

	public function generate_SVG( $p = '' ): string {

		if ( $p ) {
			return parent::generate_SVG( $p );
		}

		/*
		PATTERN TRIANGLES, created programmatically as a path.
		================================================
				/\  /\  /\  /\  /\  /\  /\  /\  /\  /\
			/  \/  \/  \/  \/  \/  \/  \/  \/  \/  \
		================================================
		*/

		// calculate the params to draw the points.
		$svg = parent::generate_SVG();

		return $svg;
	}
}
