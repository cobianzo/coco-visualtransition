<?php
/**
 * Square SVG Class
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
 * PATTERN SQUARES, created programmatically as a path.
 * ================================================
 *         ┌───┐   ┌───┐   ┌───┐
 *         │   │   │   │   │   │
 *       ──┘   └───┘   └───┘   └───
 */
class Squares_SVG extends SVG_Generator {

	/**
	 * The ID of the SVG element
	 *
	 * @var string
	 */
	public $id;

	/**
	 * The height of the pattern
	 *
	 * @var string
	 */
	public $pattern_height = '0.6';

	/**
	 * The number of figures in the pattern
	 *
	 * @var string
	 */
	public $number_figures = '5';
}
