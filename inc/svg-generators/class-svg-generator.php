<?php
/**
 * SVG Generator.
 *
 * From the pattern name and parameters, create the <svg> element to insert inline in the document.
 *
 * @package CocoVisualTransition
 */

namespace Coco\VisualTransition;

use Coco\VisualTransition\Helpers\Generic_Helpers;
use Coco\VisualTransition\Helpers\SVGPath_Helpers;

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
	 * Computed from $id above. It's the id for the <svg><clipPath id="pattern-<id>" or
	 * <svg><mask id="pattern-<id>" that we reference in the css with clip-path or mask: url(#pattern-<id>)
	 *
	 * @var string
	 */
	public string $pattern_id;

	/**
	 * Name of the pattern to be generated
	 *
	 * @var string
	 */
	public string $pattern_name = 'triangles';

	/**
	 * Height of the pattern in relative units. (% per 1)
	 * Correspnds to `patternHeight` attribute in the block.
	 * and converts into {y_size} placeholder in the pattern.
	 *
	 * @var float
	 */
	public float $pattern_height = 0.0;

	/**
	 * Width of the pattern in relative units. (% per 1)
	 * Correspnds to `patternWidth` attribute in the block.
	 * and converts into {x_size} placeholder in the pattern.
	 *
	 * @var float
	 */
	public float $pattern_width = 0.0;

	/**
	 * Pattern data loaded from `src/patterns.json` file.
	 *
	 * @var array<string, mixed>
	 */
	public array $pattern_data;

	/**
	 * Important prop. These is what matters, the svg and points for the shape.
	 *
	 * @var string
	 */
	public string $points_string;

	/**
	 * Important prop. The html `<svg ...> </svg>` string representation for the mask.
	 * We create it from the 'pattern' in 'patterns.json', or we can overwrite it in php.
	 *
	 * @var string
	 */
	public string $svg_string;

	/**
	 * Unit type for the pattern height (either '%' or 'px').
	 *
	 * @var string
	 */
	public string $type_pattern = '%';

	/**
	 * We generate %this->points, which define the shape of the mask.
	 *
	 * @param string               $pattern_name ie trianges, waves, squares ...
	 * @param string               $id something like vt_d0c75d9c-98fd-4f10-acec-bb95921d8211
	 * @param array<string, mixed> $atts parameters to build the shape of the mask, like height of the fret in percentage.
	 * @throws \InvalidArgumentException If pattern_name or id is empty or contains only whitespace.
	 */
	public function __construct( string $pattern_name = '', string $id = 'mi-greca', array $atts = [] ) {

		// Validate pattern_name
		if ( empty( trim( $pattern_name ) ) || empty( trim( $id ) ) ) {
			throw new \InvalidArgumentException( 'Args cannot be empty or contain only whitespace.' );
		}

		$this->id           = $id;
		$this->pattern_name = $pattern_name;
		$this->pattern_id   = $this->get_pattern_id();

		$this->pattern_data = Generic_Helpers::load_pattern_json( $pattern_name );

		// init to empty, we'll generate the values.
		$this->points_string = '';
		$this->svg_string    = '';

		// the $atts params which customizes the pattern mask.
		$this->pattern_height = ( isset( $atts['pattern-height'] ) && '' !== $atts['pattern-height'] )
			? (float) Generic_Helpers::to_float( $atts['pattern-height'] ) : 0.0;

		$this->pattern_width = ( isset( $atts['pattern-width'] ) && '' !== $atts['pattern-width'] )
			? (float) Generic_Helpers::to_float( $atts['pattern-width'] ) : 0.0;

        $this->type_pattern = ( isset( $atts['type-pattern'] ) && in_array( $atts['type-pattern'], [ '%', 'px' ], true ) ) ? $atts['type-pattern'] : '%';


		// these will create the $this->svg_string.
		// you can create a child class for a custom vt pattern
		// and overwrite any of these two functions to have more control.
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
	 * Generates an SVG based on the provided parameters
	 *
	 * @return string The generated SVG markup
	 */
	public function generate_svg(): string {
		$points = $this->points_string;

		$extra_attrs = [
			'style' => 'position:absolute;overflow:hidden;',
		];

		$is_trajectory_path = SVGPath_Helpers::is_trajectory_path( $points );
		$shape_string       = $is_trajectory_path ? '<path d="%s" />' : '<polygon points="%s" />';
		$shape_string       = sprintf( $shape_string, $points );
		$extra_attrs_string = array_reduce(
			array_keys( $extra_attrs ),
			fn( string $carry, string $attr ) => $carry . ' ' . sprintf( '%s="%s"', $attr, esc_attr( $extra_attrs[ $attr ] ) ),
			''
		);

		/**
		 * IMPORTANT:
		 *
		 * HERE is where we create the <svg>, it will be appended right after the div
		 */
		if ( ! isset( $this->pattern_data['type'] ) || 'clipPath' === $this->pattern_data['type'] ) {
			$this->svg_string = '<svg width="0" height="0" ' . $extra_attrs_string . '>
		<defs>
			<clipPath id="' . $this->pattern_id . '" clipPathUnits="objectBoundingBox">
				' . $shape_string . '
			</clipPath>
		</defs>
	</svg>';
		} else {

			$shape_string = str_replace( '/>', ' fill="rgba(255,255,255, 1)" />', $shape_string );

			$this->svg_string = '<svg width="0" height="0" ' . $extra_attrs_string . '>
		<defs>
			<mask id="' . $this->pattern_id . '" maskUnits="objectBoundingBox" maskContentUnits="objectBoundingBox">
				' . $shape_string . '
			</mask>
		</defs>
	</svg>';
		}
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
	 * Generates points string from pattern by repeating it horizontally.
	 * It returns coordenates always in % per 1.
	 * It can accept pattern coordenates in % per 100  (as long as $this->scale is 100)
	 *
	 * @param float $offset_x The horizontal offset to apply to the pattern in % per 1.
	 * @param float $offset_y The vertical offset to apply to the pattern  in % per 1..
	 * @return string The generated points string for the SVG shape.
	 */
	public function generate_points_string_from_pattern( float $offset_x = 0, float $offset_y = 0.1 ): string {
		$pattern       = is_string( $this->pattern_data['pattern'] )
			? $this->pattern_data['pattern']
			: '';
		$is_trajectory = SVGPath_Helpers::is_trajectory_path( trim( $pattern ) );
		$pattern_array = false !== $is_trajectory ? $is_trajectory : explode( ',', trim( $pattern ) );
		$scale         = isset( $this->pattern_data['scale'] ) && is_numeric( $this->pattern_data['scale'] ) ? (float) $this->pattern_data['scale'] : 1.0;
		$start_point_x = 0 - $offset_x;
		$end_point_x   = 1 + $offset_x;
		$end_point_y   = 1 + $offset_y;

		$path_string = ( $is_trajectory ? 'M ' : '' ) . "$start_point_x 0";
		$x_size      = $this->pattern_width;

		// $path_string building on every iteration.
		// we add one set of the 'pattern' on every iteration.
		$i = 0;
		do {
			$i++;
			$latest_x_point = SVGPath_Helpers::get_last_x_point( $path_string );

			$coordenates_from_pattern = '';
			foreach ( $pattern_array as $points ) {
				$coordenates_from_pattern .= ' ' . SVGPath_Helpers::replace_points_placeholders( $points, $latest_x_point, [
					'x_size' => $x_size,
					'y_size' => $this->pattern_height,
					'scale'  => $scale,
				] );
			}
			$path_string   .= $coordenates_from_pattern;
			$latest_x_point = SVGPath_Helpers::get_last_x_point( $path_string );

		} while ( $latest_x_point < $end_point_x && $i < 100 ); // the $i is just in case we get into infitive loop.

		// close the path by adding vertex in every corner of the container.
		$path_string .= ( $is_trajectory ? ' L' : '' ) . " $end_point_x 0";
		$path_string .= ( $is_trajectory ? ' L' : '' ) . " $end_point_x $end_point_y";
		$path_string .= ( $is_trajectory ? ' L' : '' ) . " $start_point_x $end_point_y";
		$path_string .= $is_trajectory ? ' Z' : " $start_point_x 0";

		return $path_string;
	}
}
