<?php

class SVG_Generator {

	public $id;
	public $pattern_height = '50px';
	public $number_figures = '10';

	protected $offset_x = 0.5;
	protected $offset_y = 0.5;
	protected $start_point_x;
	protected $end_point_x;
	protected $end_point_y;

	public $points_string = '';
	public $svg_string    = '';

	function __construct( string $id, $atts = [] ) {
		$this->id = $id;
		if ( isset( $atts['pattern-height'] ) ) {
			$this->pattern_height = $atts['pattern-height'];
		}
		if ( isset( $atts['number-figures'] ) ) {
			$this->number_figures = $atts['number-figures'];
		}


		// calculate boundaies of the the clip mask
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
}