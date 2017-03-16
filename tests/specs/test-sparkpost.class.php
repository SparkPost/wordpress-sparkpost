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

    function test_obfuscate_api_key() {
        $original_key='my_secret_key';
        $obfuscated_key=SparkPost::obfuscate_api_key($original_key);

        $this->assertNotFalse(strpos($obfuscated_key, '*'));
        $this->assertEquals(substr($original_key, 0, 4), substr($obfuscated_key, 0, 4));
    }

    function test_is_key_obfuscated() {
        $this->assertTrue(SparkPost::is_key_obfuscated('my_obfuscated_***'));
        $this->assertFalse(SparkPost::is_key_obfuscated('my_unobfuscated_key'));
    }

    function test_is_sandbox() {
        $this->assertTrue(SparkPost::is_sandbox('testing@sparkpostbox.com'));
        $this->assertFalse(SparkPost::is_sandbox('testing@mydoman.com'));
    }
}
