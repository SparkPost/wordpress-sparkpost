<?php
// If ABSPATH is defined, we assume WP is calling us.
// Otherwise, this could be an illicit direct request.
if (!defined('ABSPATH')) exit();


/**
 * @package wp-sparkpost
 */
class SparkPostSMTPMailer
{

    public function __construct()
    {
        add_action('phpmailer_init', array($this, 'configure_phpmailer'), 2);
    }

    public function configure_phpmailer($phpmailer) {
        $options = SparkPost::get_options();

        if (!$options['enable_sparkpost'] || empty($options['password'])) {
            return;
        }
        $tracking_enabled = !!$options['enable_tracking'];
        $x_msys_api = array(
            'options' => array (
                'open_tracking' => $tracking_enabled,
                'click_tracking' => $tracking_enabled
            )
        );

        $phpmailer->isSMTP();
        $phpmailer->SMTPSecure = 'tls';
        $phpmailer->Port = !empty($options['port']) ? intval($options['port']) : 587;
        $phpmailer->Host = 'smtp.sparkpostmail.com';
        
        $phpmailer->SMTPAuth = true;
        $phpmailer->Username = 'SMTP_Injection';
        $phpmailer->Password = $options['password'];

        $phpmailer->addCustomHeader('X-MSYS-API', json_encode($x_msys_api));
    }
}
