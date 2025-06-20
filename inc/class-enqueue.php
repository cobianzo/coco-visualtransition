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
	public static function init() {
			add_action( 'enqueue_block_editor_assets', [ __CLASS__, 'enqueue_scripts' ] );
	}

	/**
	 * Enqueue scripts and styles
	 */
	public static function enqueue_scripts() {

		$asset_file = include plugin_dir_path( __DIR__ ) . 'build/index.asset.php';

		wp_enqueue_script(
			'coco-visualtransition',
			plugins_url( '/build/index.js', __DIR__ ),
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);

		wp_localize_script(
			'coco-visualtransition',
			'cocoData',
			[
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			]
		);
	}
}

Enqueue::init();
