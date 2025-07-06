<?php

// The tests: It's broken after the refactor of the genreator
// Needs refactor here too

use Coco\VisualTransition\SVG_Generator;
use Coco\VisualTransition\Helpers\Generic_Helpers;
use Coco\VisualTransition\Helpers\SVGPath_Helpers;

/**
 * usage
 * npm run test:php tests/Unit/GenerateSVGTest.php
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

		// Test case 4: Decimals and spaces bothering
		$points = "  0.01 0.4,   0.30 0.040,   0.50 0.460";
		$result = SVGPath_Helpers::get_last_x_point($points);
		$this->assertEquals(0.5, $result, 'Should return 0.50 value of last point');
	}

	/**
	 * Test generate_points_string_from_pattern method
	 */
	public function test_generate_points_string_from_pattern_polygon_type()
	{
		// Create an instance of SVG_Generator with a test pattern
		$svg_generator = new SVG_Generator('test-pattern', 'test-id', [
			'pattern-height' => 0.5,
			'pattern-width' => 0.3
		]);

		// Test case 1: Simple pattern with single figure
		// We need to set up the pattern data manually since we're testing the method directly
		$svg_generator->pattern_data = [
			'pattern' => "0 0, {x_size} {y_size}",
			'scale' => 1.0
		];
		$svg_generator->pattern_width = 0.3;
		$svg_generator->pattern_height = 0.5;

		$result = $svg_generator->generate_points_string_from_pattern(0, 0);
		$expected = "0 0 0 0 0.3 0.5 0.3 0 0.6 0.5 0.6 0 0.9 0.5 0.9 0 1.2 0.5 1 0 1 1 0 1 0 0";
		echo "Pattern: " . $svg_generator->pattern_data['pattern'] . "\n";
		$this->assertEquals($expected, $result, 'First test: Should generate correct points for single figure');

		// Test case 2: Pattern with multiple figures
		$svg_generator->pattern_data = [
			'pattern' => "{x_size} {y_size}, {2*x_size} 0",
			'scale' => 1.0
		];
		$svg_generator->pattern_width = 0.8;
		$svg_generator->pattern_height = 0.5;

		$result = $svg_generator->generate_points_string_from_pattern(0, 0);
		$expected = "0 0 0.8 0.5 1.6 0 1 0 1 1 0 1 0 0";
		echo "Pattern: " . $svg_generator->pattern_data['pattern'] . "\n";
		$this->assertEquals($expected, $result, 'Should generate correct points for multiple figures');

		// Test case 3: Pattern with 2*x_size placeholder
		$svg_generator->pattern_data = [
			'pattern' => "{x_size} 0, {x_size} {y_size}, {2*x_size} {y_size}, {2*x_size} 0",
			'scale' => 1.0
		];
		$svg_generator->pattern_width = 0.2;
		$svg_generator->pattern_height = 0.5;

		$result = $svg_generator->generate_points_string_from_pattern(0, 0);
		$expected = "0 0 0.2 0 0.2 0.5 0.4 0.5 0.4 0 0.6 0 0.6 0.5 0.8 0.5 0.8 0 1 0 1 0.5 1.2 0.5 1.2 0 1 0 1 1 0 1 0 0";
		echo "Pattern: " . $svg_generator->pattern_data['pattern'] . "\n";
		$this->assertEquals($expected, $result, 'Should handle 2*x_size placeholder correctly');

		// Test case 4: Pattern with offsets
		$svg_generator->pattern_data = [
			'pattern' => "{x_size} 0, {x_size} {y_size}",
			'scale' => 1.0
		];
		$svg_generator->pattern_width = 0.5;
		$svg_generator->pattern_height = 0.3;

		$result = $svg_generator->generate_points_string_from_pattern(0.1, 0.1);
		$expected = "-0.1 0 0.4 0 0.4 0.3 0.9 0 0.9 0.3 1.4 0 1.4 0.3 1.1 0 1.1 1.1 -0.1 1.1 -0.1 0";
		echo "Pattern: " . $svg_generator->pattern_data['pattern'] . "\n";
		$this->assertEquals($expected, $result, 'Should handle offsets correctly');
	}

	/**
	 * trajectory type are the paths with beizer commands like 'C', 'M' ...
	 */
	public function test_generate_points_string_from_pattern_trajectory_type() {
		// Create an instance of SVG_Generator with a test pattern
		$svg_generator = new SVG_Generator('test-pattern', 'test-id', [
			'pattern-height' => 0.5,
			'pattern-width' => 0.3
		]);

		// Test case 1: Trajectory pattern with L commands
		$svg_generator->pattern_data = [
			'pattern' => "L 0 {y_size} L {x_size} 0 L {2*x_size} {y_size}",
			'scale' => 1.0
		];
		$svg_generator->pattern_width = 0.3;
		$svg_generator->pattern_height = 0.5;

		$result = $svg_generator->generate_points_string_from_pattern(0, 0);
		$expected = "M 0 0 L 0 0.5 L 0.3 0 L 0.6 0.5 L 0.6 0.5 L 0.9 0 L 1.2 0.5 L 1 0 L 1 1 L 0 1 Z";
		echo "Pattern: " . $svg_generator->pattern_data['pattern'] . "\n";
		$this->assertEquals($expected, $result, 'T1. Should generate correct trajectory path for L commands pattern');

		// Test case 2: Trajectory pattern with different dimensions
		$svg_generator->pattern_data = [
			'pattern' => "L 0 {y_size} L {x_size} 0 L {2*x_size} {y_size}",
			'scale' => 1.0
		];
		$svg_generator->pattern_width = 0.4;
		$svg_generator->pattern_height = 0.6;

		$result = $svg_generator->generate_points_string_from_pattern(0, 0);
		$expected = "M 0 0 L 0 0.6 L 0.4 0 L 0.8 0.6 L 0.8 0.6 L 1.2 0 L 1.6 0.6 L 1 0 L 1 1 L 0 1 Z";
		echo "Pattern: " . $svg_generator->pattern_data['pattern'] . "\n";
		$this->assertEquals($expected, $result, 'T2. Should generate correct trajectory path with different dimensions');

		// Test case 3: Trajectory pattern with scale
		$svg_generator->pattern_data = [
			'pattern' => "L 0 {y_size} L {x_size} 10 L {2*x_size} {y_size}",
			'scale' => 100
		];
		$svg_generator->pattern_width = 0.3;
		$svg_generator->pattern_height = 0.15;

		$result = $svg_generator->generate_points_string_from_pattern(0, 0);
		$expected = "M 0 0 L 0 0.15 L 0.3 0.1 L 0.6 0.15 L 0.6 0.15 L 0.9 0.1 L 1.2 0.15 L 1 0 L 1 1 L 0 1 Z";
		echo "Pattern: " . $svg_generator->pattern_data['pattern'] . "\n";
		$this->assertEquals($expected, $result, 'T3. Should generate correct trajectory path with scale applied');

		// Test case 4: Trajectory pattern with offsets
		$svg_generator->pattern_data = [
			'pattern' => "L 0 {y_size} L {x_size} 0 L {2*x_size} {y_size}",
			'scale' => 1.0
		];
		$svg_generator->pattern_width = 0.3;
		$svg_generator->pattern_height = 0.5;

		$result = $svg_generator->generate_points_string_from_pattern(0.1, 0.1);
		$expected = "M -0.1 0 L -0.1 0.5 L 0.2 0 L 0.5 0.5 L 0.5 0.5 L 0.8 0 L 1.1 0.5 L 1.1 0 L 1.1 1.1 L -0.1 1.1 Z";
		echo "Pattern: " . $svg_generator->pattern_data['pattern'] . "\n";
		$this->assertEquals($expected, $result, 'T4. Should handle offsets correctly in trajectory path');
	}
}
