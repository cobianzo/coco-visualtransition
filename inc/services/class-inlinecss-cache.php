<?php
/**
 * InlineCSS Cache Service
 *
 * @package    CocoVisualTransition
 * @subpackage Services
 * @since      1.0.0
 */

namespace COCO\VisualTransition\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Service for caching SVG and CSS results.
 */
final class InlineCSS_Cache {

	// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Parameters used in compact() function
	/**
	 * Get cached SVG and CSS by key.
	 *
	 * @param string                      $pattern Pattern name.
	 * @param string                      $id Unique identifier.
	 * @param array<string, string|float> $atts Pattern attributes.
	 * @return string|null
	 */
	public static function get( string $pattern, string $id, array $atts = [] ): ?string {
		$cache_data     = compact( 'pattern', 'id', 'atts' );
		$cache_key_hash = md5( (string) wp_json_encode( $cache_data ) );
		$svg_and_style  = get_transient( 'coco_vt_' . $cache_key_hash );
		if ( ! empty( $svg_and_style ) && is_string( $svg_and_style ) ) {
			return $svg_and_style;
		}
		return null;
	}
	// phpcs:enable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Parameters used in compact() function

	/**
	 * Set cached SVG and CSS by key.
	 *
	 * @param string                      $pattern Pattern name.
	 * @param string                      $id Unique identifier.
	 * @param array<string, string|float> $atts Pattern attributes.
	 * @param string                      $svg_and_style SVG and CSS string.
	 * @return void
	 */
	public static function set( string $pattern, string $id, array $atts = [], string $svg_and_style = '' ): void {
		$cache_data     = compact( 'pattern', 'id', 'atts' );
		$cache_key_hash = md5( (string) wp_json_encode( $cache_data ) );
		set_transient( 'coco_vt_' . $cache_key_hash, $svg_and_style, \DAY_IN_SECONDS );
	}
}
