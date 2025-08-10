<?php
/**
 * CSS Template for Visual Transition (typePattern 'px')
 *
 * @package CocoVisualTransition
 *
 * phpcs:disable
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * @var string $id Unique identifier for the transition
 * @var string $pattern_id Pattern ID for the SVG reference
 * @var bool $is_mask Whether to use mask instead of clip-path
 * @var array<string, mixed> $atts Additional attributes including y-offset
 * @var string $selector The data attribute selector for the CSS ('data-cocovisualtransitionid' | 'data-block' )
 */
$y_offset = is_string( $atts['y-offset'] ) || is_numeric( $atts['y-offset'] ) ? (float) $atts['y-offset'] : 0;
?>
<style id="coco-vt-<?php echo esc_attr( $id ); ?>" class="coco-vt--style-px">
	<?php ob_start(); ?>
		[<?php echo esc_attr( $selector ); ?>="<?php echo esc_attr( $id ); ?>"]{

			<?php
			if ( ! empty( $y_offset ) ) :
				?>
				margin-top: <?php echo esc_html( (string) $y_offset ); ?>px;
			<?php endif; ?>

			&::before {
				content: '';
				height: <?php echo esc_attr( $atts['pattern-height'] ); ?>px;
				width: 100%;
				left: 0;
				top: 0;
				background: inherit;
				position: absolute;
				translate: 0 -100%;

					<?php if ( ! $is_mask ) : ?>
						clip-path: url(#<?php echo esc_attr( $pattern_id ); ?>);
						-webkit-clip-path: url(#<?php echo esc_attr( $pattern_id ); ?>);
					<?php else : ?>
						mask: url(#<?php echo esc_attr( $pattern_id ); ?>);
						-webkit-mask: url(#<?php echo esc_attr( $pattern_id ); ?>);
					<?php endif; ?>
			}
		}
	<?php
	$css = ob_get_clean();
	if ( ! empty( $atts['only-desktop'] ) ) {
		$css = "@media (min-width: 769px) { $css }";
	}
	echo apply_filters( 'coco_visual_transition_css', $css, $id, $atts );
	?>
</style>
<?php
// phpcs:enable