<?php
/**
* @package wp-sparkpost
*/
namespace WPSparkPost;

class TestHttpMailer extends TestSparkPost {
  var $mailer;

  function setUp() {
    $this->mailer = new SparkPostHTTPMailer();
  }

  function call($method) {
    return $this->invokeMethod($this->mailer, $method);
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
    $this->assertTrue($this->call('get_recipients') == $prepared_list);
  }

  function test_sender_with_name() {
    $this->mailer->setFrom( 'me@hello.com', 'me' );
    $sender = array(
      'name' => 'me',
      'email' => 'me@hello.com'
    );

    $this->assertTrue($this->call('get_sender') == $sender);
  }

  function test_sender_without_name() {
    $this->mailer->setFrom( 'me@hello.com', '' );
    $sender = array(
      'email' => 'me@hello.com'
    );

    $this->assertTrue($this->call('get_sender') == $sender);
  }

  function test_get_request_headers() {
    $settings = $this->getProperty($this->mailer, 'settings');
    $expected = array(
      'User-Agent' => 'wordpress-sparkpost/' . WPSP_PLUGIN_VERSION,
      'Content-Type' => 'application/json',
      'Authorization' => $settings['password']
    );
    $this->assertTrue( $this->call('get_request_headers') == $expected);
  }

}
