<?php
/**
 * InlineCSS REST Controller for the Editor.
 *
 * @package    CocoVisualTransition
 * @subpackage Controllers
 * @since      1.0.0
 */

namespace COCO\VisualTransition\Controllers;

use COCO\VisualTransition\Services\InlineCSS_Renderer;
use COCO\VisualTransition\Services\InlineCSS_Cache;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Controller for registering REST API endpoint for SVG/CSS generation.
 */
final class InlineCSS_REST_Controller {
	/**
	 * Register the REST API route.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'rest_api_init', [ __CLASS__, 'register_rest_route_for_editor_use' ] );
	}

	/**
	 * Register the REST route and callback.
	 *
	 * @return void
	 */
	public static function register_rest_route_for_editor_use(): void {
		register_rest_route( 'coco/v1', '/vtstyle', [
			'methods'             => 'POST',
			'allow_non_ssl'       => true,
			'callback'            => [ __CLASS__, 'handle_rest_request' ],
			'permission_callback' => fn() => current_user_can( 'edit_posts' ),
			'args'                => [
				'block_id'     => [
					'required' => true,
					'type'     => 'string',
				],
				'pattern_name' => [
					'required' => true,
					'type'     => 'string',
				],
				'pattern_atts' => [
					'required' => false,
					'type'     => 'object',
				],
			],
		] );
	}

	// phpcs:disable Squiz.Functions.MultiLineFunctionDeclaration.ContentAfterBrace
	/**
	 * Handle the REST API request for SVG/CSS generation. Used by typescript.
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @return string|\WP_Error
	 */
	public static function handle_rest_request( \WP_REST_Request $request ) { // @phpstan-ignore-line.

		// phpcs:enable Squiz.Functions.MultiLineFunctionDeclaration.ContentAfterBrace
		$params = $request->get_params();
		if ( ! isset( $params['block_id'] ) || ! isset( $params['pattern_name'] ) ) {
			return new \WP_Error(
				'missing_params',
				'Missing required parameters: block_id and pattern_name are required',
				[ 'status' => 400 ]
			);
		}

		// Ensure block_id and pattern_name are strings before sanitizing
		$pattern_name_raw = $params['pattern_name'];
		$block_id_raw     = $params['block_id'];
		if ( ! is_string( $pattern_name_raw ) || ! is_string( $block_id_raw ) ) {
			return new \WP_Error(
				'invalid_params',
				'block_id and pattern_name must be strings',
				[ 'status' => 400 ]
			);
		}
		$pattern_name  = sanitize_text_field( $pattern_name_raw );
		$block_id      = sanitize_text_field( $block_id_raw );
		$pattern_attrs = isset( $params['pattern_atts'] ) ? (array) $params['pattern_atts'] : [];

		$pattern_height = isset( $pattern_attrs['patternHeight'] ) && is_numeric( $pattern_attrs['patternHeight'] ) ? (float) $pattern_attrs['patternHeight'] : 0.08;
		$pattern_width  = isset( $pattern_attrs['patternWidth'] ) && is_numeric( $pattern_attrs['patternWidth'] ) ? (float) $pattern_attrs['patternWidth'] : 0.1;
		$y_offset       = isset( $pattern_attrs['YOffset'] ) && is_numeric( $pattern_attrs['YOffset'] ) ? (int) $pattern_attrs['YOffset'] : 0;
        $type_pattern   = isset( $pattern_attrs['typePattern'] ) && in_array( $pattern_attrs['typePattern'], [ '%', 'px' ], true ) ? $pattern_attrs['typePattern'] : '%';

		$atts          = [
			'pattern-height' => $pattern_height,
			'pattern-width'  => $pattern_width,
			'y-offset'       => $y_offset,
            'type-pattern'    => $type_pattern,
		];
		$svg_and_style = InlineCSS_Cache::get( $pattern_name, $block_id, $atts );
		if ( null === $svg_and_style ) {
			$rendered      = InlineCSS_Renderer::generate_svg_and_css( $pattern_name, $block_id, $atts, 'data-block' );
			$svg_and_style = $rendered['svg'] . $rendered['css'];
			InlineCSS_Cache::set( $pattern_name, $block_id, $atts, $svg_and_style );
		}
		return $svg_and_style;
	}
}
