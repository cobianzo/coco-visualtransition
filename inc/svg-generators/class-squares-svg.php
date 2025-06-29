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
 * Class Squares
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
}
