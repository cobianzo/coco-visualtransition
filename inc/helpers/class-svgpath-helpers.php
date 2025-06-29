<?php
/**
 * SVG Path Helper Class
 *
 * Helper class for handling SVG path operations and manipulations
 *
 * @package CocoVisualTransition
 * @since 1.0.0
 */

namespace Coco\VisualTransition\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class SVGPath_Helpers
 */
final class SVGPath_Helpers {


	/**
	 * Identifies format M -5.033 7.474 C 15.496 3.269 33.904 1.285 54.586 3.603  respect -0.1 0, 0.07 0, 0.07 0.16,
	 *
	 * @param string $string_path The path string to analyze for trajectory format.
	 * @return array<int, string>|false Returns array of path commands if valid trajectory, false otherwise.
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

				// Replace multiple spaces with a single space
				$params = (string) preg_replace( '/\s+/', ' ', $params );

				// Split values by spaces and join with spaces
				$params_array = preg_split( '/\s+/', $params );
				if ( $params_array === false ) {
					$params_array = [];
				}
				$params = implode( ' ', $params_array );

				$result[] = $command . ' ' . $params;
			}

			// Replace placeholders back
			foreach ( $valid_placeholders as $placeholder_id => $placeholder_name ) {
				$result = str_replace( '{' . $placeholder_id . '}', '{' . $placeholder_name . '}', $result );
			}

			return $result;
		}

		return false;
	}

	/**
	 * Replaces placeholder values in points string with actual coordinates.
	 * ie {x_size}, {y_size}
	 * It also adds the offset_x to the x coordenates.
	 *
	 * @param string               $points_string The points string containing placeholders.
	 * @param float                $base_x_coord  The base x coordinate to offset points.
	 * @param array<string, float> $param_values  Array of parameter values to replace placeholders.
	 * @return string The processed points string with replaced values.
	 */
	public static function replace_points_placeholders( string $points_string, float $base_x_coord = 0.0, array $param_values = [] ): string {
		// Sanitize points string by removing double spaces
		$points_string = preg_replace( '/\s+/', ' ', trim( (string) $points_string ) );
		$scale         = $param_values['scale'] ?? 1.0;

		// separate every coordenate
		$points_array      = explode( ' ', trim( (string) $points_string ) );
		$is_x              = true; // we examinate every coordenate identifying if its X coord or Y (or it can be a letter)
		$new_points_string = '';

		foreach ( $points_array as $coordenate ) {
			foreach ( $param_values as $param_name => $param_value ) {
				$param_value_str = (string) ( $param_value * $scale );
				$coordenate      = str_replace( "{{$param_name}}", $param_value_str, $coordenate );
				$coordenate      = str_replace( "{2*$param_name}", (string) ( 2 * $param_value * $scale ), $coordenate );
			}

			if ( is_numeric( $coordenate ) ) {
				$coordenate_float = (float) $coordenate / $scale;

				// Important. We are repeating the pattern over the X axis, so we need to shift the X coordenates
				// based on where this pattern starts in X.
				if ( $is_x ) {
					$coordenate_float += $base_x_coord;
				}

				$coordenate = (string) $coordenate_float;
				$is_x       = ! $is_x;
			}

			$new_points_string .= ( strlen( $new_points_string ) ? ' ' : '' ) . $coordenate;
		}

		return $new_points_string;
	}

		/**
		 * Helper.
		 * From a set of points as a string in $this->points_string or the arg, ie ( 0 0, 1 1, 2 0, 3 1, 4 0 ).
		 * returns the last x point. (in this case (int) 4)
		 *
		 * @param string $string_points The points string to extract the last x coordinate from.
		 * @return float The last x coordinate value.
		 */
	public static function get_last_x_point( string $string_points ): float {
		$points = preg_split( '/[,\s]+/', trim( $string_points ) );
		if ( ! is_array( $points ) ) {
			return 0.0;
		}
		$count  = count( $points );
		$last_x = $count >= 2 ? trim( (string) $points[ $count - 2 ] ) : '0';
		$last_x = preg_replace( '/\s+/', ' ', $last_x );

		return floatval( $last_x );
	}

	/**
	 * Simple helper. From '4.5 3.0' returns the required point. ( 4.5 if arg $coordenate is 'x')
	 * TODELETE: Not in use so far.
	 *
	 * @param string  $pair_x_y   A string containing two numbers separated by space (e.g. "12 3")
	 * @param 'x'|'y' $coordenate Either 'x' or 'y' coordinate to extract from the pair
	 * @return float The extracted coordinate value
	 */
	public static function get_point_from_pair( string $pair_x_y, string $coordenate = 'x' ): float {
		$pair_x_y = trim( (string) preg_replace( '/\s+/', ' ', $pair_x_y ) ); // clean double spaces
		$x_y      = explode( ' ', $pair_x_y );
		$count    = count( $x_y );

		if ( 'x' === $coordenate && isset( $x_y[ $count - 2 ] ) ) {
			return floatval( $x_y[ $count - 2 ] );
		}
		if ( 'y' === $coordenate && isset( $x_y[ $count - 1 ] ) ) {
			return floatval( $x_y[ $count - 1 ] );
		}
		return 0.0;
	}
}
