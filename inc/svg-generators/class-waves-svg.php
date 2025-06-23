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
class Waves_SVG extends SVG_Generator {

	// props are set up in the constructor.

	public $id;
	public $pattern_height = '0.6';
	public $number_figures = '10';

	public function generate_points(): string {
		$this->points_string = <<<PATH_POINTS
		M 0,$this->pattern_height
		C 0.033,0.1 0.066,0 0.1,0
		S 0.166,0.1 0.2,$this->pattern_height
		S 0.266,0 0.3,0
		S 0.366,0.1 0.4,$this->pattern_height
		S 0.466,0 0.5,0
		S 0.566,0.1 0.6,$this->pattern_height
		S 0.666,0 0.7,0
		S 0.766,0.1 0.8,$this->pattern_height
		S 0.866,0 0.9,0
		S 0.966,0.1 1,$this->pattern_height
		L 1,1 L 0,1
		Z
PATH_POINTS;

		return $this->points_string;
	}
}
