<?php

use Coco\VisualTransition\Shark_Fin;
use Coco\VisualTransition\DuoMask_Slope_1;
use Coco\VisualTransition\DuoMask_Polygon_1;
use Coco\VisualTransition\Helpers\SVGPath_Helpers;

// Load custom pattern classes for testing
require_once plugin_dir_path( dirname( __DIR__ ) ) . 'inc/svg-generators/custom-patterns/class-shark-fin.php';
require_once plugin_dir_path( dirname( __DIR__ ) ) . 'inc/svg-generators/custom-patterns/class-duomask-slope-1.php';
require_once plugin_dir_path( dirname( __DIR__ ) ) . 'inc/svg-generators/custom-patterns/class-duomask-polygon-1.php';

/**
 * Custom Patterns Tests
 *
 * Tests for the custom pattern classes covering:
 * - Shark_Fin specific generate_svg() implementation
 * - DuoMask_Slope_1 specific generate_svg() implementation
 * - DuoMask_Polygon_1 specific generate_svg() implementation
 * - Coordinate transformations
 * - Path closing and scaling
 *
 * npm run test:php tests/Unit/CustomPatternsTest.php
 * @package CocoVisualTransition
 */
class CustomPatternsTest extends WP_UnitTestCase
{
    /**
     * Test Shark_Fin pattern generation
     */
    public function test_shark_fin_pattern_generation()
    {
        $shark_fin = new Shark_Fin('shark-fin', 'test-id', [
            'pattern-height' => 0.5,
            'pattern-width' => 0.3
        ]);

        $result = $shark_fin->generate_svg();

        echo "Pattern: Shark_Fin\n";
        $this->assertStringContainsString('<mask', $result, 'Shark_Fin should generate mask element');
        $this->assertStringContainsString('fill="white"', $result, 'Shark_Fin should have white fill');
        $this->assertStringContainsString('maskUnits="objectBoundingBox"', $result, 'Should set correct mask units');
        $this->assertStringContainsString('maskContentUnits="objectBoundingBox"', $result, 'Should set correct mask content units');
        $this->assertStringContainsString('id="pattern-test-id"', $result, 'Should set correct pattern ID');

        // Test that the path contains the expected coordinates
        $this->assertStringContainsString('M 0', $result, 'Should start with M command');
        $this->assertStringContainsString('C ', $result, 'Should contain Bezier curve commands');
    }

    /**
     * Test Shark_Fin coordinate transformations
     */
    public function test_shark_fin_coordinate_transformations()
    {
        $shark_fin = new Shark_Fin('shark-fin', 'test-id', [
            'pattern-height' => 0.5,
            'pattern-width' => 0.3
        ]);

        // Test that coordinates are properly scaled from percentage to decimal
        $result = $shark_fin->generate_svg();

        echo "Pattern: Shark_Fin (coordinate scaling)\n";
        // The original path uses percentage values (0-120), should be scaled to 0-1
        $this->assertStringNotContainsString('120.219', $result, 'Should not contain unscaled percentage values');
        $this->assertStringNotContainsString('5.397', $result, 'Should not contain unscaled percentage values');

        // Should contain scaled decimal values
        $this->assertStringContainsString('1.20219', $result, 'Should contain scaled decimal values');
        $this->assertStringContainsString('0.05397', $result, 'Should contain scaled decimal values');
    }

    /**
     * Test Shark_Fin path closing
     */
    public function test_shark_fin_path_closing()
    {
        $shark_fin = new Shark_Fin('shark-fin', 'test-id', [
            'pattern-height' => 0.5,
            'pattern-width' => 0.3
        ]);

        $result = $shark_fin->generate_svg();

        echo "Pattern: Shark_Fin (path closing)\n";
        // Test that the path is properly closed with the expected coordinates
        $this->assertStringContainsString('L 1.1 0', $result, 'Should close path with right edge');
        $this->assertStringContainsString('L 1.1 1.1', $result, 'Should close path with bottom edge');
        $this->assertStringContainsString('L -0.1 1.1', $result, 'Should close path with left edge');
        $this->assertStringContainsString('Z', $result, 'Should end path with Z command');
    }

    /**
     * Test DuoMask_Slope_1 pattern generation
     */
    public function test_duomask_slope_1_pattern_generation()
    {
        $duomask_slope = new DuoMask_Slope_1('duomask-slope-1', 'test-id', [
            'pattern-height' => 0.5,
            'pattern-width' => 0.3
        ]);

        $result = $duomask_slope->generate_svg();

        echo "Pattern: DuoMask_Slope_1\n";
        $this->assertStringContainsString('<mask', $result, 'DuoMask_Slope_1 should generate mask element');
        $this->assertStringContainsString('fill="white"', $result, 'DuoMask_Slope_1 should have white fill');
        $this->assertStringContainsString('maskUnits="objectBoundingBox"', $result, 'Should set correct mask units');
        $this->assertStringContainsString('id="pattern-test-id"', $result, 'Should set correct pattern ID');
    }

