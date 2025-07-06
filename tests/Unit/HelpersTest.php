<?php

// The tests: It's broken after the refactor of the genreator
// Needs refactor here too

use Coco\VisualTransition\SVG_Generator;
use Coco\VisualTransition\Helpers\Generic_Helpers;
use Coco\VisualTransition\Helpers\SVGPath_Helpers;

/**
 * usage
 * npm run test:php tests/Unit/HelpersTest.php
 * or
 * npm run test:php:single
 */
class HelpersTest extends WP_UnitTestCase
{

	/**
	 *
	 */
	public function test_transform_coordenates() {
		$points_string = 'C 0 100 200 300 400';;
		// X coords sohuld double value
		$fn_transform_x = function( $coordenate ) {
			return (float) $coordenate * 2;
		};
		// Y coords sohuld divide value by 2
		$fn_transform_y = function( $coordenate ) {
			return (float) $coordenate / 2;
		};

		// simplest check
		$points = SVGPath_Helpers::apply_transform_to_path_coordenates( $points_string, $fn_transform_x, $fn_transform_y );
		$this->assertEquals( 'C 0 50 400 150 800', $points  );

		// two coordenates and odds spaces.
		$points_string = '  M 50    55  C 0 100 200 300 400 40 L   1050 1100  ';
		$points = SVGPath_Helpers::apply_transform_to_path_coordenates( $points_string, $fn_transform_x, $fn_transform_y );
		$this->assertEquals( 'M 100 27.5 C 0 50 400 150 800 20 L 2100 550', $points );

		// Test a polygon path, not trajectory (there are no letters, only pairs of coords)
		$points_string = ' 10  20 7 10  ';
		$points = SVGPath_Helpers::apply_transform_to_path_coordenates( $points_string, $fn_transform_x, $fn_transform_y );
		$this->assertEquals( '20 10 14 5', $points );
	}
}
