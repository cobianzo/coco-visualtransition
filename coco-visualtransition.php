<?php
/**
 * Plugin Name: Coco Visual Transition
 * Plugin URI: https://cobianzo.com/plugins/coco-visualtransition/
 * Description: A plugin for visual transitions
 * Version: 1.0.3
 * Author: cobianzo
 * Author URI: https://cobianzo.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: coco-visualtransition
 * Domain Path: /languages
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
define( 'COCO_VT_VERSION', '1.0.3' );
define( 'COCO_VT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'COCO_VT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load plugin textdomain
 *
 * @return void
 */
function coco_vt_load_textdomain() {
	load_plugin_textdomain(
		'coco-visualtransition',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}

/**
 * Initialize plugin
 */
function coco_vt_init() {
	// quick hooks
	add_action( 'plugins_loaded', 'coco_vt_load_textdomain' );

	// includes
	require_once COCO_VT_PLUGIN_DIR . 'inc/class-enqueue.php';
}

// Initialize the plugin
coco_vt_init();
