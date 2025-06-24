<?php
/**
 * SVG Generator Factory Class
 *
 * @package CocoVisualTransition
 */

namespace Coco\VisualTransition;

/**
 * Factory class for creating SVG generators
 */
class SVG_Generator_Factory {

	/**
	 * Creates and returns an SVG generator instance based on the type
	 *
	 * @param string              $pattern The pattern type to generate.
	 * @param string              $id      The unique identifier for the SVG.
	 * @param array<string,mixed> $atts Optional attributes for the SVG generator.
	 * @throws \Exception If invalid generator type is provided.
	 * @return SVG_Generator Instance of the requested SVG generator.
	 */
	public static function create( string $pattern, string $id, array $atts = [] ) {

		require_once plugin_dir_path( __FILE__ ) . "class-$pattern-svg.php";

		switch ( $pattern ) {
			case 'triangles':
				return new Triangles_SVG( $pattern, $id, $atts );
			case 'squares':
				return new Squares_SVG( $pattern, $id, $atts );
			case 'waves':
				return new Waves_SVG( $pattern, $id, $atts );
			default:
				throw new \Exception(
					sprintf(
						'Invalid SVG generator type: %s',
						esc_html( $pattern )
					)
				);
		}
	}

	/**
	 * Gets available generator types
	 *
	 * @return array<string> List of available generator types
	 */
	public static function get_available_types(): array {
		return [
			'triangles',
			'circles',
			'squares',
			'lines',
		];
	}
}
