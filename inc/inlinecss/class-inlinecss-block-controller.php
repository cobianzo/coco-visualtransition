<?php
/**
 * InlineCSS Block Controller for the Frontend
 *
 * It registers filter on the block rendering process (render_block) to inject custom HTML attributes and inline SVG/CSS
 * It appends the SVG and CSS for the visual transition effect. Caching is used to avoid redundant SVG/CSS generation.
 *
 * Responsibilities:
 * - Registers the render_block filter to modify block output at render time.
 * - Detects core/group blocks with visual transition attributes.
 * - Injects a unique data attribute for identification and styling.
 * - Retrieves or generates the required SVG and CSS for the visual transition effect using the InlineCSS_Renderer and InlineCSS_Cache services.
 * - Appends the generated SVG and CSS to the block's rendered HTML.
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
 * Controller for handling the render_block filter for visual transition.
 */
final class InlineCSS_Block_Controller {
	/**
	 * Register the render_block filter.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_filter( 'render_block', [ __CLASS__, 'render_block_with_html_attributes' ], 10, 2 );
	}

	/**
	 * Injects an attribute into the first <div> of the block content.
	 *
	 * @param string $block_content The block content HTML.
	 * @param string $attribute The attribute name to add.
	 * @param string $value The value of the attribute.
	 * @return string Modified block content with the attribute injected.
	 */
	private static function inject_attribute_to_first_div( string $block_content, string $attribute, string $value ): string {
		$result = preg_replace(
			'/<div\\b(.*?)>/',
			'<div$1 ' . $attribute . '="' . esc_attr( $value ) . '">',
			$block_content,
			1
		);
		return is_string( $result ) ? $result : (string) $block_content;
	}

	/**
	 * Custom render function for group blocks with visual transition.
	 *
	 * @param string               $block_content The block content about to be rendered.
	 * @param array<string, mixed> $block The block data.
	 * @return string Modified block content.
	 */
	public static function render_block_with_html_attributes( string $block_content, array $block ): string {
		if ( ! isset( $block['blockName'] ) || ! isset( $block['attrs'] ) || ! is_array( $block['attrs'] )
			|| ! isset( $block['attrs']['visualTransitionName'] ) ) {
			return $block_content;
		}
		// add attribute to the block (for the frontend)
		if ( 'core/group' === $block['blockName'] && ! empty( $block['attrs']['visualTransitionName'] ) ) {
			$random_id     = 'vt_' . wp_generate_uuid4();
			$block_content = self::inject_attribute_to_first_div(
				$block_content,
				'data-cocovisualtransitionid',
				$random_id
			);
			$atts          = [
				'pattern-height' => isset( $block['attrs']['patternHeight'] ) && is_numeric( $block['attrs']['patternHeight'] ) ? (float) $block['attrs']['patternHeight'] : 0.08,
				'pattern-width'  => isset( $block['attrs']['patternWidth'] ) && is_numeric( $block['attrs']['patternWidth'] ) ? (float) $block['attrs']['patternWidth'] : 0.1,
				'y-offset'       => isset( $block['attrs']['YOffset'] ) && is_numeric( $block['attrs']['YOffset'] ) ? (float) $block['attrs']['YOffset'] : 0.0,
				'type-pattern'   => isset( $block['attrs']['typePattern'] ) && in_array( $block['attrs']['typePattern'], [ '%', 'px' ], true ) ? $block['attrs']['typePattern'] : '%',
				'only-desktop'   => ! empty( $block['attrs']['onlyDesktop'] ),
			];
			$pattern       = is_string( $block['attrs']['visualTransitionName'] )
				? $block['attrs']['visualTransitionName']
				: '';
			$svg_and_style = InlineCSS_Cache::get( $pattern, $random_id, $atts );
			if ( null === $svg_and_style ) {
				$rendered      = InlineCSS_Renderer::generate_svg_and_css( $pattern, $random_id, $atts );
				$svg_and_style = $rendered['svg'] . $rendered['css'];
				InlineCSS_Cache::set( $pattern, $random_id, $atts, $svg_and_style );
			}
			$block_content .= $svg_and_style;
		}
		return $block_content;
	}
}
