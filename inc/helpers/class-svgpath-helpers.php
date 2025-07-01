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

	const TRAJECTORY_PATH_VALID_CHARS = 'MmLlHhVvzCcSsQqTtAa';


	/**
	 * Identifies format M -5.033 7.474 C 15.496 3.269 33.904 1.285 54.586 3.603  respect -0.1 0, 0.07 0, 0.07 0.16,
	 * If it's format with 'M', 'C' ... it transform every vertex info into an item of an array.
	 * ie [ 'M 5 4', 'C 5.5 4.0 5.2 3.3 5.6 3.3', ... ]
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

		$is_trajectory_path = preg_match( '/[' . self::TRAJECTORY_PATH_VALID_CHARS . ']/', $string_path );

		if ( $is_trajectory_path ) {
			$pattern = '/([' . preg_quote( self::TRAJECTORY_PATH_VALID_CHARS, '/' ) . '])([^' . preg_quote( self::TRAJECTORY_PATH_VALID_CHARS, '/' ) . ']*)/';

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
		$points_string = self::sanitize_string_path( $points_string );
		if ( null === $points_string ) {
			return '';
		}
		$scale = $param_values['scale'] ?? 1.0;

		// =======
		$fn_transform_any_coordenate = function ( string|float $coordenate ) use ( $scale, $param_values ) {
			$coordenate = (string) $coordenate;
			foreach ( $param_values as $param_name => $param_value ) {
				$param_value_str = (string) ( $param_value * $scale );
				$coordenate      = str_replace( "{{$param_name}}", $param_value_str, $coordenate );
				$coordenate      = str_replace( "{2*$param_name}", (string) ( 2 * $param_value * $scale ), $coordenate );
			}
			$coordenate_float = (float) $coordenate / $scale;
			return (string) $coordenate_float;
		};
		// =======
		$fn_transform_x_coordenate = function ( string|float $coordenate ) use ( $base_x_coord, $fn_transform_any_coordenate ) {
			$coordenate       = $fn_transform_any_coordenate( $coordenate );
			$coordenate_float = (float) $coordenate + $base_x_coord;
			return $coordenate_float;
		};
		// =======
		$new_points_string = self::apply_transform_to_path_coordenates(
			$points_string,
			$fn_transform_x_coordenate,
			$fn_transform_any_coordenate
		);

		return $new_points_string;
	}


	/**
	 * Using callbacks fns, applies transformation to x and y coordinates in an SVG path string.
	 *
	 * Takes a path string containing coordinates and applies separate transformation functions
	 * to the x and y coordinates. Works with both trajectory paths (using commands like M, L, C)
	 * and polygon paths (simple coordinate pairs).
	 *
	 * @param string   $points_string      The SVG path string containing coordinates to transform
	 * @param callable $transform_points_x_fn  Function to transform x coordinates
	 * @param callable $transform_points_y_fn  Function to transform y coordinates
	 * @return string                      The transformed path string with updated coordinates
	 */
	public static function apply_transform_to_path_coordenates( string $points_string, callable $transform_points_x_fn, callable $transform_points_y_fn ): string {
		if ( empty( $points_string ) ) {
			return '';
		}

		$array_coordenates = self::is_trajectory_path( $points_string );
		if ( ! is_array( $array_coordenates ) ) { // it's a poligon path set as couple of coords
			$array_coordenates = self::convert_points_to_pairs( $points_string );
		}

		// now every item in $array_coordenates is a coordenate, set by a string of several X and Ys. Can start by a letter.

		$is_x = true;
		foreach ( $array_coordenates as $i => $coordenates ) { // $coordenates looks like : 'C 4 3 2 3 4 5' or '34 22'
			$array_points = explode( ' ', $coordenates );
			foreach ( $array_points as $j => $coordenate ) { // for every number

				// case the coordenate is not a coordenate, but ie the 'C' in 'C 34 22 35 10  ...'
				if ( 1 === strlen( $coordenate ) && str_contains( self::TRAJECTORY_PATH_VALID_CHARS, $coordenate ) ) {
					continue;
				}


				if ( 'Z' === $coordenate ) {
					continue;
				}

				if ( $is_x ) {
					$coordenate = $transform_points_x_fn( $coordenate );
				} else {
					$coordenate = $transform_points_y_fn( $coordenate );
				}
				$is_x = ! $is_x;

				// update the array with the tansformed coordenate.
				$array_points[ $j ] = $coordenate;
			} // end evaluating points for a vertex

			// re-glue all coords and letters back again int o string.
			$array_coordenates[ $i ] = implode( ' ', $array_points );
		}

		return implode( ' ', $array_coordenates );
	}


	/**
	 * Converts a string of coordinate pairs into an array of coordinate pairs.
	 * Each pair consists of an x and y coordinate separated by spaces.
	 *
	 * @param string $points_string String containing coordinate pairs
	 * @return array<int,string> Array of coordinate pair strings
	 */
	private static function convert_points_to_pairs( string $points_string ): array {
		if ( empty( $points_string ) ) {
			return [];
		}

		// Cleanup double spaces
		$points_string = self::sanitize_string_path( $points_string );

		// Split into individual coordinates
		$points_array = preg_split( '/[,\s]+/', $points_string );
		if ( ! is_array( $points_array ) ) {
			return [];
		}

		$array_coordinates = [];
		$points            = '';
		$is_x              = true;

		// Group coordinates into pairs
		foreach ( $points_array as $point ) {
			$points .= ( strlen( $points ) ? ' ' : '' ) . $point;
			if ( ! $is_x ) {
				$array_coordinates[] = $points;
				$points              = '';
			}
			$is_x = ! $is_x;
		}

		return $array_coordinates;
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
		if ( empty( $string_points ) ) {
			return 0.0;
		}

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

	public static function close_path( string $path_points, float $scale = 1, float $offset_y = 0.1, float $offset_x = 0.1 ): string {
		$path_points = self::sanitize_string_path( $path_points );
		$path = $path_points;
		if ( ! str_starts_with ( trim($path_points), 'M')) {
				$path = 'M ' . ( -1 * $offset_x ) . ' 0 ' . $path_points;
		}

		$path .= ' L '. ( 1 * $scale + $offset_x ).' 0 '  // top right
			. ' L '. ( 1 * $scale + $offset_x ).' '. ( 1 * $scale + $offset_y )  // bottom right
			. ' L '. ( -1 * $offset_x ).' '. ( 1 * $scale + $offset_y ).' Z'; // bottom left

		return $path;
	}

	public static function sanitize_string_path( $path ) {
		// remove double strings.
		$path = preg_replace( '/\s+/',' ', $path );
		$path = trim( $path );
		return $path;
	}
}
