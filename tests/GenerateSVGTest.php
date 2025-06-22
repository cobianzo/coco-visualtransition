<?php

use Coco\VisualTransition\SVG_Generator;

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
		$svg_generator = new SVG_Generator('', '', []);

		// Test case 1: Empty points array
		$points = '';
		$result = $svg_generator->get_last_x_point($points);
		$this->assertEquals(0, $result, 'Should return 0 for empty points array');

		// Test case 2: Single point
		$points = "100 50";
		$result = $svg_generator->get_last_x_point($points);
		$this->assertEquals(100, $result, 'Should return x value of single point (100 in this case)');

		// Test case 3: Multiple points
		$points = "10 20, 30 40, 50 60";
		$result = $svg_generator->get_last_x_point($points);
		$this->assertEquals(50, $result, 'Should return x value of last point');
	}
}
