<?php
/**
 * SVG Generator.
 *
 * From the pattern name and parameters, create the <svg> element to insert inline in the document.
 *
 * @package Coco\VisualTransition
 */

namespace Coco\VisualTransition;

/**
 * The parent class that initializes the generic stuff,
 * and the child classes will extend it.
 *
 * It creates the <svg> <defs> <clipPath... ... for the mask that will clip the core/>block div,
 * it will be applied by using css rules
 *
 * Important: svg_string ( and points_string ) is whan determines the shape of the svg, that's what matters.
 *
 * We never call this class directly (we call the children classes for evert pattern shape)
 */
class SVG_Generator {

	/**
	 * Unique identifier for the SVG element
	 *
	 * @var string
	 */
	public string $id;

	/**
	 * Name of the pattern to be generated
	 *
	 * @var string
	 */
	public string $pattern_name = 'triangles';

	/**
	 * Height of the pattern in relative units
	 *
	 * @var float
	 */
	public float $pattern_height;

	/**
	 * Number of pattern figures to generate
	 *
	 * @var int
	 */
	public int $number_figures;

	/**
	 * Pattern data loaded from JSON file
	 *
	 * @var array{
	 *     value?: string,
	 *     patternRepeat?: string,
	 *     pattern?: string
	 * }
	 */
	protected array $pattern_data;

	/**
	 * These is what matters, the svg and points for the shape.
	 *
	 * @var string
	 */
	public string $points_string;

	/**
	 * The SVG string representation
	 *
	 * @var string
	 */
	public string $svg_string;

	/**
	 * We generate %this->points, which define the shape of the mask.
	 *
	 * @param string               $pattern_name ie trianges, waves, squares ...
	 * @param string               $id something like vt_d0c75d9c-98fd-4f10-acec-bb95921d8211
	 * @param array<string, mixed> $atts parameters to build the shape of the mask, like height of the fret in percentage.
	 */
	public function __construct( string $pattern_name = '', string $id = 'mi-greca', array $atts = [] ) {

		$this->id           = $id;
		$this->pattern_name = $pattern_name;

		// init to empty, we'll generate the values.
		$this->points_string = '';
		$this->svg_string    = '';

		// the $atts params which customizes the pattern mask.
		$this->pattern_height = ( isset( $atts['pattern-height'] ) && '' !== $atts['pattern-height'] )
			? (float) self::to_float( $atts['pattern-height'] ) : 0.1;


		$this->number_figures = ( isset( $atts['number-figures'] ) && '' !== $atts['number-figures'] )
			? (int) self::to_float( $atts['number-figures'] ) : 10;


		$this->generate_points();
	}

	/**
	 * Generates the points for the svg based on the "pattern" key inside patterns.json.
	 *
	 * @return string
	 */
	public function generate_points(): string {
		if ( ! empty( $this->pattern_name ) ) {
			// loads the pattern single
			$plugin_root       = plugin_dir_path( dirname( __DIR__ ) );
			$patterns_filename = $plugin_root . '/src/patterns.json';
			$patterns_json     = wp_json_file_decode( $patterns_filename, [ 'associative' => true ] );

			if ( ! is_array( $patterns_json ) ) {
				return $this->points_string;
			}

			/**
			 * Fret pattern array structure containing optional value, patternRepeat and pattern strings.
			 *
			 * @var array{value?: string, patternRepeat?: string, pattern?: string}|null $pattern
			 */
			$pattern = array_find(
				$patterns_json,
				fn( mixed $pattern_data ) => $this->pattern_name === ( ( (array) $pattern_data )['value'] ?? '' )
			);

			if ( ! is_array( $pattern ) ) {
				return $this->points_string;
			}

			$this->pattern_data = $pattern;

			if ( ! empty( $this->pattern_data['patternRepeat'] ) ) {
				if ( 'repeat-x' === $this->pattern_data['patternRepeat'] ) {
					// prepare the pattern for single figure using
					// now we loop the pattern moving right until getting the 100% (end_point_x).
					$offset_x = 0.1;
					$offset_y = 0.1;

					if ( isset( $this->pattern_data['pattern'] ) ) {
						$this->points_string = self::generate_points_string_from_pattern(
							$this->pattern_data['pattern'],
							$this->number_figures,
							$this->pattern_height,
							$offset_x,
							$offset_y
						);
					}
				}
			}
		}
		return $this->points_string;
	}

