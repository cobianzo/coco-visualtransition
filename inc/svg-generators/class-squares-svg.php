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
	public string $id;

	/**
	 * The height of the pattern
	 *
	 * @var float
	 */
	public float $pattern_height;

	/**
	 * The number of figures in the pattern
	 *
	 * @var int
	 */
	public int $number_figures;
}
