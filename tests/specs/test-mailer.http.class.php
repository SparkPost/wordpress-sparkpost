<?php
/**
* @package wp-sparkpost
*/
namespace WPSparkPost;
use \Nyholm\NSA;
use \Mockery;
use phpmock\phpunit\PHPMock;


class TestHttpMailer extends \WP_UnitTestCase {
  use PHPMock;

  var $mailer;

  function setUp() {
    $this->mailer = new SparkPostHTTPMailer();
  }

  public function tearDown() {
     \Mockery::close();
  }

  function test_mailSend_calls_sparkpost_send() {
    $stub = Mockery::mock($this->mailer);
    $stub->shouldReceive('sparkpost_send')->andReturn('woowoo');

    $this->assertTrue(NSA::invokeMethod($stub, 'mailSend', null, null) == 'woowoo');
  }

  function test_mailer_is_a_mailer_instance() {
    $this->assertTrue( $this->mailer instanceof \PHPMailer );
  }

  function test_get_sender_with_name() {
    $this->mailer->setFrom( 'me@hello.com', 'me' );
    $sender = array(
      'name' => 'me',
      'email' => 'me@hello.com'
    );

    $this->assertTrue(NSA::invokeMethod($this->mailer, 'get_sender') == $sender);
  }

  function test_get_sender_without_name() {
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

  function test_get_recipients() {
    $this->mailer->addAddress('to@abc.com');
    $this->mailer->addAddress('to1@abc.com', 'to1');
    $this->mailer->addCc('cc@abc.com');
    $this->mailer->addCc('cc1@abc.com', 'cc1');
    $this->mailer->addBcc('bcc@abc.com');
    $this->mailer->addBcc('bcc1@abc.com', 'bcc1');

    $header_to = implode(', ', [
      'to@abc.com',
      'to1 <to1@abc.com>',
    ]);

    $expected = [
      [
        'address' => [
          'email' => 'to@abc.com',
          'header_to' => $header_to
        ]
      ],
      [
        'address' => [
          'email' => 'to1@abc.com',
          'header_to' => $header_to
        ]
      ],
      [
        'address' => [
          'email' => 'bcc@abc.com',
          'header_to' => $header_to
        ]
      ],
      [
        'address' => [
          'email' => 'bcc1@abc.com',
          'header_to' => $header_to
        ]
      ],
      [
        'address' => [
          'email' => 'cc@abc.com',
          'header_to' => $header_to
        ]
      ],
      [
        'address' => [
          'email' => 'cc1@abc.com',
          'header_to' => $header_to
        ]
      ]
    ];

    $recipients = NSA::invokeMethod($this->mailer, 'get_recipients');
    $this->assertTrue($recipients == $expected);
  }

  function test_get_attachments() {
    /* TODO avoid writing to actual file. */
    $temp = tempnam('/tmp', 'php-wordpress-sparkpost');
    file_put_contents($temp, 'TEST');
    $this->mailer->addAttachment($temp);
    $attachments = NSA::invokeMethod($this->mailer, 'get_attachments');
    $this->assertTrue($attachments[0]['type'] === 'application/octet-stream');
    $this->assertTrue($attachments[0]['name'] === basename($temp));
    $this->assertTrue($attachments[0]['data'] === base64_encode('TEST'));
    unlink($temp);
  }

  function test_isMail() {
    // test if isMail sets correct mailer
    $this->mailer->Mailer = 'abc';
    $this->assertTrue($this->mailer->Mailer === 'abc');
    $this->mailer->isMail();
    $this->assertTrue($this->mailer->Mailer === 'sparkpost');
  }

  function test_get_request_body_without_template() {
    // WITHOUT TEMPLATE
    $this->mailer->addAddress('abc@xyz.com', 'abc');
    $this->mailer->addBcc('bcc@xyz.com', 'bcc');
    $this->mailer->addCc('cc@xyz.com', 'cc');
    $this->mailer->setFrom( 'me@hello.com', 'me');

    NSA::setProperty($this->mailer, 'settings', [
      'enable_tracking' => true,
      'transactional' => false,
      'template' => ''
    ]);

    $header_to = 'abc <abc@xyz.com>';
    $expected_request_body = [
      'recipients' => [
        [
          'address' => [
            'email' => 'abc@xyz.com',
            'header_to' => $header_to
          ]
        ],
        [
          'address' => [
            'email' => 'bcc@xyz.com',
            'header_to' => $header_to
          ]
        ],
        [
          'address' => [
            'email' => 'cc@xyz.com',
            'header_to' => $header_to
          ]
        ]
      ],
      'options' => [
        'open_tracking' => (bool) true,
        'click_tracking' => (bool) true,
        'transactional' => (bool) false
      ],
      'content' => [
        'from' => [
          'name' => 'me',
          'email' =>'me@hello.com'
        ],
        'subject' => '',
        'headers' => [],
        'text' => ''
      ]
    ];

    $actual = NSA::invokeMethod($this->mailer, 'get_request_body');
    // for simpler expectation reset content.headers to empty array.
    // alternative is to stub get_headers which isn't working expectedly
    $actual['content']['headers'] = [];
    $this->assertTrue($expected_request_body == $actual);

    //INCLUDE REPLYTO
    $this->mailer->addReplyTo('reply@abc.com', 'reply-to');
    $this->mailer->addCustomHeader('Reply-To', 'reply-to <reply@abc.com>'); //for below version v4.6
    $actual = NSA::invokeMethod($this->mailer, 'get_request_body');
    $actual['content']['headers'] = []; //see note above
    $expected_request_body['content']['reply_to'] = 'reply-to <reply@abc.com>';
    $this->assertTrue($expected_request_body == $actual);
  }

  function test_get_request_body_template_in_hook_but_not_in_settings() {
    $this->mailer->addAddress('abc@xyz.com', 'abc');
    $this->mailer->setFrom( 'me@hello.com', 'me');

    $callback = function(){
      return 'test-template';
    };

    add_filter('wpsp_template_id', $callback);

    NSA::setProperty($this->mailer, 'settings', [
      'enable_tracking' => true,
      'transactional' => false,
      'template' => ''
    ]);

    $body = NSA::invokeMethod($this->mailer, 'get_request_body');
    remove_filter('wpsp_template_id', $callback);
    $this->assertTrue($body['content']['template_id'] == 'test-template');
  }

  function test_get_request_body_with_template() {
    $this->mailer->addAddress('abc@xyz.com', 'abc');
    $this->mailer->addBcc('bcc@xyz.com', 'bcc');
    $this->mailer->addCc('cc@xyz.com', 'cc');
    $this->mailer->setFrom( 'me@hello.com', 'me');
    $header_to = 'abc <abc@xyz.com>';
    NSA::setProperty($this->mailer, 'settings', [
      'enable_tracking' => true,
      'transactional' => false,
      'template'   => 'hello'
    ]);

    $expected_request_body = [
      'recipients' => [
        [
          'address' => [
            'email' => 'abc@xyz.com',
            'header_to' => $header_to
          ]
        ],
        [
          'address' => [
            'email' => 'bcc@xyz.com',
            'header_to' => $header_to
          ]
        ],
        [
          'address' => [
            'email' => 'cc@xyz.com',
            'header_to' => $header_to
          ]
        ]
      ],
      'options' => [
        'open_tracking' => (bool) true,
        'click_tracking' => (bool) true,
        'transactional' => (bool) false
      ],
      'content' => [
        'template_id' => 'hello',
      ],
      'substitution_data' => [
        'content' => '',
        'subject' => '',
        'from_name' => 'me',
        'from' => 'me@hello.com',
        'from_localpart'  => 'me'
      ]
    ];

    $actual = NSA::invokeMethod($this->mailer, 'get_request_body');
    $this->assertTrue($expected_request_body == $actual);

    //INCLUDE REPLYTO
    $this->mailer->addReplyTo('reply@abc.com', 'reply-to');
    $this->mailer->addCustomHeader('Reply-To', 'reply-to <reply@abc.com>'); //for below version v4.6
    $actual = NSA::invokeMethod($this->mailer, 'get_request_body');
    $expected_request_body['substitution_data']['reply_to'] = 'reply-to <reply@abc.com>';
    $this->assertTrue($expected_request_body == $actual);
  }

  function test_get_request_body_with_template_and_attachments() {
    $template_data =  (object) array(
        'from' => array(
            'from' =>   'me@hello.com',
            'from_name' => 'me'
        ),
        'subject' => 'test subject',
        'headers' => array(),
        'html'  => '<h1>Hello there<h1>',
        'text'  => 'hello there',
        'reply_to'  => 'me@hello.com'
    );
    $attachments_data = [
      'name'  => 'php-wordpress-sparkpost.txt',
      'type'  => 'plain/text',
      'data'  => base64_encode('TEST')
    ];

    $mailer = $this->getMockBuilder('WPSparkPost\SparkPostHTTPMailer')
      ->setMethods(array('get_attachments'))
      ->getMock();

    $template = $this->getMockBuilder('WPSparkPost\SparkPostTemplates')
      ->setConstructorArgs(array($mailer))
      ->setMethods(array('preview'))
      ->getMock();

    $template->expects($this->once())
      ->method('preview')
      ->will($this->returnValue($template_data));

    $mailer->template = $template;

    $mailer->addAddress('abc@xyz.com', 'abc');
    $mailer->setFrom( 'me@hello.com', 'me');


    $mailer->expects($this->once())
      ->method('get_attachments')
      ->will($this->returnValue($attachments_data));

    $header_to = 'abc <abc@xyz.com>';
    NSA::setProperty($mailer, 'settings', [
      'enable_tracking' => true,
      'transactional' => false,
      'template'   => 'hello'
    ]);

    $expected_request_body = [
      'recipients' => [
        [
          'address' => [
            'email' => 'abc@xyz.com',
            'header_to' => $header_to
          ]
        ]
      ],
      'options' => [
        'open_tracking' => (bool) true,
        'click_tracking' => (bool) true,
        'transactional' => (bool) false
      ],
      'content' => [
        'from'  =>  [
          'from_name'  => 'me',
          'from'  => 'me@hello.com'
        ],
        'subject' => 'test subject',
        'html'  => '<h1>Hello there<h1>',
        'text'  => 'hello there',
        'reply_to'  => 'me@hello.com',
        'attachments' => $attachments_data
      ]
    ];

    $actual = NSA::invokeMethod($mailer, 'get_request_body');
    unset($actual['content']['headers']); //to simplify assertion
    $this->assertTrue($expected_request_body == $actual);
  }

  function test_sparkpost_send_false_on_error() {
    $mailer = $this->getMockBuilder('WPSparkPost\SparkPostHTTPMailer')
      ->setMethods(array('get_request_body'))
      ->getMock();

    $mailer->expects($this->once())
      ->method('get_request_body')
      ->will($this->returnValue(false));

    $result = NSA::invokeMethod($mailer, 'sparkpost_send');
    $this->assertEquals($result, false);
  }

  function test_get_request_body_with_attachments_returns_false_on_error() {
    $mailer = $this->getMockBuilder('WPSparkPost\SparkPostHTTPMailer')
      ->setMethods(array('get_attachments', 'get_sender','get_reply_to', 'get_template_substitutes'))
      ->getMock();

    $mailer->expects($this->once())
      ->method('get_attachments')
      ->will($this->returnValue(array('name' => 'test-attachment.txt')));

    $template = $this->getMockBuilder('WPSparkPost\SparkPostTemplates')
      ->setConstructorArgs(array($mailer))
      ->setMethods(array('preview'))
      ->getMock();

    $template->expects($this->once())
      ->method('preview')
      ->will($this->returnValue(false));

    NSA::setProperty($mailer, 'settings', [
      'enable_tracking' => true,
      'transactional' => false,
      'template'   => 'hello'
    ]);

    $mailer->template = $template;

    $result = NSA::invokeMethod($mailer, 'get_request_body');
    $this->assertEquals($result, false);
  }

  function test_get_request_body_content_type_text_plain() {
    $this->mailer->ContentType = 'text/plain';
    $this->mailer->Body = '<h1>hello world</h1>';
    $this->mailer->AltBody = 'hello world';
    $actual = NSA::invokeMethod($this->mailer, 'get_request_body');

    $this->assertFalse(array_key_exists('html', $actual['content']));
    $this->assertEquals($actual['content']['text'], '<h1>hello world</h1>');
  }

  function test_get_request_body_content_type_multipart() {
    $this->mailer->ContentType = 'multipart/alternative';
    $this->mailer->Body = '<h1>hello world</h1>';
    $this->mailer->AltBody = 'hello world';
    $actual = NSA::invokeMethod($this->mailer, 'get_request_body');

    $this->assertEquals($actual['content']['html'], '<h1>hello world</h1>');
    $this->assertEquals($actual['content']['text'], 'hello world');
  }

  function test_get_request_body_content_type_default() {
    $this->mailer->ContentType = null;
    $this->mailer->Body = '<h1>hello world</h1>';
    $this->mailer->AltBody = 'hello world';
    $actual = NSA::invokeMethod($this->mailer, 'get_request_body');

    $this->assertEquals($actual['content']['html'], '<h1>hello world</h1>');
    $this->assertFalse(array_key_exists('text', $actual['content']));
  }

  function test_get_request_body_with_attachments() {
    /* TODO avoid creating actual file */
    $temp = tempnam('/tmp', 'php-wordpress-sparkpost');
    $this->mailer->addAttachment($temp);
    $actual = NSA::invokeMethod($this->mailer, 'get_request_body');
    $this->assertEquals(count($actual['content']['attachments']), 1);
    unlink($temp);
  }

  function test_get_request_body_with_sandbox() {
    $mailer = $this->getMockBuilder('WPSparkPost\SparkPostHTTPMailer')
      ->setMethods(array('get_attachments', 'get_reply_to', 'get_recipients'))
      ->getMock();

    $mailer->addAddress('abc@xyz.com', 'abc');
    $mailer->setFrom( 'me@sparkpostbox.com', 'me');
    NSA::setProperty($mailer, 'settings', [
      'enable_tracking' => true,
      'transactional' => false,
      'template'  => null
    ]);

    $body = NSA::invokeMethod($mailer, 'get_request_body');
    $this->assertTrue($body['options']['sandbox'] == true);
  }

  function sparkpost_send_prepare_mocks($num_rejected) {
    $this->mailer->addAddress('abc@xyz.com', 'abc');
    $response = array(
      'response'  => array(
        'code'  => 200
      ),
      'headers' => array(),
      'body' => json_encode(array(
        'results' => array(
          'total_rejected_recipients' => $num_rejected,
          'total_accepted_recipients' => 1,
          'id'  => 88388383737373
        )
      ))
    );
    $http_lib_mock = Mockery::mock('httplib', array('request' => $response ));
    $lib_mock = $this->getFunctionMock(__NAMESPACE__, '_wp_http_get_object');
    $lib_mock->expects($this->at(0))->willReturn($http_lib_mock);

    return;
  }

  function test_sparkpost_send_success() {
    $this->sparkpost_send_prepare_mocks(0);
    $this->assertTrue($this->mailer->sparkpost_send());
  }

  function test_sparkpost_send_failure() {
    $this->sparkpost_send_prepare_mocks(1);
    $this->assertFalse($this->mailer->sparkpost_send());
  }

  function test_sparkpost_send_false_on_wp_error() {
    $response = new \WP_Error(500, 'some error');
    $http_lib_mock = Mockery::mock('httplib', array('request' => $response ));
    $this
      ->getFunctionMock(__NAMESPACE__, '_wp_http_get_object')
      ->expects($this->at(0))->willReturn($http_lib_mock);


    $this->assertFalse($this->mailer->sparkpost_send());
  }

  function test_sparkpost_send_skip_processing() {
    // Testing that it should not handle response if wpsp_handle_response hook returns boolean
    $this->sparkpost_send_prepare_mocks(1); // set to return false; will override below
    $callback = function(){
      return true; // returns true even if http response (mock) was supposed to cause it return false
    };
    add_filter('wpsp_handle_response', $callback);
    $this->assertTrue($this->mailer->sparkpost_send());
    remove_filter('wpsp_handle_response', $callback);
  }

  function test_sparkpost_send_response_with_errors() {
    $response = array(
      'headers' => array(),
      'body' => json_encode(array(
        'errors' => array(
          'you are done'
        )
      ))
    );
    $http_lib_mock = Mockery::mock('httplib', array('request' => $response ));
    $this
      ->getFunctionMock(__NAMESPACE__, '_wp_http_get_object')
      ->expects($this->at(0))->willReturn($http_lib_mock);

    $this->assertFalse($this->mailer->sparkpost_send());
  }

  function test_sparkpost_send_response_unknown_api_response() {
    $response = array(
      'headers' => array(),
      'body' => json_encode(array(
        'something_else' => array()
      ))
    );
    $http_lib_mock = Mockery::mock('httplib', array('request' => $response ));
    $this
      ->getFunctionMock(__NAMESPACE__, '_wp_http_get_object')
      ->expects($this->at(0))->willReturn($http_lib_mock);

    $this->assertFalse($this->mailer->sparkpost_send());
  }

  function test_sparkpost_send_response_uncaught() {
    $response = array(
      'headers' => array(),
      'body' => json_encode(array(
        'results' => array(
          'total_rejected_recipients' => 0,
          'total_accepted_recipients' => 0
        )
      ))
    );
    $http_lib_mock = Mockery::mock('httplib', array('request' => $response ));
    $this
      ->getFunctionMock(__NAMESPACE__, '_wp_http_get_object')
      ->expects($this->at(0))->willReturn($http_lib_mock);

    $this->assertFalse($this->mailer->sparkpost_send());
  }

  function test_parse_reply_to_from_custom_header() {
    NSA::setProperty($this->mailer, 'CustomHeader', array(array('Reply-To', 'abc@xyz.com')));

    $this->assertEquals(NSA::invokeMethod($this->mailer, 'parse_reply_to_from_custom_header'), 'abc@xyz.com');
  }

  function test_parse_reply_to() {
    NSA::setProperty($this->mailer, 'ReplyTo', array(
      array('abc@xyz.com', 'abc'),
      array('def@xyz.com', '')
    ));
    $actual = 'abc <abc@xyz.com>,def@xyz.com';
    $this->assertEquals(NSA::invokeMethod($this->mailer, 'parse_reply_to'), $actual);
  }

  function test_get_reply_to_below_wp46(){
    NSA::setProperty($this->mailer, 'CustomHeader', array(array('Reply-To', 'abc@xyz.com')));
    $GLOBALS['wp_version'] = '4.5';
    $this->assertEquals(NSA::invokeMethod($this->mailer, 'get_reply_to'), 'abc@xyz.com');
  }

  function test_check_permission_error(){
    $response = [
      'response' => [
        'code' => 403
      ]
    ];

    $mailer = $this->getMockBuilder('WPSparkPost\SparkPostHTTPMailer')
      ->setMethods(array('debug', 'error'))
      ->getMock();

    $this->assertTrue($mailer->check_permission_error($response, 'test_perm') === true);

    $response['response']['code'] = 200;
    $this->assertTrue($mailer->check_permission_error($response, 'test_perm') === false);
  }


}
