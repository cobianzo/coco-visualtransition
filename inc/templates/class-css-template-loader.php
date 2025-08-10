<?php
/**
 * CSS Template Loader for Visual Transition
 *
 * @package CocoVisualTransition
 */


namespace COCO\VisualTransition\Templates;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class CSS_Template_Loader {


	/**
	 * Render CSS template for typePattern '%'.
	 *
	 * @param string               $id
	 * @param string               $pattern_id
	 * @param bool                 $is_mask
	 * @param array<string, mixed> $atts
	 * @param string               $selector
	 * @return string
	 */
	public static function render_percent( $id, $pattern_id, $is_mask, $atts, $selector ) {
		ob_start();
		include __DIR__ . '/css-template-percent.php';
		return ob_get_clean();
	}

	/**
	 * Render CSS template for typePattern 'px'.
	 *
	 * @param string               $id
	 * @param string               $pattern_id
	 * @param bool                 $is_mask
	 * @param array<string, mixed> $atts
	 * @param string               $selector
	 * @return string
	 */
	public static function render_px( $id, $pattern_id, $is_mask, $atts, $selector ) {
		ob_start();
		include __DIR__ . '/css-template-px.php';
		return ob_get_clean();
	}
}
