<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Wordpress_Sparkpost
 */

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
	require dirname( dirname( __FILE__ ) ) . '/wordpress-sparkpost.php';
	require dirname( dirname( __FILE__ ) ) . '/mailer.http.class.php';

}
tests_add_filter( 'plugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
require __DIR__. '/wp-sparkpost.php';

function get_protected_method($class, $method, $args) {
	$class = new ReflectionClass($class);
	$method = $class->getMethod($method);
	$method->setAccessible(true);
	return $method;
}
