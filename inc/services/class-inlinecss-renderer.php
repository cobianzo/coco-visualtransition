<?php
/**
 * InlineCSS Renderer Service
 *
 * @package    CocoVisualTransition
 * @subpackage Services
 * @since      1.0.0
 */

namespace COCO\VisualTransition\Services;

use Coco\VisualTransition\SVG_Generator_Factory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Service for generating SVG and CSS for visual transitions.
 */
final class InlineCSS_Renderer {
	// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	/**
	 * Generate SVG and CSS for a given pattern, id, and attributes.
	 *
	 * @param string                      $pattern Pattern name.
	 * @param string                      $id Unique identifier.
	 * @param array<string, string|float> $atts Pattern attributes.
	 * @param string                      $selector The data attribute selector for the CSS (default: 'data-cocovisualtransitionid').
	 * @return array{svg: string, css: string, pattern_id: string, is_mask: bool}
	 */
	public static function generate_svg_and_css( string $pattern, string $id, array $atts = [], string $selector = 'data-cocovisualtransitionid' ): array {
		require_once plugin_dir_path( __FILE__ ) . '/../svg-generators/class-svg-generator-factory.php';
		$generator  = SVG_Generator_Factory::create( $pattern, $id, $atts );
		$svg        = $generator->svg_string;
		$pattern_id = $generator->pattern_id;
		$is_mask    = isset( $generator->pattern_data['type'] ) && 'mask' === $generator->pattern_data['type'];
		// Include and use the CSS template
		ob_start();
		// Make $selector available to the template
		include plugin_dir_path( __FILE__ ) . '/../templates/css-template.php';
		$css = ob_get_clean();
		return [
			'svg'        => $svg,
			'css'        => $css ? $css : '',
			'pattern_id' => $pattern_id,
			'is_mask'    => $is_mask,
		];
	}
	// phpcs:enable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
}
