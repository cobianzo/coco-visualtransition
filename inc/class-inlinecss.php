<?php

/**
 * InlineCSS Class
 *
 * This class handles the inline CSS functionality for the Visual Transition plugin.
 * It provides methods to add and manage inline CSS styles.
 *
 * @package    VisualTransition
 * @subpackage InlineCSS
 * @since      1.0.0
 */

/** @phpstan-type BlockAttributes array{
 *   pattern-height?: string
 * }
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
class InlineCSS {


	public static function init(): void {
		add_filter( 'render_block', [ __CLASS__, 'my_custom_group_block_render' ], 10, 2 );
	}

	public static function my_custom_group_block_render( $block_content, $block ) {
		// print_r($block['attrs']);
		if ( 'core/group' === $block['blockName'] && ! empty( $block['attrs']['visualTransitionName'] ) ) {


			// adds an id as data attr to the container, so we can apply the css to that single element.
			$random_id     = 'vt_' . wp_generate_uuid4();
			$block_content = preg_replace(
				'/<div\b(.*?)>/',
				'<div$1 data-cocovisualtransitionid="' . $random_id . '">',
				$block_content,
				1
			);

			$atts = [];

			$pattern = $block['attrs']['visualTransitionName'];

			$svg_and_style  = self::insert_inline_css( $pattern, $random_id, $atts );
			$block_content .= $svg_and_style;

		}

		return $block_content;
	}

	/**

	 */
	/** @var BlockAttributes $atts */
	public static function insert_inline_css( string $pattern, string $id, $atts = [] ): string {

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
