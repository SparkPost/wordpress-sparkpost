<?php
/**
 * @package wp-sparkpost
 */

namespace WPSparkPost {

require __DIR__ . '/vendor/autoload.php';

class TestWordPressSparkPost extends \WP_UnitTestCase {
	public static function setUpBeforeClass() {
      global $globalStub;
      $globalStub->setNamespace('WPSparkPost');
  }

	public function tearDown() {
        echo is_admin()."\n";
        echo \is_admin()."\n";
    }

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

	function test_is_admin() {
		global $globalStub;
    $globalStub->method('is_admin')->willReturn('local function');
		$this->assertEquals('local function', is_admin());
		//
		$this->assertTrue(class_exists('WPSparkPost\SparkPostSMTPMailer'));
	}

}
}
