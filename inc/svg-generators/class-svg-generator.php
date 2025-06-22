<?php

namespace Coco\VisualTransition;

/**
 * The parent class that initializes the generic stuff,
 * and the child classes will extend it.
 *
 * We never call this class directly.
 */
class SVG_Generator {

	public $id;
	public $pattern_height = '50px';
	public $number_figures = '10';

	protected $pattern_data;
	protected $offset_x = 0.5;
	protected $offset_y = 0.5;
	protected $start_point_x;
	protected $end_point_x;
	protected $end_point_y;

	public $points_string = '';
	public $svg_string    = '';

	function __construct( string $pattern_name, string $id, $atts = [] ) {

		$this->id = $id;

		// the params which customizes the pattern mask.
		if ( isset( $atts['pattern-height'] ) ) {
			$this->pattern_height = $atts['pattern-height'];
		}
		if ( isset( $atts['number-figures'] ) ) {
			$this->number_figures = $atts['number-figures'];
		}

		// loads the pattern single
		$plugin_root       = plugin_dir_path( dirname( dirname( __FILE__ ) ) );
		$patterns_filename = $plugin_root . '/src/patterns.json';
		$patterns_json     = json_decode(file_get_contents($patterns_filename), true);
		$this->pattern_data = array_find( $patterns_json, fn( $pattern_data ) => $pattern_name === $pattern_data['value'] );

		// print_r( $this->pattern_data ); todelete

		if ( ! empty( $this->pattern_data['patternRepeat'] ) && 'repeat-x' === $this->pattern_data['patternRepeat'] ) {
			$this->points_string = $this->pattern_data['pattern'];
		}

		// calculate boundaies of the the clip mask, we'll use it in the child.
		$this->start_point_x = 0 - $this->offset_x;
		$this->end_point_x   = 1 + $this->offset_x;
		$this->end_point_y   = 1 + $this->offset_y;
	}

	/**
	 * Generates an SVG based on the provided parameters
	 *
	 * @return string The generated SVG markup
	 */
	public function generate_SVG(): string {
		$points = $this->points_string ?? '';
		$this->svg_string = <<<SVG
<svg width="0" height="0">
	<defs>
		<clipPath id="$this->id" clipPathUnits="objectBoundingBox">
			<polygon
				points="$points"
			/>
		</clipPath>
	</defs>
</svg>
SVG;
		return $this->svg_string;
	}


	// Helpers

	/**
	 * From a set of points as a string in $this0>points_string or the arg, ie ( 0 0, 1 1, 2 0, 3 1, 4 0 ).
	 * returns the last x point. (in this case (int) 4)
	 *
	 * @return void
	 */
	public function get_last_x_point( ?string $overwrite_points ): int {
		$string_points = $overwrite_points?? $this->points_string;

		$points     = explode( ',', trim( $string_points ) );
		$last_point = trim( end( $points ) );

		$last_x = explode( ' ', $last_point )[0];
		$last_x = intval( $last_x );

		return $last_x;
	}

}