<?php
/**
 * InlineCSS Bootstrapper
 *
 * @package    CocoVisualTransition
 * @subpackage InlineCSS
 * @since      1.0.0
 */

namespace COCO\VisualTransition;

use COCO\VisualTransition\Controllers\InlineCSS_REST_Controller;
use COCO\VisualTransition\Controllers\InlineCSS_Block_Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bootstrapper for InlineCSS logic.
 */
final class InlineCSS {
	/**
	 * Initialize the class and set up hooks
	 *
	 * @return void
	 */
	public static function init(): void {

		// Register block render filter: injects stuff in the block in frontend
		InlineCSS_Block_Controller::register();
		// Register REST API endpoint: injects stuff in the block in the editor
		InlineCSS_REST_Controller::register();
	}
}

InlineCSS::init();
