<?php
/**
 * Helper functions for the Visual Transition plugin
 *
 * @package CocoVisualTransition
 */

namespace Coco\VisualTransition;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Helpers
 * Contains static helper methods for the Visual Transition plugin
 */
class Helpers {

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
	 * Identifies format M -5.033 7.474 C 15.496 3.269 33.904 1.285 54.586 3.603  respect -0.1 0, 0.07 0, 0.07 0.16,
	 *
	 * @param string $string_path The path string to analyze for trajectory format.
	 * @return array|false Returns array of path commands if valid trajectory, false otherwise.
	 */
	public static function is_trajectory_path( string $string_path ): array|false {
		// we call trajectory path, if the points use beizer vertex, we use path d, otherwise we use polygon, for right lines.

		// cleanup path removing placeholders like {x_size}
		$valid_placeholders = [
			1 => 'x_size',
			2 => 'y_size',
			3 => '2*x_size',
		];
		foreach ( $valid_placeholders as $placeholder_id => $placeholder_name ) {
			$string_path = str_replace( '{' . $placeholder_name . '}', '{' . $placeholder_id . '}', $string_path );
		}


		$trajectory_path_chars = 'MmLlHhVvzCcSsQqTtAa';
		$is_trajectory_path    = preg_match( '/[' . $trajectory_path_chars . ']/', $string_path );

		if ( $is_trajectory_path ) {
			$pattern = '/([' . preg_quote( $trajectory_path_chars, '/' ) . '])([^' . preg_quote( $trajectory_path_chars, '/' ) . ']*)/';

			preg_match_all( $pattern, $string_path, $matches, PREG_SET_ORDER );

			$result = [];
			foreach ( $matches as $match ) {
				$command = $match[1];
				$params  = trim( $match[2] );

				// Reemplaza múltiples espacios por uno solo
				$params = preg_replace( '/\s+/', ' ', $params );

				// Separa los valores por espacios y junta con comas
				$params = implode( ' ', preg_split( '/\s+/', $params ) );

				$result[] = $command . ' ' . $params;
			}

			// we finished, now we replace the placeholders back
			// cleanup path removing placeholders like {x_size}
			foreach ( $valid_placeholders as $placeholder_id => $placeholder_name ) {
				$result = str_replace( '{' . $placeholder_id . '}', '{' . $placeholder_name . '}', $result );
			}

			return $result;
		}

		return false;
	}

	/**
	 * Loads patterns.json
	 *
	 * @param string|null $single_pattern_name If provided, filters the result to the given pattern name.
	 * @return array The loaded patterns data, filtered by pattern name if specified.
	 */
	public static function load_patterns_json( ?string $single_pattern_name = null ): array {
		$plugin_root       = plugin_dir_path( __DIR__ );
		$patterns_filename = $plugin_root . '/src/patterns.json';
		$patterns_json     = wp_json_file_decode( $patterns_filename, [ 'associative' => true ] );

		if ( null !== $single_pattern_name ) {
			$patterns_json = array_find(
				$patterns_json,
				fn( mixed $pattern_data ) => $single_pattern_name === ( ( (array) $pattern_data )['value'] ?? '' )
			);
		}

		return $patterns_json ?? [];
	}
}
