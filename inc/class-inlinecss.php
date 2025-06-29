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

use Coco\VisualTransition\SVG_Generator;
use Coco\VisualTransition\SVG_Generator_Factory;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * InlineCSS Class
 *
 * Main class for handling inline CSS and SVG generation for visual transitions.
 */
final class InlineCSS {

	/**
	 * Initialize the class and set up hooks
	 *
	 * @return void
	 */
	public static function init(): void {

		// For the Frontend.
		add_filter( 'render_block', [ __CLASS__, 'render_block_with_html_attributes' ], 10, 2 );

		// For the Editor. Rest endpoint handler for generating SVG and CSS on demand.
		add_action( 'rest_api_init', [ __CLASS__, 'register_rest_route_for_editor_use' ] );
	}


	/**
	 * Reusable in Frontend and Backend.
	 * Generate and insert inline CSS and SVG for visual transitions
	 *
	 * @param string                      $pattern The pattern name to generate. ie 'waves'
	 * @param string                      $id Unique identifier for the transition.
	 * @param array<string, string|float> $atts Additional attributes for the pattern. ie ['pattern-height' => 0.08]
	 * @return string Generated SVG and CSS styles
	 */
	public static function insert_inline_css( string $pattern, string $id, array $atts = [] ): string {
		require_once plugin_dir_path( __FILE__ ) . 'svg-generators/class-svg-generator-factory.php';

		$generator = SVG_Generator_Factory::create( $pattern, $id, $atts );
		$svg       = $generator->svg_string;

		/**
		 * $atts ie: [
		 *   'pattern-height' => 0.08,
		 *   'pattern-width'  => 0.1,
		 *   'y-offset'       => -0.12,
		 * ]
		 */
		// The inline css. When used in the editor, we select the div with [data-block], not [data-cocovisualtransitionid]
		$pattern_id = $generator->get_pattern_id();
		ob_start(); ?>
		<style id="coco-vt-<?php echo esc_attr( $id ); ?>">
				[data-cocovisualtransitionid="<?php echo esc_attr( $id ); ?>"]{
						clip-path: url(#<?php echo esc_attr( $pattern_id ); ?>);
						webkit-clip-path: url(#<?php echo esc_attr( $pattern_id ); ?>);
						<?php
						// We add negative margin to 'merge' the core/block with the previous block on top.
						// the YOffset actually changes the style.margin-top, so this wouldn't be needed, but it helps to understand.
						if ( ! empty( $atts['y-offset'] ) ) :
							?>
						margin-top: <?php echo esc_html( (string) $atts['y-offset'] ); ?>px;
							<?php
						endif;
						?>
				}
		</style>
		<?php
		$css = ob_get_clean();

		return $svg . $css;
	}


	/**
	 * Frontend: Custom render function for group blocks with visual transition.
	 * Appends, after the render of the block, the tags for <svg> and <style> elements.
	 *
	 * @phpstan-type MyBlockType array<string, mixed>
	 *
	 * @param string               $block_content The block content about to be rendered
	 * @param array<string, mixed> $block Los datos del bloque a procesar.
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
				/**
				 * Pattern height value for the transition effect
				 *
				 * @phpstan-ignore cast.double
				 */
				'pattern-height' => isset( $block['attrs']['patternHeight'] ) ? (float) $block['attrs']['patternHeight'] : 0.08,
				/**
				 * Pattern width value for the transition effect
				 *
				 * @phpstan-ignore cast.double
				 */
				'pattern-width'  => isset( $block['attrs']['patternWidth'] ) ? (float) $block['attrs']['patternWidth'] : 0.1,
				/**
				 * Y-axis offset value for positioning the transition
				 *
				 * @phpstan-ignore cast.double
				 */
				'y-offset'       => isset( $block['attrs']['YOffset'] ) ? (float) $block['attrs']['YOffset'] : 0.0,
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
	 * REST API handler for generating SVG and CSS on demand
	 * Usage: wp-json/coco/v1/vtstyle/?block_id=unique_id&pattern_name=squares&pattern_atts={"patternHeight":0.08}
	 *
	 * @return void
	 */
	public static function register_rest_route_for_editor_use(): void {

		register_rest_route( 'coco/v1', '/vtstyle', [
			'methods'             => 'POST',
			'allow_non_ssl'       => true, // Allow non-SSL requests for GET method
			'callback'            => function ( \WP_REST_Request $request ) {

				$params = $request->get_params();

				if ( ! isset( $params['block_id'] ) || ! isset( $params['pattern_name'] ) ) {
					return new \WP_Error(
						'missing_params',
						'Missing required parameters: block_id and pattern_name are required',
						[ 'status' => 400 ]
					);
				}

				/**
				 * Pattern name from request parameters
				 *
				 * @var string $pattern_name
				 */
				$pattern_name = $params['pattern_name'];

				/**
				 * Block identifier from request parameters
				 *
				 * @var string $block_id
				 */
				$block_id = $params['block_id'];

				/**
				 * Pattern attributes with height settings
				 *
				 * @var array<string, string|float> $pattern_attrs
				 */
				$pattern_attrs = isset( $params['pattern_atts'] ) ? (array) $params['pattern_atts'] : [];

				// Generate SVG and CSS
				$svg_and_style = self::insert_inline_css(
					sanitize_text_field( $pattern_name ),
					sanitize_text_field( $block_id ),
					[
						'pattern-height' => isset( $pattern_attrs['patternHeight'] ) ? (float) $pattern_attrs['patternHeight'] : 0.08,
						'pattern-width'  => isset( $pattern_attrs['patternWidth'] ) ? (float) $pattern_attrs['patternWidth'] : 0.1,
						'y-offset'       => isset( $pattern_attrs['YOffset'] ) ? (int) $pattern_attrs['YOffset'] : 0,
					]
				);

				// change the name of the selector, which is different in the editor than in the FE.
				$svg_and_style = str_replace( 'data-cocovisualtransitionid', 'data-block', $svg_and_style );

				return $svg_and_style;
			},
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
}

InlineCSS::init();
