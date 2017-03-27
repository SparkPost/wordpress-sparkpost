<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Wordpress_Sparkpost
 */
define('BASE_DIR', dirname(dirname( dirname( __FILE__ ))));
$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require BASE_DIR . '/wordpress-sparkpost.php';
	require BASE_DIR . '/templates.class.php';
	require BASE_DIR . '/mailer.http.class.php';
}

tests_add_filter( 'plugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
require dirname(__DIR__) . '/vendor/autoload.php';
