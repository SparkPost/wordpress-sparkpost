<?php
/**
 * @package wp-sparkpost
 */

namespace WPSparkPost;

class TestHttpMailer extends TestSparkPost {
  var $mailer;

  function setUp() {
    global $phpmailer;
    $this->phpmailer = new SparkPostHTTPMailer();
  }

  function call($method) {
    return $this->invokeMethod($this->phpmailer, $method);
  }

	function test_mailer_is_a_phpmailer_instance() {
		$this->assertTrue( $this->phpmailer instanceof \PHPMailer );
	}

  function test_recipients_list() {

    $this->phpmailer->addAddress('abc@xyz.com', 'abc');
    $this->phpmailer->addAddress('def@xyz.com', 'def');
    $this->phpmailer->addAddress('noname@xyz.com');
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
    $this->phpmailer->setFrom( 'me@hello.com', 'me' );
    $sender = array(
      'name' => 'me',
      'email' => 'me@hello.com'
    );

    $this->assertTrue($this->call('get_sender') == $sender);
  }

  function test_sender_without_name() {
    $this->phpmailer->setFrom( 'me@hello.com', '' );
    $sender = array(
      'email' => 'me@hello.com'
    );

    $this->assertTrue($this->call('get_sender') == $sender);
  }

}
