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

	public function test_get_max_y_from_path() {
		// Caso simple: trayectoria con varios valores Y
		$path = 'M 0 10 L 5 20 L 10 15';
		$this->assertEquals( 20.0, SVGPath_Helpers::get_max_y_from_path( $path ) );

		// Caso poligonal: solo pares de coordenadas
		$polygon = '1 2 3 8 5 4 7 6';
		$this->assertEquals( 8.0, SVGPath_Helpers::get_max_y_from_path( $polygon ) );

		// Caso con valores negativos
		$path_neg = 'M 0 -5 L 2 -10 L 4 0';
		$this->assertEquals( 0.0, SVGPath_Helpers::get_max_y_from_path( $path_neg ) );

		// Caso borde: string vacío
		$this->assertEquals( 0.0, SVGPath_Helpers::get_max_y_from_path( '' ) );

		// Caso borde: un solo punto
		$superpath = 'M20 10c0-5.51-4.49-10-10-10C4.48 0 0 4.49 0 10c0 5.52 4.48 10 10 10 5.51 0 10-4.48 10-10zM7.78 15.37L4.37 6.22c.55-.02 1.17-.08 1.17-.08.5-.06.44-1.13-.06-1.11 0 0-1.45.11-2.37.11-.18 0-.37 0-.58-.01C4.12 2.69 6.87 1.11 10 1.11c2.33 0 4.45.87 6.05 2.34-.68-.11-1.65.39-1.65 1.58 0 .74.45 1.36.9 2.1.35.61.55 1.36.55 2.46 0 1.49-1.4 5-1.4 5l-3.03-8.37c.54-.02.82-.17.82-.17.5-.05.44-1.25-.06-1.22 0 0-1.44.12-2.38.12-.87 0-2.33-.12-2.33-.12-.5-.03-.56 1.2-.06 1.22l.92.08 1.26 3.41zM17.41 10c.24-.64.74-1.87.43-4.25.7 1.29 1.05 2.71 1.05 4.25 0 3.29-1.73 6.24-4.4 7.78.97-2.59 1.94-5.2 2.92-7.78zM6.1 18.09C3.12 16.65 1.11 13.53 1.11 10c0-1.3.23-2.48.72-3.59C3.25 10.3 4.67 14.2 6.1 18.09zm4.03-6.63l2.58 6.98c-.86.29-1.76.45-2.71.45-.79 0-1.57-.11-2.29-.33.81-2.38 1.62-4.74 2.42-7.1z';
		$this->assertEquals( 18.09, SVGPath_Helpers::get_max_y_from_path( $superpath ) );
	}

	public function test_scale_y_to_unit_interval() {
		// Simple trajectory path
		$path = 'M 0 10 L 5 20 L 10 15';
		$scaled = SVGPath_Helpers::scale_y_to_unit_interval( $path );
		// max Y is 20, so Y values should be divided by 20
		$this->assertEquals( 'M 0 0.5 L 5 1 L 10 0.75', $scaled );

		// Polygon path
		$polygon = '1 2 3 8 5 4 7 6';
		$scaled = SVGPath_Helpers::scale_y_to_unit_interval( $polygon );
		// max Y is 8, so Y values should be divided by 8
		$this->assertEquals( '1 0.25 3 1 5 0.5 7 0.75', $scaled );

		// Path with negative Y values
		$path_neg = 'M 0 -5 L 2 -10 L 4 0';
		$scaled = SVGPath_Helpers::scale_y_to_unit_interval( $path_neg );
		// max Y is 0, so should return original path
		$this->assertEquals( $path_neg, $scaled );

		// Empty string
		$scaled = SVGPath_Helpers::scale_y_to_unit_interval( '' );
		$this->assertEquals( '', $scaled );

		// Single point
		$single = 'M 0 10';
		$scaled = SVGPath_Helpers::scale_y_to_unit_interval( $single );
		// max Y is 10, so Y should be 1
		$this->assertEquals( 'M 0 1', $scaled );

	// Test with multiplier_factor applied
	$path = 'M 0 10 L 5 20 L 10 15';
	// max Y is 20, so Y values should be divided by 20 and then multiplied by 2 (multiplier_factor)
	$scaled = SVGPath_Helpers::scale_y_to_unit_interval( $path, 2.0 );
	// Y values: 10/20*2 = 1, 20/20*2 = 2, 15/20*2 = 1.5
	$this->assertEquals( 'M 0 1 L 5 2 L 10 1.5', $scaled );

	// Another test: polygon with multiplier_factor 0.5
	$polygon = '1 2 3 8 5 4 7 6';
	$scaled = SVGPath_Helpers::scale_y_to_unit_interval( $polygon, 0.5 );
	// max Y is 8, so Y values: 2/8*0.5=0.125, 8/8*0.5=0.5, 4/8*0.5=0.25, 6/8*0.5=0.375
	$this->assertEquals( '1 0.125 3 0.5 5 0.25 7 0.375', $scaled );

	}
}
