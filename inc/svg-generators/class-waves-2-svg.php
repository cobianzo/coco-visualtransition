<?php
/**
 *
 * @package    VisualTransition
 */

namespace Coco\VisualTransition;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Waves_2_SVG extends SVG_Generator {

	/**
	 * The unique identifier for this SVG.
	 *
	 * @var string
	 */
	public string $id;

	public function generate_svg(): string {
		$pattern_id = parent::get_pattern_id( $this->id );
		$svg = <<<SVG
		<svg xmlns="http://www.w3.org/2000/svg">
			<defs>
				<clipPath id="$pattern_id" clipPathUnits="objectBoundingBox">
					<polygon points="-0.1 0, 0.5 0, 0.5 0.18, 1.1 0.18, 1.1 0, 1.1 0, 1.1 1.1, -0.1 1.1Z"></polygon>
				</clipPath>
			</defs>
		</svg>
SVG;

		return $svg;
	}

}
