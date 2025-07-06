<?php

use Coco\VisualTransition\SVG_Generator;
use Coco\VisualTransition\SVG_Generator_Factory;

// Load the SVG Generator Factory class for testing
require_once plugin_dir_path( dirname( __DIR__ ) ) . 'inc/svg-generators/class-svg-generator.php';
require_once plugin_dir_path( dirname( __DIR__ ) ) . 'inc/svg-generators/class-svg-generator-factory.php';

// Load custom pattern classes for factory testing
require_once plugin_dir_path( dirname( __DIR__ ) ) . 'inc/svg-generators/custom-patterns/class-shark-fin.php';
require_once plugin_dir_path( dirname( __DIR__ ) ) . 'inc/svg-generators/custom-patterns/class-duomask-slope-1.php';
require_once plugin_dir_path( dirname( __DIR__ ) ) . 'inc/svg-generators/custom-patterns/class-duomask-polygon-1.php';

/**
 * SVG Generator Factory Tests
 *
 * Tests for the SVG_Generator_Factory class covering:
 * - Factory creates correct generator types
 * - Custom pattern class instantiation
 * - Fallback to generic generator
 * - Error handling for invalid patterns
 *
 * npm run test:php tests/Unit/GenerateSVGTest.php
 * @package CocoVisualTransition
 */
class SVGGeneratorFactoryTest extends WP_UnitTestCase
{
    /**
     * Test factory creates correct generator types
     */
    public function test_factory_creates_correct_generator_types()
    {
        // Test default/generic generator
        $generator = SVG_Generator_Factory::create('unknown-pattern', 'test-id');
        $this->assertInstanceOf(SVG_Generator::class, $generator, 'Should create generic SVG_Generator for unknown patterns');
        $this->assertEquals('unknown-pattern', $generator->pattern_name, 'Should set correct pattern name');

        // Test with valid pattern name
        $generator = SVG_Generator_Factory::create('triangles', 'test-id');
        $this->assertInstanceOf(SVG_Generator::class, $generator, 'Should create generic SVG_Generator for valid patterns');
        $this->assertEquals('triangles', $generator->pattern_name, 'Should set correct pattern name');
    }

    /**
     * Test custom pattern class instantiation
     */
    public function test_custom_pattern_class_instantiation()
    {
        // Test duomask-slope-1 pattern
        $generator = SVG_Generator_Factory::create('duomask-slope-1', 'test-id');
        $this->assertInstanceOf(\Coco\VisualTransition\DuoMask_Slope_1::class, $generator, 'Should create DuoMask_Slope_1 for duomask-slope-1 pattern');
        $this->assertEquals('duomask-slope-1', $generator->pattern_name, 'Should set correct pattern name');

        // Test duomask-polygon-1 pattern
        $generator = SVG_Generator_Factory::create('duomask-polygon-1', 'test-id');
        $this->assertInstanceOf(\Coco\VisualTransition\DuoMask_Polygon_1::class, $generator, 'Should create DuoMask_Polygon_1 for duomask-polygon-1 pattern');
        $this->assertEquals('duomask-polygon-1', $generator->pattern_name, 'Should set correct pattern name');

        // Test shark-fin pattern
        $generator = SVG_Generator_Factory::create('shark-fin', 'test-id');
        $this->assertInstanceOf(\Coco\VisualTransition\Shark_Fin::class, $generator, 'Should create Shark_Fin for shark-fin pattern');
        $this->assertEquals('shark-fin', $generator->pattern_name, 'Should set correct pattern name');
    }

    /**
     * Test custom pattern class instantiation with attributes
     */
    public function test_custom_pattern_class_instantiation_with_attributes()
    {
        $atts = [
            'pattern-height' => 0.8,
            'pattern-width' => 0.4,
            'custom-attr' => 'test-value'
        ];

        // Test custom pattern with attributes
        $generator = SVG_Generator_Factory::create('shark-fin', 'test-id', $atts);
        $this->assertInstanceOf(\Coco\VisualTransition\Shark_Fin::class, $generator, 'Should create Shark_Fin with attributes');
        $this->assertEquals(0.8, $generator->pattern_height, 'Should set pattern height from attributes');
        $this->assertEquals(0.4, $generator->pattern_width, 'Should set pattern width from attributes');

        // Test generic pattern with attributes
        $generator = SVG_Generator_Factory::create('triangles', 'test-id', $atts);
        $this->assertInstanceOf(SVG_Generator::class, $generator, 'Should create generic SVG_Generator with attributes');
        $this->assertEquals(0.8, $generator->pattern_height, 'Should set pattern height from attributes');
        $this->assertEquals(0.4, $generator->pattern_width, 'Should set pattern width from attributes');
    }

    /**
     * Test fallback to generic generator
     */
    public function test_fallback_to_generic_generator()
    {
        // Test with non-existent custom pattern
        $generator = SVG_Generator_Factory::create('non-existent-pattern', 'test-id');
        $this->assertInstanceOf(SVG_Generator::class, $generator, 'Should fallback to generic SVG_Generator');
        $this->assertEquals('non-existent-pattern', $generator->pattern_name, 'Should set the requested pattern name');
    }