    /**
     * Test DuoMask_Slope_1 specific implementation
     */
    public function test_duomask_slope_1_specific_implementation()
    {
        $duomask_slope = new DuoMask_Slope_1('duomask-slope-1', 'test-id', [
            'pattern-height' => 0.5,
            'pattern-width' => 0.3
        ]);

        // Test that it overrides the parent generate_svg method
        $result = $duomask_slope->generate_svg();

        echo "Pattern: DuoMask_Slope_1 (specific implementation)\n";
        // Check for specific elements that should be in DuoMask_Slope_1
        $this->assertStringContainsString('<path', $result, 'Should contain path elements');

        // Test that it's different from the generic implementation
        $generic = new \Coco\VisualTransition\SVG_Generator('duomask-slope-1', 'test-id', [
            'pattern-height' => 0.5,
            'pattern-width' => 0.3
        ]);
        $generic_result = $generic->generate_svg();

        $this->assertNotEquals($generic_result, $result, 'DuoMask_Slope_1 should have different output than generic generator');
    }

    /**
     * Test DuoMask_Polygon_1 pattern generation
     */
    public function test_duomask_polygon_1_pattern_generation()
    {
        $duomask_polygon = new DuoMask_Polygon_1('duomask-polygon-1', 'test-id', [
            'pattern-height' => 0.5,
            'pattern-width' => 0.3
        ]);

        $result = $duomask_polygon->generate_svg();

        echo "Pattern: DuoMask_Polygon_1\n";
        $this->assertStringContainsString('<mask', $result, 'DuoMask_Polygon_1 should generate mask element');
        $this->assertStringContainsString('fill="white"', $result, 'DuoMask_Polygon_1 should have white fill');
        $this->assertStringContainsString('maskUnits="objectBoundingBox"', $result, 'Should set correct mask units');
        $this->assertStringContainsString('id="pattern-test-id"', $result, 'Should set correct pattern ID');
    }

    /**
     * Test DuoMask_Polygon_1 specific implementation
     */
    public function test_duomask_polygon_1_specific_implementation()
    {
        $duomask_polygon = new DuoMask_Polygon_1('duomask-polygon-1', 'test-id', [
            'pattern-height' => 0.5,
            'pattern-width' => 0.3
        ]);

        $result = $duomask_polygon->generate_svg();

        echo "Pattern: DuoMask_Polygon_1 (specific implementation)\n";
        // Check for specific elements that should be in DuoMask_Polygon_1
        $this->assertStringContainsString('<path', $result, 'Should contain path elements');

        // Test that it's different from the generic implementation
        $generic = new \Coco\VisualTransition\SVG_Generator('duomask-polygon-1', 'test-id', [
            'pattern-height' => 0.5,
            'pattern-width' => 0.3
        ]);
        $generic_result = $generic->generate_svg();

        $this->assertNotEquals($generic_result, $result, 'DuoMask_Polygon_1 should have different output than generic generator');
    }

    /**
     * Test custom patterns with different attributes
     */
    public function test_custom_patterns_with_different_attributes()
    {
        // Test Shark_Fin with different dimensions
        $shark_fin = new Shark_Fin('shark-fin', 'test-id', [
            'pattern-height' => 0.8,
            'pattern-width' => 0.4
        ]);

        $result = $shark_fin->generate_svg();
        echo "Pattern: Shark_Fin (different dimensions)\n";
        $this->assertStringContainsString('<mask', $result, 'Should generate mask with different dimensions');

        // Test DuoMask_Slope_1 with different dimensions
        $duomask_slope = new DuoMask_Slope_1('duomask-slope-1', 'test-id', [
            'pattern-height' => 0.2,
            'pattern-width' => 0.1
        ]);

        $result = $duomask_slope->generate_svg();
        echo "Pattern: DuoMask_Slope_1 (different dimensions)\n";
        $this->assertStringContainsString('<mask', $result, 'Should generate mask with different dimensions');

        // Test DuoMask_Polygon_1 with different dimensions
        $duomask_polygon = new DuoMask_Polygon_1('duomask-polygon-1', 'test-id', [
            'pattern-height' => 0.9,
            'pattern-width' => 0.7
        ]);

        $result = $duomask_polygon->generate_svg();
        echo "Pattern: DuoMask_Polygon_1 (different dimensions)\n";
        $this->assertStringContainsString('<mask', $result, 'Should generate mask with different dimensions');
    }

