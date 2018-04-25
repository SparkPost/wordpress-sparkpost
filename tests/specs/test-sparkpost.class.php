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

    function test_us_hostnames() {
        $us_api = 'https://api.sparkpost.com';
        $us_smtp = 'smtp.sparkpostmail.com';

        $this->assertTrue($this->SparkPost->get_hostname('api') === $us_api);
        $this->assertTrue($this->SparkPost->get_hostname() === $us_api);
        $this->assertTrue($this->SparkPost->get_hostname('smtp') === 'smtp.sparkpostmail.com');
    }

    function test_eu_hostnames() {
        $eu_api = 'https://api.eu.sparkpost.com';
        $eu_smtp = 'smtp.eu.sparkpostmail.com';

        $this->SparkPost->settings['location'] = 'eu';

        $this->assertTrue($this->SparkPost->get_hostname('api') === 'https://api.eu.sparkpost.com');
        $this->assertTrue($this->SparkPost->get_hostname('smtp') === 'smtp.eu.sparkpostmail.com');
    }
}
