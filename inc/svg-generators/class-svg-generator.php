<?php
/**
 * SVG Generator.
 *
 * From the pattern name and parameters, create the <svg> element to insert inline in the document.
 *
 * @package Coco\VisualTransition
 */

namespace Coco\VisualTransition;

use Coco\VisualTransition\Helpers;

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
 * We call  `SVG _ Generator _ Factory :: create`
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
	 * Width of the pattern in relative units
	 *
	 * @var float
	 */
	public float $pattern_width;

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

		$this->pattern_data = Helpers::load_patterns_json( $pattern_name );

		// init to empty, we'll generate the values.
		$this->points_string = '';
		$this->svg_string    = '';

		// the $atts params which customizes the pattern mask.
		$this->pattern_height = ( isset( $atts['pattern-height'] ) && '' !== $atts['pattern-height'] )
			? (float) Helpers::to_float( $atts['pattern-height'] ) : 0.1;

		$this->pattern_width = ( isset( $atts['pattern-width'] ) && '' !== $atts['pattern-width'] )
			? (float) Helpers::to_float( $atts['pattern-width'] ) : 0.1;

		$this->generate_points();
		$this->generate_svg();
	}

	/**
	 * Generates the points for the svg based on the "pattern" key inside patterns.json.
	 *
	 * @return string
	 */
	public function generate_points(): string {
		if ( ! empty( $this->pattern_name ) ) {

			if ( empty( $this->pattern_data ) ) {
				return $this->points_string;
			}

			$offset_x = 0.1;
			$offset_y = 0.1;

			if ( isset( $this->pattern_data['pattern'] ) ) {
				$this->points_string = $this->generate_points_string_from_pattern(
					$offset_x,
					$offset_y
				);
			}
		}

		return $this->points_string;
	}

	/**
	 * Replaces placeholder values in points string with actual coordinates.
	 *
	 * @param string $points_string The points string containing placeholders.
	 * @param float  $base_x_coord  The base x coordinate to offset points.
	 * @param array  $param_values  Array of parameter values to replace placeholders.
	 * @return string The processed points string with replaced values.
	 */
	public static function replace_points_placeholders( string $points_string, float $base_x_coord = 0.0, array $param_values = [] ): string {

		// Sanitize points string by removing double spaces
		$points_string = preg_replace( '/\s+/', ' ', trim( $points_string ) );
		$scale         = $param_values['scale'] ?? 1.0;

		// separate every coordenate
		$points_array      = explode( ' ', trim( $points_string ) );
		$is_x              = true;
		$new_points_string = '';

		foreach ( $points_array as $coordenate ) {
			foreach ( $param_values as $param_name => $param_value ) {
				$coordenate = str_replace( "{{$param_name}}", (string) $param_value * $scale, $coordenate );
				$coordenate = str_replace( "{2*$param_name}", (string) ( 2 * (float) $param_value * $scale ), $coordenate );
			}

			if ( is_numeric( $coordenate ) ) {
				$coordenate = (float) $coordenate / $scale;

				if ( $is_x ) {
					$coordenate = (float) $coordenate + (float) $base_x_coord;
				}

				$is_x = ! $is_x;
			}

			$new_points_string .= ( strlen( $new_points_string ) ? ' ' : '' ) . $coordenate;
		}

		return $new_points_string;
	}

	/**
	 * Generates an SVG based on the provided parameters
	 *
	 * @return string The generated SVG markup
	 */
	public function generate_svg(): string {
		$points = $this->points_string;

		if ( false === strpos( $points, 'Z' ) ) {
			$points .= 'Z';
		}

		$extra_attrs = [
			'style' => 'position:absolute;overflow:hidden;',
		];

		$is_trajectory_path = Helpers::is_trajectory_path( $points );
		$shape_string       = $is_trajectory_path ? '<path d="%s" />' : '<polygon points="%s" />';
		$shape_string       = sprintf( $shape_string, $points );
		$pattern_id         = $this->get_pattern_id();
		$extra_attrs_string = array_reduce(
			array_keys( $extra_attrs ),
			fn( string $carry, string $attr ) => $carry . ' ' . sprintf( '%s="%s"', $attr, esc_attr( $extra_attrs[ $attr ] ) ),
			''
		);

		$this->svg_string = <<<SVG
<svg width="0" height="0" $extra_attrs_string>
	<defs>
		<clipPath id="$pattern_id" clipPathUnits="objectBoundingBox">
			$shape_string
		</clipPath>
	</defs>
</svg>
SVG;
		return $this->svg_string;
	}

	/**
	 * Helper. The id for the <clipPath id="<pattern-unique-id".
	 * It will also be referenced in the css at the clip-path: url(#<pattern-unique-id")
	 *
	 * @return string The pattern ID string.
	 */
	public function get_pattern_id(): string {
		return "pattern-$this->id";
	}

	/**
	 * Helper.
	 * From a set of points as a string in $this->points_string or the arg, ie ( 0 0, 1 1, 2 0, 3 1, 4 0 ).
	 * returns the last x point. (in this case (int) 4)
	 *
	 * @param string $string_points The points string to extract the last x coordinate from.
	 * @return float The last x coordinate value.
	 */
	public static function get_last_x_point( string $string_points ): float {
		$points = preg_split( '/[,\s]+/', trim( $string_points ) );
		$count  = count( $points );
		$last_x = $count >= 2 ? trim( $points[ $count - 2 ] ) : 0;
		$last_x = preg_replace( '/\s+/', ' ', trim( $last_x ) );

		return floatval( $last_x );
	}

	/**
	 * Simple helper. From '4.5 3.0' returns the required point. ( 4.5 if arg $coordenate is 'x')
	 *
	 * @param string  $pair_x_y   A string containing two numbers separated by space (e.g. "12 3")
	 * @param 'x'|'y' $coordenate Either 'x' or 'y' coordinate to extract from the pair
	 * @return float The extracted coordinate value
	 */
	public static function get_point_from_pair( string $pair_x_y, string $coordenate = 'x' ): float {
		$pair_x_y = trim( preg_replace( '/\s+/', ' ', $pair_x_y ) );
		$x_y      = explode( ' ', $pair_x_y );
		$count    = count( $x_y );
		if ( 'x' === $coordenate ) {
			return isset( $x_y[ $count - 2 ] ) ? floatval( $x_y[ $count - 2 ] ) : 0.0;
		}
		if ( 'y' === $coordenate ) {
			return isset( $x_y[ $count - 1 ] ) ? floatval( $x_y[ $count - 1 ] ) : 0.0;
		}
		return 0.0;
	}

	/**
	 * Generates points string from pattern by repeating it horizontally.
	 *
	 * @param float $offset_x The horizontal offset to apply to the pattern.
	 * @param float $offset_y The vertical offset to apply to the pattern.
	 * @return string The generated points string for the SVG shape.
	 */
	public function generate_points_string_from_pattern( float $offset_x = 0, float $offset_y = 0.1 ): string {
		$is_trajectory = Helpers::is_trajectory_path( trim( $this->pattern_data['pattern'] ) );
		$pattern_array = false !== $is_trajectory ? $is_trajectory : explode( ',', trim( $this->pattern_data['pattern'] ) );
		$scale         = $this->pattern_data['scale'] ?? 1;
		$start_point_x = 0 - $offset_x;
		$end_point_x   = 1 + $offset_x;
		$end_point_y   = 1 + $offset_y;

		$path_string = ( $is_trajectory ? 'M ' : '' ) . "$start_point_x 0";
		$x_size      = $this->pattern_width;

		$i = 0;
		do {
			$i++;
			$latest_x_point = self::get_last_x_point( $path_string );

			$coordenates_from_pattern = '';
			foreach ( $pattern_array as $points ) {
				$coordenates_from_pattern .= ' ' . self::replace_points_placeholders( $points, $latest_x_point, [
					'x_size' => $x_size,
					'y_size' => $this->pattern_height,
					'scale'  => $scale,
				] );
			}
			$path_string   .= $coordenates_from_pattern;
			$latest_x_point = self::get_last_x_point( $path_string );

		} while ( $latest_x_point < $end_point_x && $i < 15 );

		$path_string .= ( $is_trajectory ? 'L' : '' ) . " $end_point_x 0";
		$path_string .= ( $is_trajectory ? 'L' : '' ) . " $end_point_x $end_point_y";
		$path_string .= ( $is_trajectory ? 'L' : '' ) . " $start_point_x $end_point_y";
		$path_string .= $is_trajectory ? 'Z' : " $start_point_x 0";

		return $path_string;
	}
}
