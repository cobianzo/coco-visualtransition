<?php
/**
 * PHPUnit bootstrap file.
 * This is the bootstrap setup for non dockerized environments.
 * The difference with wp-env is that this one calls tests/wp-config.php to connect to the test DB,
 * and uses the WP core files where this plugin is installed, under wp-content/plugins.
 */

/*
Beware, $_ENV is not the same as getenv() when running outside wp-env
	before starting, we should ahve called tests with the env variable:
	:=> WP_PHPUNIT__TESTS_CONFIG=tests/wp-config.php
*/


if ( ! file_exists( getenv( 'WP_PHPUNIT__TESTS_CONFIG' ) ) ) {
	echo PHP_EOL . '⚠️ In this setup of PHPUnit, we need the file wp-config.php in getenv "WP_PHPUNIT`__TESTS_CONFIG" '
		. PHP_EOL . 'Try' . PHP_EOL . 'WP_PHPUNIT__TESTS_CONFIG=tests/wp-config.php composer run test' . PHP_EOL;
	exit;
} else {
	echo PHP_EOL . '✔️ Found ' . getenv( 'WP_PHPUNIT__TESTS_CONFIG' ) . PHP_EOL;
}

// WordPress for testing is installed with wp cli
$_wp_unit_vendor = getenv( 'WP_PHPUNIT__DIR' ) ? getenv( 'WP_PHPUNIT__DIR' ) : 'vendor/wp-phpunit/wp-phpunit';


$_phpunit_polyfills_path = getenv( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' );
$_phpunit_polyfills_path = empty( $_phpunit_polyfills_path ) ? 'vendor/yoast/phpunit-polyfills' : $_phpunit_polyfills_path;

// Composer autoloader must be loaded before WP_PHPUNIT__DIR will be available
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// echo PHP_EOL. 'WP_PHPUNIT__DIR: ' . getenv( 'WP_PHPUNIT__DIR' );
// echo PHP_EOL. 'WP_PHPUNIT__TESTS_CONFIG' . getenv( 'WP_PHPUNIT__TESTS_CONFIG' );
// echo PHP_EOL. 'WP_TESTS_DIR:' . $_tests_dir;
// echo PHP_EOL. 'WP_TESTS_PHPUNIT_POLYFILLS_PATH:' . $_phpunit_polyfills_path;
// echo PHP_EOL;
// exit;

// Forward custom PHPUnit Polyfills configuration to PHPUnit bootstrap file.
if ( false !== $_phpunit_polyfills_path ) {
	define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_phpunit_polyfills_path );
}

if ( ! file_exists( "{$_wp_unit_vendor}/includes/functions.php" ) ) {
	echo "⚠️ Could not find {$_wp_unit_vendor}/includes/functions.php, hace you run composer install, and set the WP_PHPUNIT__DIR env var ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_wp_unit_vendor . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	$plugin_path = dirname( __DIR__ ) . '/aside-related-article-block.php';
	if ( ! file_exists( $plugin_path ) ) {
		echo '⚠️ >>>>>>>>>>> ERROR Plugin not found at ' . $plugin_path . PHP_EOL;
		return;
	}
	echo PHP_EOL . '===========' . PHP_EOL .
		'✔️ Artificially required the plugin at ' . PHP_EOL .
		$plugin_path . PHP_EOL . '===========' . PHP_EOL;
	require $plugin_path;
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require "{$_wp_unit_vendor}/includes/bootstrap.php";
