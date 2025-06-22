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
	 * @throws \Exception If invalid generator type is provided
	 */
	public static function create( string $pattern, string $id, $atts = [] ) {

		require_once plugin_dir_path( __FILE__ ) . "class-$pattern-svg.php";

		switch ( $pattern ) {
			case 'triangles':
				return new Triangles_SVG( $id, $atts );
			case 'squares':
				return new Squares_SVG( $id, $atts );
			case 'waves':
				return new Waves_SVG( $id, $atts );
			default:
				throw new \Exception( "Invalid SVG generator type: {$type}" );
		}
	}

	/**
	 * Gets available generator types
	 *
	 * @return array List of available generator types
	 */
	public static function get_available_types() {
		return [
			'triangles',
			'circles',
			'squares',
			'lines',
		];
	}
}