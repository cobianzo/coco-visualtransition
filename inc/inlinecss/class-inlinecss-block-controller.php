<?php
/**
 * InlineCSS Block Controller using wp_add_inline_style
 *
 * This version uses WordPress's wp_add_inline_style function to add CSS to the document head
 * instead of injecting it inline with the content. This provides better separation of concerns
 * and follows WordPress best practices.
 *
 * Responsibilities:
 * - Registers the render_block filter to modify block output at render time.
 * - Detects core/group blocks with visual transition attributes.
 * - Injects a unique data attribute for identification and styling.
 * - Retrieves or generates the required SVG and CSS for the visual transition effect.
 * - Adds CSS to document head using wp_add_inline_style.
 * - Appends only the SVG to the block's rendered HTML.
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
 * Controller for handling the render_block filter for visual transition using wp_add_inline_style.
 */
final class InlineCSS_Block_Controller {
	
	/**
	 * Whether the base style has been enqueued.
	 *
	 * @var bool
	 */
	private static bool $base_style_enqueued = false;

	/**
	 * Register the render_block filter.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_filter( 'render_block', [ __CLASS__, 'render_block_with_html_attributes' ], 10, 2 );
	}

	/**
	 * Ensure base style is enqueued and ready for inline styles.
	 *
	 * @return void
	 */
	private static function ensure_base_style(): void {
		if ( self::$base_style_enqueued ) {
			return;
		}
		
		// Register and enqueue a base handle for our inline styles
		wp_register_style( 'coco-visual-transition', false );
		wp_enqueue_style( 'coco-visual-transition' );
		
		self::$base_style_enqueued = true;
	}

	/**
	 * Add CSS to be included in the head using wp_add_inline_style.
	 *
	 * @param string $css CSS content to add.
	 * @return void
	 */
	private static function add_inline_style( string $css ): void {
		self::ensure_base_style();
		
		// Remove <style> tags if present and get clean CSS
		$clean_css = preg_replace( '/<style[^>]*>(.*?)<\/style>/s', '$1', $css );
		$clean_css = trim( $clean_css );
		
		if ( ! empty( $clean_css ) ) {
			wp_add_inline_style( 'coco-visual-transition', $clean_css );
		}
	}

	/**
	 * Injects an attribute into the first <div> of the block content.
	 * We use it on the html of the block, to add a class to the div.
	 *
	 * @param string $block_content The block content HTML.
	 * @param string $attribute The attribute name to add.
	 * @param string $value The value of the attribute.
	 * @return string Modified block content with the attribute injected.
	 */
	private static function inject_attribute_to_first_div( string $block_content, string $attribute, string $value ): string {
		$result = preg_replace(
			'/<div\b(.*?)>/',
			'<div$1 ' . $attribute . '="' . esc_attr( $value ) . '">',
			$block_content,
			1
		);
		return is_string( $result ) ? $result : (string) $block_content;
	}

	/**
	 * Extract SVG content from combined SVG+CSS content.
	 *
	 * @param string $combined_content Combined SVG and CSS content.
	 * @return string SVG content only.
	 */
	private static function extract_svg_only( string $combined_content ): string {
		// Split by <style> tag and return everything before it
		$parts = preg_split( '/<style[^>]*>/', $combined_content, 2 );
		return isset( $parts[0] ) ? trim( $parts[0] ) : '';
	}

	/**
	 * Extract CSS content from combined SVG+CSS content.
	 *
	 * @param string $combined_content Combined SVG and CSS content.
	 * @return string CSS content only.
	 */
	private static function extract_css_only( string $combined_content ): string {
		if ( preg_match( '/<style[^>]*>(.*?)<\/style>/s', $combined_content, $matches ) ) {
			return trim( $matches[1] );
		}
		return '';
	}

	/**
	 * Custom render function for group blocks with visual transition.
	 * This version separates SVG (inline) from CSS (head).
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

		// Process core/group blocks with visual transition
		if ( 'core/group' === $block['blockName'] && ! empty( $block['attrs']['visualTransitionName'] ) ) {
			$random_id     = 'vt_' . wp_generate_uuid4();
			$block_content = self::inject_attribute_to_first_div(
				$block_content,
				'data-cocovisualtransitionid',
				$random_id
			);
			
			$atts = [
				'pattern-height' => isset( $block['attrs']['patternHeight'] ) && is_numeric( $block['attrs']['patternHeight'] ) ? (float) $block['attrs']['patternHeight'] : 0.08,
				'pattern-width'  => isset( $block['attrs']['patternWidth'] ) && is_numeric( $block['attrs']['patternWidth'] ) ? (float) $block['attrs']['patternWidth'] : 0.1,
				'y-offset'       => isset( $block['attrs']['YOffset'] ) && is_numeric( $block['attrs']['YOffset'] ) ? (float) $block['attrs']['YOffset'] : 0.0,
				'type-pattern'   => isset( $block['attrs']['typePattern'] ) && in_array( $block['attrs']['typePattern'], [ '%', 'px' ], true ) ? $block['attrs']['typePattern'] : '',
				'only-desktop'   => ! empty( $block['attrs']['onlyDesktop'] ),
			];

			$pattern = is_string( $block['attrs']['visualTransitionName'] )
				? $block['attrs']['visualTransitionName']
				: '';

			// Get SVG and CSS (from cache or generate new)
			$svg_and_style = InlineCSS_Cache::get( $pattern, $random_id, $atts );
			if ( null === $svg_and_style ) {
				$rendered      = InlineCSS_Renderer::generate_svg_and_css( $pattern, $random_id, $atts );
				$svg_and_style = $rendered['svg'] . $rendered['css'];
				InlineCSS_Cache::set( $pattern, $random_id, $atts, $svg_and_style );
			}

			// Separate SVG and CSS
			$svg_content = self::extract_svg_only( $svg_and_style );
			$css_content = self::extract_css_only( $svg_and_style );

			// Add CSS to head using wp_add_inline_style
			if ( ! empty( $css_content ) ) {
				self::add_inline_style( $css_content );
			}

			// Add only SVG to block content
			$block_content .= $svg_content;
		}
		
		return $block_content;
	}
}