	/**
	 * Generates an SVG based on the provided parameters
	 *
	 * @return string The generated SVG markup
	 */
	public function generate_svg(): string {
		$points = $this->points_string;

		// sanitization
		if ( false === strpos( $points, 'Z' ) ) {
			$points .= 'Z';
		}

		// if the points use beizer vertex, we use path d, otherwise we use polygon, for right lines.
		$shape_string = '<polygon points="%s" />';
		if ( false !== strpos( $points, 'C ' ) || false !== strpos( $points, 'S' ) ) {
			$shape_string = '<path d="%s" />';
		}

		$shape_string = sprintf( $shape_string, $points );

		$this->svg_string = <<<SVG
<svg width="0" height="0">
	<defs>
		<clipPath id="$this->id" clipPathUnits="objectBoundingBox">
			$shape_string
		</clipPath>
	</defs>
</svg>
SVG;
		return $this->svg_string;
	}

	/**
	 * Helper.
	 * From a set of points as a string in $this0>points_string or the arg, ie ( 0 0, 1 1, 2 0, 3 1, 4 0 ).
	 * returns the last x point. (in this case (int) 4)
	 *
	 * @param string|null $overwrite_points Optional points string to use instead of $this->points_string.
	 * @return float The last x coordinate value.
	 */
	public function get_last_x_point( ?string $overwrite_points ): float {
		$string_points = $overwrite_points ?? $this->points_string;

		$points     = explode( ',', trim( $string_points ) );
		$last_point = trim( end( $points ) );
		$last_point = preg_replace( '/\s+/', ' ', trim( $last_point ) );

		$last_x = explode( ' ', (string) $last_point )[0];
		$last_x = floatval( $last_x );

		return $last_x;
	}

	/**
	 * Helper
	 * from a given pattern string, using the placeholders for the gap of the x and y coords,
	 * we repeat the pattern a number of times. Example of pattern: "0 0, {x_size} 0, {x_size} {y_size}, {2*x_size} {y_size}"
	 * Will return something like: "0 0, 0.25 0, 0.25 0.05, 0.5 0.05, "
	 *
	 * @param string $pattern The pattern string containing placeholders for coordinates.
	 * @param int    $number_figures The number of times to repeat the pattern.
	 * @param float  $pattern_height The height of each pattern repetition.
	 * @param float  $offset_x The horizontal offset to apply to the pattern.
	 * @param float  $offset_y The vertical offset to apply to the pattern.
	 * @return string The generated points string for the SVG shape.
	 */
	public static function generate_points_string_from_pattern( string $pattern, int $number_figures, float $pattern_height, float $offset_x = 0, float $offset_y = 0.1 ): string {
		// prepare the pattern for single figure using
		// calculate boundaies of the the clip mask, we'll use it in the child.
		$start_point_x = 0 - $offset_x;
		$end_point_x   = 1 + $offset_x;
		$end_point_y   = 1 + $offset_y;

		// now we loop the pattern moving right until getting the 100% (end_point_x).
		$points_string = "$start_point_x 0";
		$point_x_step  = ( $end_point_x - $start_point_x ) / $number_figures;
		$pattern_array = explode( ',', trim( $pattern ) );

		for ( $index = 0; $index < $number_figures; $index++ ) {
			$latest_x_point = ( new self() )->get_last_x_point( $points_string );

			// we go coordenate by coordenate replacing the placeholders
			foreach ( $pattern_array as $points ) {
				$points = str_replace( '{x_size}', (string) $point_x_step, $points );
				$points = str_replace( '{2*x_size}', (string) ( 2 * $point_x_step ), $points );
				$points = str_replace( '{y_size}', (string) $pattern_height, $points );

				// we run every [x , y]]
				$x_y = explode( ' ', trim( $points ) );
				$x   = ( new self() )->get_last_x_point( implode( ' ', $x_y ) );
				$y   = $x_y[1];

				$x             += $latest_x_point;
				$points_string .= ", $x $y";
			}
		}

		// now points_string follows the pattern at the top of the container. We close it with lines to the bottom right, and bottom left
		$points_string .= ", $end_point_x $end_point_y, $start_point_x $end_point_y";
		$points_string .= 'Z';

		return $points_string;
	}

	/**
	 * Just a helper. phpstan needs it.
	 *
	 * @param mixed $value the number or variable to convert into float, if possible
	 * @param float $default the fallback
	 * @return float
	 */
	protected static function to_float( mixed $value, float $default = 0.0 ): float {
		if ( is_numeric( $value ) ) {
			return (float) $value;
		}
		return $default;
	}
}
