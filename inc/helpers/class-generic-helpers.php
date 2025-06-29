<?php
/**
 * Helper functions for the Visual Transition plugin
 *
 * @package Coco\VisualTransition
 */

namespace Coco\VisualTransition\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Helpers
 * Contains static helper methods for the Visual Transition plugin
 */
class Generic_Helpers {

	/**
	 * Just a helper. phpstan needs it.
	 *
	 * @param mixed $value   The number or variable to convert into float, if possible.
	 * @param float $default The fallback value if conversion fails.
	 * @return float The converted float value or default.
	 */
	public static function to_float( mixed $value, float $default = 0.0 ): float {
		if ( is_numeric( $value ) ) {
			return (float) $value;
		}
		return $default;
	}

	/**
	 * Loads patterns from JSON file
	 *
	 * @return array<int, array<string, mixed>> The loaded patterns data
	 */
	public static function load_patterns_json(): array {
		$plugin_root       = plugin_dir_path( plugin_dir_path( __DIR__ ) ); // two levels down to the root of the plugin.
		$patterns_filename = $plugin_root . 'src/patterns.json';
		$patterns_json     = wp_json_file_decode( $patterns_filename, [ 'associative' => true ] );

		if ( ! is_array( $patterns_json ) ) {
			return [];
		}

		// @phpstan-ignore return.type
		return $patterns_json;
	}

	/**
	 * Load a specific pattern by name from patterns.json
	 *
	 * @param string $pattern_name The name of the pattern to load
	 * @return array<string, mixed> The pattern data or empty array if not found
	 */
	public static function load_pattern_json( string $pattern_name ): array {
		$patterns = self::load_patterns_json();

		$found_pattern = array_filter(
			$patterns,
			fn( array $pattern ): bool => isset( $pattern['value'] ) && $pattern['value'] === $pattern_name
		);

		return reset( $found_pattern ) ?: [];
	}
}
