<?php
/**
 * Enqueue class to handle scripts and styles in the Editor and de
 * admin area in general.
 *
 * @package CocoVisualTransition
 */

namespace COCO\VisualTransition;

/**
 * The class.
 */
class Admin_Enqueue {

	/**
	 * Constructor. Call hooks.
	 */
	public static function init(): void {

		// editor.
		add_action( 'enqueue_block_editor_assets', [ __CLASS__, 'enqueue_scripts_for_editor_block' ] );
	}

	/**
	 * Editor. Enqueue scripts and styles.
	 */
	public static function enqueue_scripts_for_editor_block(): void {

		/**
		 * Asset file containing dependencies and version information.
		 *
		 * @var array{dependencies: string[], version: string} $asset_file
		 */
		$asset_file = include plugin_dir_path( __DIR__ ) . 'build/index.asset.php';

		$script_handle = 'coco-visualtransition';

		// Register the script before enqueueing.
		wp_register_script(
			$script_handle,
			plugins_url( '/build/index.js', __DIR__ ),
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);

		// Localize script to expose custom variables to window object
		// TODELETE: we are not using this nonce yet.
		wp_localize_script(
			$script_handle,
			'cocoVisualTransition',
			[
				'nonce' => wp_create_nonce( 'coco_visual_transition_nonce' ),
			]
		);

		// Load JSON translations for the plugin.
		$languages_dir = plugin_dir_path( __DIR__ ) . 'languages';
		wp_set_script_translations(
			$script_handle,
			'coco-visualtransition',
			$languages_dir
		);

		wp_enqueue_script( $script_handle );
	}
}

Admin_Enqueue::init();
