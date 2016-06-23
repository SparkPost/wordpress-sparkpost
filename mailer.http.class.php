<?php
// If ABSPATH is defined, we assume WP is calling us.
// Otherwise, this could be an illicit direct request.
if (!defined('ABSPATH')) exit();


require_once ABSPATH . WPINC . '/class-phpmailer.php';

class SparkPostHTTPMailer extends PHPMailer
{
    protected $endpoint = 'https://api.sparkpost.com/api/v1/transmissions';
    private $options;

    /**
     * Constructor.
     * @param boolean $exceptions Should we throw external exceptions?
     */
    function __construct($exceptions = false)
    {
        $this->options = SparkPost::get_options();

        parent::__construct($exceptions);
    }

    /**
     * Send mail using SparkPost
     * @param string $header The message headers
     * @param string $body The message body
     * @throws SparkPostException
     * @access protected
     * @return boolean
     */
    protected function mailSend($header, $body)
    {
        return $this->sparkpostSend();
    }

    function sparkpostSend()
    {
        $this->edebug('Preparing request data');

        $data = array(
            'method' => 'POST',
            'timeout' => 15,
            'headers' => $this->get_request_headers(),
            'body' => json_encode($this->get_request_body())

        );

        $http = _wp_http_get_object();

        $this->edebug(sprintf('Request headers: %s', print_r($this->get_request_headers(true), true)));
        $this->edebug(sprintf('Request body: %s', $data['body']));
        $this->edebug(sprintf('Making HTTP POST request to %s', $this->endpoint));
        $result = $http->request($this->endpoint, $data);
        $this->edebug('Response received');

        return $this->handle_response($result);
    }

    /**
     * Build the request body to be sent to the SparkPost API.
     */
    protected function get_request_body()
    {
        $tracking_enabled = !!$this->options['enable_tracking'];
        $sender = $this->get_sender();
        $replyTo = $this->get_reply_to();
        $body = array();

        // add recipients
        $body['recipients'] = $this->get_recipients();

        // enable engagement tracking
        $body['options'] = array(
            'open_tracking' => $tracking_enabled,
            'click_tracking' => $tracking_enabled
        );

        // pass through either stored template or inline content
        if (!empty($this->options['template'])) {
            // stored template
            $body['content']['template_id'] = $this->options['template'];

            // supply substitution data so users can add variables to templates
            $body['substitution_data']['content'] = $this->Body;
            $body['substitution_data']['subject'] = $this->Subject;
            $body['substitution_data']['from_name'] = $sender['name'];
            $body['substitution_data']['from'] = $sender['name'] . ' <' . $sender['email'] . '>';
            if ($replyTo) {
                $body['substitution_data']['reply_to'] = $replyTo;
            }
            $localpart = explode('@', $sender['email']);
            if (!empty($localpart)) {
                $body['substitution_data']['from_localpart'] = $localpart[0];
            }
        } else {
            // inline content
            $body['content'] = array(
                'from' => $sender,
                'subject' => $this->Subject,
                'headers' => $this->get_headers()
            );

            if ($replyTo) {
                $body['content']['reply_to'] = $replyTo;
            }

            switch($this->ContentType) {
                case 'multipart/alternative':
                    $body['content']['html'] = $this->Body;
                    $body['content']['text'] = $this->AltBody;
                    break;
                case 'text/plain':
                    $body['content']['text'] = $this->Body;
                    break;
                default:
                    $body['content']['html'] = $this->Body;
                    break;
            }
        }

        $attachments = $this->get_attachments();
        if (count($attachments)) {
            $body['content']['attachments'] = $attachments;
        }

        return $body;
    }


    protected function get_sender()
    {
        $from = array(
            'email' => $this->From
        );

        if (!empty($this->FromName)) {
            $from['name'] = $this->FromName;
        }

        return $from;
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

        $this->edebug('Response headers: ' . print_r($response['headers'], true));
        $this->edebug('Response body: ' . print_r($response['body'], true));

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
        // Handle Cc and Bcc
        foreach ($this->cc as $cc) {
            $recipients[] = array(
                'address' => array(
                    'email' => $cc[0],
                    'header_to' => $recipients[0]['address']['email']
                ));
        }
        foreach ($this->bcc as $bcc) {
            $recipients[] = array(
                'address' => array(
                    'email' => $bcc[0],
                    'header_to' => $recipients[0]['address']['email']
                ));
        }

        return $recipients;
    }

    protected function get_request_headers($hide_api_key = false)
    {
        $api_key = $this->options['password'];
        if ($hide_api_key) {
            $api_key = SparkPost::obfuscate_api_key($api_key);
        }
        return array(
            'User-Agent' => 'wordpress-sparkpost',
            'Content-Type' => 'application/json',
            'Authorization' => $api_key
        );
    }

    /**
     * Returns the list of Reply-To headers
     * @return array
     */
    protected function get_reply_to()
    {
        $replyTos = array();
        foreach ($this->CustomHeader as $header) { // wp_mail sets Reply-To as custom header (does not use phpmailer->addReplyTo)
            list($name, $value) = $header;
            if ($name === 'Reply-To' && !empty($value)) {
                $replyTos[] = trim($value);
            }
        }

        return implode(',', $replyTos);
    }

    /**
     * Returns a collection that can be sent as headers in body
     * @return array
     */
    protected function get_headers()
    {
        $unsupported_headers = array(
            'From', 'Subject', 'To', 'Reply-To', 'Bcc',
            'Content-Type', 'Content-Transfer-Encoding', 'MIME-Version'
        );
        $headers = $this->createHeader();

        $formatted_headers = new StdClass();
        // split by line separator
        foreach (explode($this->LE, $headers) as $line) {

            $splitted_line = explode(': ', $line);
            $key = trim($splitted_line[0]);

            if (!in_array($key, $unsupported_headers) && !empty($key) && !empty($splitted_line[1])) {
                $formatted_headers->{$key} = trim($splitted_line[1]);
            }
        }

        return $formatted_headers;
    }
}