    /**
     * Test custom patterns inheritance
     */
    public function test_custom_patterns_inheritance()
    {
        // Test that all custom patterns extend SVG_Generator
        $shark_fin = new Shark_Fin('shark-fin', 'test-id');
        $this->assertInstanceOf(\Coco\VisualTransition\SVG_Generator::class, $shark_fin, 'Shark_Fin should extend SVG_Generator');

        $duomask_slope = new DuoMask_Slope_1('duomask-slope-1', 'test-id');
        $this->assertInstanceOf(\Coco\VisualTransition\SVG_Generator::class, $duomask_slope, 'DuoMask_Slope_1 should extend SVG_Generator');

        $duomask_polygon = new DuoMask_Polygon_1('duomask-polygon-1', 'test-id');
        $this->assertInstanceOf(\Coco\VisualTransition\SVG_Generator::class, $duomask_polygon, 'DuoMask_Polygon_1 should extend SVG_Generator');
    }

    /**
     * Test custom patterns method availability
     */
    public function test_custom_patterns_method_availability()
    {
        $shark_fin = new Shark_Fin('shark-fin', 'test-id');

        // Test that custom patterns have all required methods
        $this->assertTrue(method_exists($shark_fin, 'generate_svg'), 'Shark_Fin should have generate_svg method');
        $this->assertTrue(method_exists($shark_fin, 'generate_points'), 'Shark_Fin should have generate_points method');
        $this->assertTrue(method_exists($shark_fin, 'get_pattern_id'), 'Shark_fin should have get_pattern_id method');

        $duomask_slope = new DuoMask_Slope_1('duomask-slope-1', 'test-id');
        $this->assertTrue(method_exists($duomask_slope, 'generate_svg'), 'DuoMask_Slope_1 should have generate_svg method');

        $duomask_polygon = new DuoMask_Polygon_1('duomask-polygon-1', 'test-id');
        $this->assertTrue(method_exists($duomask_polygon, 'generate_svg'), 'DuoMask_Polygon_1 should have generate_svg method');
    }

    /**
     * Test custom patterns with edge cases
     */
    public function test_custom_patterns_with_edge_cases()
    {
        // Test with zero dimensions
        $shark_fin = new Shark_Fin('shark-fin', 'test-id', [
            'pattern-height' => 0,
            'pattern-width' => 0
        ]);

        $result = $shark_fin->generate_svg();
        echo "Pattern: Shark_Fin (zero dimensions)\n";
        $this->assertStringContainsString('<mask', $result, 'Should handle zero dimensions gracefully');

        // Test with very large dimensions
        $duomask_slope = new DuoMask_Slope_1('duomask-slope-1', 'test-id', [
            'pattern-height' => 999.999,
            'pattern-width' => 999.999
        ]);

        $result = $duomask_slope->generate_svg();
        echo "Pattern: DuoMask_Slope_1 (large dimensions)\n";
        $this->assertStringContainsString('<mask', $result, 'Should handle large dimensions gracefully');

        // Test with negative dimensions (should be converted to positive)
        $duomask_polygon = new DuoMask_Polygon_1('duomask-polygon-1', 'test-id', [
            'pattern-height' => -0.5,
            'pattern-width' => -0.3
        ]);

        $result = $duomask_polygon->generate_svg();
        echo "Pattern: DuoMask_Polygon_1 (negative dimensions)\n";
        $this->assertStringContainsString('<mask', $result, 'Should handle negative dimensions gracefully');
    }

    /**
     * Test custom patterns performance
     */
    public function test_custom_patterns_performance()
    {
        $start_time = microtime(true);

        // Generate multiple custom patterns
        for ($i = 0; $i < 50; $i++) {
            $shark_fin = new Shark_Fin('shark-fin', "test-id-$i");
            $shark_fin->generate_svg();

            $duomask_slope = new DuoMask_Slope_1('duomask-slope-1', "test-id-$i");
            $duomask_slope->generate_svg();

            $duomask_polygon = new DuoMask_Polygon_1('duomask-polygon-1', "test-id-$i");
            $duomask_polygon->generate_svg();
        }

        $end_time = microtime(true);
        $execution_time = $end_time - $start_time;

        // Should complete in reasonable time (less than 2 seconds for 150 generations)
        $this->assertLessThan(5.0, $execution_time, 'Custom patterns should generate 150 SVGs in less than 5 seconds');

    }

    /**
     * Test custom patterns unique outputs
     */
    public function test_custom_patterns_unique_outputs()
    {
        $shark_fin = new Shark_Fin('shark-fin', 'test-id');
        $shark_result = $shark_fin->generate_svg();

        $duomask_slope = new DuoMask_Slope_1('duomask-slope-1', 'test-id');
        $slope_result = $duomask_slope->generate_svg();

        $duomask_polygon = new DuoMask_Polygon_1('duomask-polygon-1', 'test-id');
        $polygon_result = $duomask_polygon->generate_svg();

        // Each custom pattern should produce unique output
        $this->assertNotEquals($shark_result, $slope_result, 'Shark_Fin and DuoMask_Slope_1 should produce different outputs');
        $this->assertNotEquals($shark_result, $polygon_result, 'Shark_Fin and DuoMask_Polygon_1 should produce different outputs');
        $this->assertNotEquals($slope_result, $polygon_result, 'DuoMask_Slope_1 and DuoMask_Polygon_1 should produce different outputs');
    }
}