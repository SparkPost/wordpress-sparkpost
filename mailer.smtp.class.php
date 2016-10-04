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
        $options = apply_filters('wpsp_get_settings', SparkPost::get_options());

        if (!$options['enable_sparkpost'] || empty($options['password'])) {
            return;
        }

        $tracking_enabled = (bool) $options['enable_tracking'];
        $x_msys_api = array(
            'options' => array (
                'open_tracking' => (bool) apply_filters('wpsp_open_tracking', $tracking_enabled),
                'click_tracking' => (bool) apply_filters('wpsp_click_tracking', $tracking_enabled)
            )
        );

        $phpmailer->isSMTP();
        $phpmailer->SMTPSecure = 'tls';
        $phpmailer->Port = !empty($options['port']) ? intval($options['port']) : 587;
        $phpmailer->Host = 'smtp.sparkpostmail.com';
        $phpmailer->SMTPAuth = true;
        $phpmailer->Username = 'SMTP_Injection';
        $phpmailer->Password = apply_filters('wpsp_api_key', $options['password']);

        $json_x_msys_api = apply_filters('wpsp_smtp_msys_api', json_encode($x_msys_api));
        $phpmailer->addCustomHeader('X-MSYS-API', $json_x_msys_api);
    }
}
