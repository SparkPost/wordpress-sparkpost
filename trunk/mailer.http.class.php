<?php
// If ABSPATH is defined, we assume WP is calling us.
// Otherwise, this could be an illicit direct request.
if (!defined('ABSPATH')) exit();


require_once ABSPATH . WPINC . '/class-phpmailer.php';

class SparkPostHTTPMailer extends PHPMailer
{
    protected $endpoint = 'https://api.sparkpost.com/api/v1/transmissions';
    private $options;

    function __construct($exceptions = false)
    {
        $this->options = get_option('sp_settings');

        parent::__construct($exceptions);
    }

    function sparkpostSend()
    {
        $this->edebug('Preparing request data');

        $data = array(
            'method' => 'POST',
            'timeout' => 15,
            'headers' => $this->get_request_headers(),
            'body' => json_encode($this->get_body())

        );

        $http = _wp_http_get_object();

        /* TODO remove the api key from header */
        $this->edebug(sprintf('Request headers: %s', print_r($this->get_request_headers(true), true)));
        $this->edebug(sprintf('Request body: %s', $data['body']));
        $this->edebug(sprintf('Making HTTP POST request to %s', $this->endpoint));
        $result = $http->request($this->endpoint, $data);
        $this->edebug('Response received');
        $this->edebug('Response headers: ' . print_r($result['headers'], true));
        $this->edebug('Response body: ' . print_r($result['body'], true));

        return $this->handle_response($result);


    }

    protected function get_body()
    {
        $body = array(
            'recipients' => $this->get_recipients(),
            'content' => array(
                'from' => $this->From,
                'subject' => $this->Subject,
                'headers' => $this->build_email_headers()
            )
        );


        $body['content']['html'] = $this->Body;
        $body['content']['text'] = $this->AltBody;

        $attachments = $this->get_attachments();

        if (count($attachments)) {
            $body['content']['attachments'] = $attachments;
        }

        return $body;
    }

    protected function get_attachments()
    {
        $attachments = array();

        foreach ($this->attachment as $attachment) {
            $attachments[] = array(
                'name' => $attachment[2],
                'type' => $attachment[4],
                'data' => $this->encode_attachment($this->read_attachment($attachment[0]))
            );
        }

        return $attachments;
    }

    protected function encode_attachment($data)
    {
        return base64_encode($data);
    }

    protected function read_attachment($path)
    {
        return file_get_contents($path);
    }

    public function isMail()
    {
        $this->Mailer = 'sparkpost';
    }

    protected function handle_response($response)
    {

        if (is_wp_error($response)) {
            $this->edebug('Request completed with error');
            $this->setError($response->get_error_messages()); //WP_Error implements this method
            $this->edebug($response->get_error_messages());
            return false;
        }

        $body = json_decode($response['body']);
        if (property_exists($body, 'errors')) {
            $this->edebug('Error in transmission');
            $this->setError($body->errors);
            return false;
        }


        if (property_exists($body, 'results')) {
            $data = $body->results;
        } else {
            $this->edebug('API response is unknown');
            $this->setError('Unknown response');
            return false;
        }

        if ($data->total_rejected_recipients > 0) {
            $this->edebug(sprintf('Sending to %d recipient(s) failed', $data->total_rejected_recipients));
            $this->setError($data);
            return false;
        }

        if ($data->total_accepted_recipients > 0) {
            $this->edebug(sprintf('Successfully sent to %d recipient(s)', $data->total_accepted_recipients));
            $this->edebug(sprintf('Transmission ID is %s', $data->id));
            return true;
        }
        return false;
    }

    protected function get_recipients()
    {
        $recipients = array();

        foreach ($this->to as $to) {
            $recipients[] = array(
                'address' => array(
                    'email' => $to[0],
                    'name' => $to[1]
                ));
        }
        return $recipients;
    }

    protected function get_request_headers($hide_api_key = false)
    {
        $api_key = $this->options['password'];
        if($hide_api_key) { //replace all but first 5 chars with *
            $api_key = SparkPost::obfuscate_api_key($api_key);
        }
        return array(
            'User-Agent' => 'wordpress-sparkpost',
            'Content-Type' => 'application/json',
            'Authorization' => $api_key
        );
    }

    protected function build_email_headers() {
        $unsupported_headers = array('From', 'Subject', 'To', 'Reply-To', 'Content-Type',
            'Content-Transfer-Encoding', 'MIME-Version');
        $headers = $this->createHeader();

        $formatted_headers = new StdClass();
        //split by line separator
        foreach(explode($this->LE, $headers) as $line) {

            $splitted_line = explode(': ', $line);
            $key = trim($splitted_line[0]);

            if(!in_array($key, $unsupported_headers) && !empty($key) && !empty($splitted_line[1])) {
                $formatted_headers->{$key} = trim($splitted_line[1]);
            }
        }

        return $formatted_headers;

    }


}