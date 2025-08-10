<?php
/**
 * Plugin Name: Coco Visual Transition
 * Plugin URI: https://cobianzo.com/plugins/coco-visualtransition/
 * Description: A plugin for visual transitions
 * Version: 3.0.1
 * Author: cobianzo
 * Author URI: https://cobianzo.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: coco-visualtransition
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.1
 *
 * @package CocoVisualTransition
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define plugin constants
 */
define( 'COCO_VT_VERSION', '3.0.1' );
define( 'COCO_VT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'COCO_VT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Initialize plugin
 *
 * @return void
 */
function coco_vt_init(): void {

	// includes
	require_once COCO_VT_PLUGIN_DIR . 'inc/helpers/class-generic-helpers.php';
	require_once COCO_VT_PLUGIN_DIR . 'inc/helpers/class-svgpath-helpers.php';
	require_once COCO_VT_PLUGIN_DIR . 'inc/class-admin-enqueue.php';

	// services
	require_once COCO_VT_PLUGIN_DIR . 'inc/inlinecss/class-inlinecss-cache.php';
	require_once COCO_VT_PLUGIN_DIR . 'inc/inlinecss/class-inlinecss-renderer.php';

	// controllers
	require_once COCO_VT_PLUGIN_DIR . 'inc/inlinecss/class-inlinecss-block-controller.php';
	require_once COCO_VT_PLUGIN_DIR . 'inc/inlinecss/class-inlinecss-rest-controller.php';

	require_once COCO_VT_PLUGIN_DIR . 'inc/inlinecss/class-inlinecss.php';

	require_once COCO_VT_PLUGIN_DIR . 'inc/svg-generators/class-svg-generator.php';
}

// Initialize the plugin
coco_vt_init();

// Debugging functions.
// phpcs:disable
/**
 * Debug function to dump variables
 *
 * @param mixed $var Variable to dump
 * @return void
 */
if ( ! function_exists( 'dd' ) ) {
	function dd( mixed $var ): void {
		echo '<pre>';
		var_dump( $var );
		echo '</pre>';
	}
}

/**
 * Debug function to dump variables and die
 *
 * @param mixed $var Variable to dump
 * @return void
 */
if ( ! function_exists( 'ddie' ) ) {
	function ddie( mixed $var = '' ): void {
		dd( $var );
		wp_die();
	}
}
// phpcs:enable
