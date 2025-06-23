<?php

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

	public $id;
	public $pattern_name = 'triangles';
	public $pattern_height = '0.05';
	public $number_figures = '10';

	protected $pattern_data;
	protected $offset_x = 0.5;
	protected $offset_y = 0.5;
	protected $start_point_x;
	protected $end_point_x;
	protected $end_point_y;

	/**
	 * These is what matters, the svg and points for the shape.
	 *
	 * @var string
	 */
	public $points_string = '';
	public $svg_string    = '';

	/**
	 * We generate %this->points, which define the shape of the mask.
	 *
	 * @param string $pattern_name ie trianges, waves, squares ...
	 * @param string $id something like vt_d0c75d9c-98fd-4f10-acec-bb95921d8211
	 * @param array  $atts parameters to build the shape of the mask, like height of the fret in percentage.
	 */
	function __construct( string $pattern_name = '', string $id = 'mi-greca', $atts = [] ) {

		$this->id = $id;
		$this->pattern_name = $pattern_name;

		// the params which customizes the pattern mask.
		if ( isset( $atts['pattern-height'] ) ) {
			$this->pattern_height = $atts['pattern-height'];
		}
		if ( isset( $atts['number-figures'] ) ) {
			$this->number_figures = $atts['number-figures'];
		}

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
			$plugin_root        = plugin_dir_path( dirname( __DIR__ ) );
			$patterns_filename  = $plugin_root . '/src/patterns.json';
			$patterns_json      = json_decode( file_get_contents( $patterns_filename ), true );
			$this->pattern_data = array_find( $patterns_json, fn( $pattern_data ) => $this->pattern_name === $pattern_data['value'] );

			if ( ! empty( $this->pattern_data['patternRepeat'] ) && 'repeat-x' === $this->pattern_data['patternRepeat'] ) {
				// prepare the pattern for single figure using

				// calculate boundaies of the the clip mask, we'll use it in the child.
				$this->start_point_x = 0 - $this->offset_x;
				$this->end_point_x   = 1 + $this->offset_x;
				$this->end_point_y   = 1 + $this->offset_y;

				// now we loop the pattern moving right until getting the 100% (end_point_x).
				$this->points_string = self::generate_points_string_from_pattern( $this->pattern_data['pattern'], $this->number_figures, $this->pattern_height, 0.1, 0.1 );
			}
		}
		return $this->points_string;
	}

	/**
	 * Generates an SVG based on the provided parameters
	 *
	 * @return string The generated SVG markup
	 */
	public function generate_SVG(): string {
		$points = $this->points_string ?? '';

		// sanitization
		if ( false === strpos( $points, 'Z' ) ) {
			$points .= 'Z';
		}

		// if the points use beizer vertex, we use path d, otherwise we use polygon, for right lines.
		$shape_string = '<polygon points="%s" />';
		if ( false !== strpos( $points, 'C ' ) || false !== strpos( $points, 'S' ) ) {
			$shape_string = '<path d="%s" />';
		}

		$shape_string = sprintf( $shape_string, $points ); // <po

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


	// Helpers

	/**
	 * Helper.
	 * From a set of points as a string in $this0>points_string or the arg, ie ( 0 0, 1 1, 2 0, 3 1, 4 0 ).
	 * returns the last x point. (in this case (int) 4)
	 *
	 * @return void
	 */
	public function get_last_x_point( ?string $overwrite_points ): float {
		$string_points = $overwrite_points ?? $this->points_string;

		$points     = explode( ',', trim( $string_points ) );
		$last_point = trim( end( $points ) );
		$last_point = preg_replace( '/\s+/', ' ', trim( $last_point ) );


		$last_x = explode( ' ', $last_point )[0];
		$last_x = floatval( $last_x );

		return $last_x;
	}

	/**
	 * Helper
	 * from a given pattern string, using the placeholders for the gap of the x and y coords,
	 * we repeat the pattern a number of times. Example of pattern: "0 0, {x_size} 0, {x_size} {y_size}, {2*x_size} {y_size}"
	 * Will return something like: "0 0, 0.25 0, 0.25 0.05, 0.5 0.05, "
	 *
	 * @param string  $pattern
	 * @param integer $number_figures
	 * @param float   $pattern_height
	 * @param float   $offset_x
	 * @param float   $offset_y
	 * @return string
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

				$points = str_replace( '{x_size}', $point_x_step, $points );
				$points = str_replace( '{2*x_size}', 2 * $point_x_step, $points );
				$points = str_replace( '{y_size}', $pattern_height, $points );

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
}
