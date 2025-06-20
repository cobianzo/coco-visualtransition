<?php

/**
 * Useful for debugging:
 * print_r( getenv() );
 */

if ( 'tests-mysql' === getenv( 'WORDPRESS_DB_HOST' ) || ! empty( getenv( 'IS_WATCHING' ) ) ) {
	require 'bootstrap-wp-env.php';
	// we are in wp-env (local), we know it because the host is tests-mysql and the db is tests-wordpress
} else {
	// we are outside the dockerized env, ie in github actions, in wp-content/plugins/cartelera-scrap/ folder of  WordPress installation
	require 'bootstrap-standard.php';
}
return;
