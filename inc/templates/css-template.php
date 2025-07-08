<?php
/**
 * CSS Template for Visual Transition
 *
 * @package CocoVisualTransition
 *
 * phpcs:disable
 */

/**
 * @var string $id Unique identifier for the transition
 * @var string $pattern_id Pattern ID for the SVG reference
 * @var bool $is_mask Whether to use mask instead of clip-path
 * @var array<string, mixed> $atts Additional attributes including y-offset
 * @var string $selector The data attribute selector for the CSS ('data-cocovisualtransitionid' | 'data-block' )
 */


// Extract and sanitize all attributes in $atts that we might use.
$y_offset = is_string( $atts['y-offset'] ) || is_numeric( $atts['y-offset'] ) ? (float) $atts['y-offset'] : 0;

?>
<style id="coco-vt-<?php echo esc_attr( $id ); ?>">

	<?php
	// ['data-cocovisualtransitionid'] {  css rules ... }
	?>
	[<?php echo esc_attr( $selector ); ?>="<?php echo esc_attr( $id ); ?>"]{
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


		<?php
		/*

		TODO: create an alternative way to create patterns
		&::before {
			content: '';
        height: 100px;
        background: green;
        position: absolute;
        width: 100%;
        left: 0;
        translate: 0 -100%;
        clip-path: url(#pattern-vt_e2013ca6-a8b8-4bf6-ade8-120b8b463ee3);
        top: 0;
		}
		*/
		?>
	}
</style>

<?php
// phpcs:enable