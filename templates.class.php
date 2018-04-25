<?php

namespace WPSparkPost;
// If ABSPATH is defined, we assume WP is calling us.
// Otherwise, this could be an illicit direct request.
if (!defined('ABSPATH')) exit();

class SparkPostTemplates
{
    public $endpoint;

    public function __construct($mailer)
    {
        $this->mailer = $mailer;
        
        $this->endpoint = apply_filters('sp_hostname', 'api') . '/api/v1/templates';
    }

    public function preview($id, $substitution_data)
    {
        $endpoint = apply_filters('sp_api_location', $this->endpoint);
        $url = "{$endpoint}/{$id}/preview?draft=false";

        $body = array(
            'substitution_data' => $substitution_data
        );

        $data = array(
            'method' => 'POST',
            'timeout' => 15,
            'headers' => $this->mailer->get_request_headers(),
            'body' => json_encode($body)
        );

        $this->mailer->debug('Making template API request');
        $this->mailer->debug(print_r($data, true));

        $response = $this->mailer->request($url, $data);
        $this->mailer->debug('Template API request completed');
        $this->mailer->check_permission_error($response, 'Templates: Read/Write');

        $body = json_decode($response['body']);

        if (property_exists($body, 'errors')) {
            $this->mailer->debug('Error in getting template data');
            $this->mailer->error($body->errors);
            return false;
        }

        if (property_exists($body, 'results')) {
            return $body->results;
        } else {
            $this->mailer->debug('API response is unknown');
            $this->mailer->error('Unknown response');
            return false;
        }
    }

}
