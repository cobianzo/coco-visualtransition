<?php
/**
 * InlineCSS Class
 *
 * This class is responsible to render in the Editor and in the Frontend the
 * CSS and SVG elements to create the Visual Transition effect.
 *
 * @package    VisualTransition
 * @subpackage InlineCSS
 * @since      1.0.0
 */

namespace COCO\VisualTransition;

use Coco\VisualTransition\SVG_Generator_Factory;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * InlineCSS Class
 */
final class InlineCSS {

	/**
	 * Initialize the class and set up hooks
	 *
	 * @return void
	 */
	public static function init(): void {
		add_filter( 'render_block', [ __CLASS__, 'render_block_with_html_attributes' ], 10, 2 );
	}

	/**
	 * Custom render function for group blocks with visual transition
	 *
	 * @param string               $block_content The block content about to be rendered
	 * @param array<string, mixed> $block The block object, with the attributes and inner blocks.
	 * @return string Modified block content
	 */
	public static function render_block_with_html_attributes( string $block_content, array $block ): string {

		// validation, only evaluate if there is a visualtransition pattern associated to the block.
		if ( ! isset( $block['blockName'] ) || ! isset( $block['attrs'] ) || ! is_array( $block['attrs'] )
			|| ! isset( $block['attrs']['visualTransitionName'] ) ) {
			return $block_content;
		}

		if ( 'core/group' === $block['blockName'] && ! empty( $block['attrs']['visualTransitionName'] ) ) {

			$random_id     = 'vt_' . wp_generate_uuid4();
			$block_content = preg_replace(
				'/<div\b(.*?)>/',
				'<div$1 data-cocovisualtransitionid="' . $random_id . '">',
				$block_content,
				1
			);

			$atts = [
				'pattern-height' => '',
			];

			$pattern = is_string( $block['attrs']['visualTransitionName'] )
				? $block['attrs']['visualTransitionName']
				: '';

			$svg_and_style  = self::insert_inline_css( $pattern, $random_id, $atts );
			$block_content .= $svg_and_style;
		}

		return $block_content;
	}

	/**
	 * Generate and insert inline CSS and SVG for visual transitions
	 *
	 * @param string                $pattern The pattern name to generate.
	 * @param string                $id Unique identifier for the transition.
	 * @param array<string, mixed > $atts Additional attributes for the pattern.
	 * @return string Generated SVG and CSS styles
	 */
	public static function insert_inline_css( string $pattern, string $id, array $atts = [] ): string {
		require_once plugin_dir_path( __FILE__ ) . 'svg-generators/class-svg-generator-factory.php';
		$generator = SVG_Generator_Factory::create( $pattern, $id, $atts );
		$svg       = $generator->generate_svg();

		$style = '<style>
            [data-cocovisualtransitionid="' . $id . '"]{
                clip-path: url(#' . $id . ');
                webkit-clip-path: url(#' . $id . ');
            }
        </style>';

		return $svg . $style;
	}
}

InlineCSS::init();