    /**
     * Test error handling for invalid patterns
     */
    public function test_error_handling_for_invalid_patterns()
    {
        // Test with empty pattern name
        $this->expectException(\WPDieException::class);
        $this->expectExceptionMessage('Pattern and ID cannot be empty or contain only whitespace.');
        SVG_Generator_Factory::create('', 'test-id');

        // Test with empty id
        $this->expectException(\WPDieException::class);
        $this->expectExceptionMessage('Pattern and ID cannot be empty or contain only whitespace.');
        SVG_Generator_Factory::create('valid-pattern', '');

        // Test with whitespace-only pattern name
        $this->expectException(\WPDieException::class);
        $this->expectExceptionMessage('Pattern and ID cannot be empty or contain only whitespace.');
        SVG_Generator_Factory::create('   ', 'test-id');

        // Test with whitespace-only id
        $this->expectException(\WPDieException::class);
        $this->expectExceptionMessage('Pattern and ID cannot be empty or contain only whitespace.');
        SVG_Generator_Factory::create('valid-pattern', '   ');

        // Test with special characters in pattern name (should still work)
        $generator = SVG_Generator_Factory::create('pattern-with-special-chars!@#', 'test-id');
        $this->assertInstanceOf(SVG_Generator::class, $generator, 'Should handle special characters gracefully');
        $this->assertEquals('pattern-with-special-chars!@#', $generator->pattern_name, 'Should preserve pattern name with special characters');

        // Test with very long pattern name
        $long_pattern_name = str_repeat('a', 1000);
        $generator = SVG_Generator_Factory::create($long_pattern_name, 'test-id');
        $this->assertInstanceOf(SVG_Generator::class, $generator, 'Should handle very long pattern names');
        $this->assertEquals($long_pattern_name, $generator->pattern_name, 'Should preserve very long pattern name');

        // Test with numeric pattern name
        $generator = SVG_Generator_Factory::create('123', 'test-id');
        $this->assertInstanceOf(SVG_Generator::class, $generator, 'Should handle numeric pattern names');
        $this->assertEquals('123', $generator->pattern_name, 'Should preserve numeric pattern name');
    }

    /**
     * Test file loading for custom patterns
     */
    public function test_file_loading_for_custom_patterns()
    {
        // Test that custom pattern files are loaded when needed
        $generator = SVG_Generator_Factory::create('shark-fin', 'test-id');

        // Verify the class exists after factory creation
        $this->assertTrue(class_exists('\Coco\VisualTransition\Shark_Fin'), 'Shark_Fin class should be loaded');

        // Test another custom pattern
        $generator = SVG_Generator_Factory::create('duomask-slope-1', 'test-id');
        $this->assertTrue(class_exists('\Coco\VisualTransition\DuoMask_Slope_1'), 'DuoMask_Slope_1 class should be loaded');
    }

    /**
     * Test pattern inheritance and method overriding
     */
    public function test_pattern_inheritance_and_method_overriding()
    {
        // Test that custom patterns extend the base SVG_Generator
        $generator = SVG_Generator_Factory::create('shark-fin', 'test-id');
        $this->assertInstanceOf(SVG_Generator::class, $generator, 'Custom patterns should extend SVG_Generator');

        // Test that custom patterns can override methods
        $this->assertTrue(method_exists($generator, 'generate_svg'), 'Custom patterns should have generate_svg method');

        // Test that the overridden method produces different output
        $generator->generate_points();
        $result = $generator->generate_svg();
        $this->assertStringContainsString('<mask', $result, 'Shark_Fin should generate mask element');
        $this->assertStringContainsString('fill="white"', $result, 'Shark_Fin should have white fill');
    }

    /**
     * Test factory with different ID formats
     */
    public function test_factory_with_different_id_formats()
    {
        // Test with UUID format
        $uuid = 'vt_' . wp_generate_uuid4();
        $generator = SVG_Generator_Factory::create('shark-fin', $uuid);
        $this->assertEquals($uuid, $generator->id, 'Should use UUID format ID');

        // Test with simple string ID
        $generator = SVG_Generator_Factory::create('shark-fin', 'simple-id');
        $this->assertEquals('simple-id', $generator->id, 'Should use simple string ID');

        // Test with numeric ID
        $generator = SVG_Generator_Factory::create('shark-fin', '123');
        $this->assertEquals('123', $generator->id, 'Should use numeric ID');

        // Test with special characters in ID
        $generator = SVG_Generator_Factory::create('shark-fin', 'id-with-special-chars!@#');
        $this->assertEquals('id-with-special-chars!@#', $generator->id, 'Should use ID with special characters');
    }

    /**
     * Test factory performance with multiple creations
     */
    public function test_factory_performance_with_multiple_creations()
    {
        $start_time = microtime(true);

        // Create multiple generators
        for ($i = 0; $i < 100; $i++) {
            $generator = SVG_Generator_Factory::create('shark-fin', "test-id-$i");
            $this->assertInstanceOf(\Coco\VisualTransition\Shark_Fin::class, $generator);
        }

        $end_time = microtime(true);
        $execution_time = $end_time - $start_time;

        // Should complete in reasonable time (less than 1 second for 100 creations)
        $this->assertLessThan(1.0, $execution_time, 'Factory should create 100 generators in less than 1 second');
    }

    /**
     * Test factory with edge case attributes
     */
    public function test_factory_with_edge_case_attributes()
    {
        // Test with empty attributes array
        $generator = SVG_Generator_Factory::create('shark-fin', 'test-id', []);
        $this->assertInstanceOf(\Coco\VisualTransition\Shark_Fin::class, $generator, 'Should handle empty attributes array');

        // Test with extreme attribute values
        $extreme_atts = [
            'pattern-height' => 999999.999,
            'pattern-width' => 0.000001,
            'custom-attr' => str_repeat('a', 10000)
        ];
        $generator = SVG_Generator_Factory::create('shark-fin', 'test-id', $extreme_atts);
        $this->assertInstanceOf(\Coco\VisualTransition\Shark_Fin::class, $generator, 'Should handle extreme attribute values');
        $this->assertEquals(999999.999, $generator->pattern_height, 'Should set extreme pattern height');
        $this->assertEquals(0.000001, $generator->pattern_width, 'Should set extreme pattern width');
    }
}