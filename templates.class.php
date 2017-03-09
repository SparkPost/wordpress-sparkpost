<?php
namespace WPSparkPost;
// If ABSPATH is defined, we assume WP is calling us.
// Otherwise, this could be an illicit direct request.
if (!defined('ABSPATH')) exit();
// require_once WPSP_PLUGIN_DIR '/sparkpost.class.php';

class SparkPostTemplates {
  public $endpoint = 'https://api.sparkpost.com/api/v1/templates';

  public function __construct(){
    $this->settings = SparkPost::get_settings();
  }

  protected function get_request_headers($hide_api_key = false){
      $api_key = apply_filters('wpsp_api_key', $this->settings['password']);

      return apply_filters('wpsp_request_headers', array(
          'User-Agent' => 'wordpress-sparkpost/' . WPSP_PLUGIN_VERSION,
          'Content-Type' => 'application/json',
          'Authorization' => $api_key
      ));
  }

  public function preview($id, $substitution_data){
    $url = "{$this->endpoint}/{$id}/preview?draft=false";
    $http = apply_filters('wpsp_get_http_lib', _wp_http_get_object());

    $body = array(
      'substitution_data' => $substitution_data
    );

    $data = array(
      'method' => 'POST',
      'timeout' => 15,
      'headers' => $this->get_request_headers(),
      'body' => json_encode($body)
    );

    $response = $http->request($url, $data);
    $body = json_decode($response['body']);

    if (property_exists($body, 'errors')) {
      $this->edebug('Error in getting template data');
      $this->setError($body->errors);
      return false;
    }

    if (property_exists($body, 'results')) {
      return $body->results;
    } else {
      $this->edebug('API response is unknown');
      $this->setError('Unknown response');
      return false;
    }
  }

}
