<?php
/**
* @package wp-sparkpost
*/
namespace WPSparkPost;

class TestTemplates extends \WP_UnitTestCase {
    function test_it_should_set_mailer_obj(){
      $mailer = new SparkPostHTTPMailer();
      $templateObj = new SparkPostTemplates($mailer);

      $this->assertTrue($templateObj->mailer instanceof SparkPostHTTPMailer);
    }

    function test_preview_returns_template_data() {
      $mailer = $this->getMockBuilder('WPSparkPost\SparkPostHTTPMailer')
        ->setMethods(array('request', 'get_request_headers', 'error', 'debug'))
        ->getMock();

      $response = array(
        'response'  => array(
          'code'  => 200
        ),
        'headers' => array(),
        'body' =>  json_encode(array(
          'results' => array(
              'from' => array(
                  'from' =>   'me@hello.com',
                  'from_name' => 'me'
              ),
              'subject' => 'test subject',
              'headers' => array(),
              'html'  => '<h1>Hello there<h1>',
              'text'  => 'hello there',
              'reply_to'  => 'me@hello.com'
          )
        ))
      );

      $mailer->expects($this->once())
        ->method('request')
        ->will($this->returnValue($response));

      $mailer->expects($this->never())
        ->method('error');

      $mailer->expects($this->exactly(3))
        ->method('debug');

      $templateObj = new SparkPostTemplates($mailer);
      $result = $templateObj->preview('abcd', array());
      $this->assertEquals($result->subject, 'test subject');
      $this->assertEquals($result->html, '<h1>Hello there<h1>');
      $this->assertEquals($result->text, 'hello there');
    }

    function test_preview_returns_false_on_error() {
      $mailer = $this->getMockBuilder('WPSparkPost\SparkPostHTTPMailer')
        ->setMethods(array('request', 'get_request_headers', 'error', 'debug'))
        ->getMock();

      $response = array(
        'response'  => array(
          'code'  => 200
        ),
        'headers' => array(),
        'body' =>  json_encode(array(
          'errors' => array(
            'some interesting error'
          )
        ))
      );

      $mailer->expects($this->once())
        ->method('request')
        ->will($this->returnValue($response));

      $mailer->expects($this->once())
        ->method('error');

      $mailer->expects($this->exactly(4))
        ->method('debug');


      $templateObj = new SparkPostTemplates($mailer);
      $result = $templateObj->preview('abcd', array());
      $this->assertEquals($result, false);
    }

    function test_preview_returns_false_on_unknown_error() {
      $mailer = $this->getMockBuilder('WPSparkPost\SparkPostHTTPMailer')
        ->setMethods(array('request', 'get_request_headers', 'error', 'debug'))
        ->getMock();

      $response = array(
        'response'  => array(
          'code'  => 200
        ),
        'headers' => array(),
        'body' =>  json_encode(array('unknown_key' => 'unknown result'))
      );

      $mailer->expects($this->once())
        ->method('request')
        ->will($this->returnValue($response));

      $mailer->expects($this->once())
        ->method('error');

      $mailer->expects($this->exactly(4))
        ->method('debug');

      $templateObj = new SparkPostTemplates($mailer);
      $result = $templateObj->preview('abcd', array());
      $this->assertEquals($result, false);
    }
}
