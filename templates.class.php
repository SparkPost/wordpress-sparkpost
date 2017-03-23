<?php
namespace WPSparkPost;
// If ABSPATH is defined, we assume WP is calling us.
// Otherwise, this could be an illicit direct request.
if (!defined('ABSPATH')) exit();
// require_once WPSP_PLUGIN_DIR '/sparkpost.class.php';

class SparkPostTemplates {
  public $endpoint = 'https://api.sparkpost.com/api/v1/templates';

  public function __construct($mailer){
    $this->mailer = $mailer;
    $this->settings = SparkPost::get_settings();
  }

  public function preview($id, $substitution_data){
    $url = "{$this->endpoint}/{$id}/preview?draft=false";
    $http = $this->mailer->get_http_lib();

    $body = array(
      'substitution_data' => $substitution_data
    );

    $data = array(
      'method' => 'POST',
      'timeout' => 15,
      'headers' => $this->mailer->get_request_headers(),
      'body' => json_encode($body)
    );

    $response = $http->request($url, $data);
    $body = json_decode($response['body']);

    if (property_exists($body, 'errors')) {
      $this->mailer->edebug('Error in getting template data');
      $this->mailer->setError($body->errors);
      return false;
    }

    if (property_exists($body, 'results')) {
      return $body->results;
    } else {
      $this->mailer->edebug('API response is unknown');
      $this->mailer->setError('Unknown response');
      return false;
    }
  }

}
