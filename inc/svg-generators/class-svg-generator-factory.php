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

		$file_path = plugin_dir_path( __FILE__ ) . "class-$pattern-svg.php";
		if ( file_exists( $file_path ) ) {
			// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
			require_once $file_path;
		}

		switch ( $pattern ) {
			case 'squares':
				return new Squares_SVG( $pattern, $id, $atts );
			case 'waves':
				return new Waves_SVG( $pattern, $id, $atts );
			default:
				// Generic
				return new SVG_Generator( $pattern, $id, $atts );
		}
	}
}
