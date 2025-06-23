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
 *
 *      PATTERN TRIANGLES, created programmatically as a path.
 *      ================================================
 *            /\  /\  /\  /\  /\  /\  /\  /\  /\  /\
 *           /  \/  \/  \/  \/  \/  \/  \/  \/  \/  \
 *      ================================================
 */
class Triangles_SVG extends SVG_Generator {

	/**
	 * The unique identifier for this SVG.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * The height of the pattern relative to viewport.
	 *
	 * @var string
	 */
	public $pattern_height = '0.1';

	/**
	 * The number of triangle figures to generate.
	 *
	 * @var string
	 */
	public $number_figures = '3';
}
