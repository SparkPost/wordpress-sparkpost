<?php
/**
 * Class SampleTest
 *
 * @package Wordpress_Sparkpost
 */

/**
 * Sample test case.
 */
class SampleTest extends WP_UnitTestCase {

	/**
	 * A single example test.
	 */
	function test_sample() {
		// Replace this with some actual testing code.
		$this->assertTrue( true );
	}

  function test_truthy() {
		$this->assertFalse(true);
	}
}
