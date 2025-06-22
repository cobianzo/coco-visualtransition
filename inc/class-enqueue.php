<?php
/**
 * Enqueue class to handle scripts and styles
 *
 * @package CocoVisualTransition
 */

namespace COCO\VisualTransition;

/**
 * The class.
 */
class Enqueue {

	/**
	 * Constructor. Call hooks.
	 */
	public static function init(): void {

		// editor.
		add_action( 'enqueue_block_editor_assets', [ __CLASS__, 'enqueue_scripts_for_editor_block' ] );

		// frontend.
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_group_block_styles_frontend' ] );
	}

	/**
	 * Editor. Enqueue scripts and styles.
	 */
	public static function enqueue_scripts_for_editor_block(): void {

		/** @var array{dependencies: string[], version: string} $asset_file */
		$asset_file = include plugin_dir_path( __DIR__ ) . 'build/index.asset.php';

		// Gutenberg filters, plugins, js in general.
		wp_enqueue_script(
			'coco-visualtransition',
			plugins_url( '/build/index.js', __DIR__ ),
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);

		// The css. editor. Loads all the styles (TODO: inefficient, we showld load them dynamically only when needed).
		wp_enqueue_style(
			'coco-visualtransition-group-block-editor',
			plugins_url( '/build/index.css', __DIR__ ),
			[],
			$asset_file['version']
		);
	}

	/**
	 * Frontend. Enqueue styles dynamically!
	 * CSS classes can be heavy and we should not loaded if not needed.
	 * So we check the post_content before loading it
	 *
	 * @return void
	 */
	public static function enqueue_group_block_styles_frontend(): void {

		// validations. Only in pages and post pages, and containing the visual transition class.
		if ( ! is_singular() ) {
			return;
		}
		global $post;
		/** @var \WP_Post $post */
		if ( strpos( $post->post_content, 'coco-has-visualtransition' ) === false ) {
			return;
		}

		/** @var array{dependencies: string[], version: string} $asset_file */
		$asset_file = include plugin_dir_path( __DIR__ ) . 'build/index.asset.php';

		// Load patterns from JSON file
		$patterns      = [];
		$patterns_file = plugin_dir_path( __DIR__ ) . 'src/patterns.json';
		if ( file_exists( $patterns_file ) ) {
			/** @var array{name: string, value: string}[] $patterns */
			$patterns = wp_json_file_decode( $patterns_file, [ 'associative' => true ] );
		}

		foreach ( (array) $patterns as $pattern ) {
			$pattern_name = $pattern['value'];
			// Check if the pattern is used in the post content.
			if ( strpos( $post->post_content, $pattern_name ) !== false ) {
				// Enqueue the CSS only for the for the pattern.
				// wp_enqueue_style(
				// 'coco-visualtransition-' . $pattern_name,
				// plugins_url( "/src/css/pattern-$pattern_name.css", __DIR__ ),
				// [],
				// $asset_file['version']
				// );
			}
		}
	}
}

Enqueue::init();