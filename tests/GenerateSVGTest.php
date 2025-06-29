<?php

// The tests: It's broken after the refactor of the genreator
// Needs refactor here too

use Coco\VisualTransition\SVG_Generator;
use Coco\VisualTransition\Helpers\Generic_Helpers;
use Coco\VisualTransition\Helpers\SVGPath_Helpers;

/**
 * usage
 * npm run test:php tests/GenerateSVGTest.php
 * or
 * npm run test:php:single
 */
class GenerateSVGTest extends WP_UnitTestCase
{

	/**
	 * Test get_last_x_point method
	 */
	public function test_get_last_x_point()
	{
		// Create an instance of SVG_Generator
		$svg_generator = new SVG_Generator('waves', 'my-id', []);

		// Test case 1: Empty points array
		$points = '';
		$result = SVGPath_Helpers::get_last_x_point($points);
		$this->assertEquals(0, $result, 'Should return 0 for empty points array');

		// Test case 2: Single point
		$points = "100 50";
		$result = SVGPath_Helpers::get_last_x_point($points);
		$this->assertEquals(100, $result, 'Should return x value of single point (100 in this case)');

		// Test case 3: Multiple points
		$points = "10 20, 30 40, 50 60";
		$result = SVGPath_Helpers::get_last_x_point($points);
		$this->assertEquals(50, $result, 'Should return x value of last point');

		// Test case 3: Decimals and spaces bothering
		$points = "  0.01 0.4,   0.30 0.040,   0.50 0.460";
		$result = SVGPath_Helpers::get_last_x_point($points);

		$this->assertEquals(0.5, $result, 'Should return 0.50 value of last point');
	}


/**
 * Test generate_points_string_from_pattern method
 */
public function test_generate_points_string_from_pattern()
{
    // Create an instance of SVG_Generator
    $svg_generator = new SVG_Generator('', '', []);

    // // Test case 1: Simple pattern with single figure
    $pattern = "0 0, {x_size} {y_size}";
    $result = SVGPath_Helpers::generate_points_string_from_pattern($pattern, 0.3, 0.5, 0, 0);
    $this->assertEquals("0 0, 0 0, 0.3 0.5, 0.3 0, 0.6 0.5, 0.6 0, 0.9 0.5, 0.9 0, 1.2 0.5, 1 0.5, 1 1, 0 1Z", $result, 'First test: Should generate correct points for single figure');

    // // Test case 2: Pattern with multiple figures
    $pattern = "{x_size} {y_size}, {2*x_size} 0";
    $result = SVGPath_Helpers::generate_points_string_from_pattern($pattern, 0.8, 0.5, 0, 0);
    $this->assertEquals("0 0, 0.8 0.5, 1.6 0, 1 0, 1 1, 0 1Z", $result, 'Should generate correct points for multiple figures');

    // Test case 3: Pattern with 2*x_size placeholder
    $pattern = "{x_size} 0, {x_size} {y_size}, {2*x_size} {y_size}, {2*x_size} 0";
    $result = SVGPath_Helpers::generate_points_string_from_pattern($pattern, 0.2, 0.5, 0, 0);
    $this->assertEquals("0 0, 0.2 0, 0.2 0.5, 0.4 0.5, 0.4 0, 0.6 0, 0.6 0.5, 0.8 0.5, 0.8 0, 1 0, 1 0.5, 1.2 0.5, 1.2 0, 1 0, 1 1, 0 1Z", $result, 'Should handle 2*x_size placeholder correctly');

    // Test case 4: Pattern with offsets
    $pattern = "{x_size} 0, {x_size} {y_size}";
    $result = SVGPath_Helpers::generate_points_string_from_pattern($pattern, 0.5, 0.3, 0.1, 0.1);
    $this->assertEquals("-0.1 0, 0.4 0, 0.4 0.3, 0.9 0, 0.9 0.3, 1.4 0, 1.4 0.3, 1.1 0.3, 1.1 1.1, -0.1 1.1Z", $result, 'Should handle offsets correctly');
}
}
