<?php

use Coco\VisualTransition\SVG_Generator;
use Coco\VisualTransition\Helpers\Generic_Helpers;
use Coco\VisualTransition\Helpers\SVGPath_Helpers;

// Load required classes for testing
require_once plugin_dir_path( dirname( __DIR__ ) ) . 'inc/svg-generators/class-svg-generator.php';
require_once plugin_dir_path( dirname( __DIR__ ) ) . 'inc/helpers/class-generic-helpers.php';
require_once plugin_dir_path( dirname( __DIR__ ) ) . 'inc/helpers/class-svgpath-helpers.php';

/**
 * SVG Generator Tests
 *
 * Tests for the main SVG_Generator class covering:
 * - Pattern placeholder replacement
 * - Trajectory vs polygon path detection
 * - Scale factor application
 * - Offset handling
 * - SVG string generation for both clipPath and mask types
 * - Pattern repetition logic
 *
 * @package CocoVisualTransition
 *
 * Usage
 *
 * npm run test:php tests/Unit/SVGGeneratorTest.php
 */
class SVGGeneratorTest extends WP_UnitTestCase
{
    private SVG_Generator $svg_generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->svg_generator = new SVG_Generator('test-pattern', 'test-id', [
            'pattern-height' => 0.5,
            'pattern-width' => 0.3
        ]);
    }

    /**
     * Test pattern placeholder replacement with various placeholders
     */
    public function test_pattern_placeholder_replacement()
    {
        // Test {x_size} placeholder
        $this->svg_generator->pattern_data = [
            'pattern' => "{x_size} 0, {x_size} {y_size}",
            'scale' => 1.0
        ];
        $this->svg_generator->pattern_width = 0.3;
        $this->svg_generator->pattern_height = 0.5;

        $result = $this->svg_generator->generate_points_string_from_pattern(0, 0);
        echo "Pattern: " . $this->svg_generator->pattern_data['pattern'] . "\n";
        $this->assertStringContainsString('0.3 0', $result, 'Should replace {x_size} with 0.3');
        $this->assertStringContainsString('0.5', $result, 'Should replace {y_size} with 0.5');

        // Test {2*x_size} placeholder
        $this->svg_generator->pattern_data = [
            'pattern' => "{x_size} 0, {2*x_size} {y_size}",
            'scale' => 1.0
        ];

        $result = $this->svg_generator->generate_points_string_from_pattern(0, 0);
        echo "Pattern: " . $this->svg_generator->pattern_data['pattern'] . "\n";
        $this->assertStringContainsString('0.6', $result, 'Should replace {2*x_size} with 0.6 (2 * 0.3)');
    }

    /**
     * Test trajectory vs polygon path detection
     */
    public function test_trajectory_vs_polygon_detection()
    {
        // Test polygon pattern (no trajectory commands)
        $this->svg_generator->pattern_data = [
            'pattern' => "0 0, {x_size} {y_size}",
            'scale' => 1.0
        ];

        $result = $this->svg_generator->generate_points_string_from_pattern(0, 0);
        echo "Pattern: " . $this->svg_generator->pattern_data['pattern'] . "\n";
        $this->assertStringNotContainsString('M ', $result, 'Polygon pattern should not start with M command');
        $this->assertStringNotContainsString('Z', $result, 'Polygon pattern should not end with Z command');

        // Test trajectory pattern (with L commands)
        $this->svg_generator->pattern_data = [
            'pattern' => "L 0 {y_size} L {x_size} 0",
            'scale' => 1.0
        ];

        $result = $this->svg_generator->generate_points_string_from_pattern(0, 0);
        echo "Pattern: " . $this->svg_generator->pattern_data['pattern'] . "\n";
        $this->assertStringContainsString('M ', $result, 'Trajectory pattern should start with M command');
        $this->assertStringContainsString('Z', $result, 'Trajectory pattern should end with Z command');
    }


    /**
     * Test SVG string generation for clipPath type
     */
    public function test_svg_string_generation_clippath()
    {
        $this->svg_generator->pattern_data = [
            'pattern' => "0 0, {x_size} {y_size}",
            'scale' => 1.0
        ];
        $this->svg_generator->pattern_width = 0.3;
        $this->svg_generator->pattern_height = 0.5;

        $this->svg_generator->generate_points();
        $result = $this->svg_generator->generate_svg();

        echo "Pattern: " . $this->svg_generator->pattern_data['pattern'] . "\n";
        $this->assertStringContainsString('<clipPath', $result, 'Should generate clipPath element');
        $this->assertStringContainsString('<polygon', $result, 'Should use polygon for non-trajectory patterns');
        $this->assertStringContainsString('clipPathUnits="objectBoundingBox"', $result, 'Should set correct clipPath units');
        $this->assertStringContainsString('id="pattern-test-id"', $result, 'Should set correct pattern ID');
    }

    /**
     * Test SVG string generation for mask type
     */
    public function test_svg_string_generation_mask()
    {
        $this->svg_generator->pattern_data = [
            'pattern' => "L 0 {y_size} L {x_size} 0",
            'scale' => 1.0,
            'type' => 'mask'
        ];
        $this->svg_generator->pattern_width = 0.3;
        $this->svg_generator->pattern_height = 0.5;

        $this->svg_generator->generate_points();
        $result = $this->svg_generator->generate_svg();

        echo "Pattern: " . $this->svg_generator->pattern_data['pattern'] . " (type: mask)\n";
        $this->assertStringContainsString('<mask', $result, 'Should generate mask element');
        $this->assertStringContainsString('<path', $result, 'Should use path for trajectory patterns');
        $this->assertStringContainsString('fill="rgba(255,255,255, 1)"', $result, 'Should set white fill for mask');
        $this->assertStringContainsString('maskUnits="objectBoundingBox"', $result, 'Should set correct mask units');
    }

    /**
     * Test pattern repetition logic
     */
    public function test_pattern_repetition_logic()
    {
        // Test with small pattern that should repeat multiple times
        $this->svg_generator->pattern_data = [
            'pattern' => "{x_size} {y_size}",
            'scale' => 1.0
        ];
        $this->svg_generator->pattern_width = 0.1; // Small width to force repetition
        $this->svg_generator->pattern_height = 0.5;

        $result = $this->svg_generator->generate_points_string_from_pattern(0, 0);
        echo "Pattern: " . $this->svg_generator->pattern_data['pattern'] . " (small width: 0.1)\n";

        // Should repeat until it covers the full width (1 + offset)
        $this->assertStringContainsString('0.1', $result, 'Should contain first repetition');
        $this->assertStringContainsString('0.2', $result, 'Should contain second repetition');
        $this->assertStringContainsString('0.3', $result, 'Should contain third repetition');
        $this->assertStringContainsString('1 0', $result, 'Should end at width 1');

        // Test with large pattern that shouldn't repeat
        $this->svg_generator->pattern_width = 1.5; // Larger than container width
        $result = $this->svg_generator->generate_points_string_from_pattern(0, 0);
        echo "Pattern: " . $this->svg_generator->pattern_data['pattern'] . " (large width: 1.5)\n";

        $this->assertStringContainsString('1.5', $result, 'Should contain the large pattern');
        $this->assertStringNotContainsString('3', $result, 'Should not repeat large pattern');
    }

    /**
     * Test edge cases and error handling
     */
    public function test_edge_cases_and_error_handling()
    {
        // Test with empty pattern
        $this->svg_generator->pattern_data = [
            'pattern' => '',
            'scale' => 1.0
        ];

        $result = $this->svg_generator->generate_points_string_from_pattern(0, 0);
        echo "Pattern: (empty)\n";
        $this->assertStringContainsString('0 0', $result, 'Should handle empty pattern gracefully');


    }

    /**
     * Test constructor with various parameters
     */
    public function test_constructor_parameters()
    {
        // Test with minimal parameters - should throw exception due to empty pattern_name
        $this->expectException(\WPDieException::class);
        $this->expectExceptionMessage('Args cannot be empty or contain only whitespace.');
        new SVG_Generator();

        // Test with custom parameters
        $generator = new SVG_Generator('custom-pattern', 'custom-id', [
            'pattern-height' => 0.8,
            'pattern-width' => 0.4
        ]);
        $this->assertEquals('custom-id', $generator->id, 'Should use custom ID');
        $this->assertEquals('custom-pattern', $generator->pattern_name, 'Should use custom pattern name');
        $this->assertEquals(0.8, $generator->pattern_height, 'Should set custom pattern height');
        $this->assertEquals(0.4, $generator->pattern_width, 'Should set custom pattern width');
    }

    /**
     * Test constructor validation with edge cases
     */
    public function test_constructor_validation_edge_cases()
    {
        // Test with empty pattern_name but valid id
        $this->expectException(\WPDieException::class);
        $this->expectExceptionMessage('Args cannot be empty or contain only whitespace.');
        new SVG_Generator('', 'valid-id');

        // Test with valid pattern_name but empty id
        $this->expectException(\WPDieException::class);
        $this->expectExceptionMessage('Args cannot be empty or contain only whitespace.');
        new SVG_Generator('valid-pattern', '');

        // Test with whitespace-only pattern_name
        $this->expectException(\WPDieException::class);
        $this->expectExceptionMessage('Args cannot be empty or contain only whitespace.');
        new SVG_Generator('   ', 'valid-id');

        // Test with whitespace-only id
        $this->expectException(\WPDieException::class);
        $this->expectExceptionMessage('Args cannot be empty or contain only whitespace.');
        new SVG_Generator('valid-pattern', '   ');

        // Test with valid parameters (should not throw exception)
        $generator = new SVG_Generator('valid-pattern', 'valid-id');
        $this->assertEquals('valid-id', $generator->id, 'Should accept valid ID');
        $this->assertEquals('valid-pattern', $generator->pattern_name, 'Should accept valid pattern name');
    }

    /**
     * Test pattern ID generation
     */
    public function test_pattern_id_generation()
    {
        $this->assertEquals('pattern-test-id', $this->svg_generator->get_pattern_id(), 'Should generate correct pattern ID');

        $generator = new SVG_Generator('test', 'custom-uuid-123');
        $this->assertEquals('pattern-custom-uuid-123', $generator->get_pattern_id(), 'Should use custom ID in pattern ID');
    }

    /**
     * Test with complex trajectory patterns
     */
    public function test_complex_trajectory_patterns()
    {
        // Test with Bezier curves
        $this->svg_generator->pattern_data = [
            'pattern' => "M 0 0 C {x_size} {y_size} {2*x_size} 0 {3*x_size} {y_size}",
            'scale' => 1.0
        ];
        $this->svg_generator->pattern_width = 0.3;
        $this->svg_generator->pattern_height = 0.5;

        $result = $this->svg_generator->generate_points_string_from_pattern(0, 0);
        echo "Pattern: " . $this->svg_generator->pattern_data['pattern'] . "\n";
        $this->assertStringContainsString('M ', $result, 'Should start with M command');
        $this->assertStringContainsString('C ', $result, 'Should contain C command');
        $this->assertStringContainsString('Z', $result, 'Should end with Z command');

        // Test with multiple commands
        $this->svg_generator->pattern_data = [
            'pattern' => "M 0 0 L {x_size} {y_size} Q {2*x_size} 0 {3*x_size} {y_size}",
            'scale' => 1.0
        ];

        $result = $this->svg_generator->generate_points_string_from_pattern(0, 0);
        echo "Pattern: " . $this->svg_generator->pattern_data['pattern'] . "\n";
        $this->assertStringContainsString('L ', $result, 'Should contain L command');
        $this->assertStringContainsString('Q ', $result, 'Should contain Q command');
    }
}