<?php
/**
 * CSS Template for Visual Transition
 *
 * @package CocoVisualTransition
 *
 * phpcs:disable
 */

/** @var string $id Unique identifier for the transition */
/** @var string $pattern_id Pattern ID for the SVG reference */
/** @var bool $is_mask Whether to use mask instead of clip-path */
/** @var array<string, mixed> $atts Additional attributes including y-offset */

// Extract and sanitize all attributes in $atts that we might use.
$y_offset = is_string( $atts['y-offset'] ) || is_numeric( $atts['y-offset'] ) ? (float) $atts['y-offset'] : 0;

?>
<style id="coco-vt-<?php echo esc_attr( $id ); ?>">
	[data-cocovisualtransitionid="<?php echo esc_attr( $id ); ?>"]{
		<?php if ( ! $is_mask ) : ?>
			clip-path: url(#<?php echo esc_attr( $pattern_id ); ?>);
			-webkit-clip-path: url(#<?php echo esc_attr( $pattern_id ); ?>);
		<?php else : ?>
			mask: url(#<?php echo esc_attr( $pattern_id ); ?>);
			-webkit-mask: url(#<?php echo esc_attr( $pattern_id ); ?>);
		<?php endif; ?>

		<?php
		// We add negative margin to 'merge' the core/block with the previous block on top.
		// the YOffset actually changes the style.margin-top, so this wouldn't be needed, but it helps to understand.
		/** @phpstan-ignore offsetAccess.nonOffsetAccessible */
		if ( ! empty( $y_offset ) ) :
			?>
			/** @phpstan-ignore cast.string */
			margin-top: <?php echo esc_html( (string) $y_offset ); ?>px;
		<?php endif; ?>
	}
</style>

<?php
// phpcs:enable