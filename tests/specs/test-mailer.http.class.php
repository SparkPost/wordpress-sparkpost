<?php
/**
* @package wp-sparkpost
*/
namespace WPSparkPost;
use \Nyholm\NSA;
use \Mockery;

class TestHttpMailer extends \WP_UnitTestCase {
  var $mailer;

  function setUp() {
    $this->mailer = new SparkPostHTTPMailer();
  }

  function test_mailer_is_a_mailer_instance() {
    $this->assertTrue( $this->mailer instanceof \PHPMailer );
  }

  function test_recipients_list() {

    $this->mailer->addAddress('abc@xyz.com', 'abc');
    $this->mailer->addAddress('def@xyz.com', 'def');
    $this->mailer->addAddress('noname@xyz.com');
    $prepared_list = array(
      array(
        'address' => array(
          'email' => 'abc@xyz.com',
          'name' => 'abc',
        )
      ),
      array(
        'address' => array(
          'name' => 'def',
          'email' => 'def@xyz.com'
        )
      ),
      array(
        'address' => array(
          'email' => 'noname@xyz.com',
          'name' => ''
        )
      )
    );
    $this->assertTrue(NSA::invokeMethod($this->mailer, 'get_recipients') == $prepared_list);
  }

  function test_sender_with_name() {
    $this->mailer->setFrom( 'me@hello.com', 'me' );
    $sender = array(
      'name' => 'me',
      'email' => 'me@hello.com'
    );

    $this->assertTrue(NSA::invokeMethod($this->mailer, 'get_sender') == $sender);
  }

  function test_sender_without_name() {
    $this->mailer->setFrom( 'me@hello.com', '' );
    $sender = array(
      'email' => 'me@hello.com'
    );

    $this->assertTrue(NSA::invokeMethod($this->mailer, 'get_sender') == $sender);
  }

  function test_get_request_headers() {
    $expected = array(
      'User-Agent' => 'wordpress-sparkpost/' . WPSP_PLUGIN_VERSION,
      'Content-Type' => 'application/json',
      'Authorization' => ''
    );
    $this->assertTrue(NSA::invokeMethod($this->mailer, 'get_request_headers') == $expected);

    NSA::setProperty($this->mailer, 'settings', array('password' => 'abcd1234'));
    $expected = array(
      'User-Agent' => 'wordpress-sparkpost/' . WPSP_PLUGIN_VERSION,
      'Content-Type' => 'application/json',
      'Authorization' => 'abcd1234'
    );
    $this->assertTrue(NSA::invokeMethod($this->mailer, 'get_request_headers') == $expected);
  }

  function test_get_request_headers_obfuscate_key() {
    NSA::setProperty($this->mailer, 'settings', array('password' => 'abcd1234'));
    $expected = array(
      'User-Agent' => 'wordpress-sparkpost/' . WPSP_PLUGIN_VERSION,
      'Content-Type' => 'application/json',
      'Authorization' => 'abcd'.str_repeat('*', 36)
    );
    $this->assertTrue(NSA::invokeMethod($this->mailer, 'get_request_headers', true) == $expected);
  }

  function test_get_headers() {
    $raw_headers = "Date: Wed, 26 Oct 2016 23:45:32 +0000
    To: undisclosed-recipients:;
    From: Root User <root@localhost>
    Subject: Hello
    Reply-To: replyto@mydomain.com
    Message-ID: <abcd@example.org>
    MIME-Version: 1.0
    Content-Type: text/plain; charset=iso-8859-1
    Content-Transfer-Encoding: 8bit";

    $expected = array(
      'Message-ID' => '<abcd@example.org>',
      'Date' => 'Wed, 26 Oct 2016 23:45:32 +0000'
    );
    $stub = Mockery::mock($this->mailer);
    $stub->shouldReceive('createHeader')->andReturn($raw_headers);
    $formatted_headers = NSA::invokeMethod($stub, 'get_headers');

    $this->assertTrue($formatted_headers == $expected);
  }


  function test_get_headers_should_include_cc_if_exists() {
    $raw_headers = "Date: Wed, 26 Oct 2016 23:45:32 +0000
    Reply-To: replyto@mydomain.com";

    $expected = array(
      'Date' => 'Wed, 26 Oct 2016 23:45:32 +0000',
      'CC' => 'hello@abc.com,Name <name@domain.com>'
    );
    $stub = Mockery::mock($this->mailer);
    $stub->shouldReceive('createHeader')->andReturn($raw_headers);
    $stub->addCc('hello@abc.com');
    $stub->addCc('name@domain.com', 'Name');

    $formatted_headers = NSA::invokeMethod($stub, 'get_headers');

    $this->assertTrue($formatted_headers == $expected);
  }
}
