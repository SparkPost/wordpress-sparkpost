<?php
/**
* @package wp-sparkpost
*/
namespace WPSparkPost;

class TestSparkPost extends \WP_UnitTestCase {
    var $SparkPost;

    function setUp() {
        $this->SparkPost = new SparkPost();
    }

    function test_is_sandbox() {
        $this->assertTrue(SparkPost::is_sandbox('testing@sparkpostbox.com'));
        $this->assertFalse(SparkPost::is_sandbox('testing@mydoman.com'));
    }
}
