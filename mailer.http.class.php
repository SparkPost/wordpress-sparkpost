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
        $recipients_header_to = array();

        foreach ($this->to as $to) {
            $recipients[] = $this->build_recipient($to[0], $to[1]);

            // prepare for header_to
            if(!empty($to[1])) {
              $recipients_header_to[] = sprintf('%s <%s>', $to[1], $to[0]);
            } else {
              $recipients_header_to[] = $to[0];
            }
        }
        $recipients_header_to = implode(',', $recipients_header_to);

        // include bcc to recipients
        // sparkposts recipients list acts as bcc by default
        $recipients = array_merge($recipients, $this->get_bcc($recipients_header_to));

        // include cc to recipients, they need to included in recipients and in headers (refer to get_headers method)
        $recipients = array_merge($recipients, $this->get_cc($recipients_header_to));

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
     * For WordPress version below 4.6
     * @return array
     * TODO Remove this when wordpress does not support version below 4.6
     */
    protected function get_reply_to_below46()
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
    * Returns list of Reply-To recipients
    * For WordPress 4.6 and above
    * @return array Formatted list of reply tos
    */
    protected function get_reply_to_above46()
    {
        $replyTos = array();
        foreach ($this->ReplyTo as $reply_to) {
            $name = $reply_to[1];
            $email = $reply_to[0];
            if(empty($name)) {
              $replyTos[] = $email;
            } else {
              $replyTos[] = sprintf('%s <%s>', $name, $email);
            }
        }

        return implode(',', $replyTos);
    }

    protected function get_reply_to() {
      $wp_version = get_bloginfo('version');
      if(version_compare($wp_version, '4.6') == -1) { // if lower than 4.6
        return $this->get_reply_to_below46();
      } else {
        return $this->get_reply_to_above46();
      }
    }

    protected function build_recipient($email, $name = '', $header_to = '') {
      $recipient = array(
        'address' => array(
          'email' => $email,
          'name' => $name,
        )
      );

      if(!empty($header_to)) {
        $recipient['address']['header_to'] = $header_to;
        /* if header_to is like 'Name <email>', then having name attribute causes
        showing weird display of name in the delivered mail. So, let's remove it
        when header_to is set.
        */
        unset($recipient['address']['name']);
      }

      return $recipient;
    }

    /**
     * Returns the list of BCC recipients
     * @return array
     */
    protected function get_bcc($header_to)
    {
        $bcc = array();
        foreach($this->getBccAddresses() as $bccAddress) {
            $bcc[] = $this->build_recipient($bccAddress[0], $bccAddress[1], $header_to);
        }
        return $bcc;
    }

    /**
     * Returns the list of CC recipients
     * @header_to string Optional, shouldn't be used for setting CC in headers
     * @return array
     */
    protected function get_cc($header_to = '')
    {
        $cc = array();
        foreach($this->getCcAddresses() as $ccAddress) {
            $cc[] = $this->build_recipient($ccAddress[0], $ccAddress[1], $header_to);
        }
        return $cc;
    }

    protected function stringify_recipients($recipients) {
      $recipients_list = array();

      foreach($recipients as $recipient) {
        if(!empty($recipient['address']['name'])){
          $recipients_list[] = sprintf('%s <%s>', $recipient['address']['name'], $recipient['address']['email']);
        } else {
          $recipients_list[] = $recipient['address']['email'];
        }
      };

      return implode(',', $recipients_list);
    }

    /**
     * Returns a collection that can be sent as headers in body
     * @return array
     */
    protected function get_headers()
    {
        $unsupported_headers = array(
            'From', 'Subject', 'To', 'Reply-To', 'Cc',
            'Content-Type', 'Content-Transfer-Encoding', 'MIME-Version'
        );
        $headers = $this->createHeader();


        $formatted_headers = array();
        // split by line separator
        foreach (explode($this->LE, $headers) as $line) {

            $splitted_line = explode(': ', $line);
            $key = trim($splitted_line[0]);

            if (!in_array($key, $unsupported_headers) && !empty($key) && !empty($splitted_line[1])) {
                $formatted_headers[$key] = trim($splitted_line[1]);
            }
        }

        // include cc in header
        $cc = $this->get_cc();
        if(!empty($cc)) {
          $formatted_headers['CC'] = $this->stringify_recipients($cc);
        }

        return $formatted_headers;
    }
}
