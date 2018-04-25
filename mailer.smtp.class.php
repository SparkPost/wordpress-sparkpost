<?php

namespace WPSparkPost;
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

    public function configure_phpmailer($phpmailer)
    {
        $xmailer = 'wordpress-sparkpost/' . WPSP_PLUGIN_VERSION . ' on PHPMailer ' . $phpmailer->Version . ' (https://github.com/PHPMailer/PHPMailer)';

        $settings = SparkPost::get_settings();

        if (!$settings['enable_sparkpost'] || empty($settings['password'])) {
            return;
        }

        $tracking_enabled = $settings['enable_tracking'];
        $x_msys_api = array(
            'options' => array(
                'open_tracking' => (bool)apply_filters('wpsp_open_tracking', $tracking_enabled),
                'click_tracking' => (bool)apply_filters('wpsp_click_tracking', $tracking_enabled),
                'transactional' => (bool)apply_filters('wpsp_transactional', $settings['transactional']),
                'sandbox' => SparkPost::is_sandbox($phpmailer->From),
            )
        );

        $phpmailer->isSMTP();
        $phpmailer->SMTPSecure = 'tls';
        $phpmailer->Port = !empty($settings['port']) ? intval($settings['port']) : 587;
        $phpmailer->Host = apply_filters('sp_hostname', 'smtp');
        $phpmailer->SMTPAuth = true;
        $phpmailer->Username = 'SMTP_Injection';
        $phpmailer->Password = apply_filters('wpsp_api_key', $settings['password']);
        $phpmailer->XMailer = $xmailer;

        $json_x_msys_api = apply_filters('wpsp_smtp_msys_api', $x_msys_api);
        $phpmailer->addCustomHeader('X-MSYS-API', json_encode($json_x_msys_api));
    }
}
