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

	/**
	 * The height of the wave pattern
	 *
	 * @var float
	 */
	public float $pattern_height = 0.6;

	/**
	 * The number of wave figures to generate
	 *
	 * @var int
	 */
	public float $pattern_width = 10;

	/**
	 * Generates the points string for the wave SVG path
	 *
	 * @return string The SVG path points string
	 */
	public function generate_points(): string {
		$this->points_string = <<<PATH_POINTS
		M 0.032 0.095 L 0.249 0.024 C 0.249 0.024 0.566 0.176 0.646 0.115 C 0.725 0.055 0.833 0 0.961 0.005 C 1.088 0.009 0.999 1 0.999 1 L 0 0.996 L 0.009 0.091 L 0.032 0.095 Z
PATH_POINTS;

		return $this->points_string;
	}
}
