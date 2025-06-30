<?php
/**
 * SVG Generator Factory Class.
 * NOTE: This might be a little bit overkilling. We coudld simply call the
 * new SVG_Generator(), and include a filter for the generate_points and generate_svg functions,
 * so if we need a really customized svg we can use the filters instead of creating a new factory.
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
	public static function create( string $pattern, string $id, array $atts = [] ): SVG_Generator {

		$file_path = plugin_dir_path( __FILE__ ) . "custom-patterns/class-$pattern.php";
		if ( file_exists( $file_path ) ) {
			// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
			require_once $file_path;
		}

		switch ( $pattern ) {
			case 'duomask-slope-1':
				return new DuoMask_Slope_1( $pattern, $id, $atts );
				break;
			default:
				// Generic
				return new SVG_Generator( $pattern, $id, $atts );
		}
	}
}
