<?php
/**
 * @package wp-sparkpost
 */

namespace WPSparkPost;

class TestWordPressSparkPost extends \WP_UnitTestCase {

	function test_plugin_dir_constants() {
		$this->assertTrue( defined('WPSP_PLUGIN_DIR') );
	}

  function test_plugin_path_constants() {
		$this->assertTrue( defined('WPSP_PLUGIN_PATH') );
    $this->assertTrue( strpos(WPSP_PLUGIN_PATH, '/wordpress-sparkpost/wordpress-sparkpost.php') !== false); // full path not available in test mode
	}

  function test_SparkPost_class_loaded() {
    $this->assertTrue( class_exists('WPSparkPost\SparkPost'));
  }
}
