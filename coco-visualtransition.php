<?php
/**
 * Plugin Name: Coco Visual Transition
 * Plugin URI: https://example.com/plugins/coco-visualtransition/
 * Description: A plugin for visual transitions
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
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
define( 'COCO_VT_VERSION', '1.0.1' );
define( 'COCO_VT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'COCO_VT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main plugin class
 */
final class Coco_Visual_Transition {

	/**
	 * Instance of this class
	 *
	 * @var Coco_Visual_Transition
	 */
	private static $instance;

	/**
	 * Get instance of this class
	 *
	 * @return Coco_Visual_Transition
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Initialize plugin
	 *
	 * @return void
	 */
	private function init() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		// Add more initialization code here.
	}

	/**
	 * Load plugin textdomain
	 * // @TODO: localization in package, via wp cli.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'coco-visualtransition',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);
	}
}

// Initialize the plugin.
Coco_Visual_Transition::get_instance();
