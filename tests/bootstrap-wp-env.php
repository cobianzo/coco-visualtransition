<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Create_Block_Theme
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	echo '‼️ - We didnt find WP_TEST_DIR, so we look for the wordpress-tests-lib in the system temp dir';
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
	echo "\n\n" . $_tests_dir;
}

if ( ! file_exists( $_tests_dir ) ) {
	echo '‼️ - We didnt find the folder $_tests_dir : ' . $_tests_dir . '' . "\n\n\n exiting... ";
	exit;
} else {
	echo '✅ - bootstrap.php - Step 1. Found ' . $_tests_dir . "\n";
}
echo "\n We will load includes/functions.php with the basic functions to run tests.\n\n";

if ( ! file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
	echo "‼️ Ey ! Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once "{$_tests_dir}/includes/functions.php";

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( __DIR__ ) . '/cartelera-scrap.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
$generic_bootstrap = "{$_tests_dir}/includes/bootstrap.php";
if ( ! file_exists( $generic_bootstrap ) ) {
	echo '‼️ - We didnt find ' . $generic_bootstrap . '.';
	exit;
} else {
	echo '✅ - bootstrap.php - Step 3. Found ' . $generic_bootstrap . ".\n\n";
	require "{$_tests_dir}/includes/bootstrap.php";
}



echo "\n🔚🎭=========🔚🎭========🔚🎭+=============\n";
echo "======E N D   B O O T S T R A P . P H P ===\n";
echo "🔚🎭=========🔚🎭========🔚🎭+=============\n\n";
