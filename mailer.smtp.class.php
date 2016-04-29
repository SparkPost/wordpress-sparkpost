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
        $settings = SparkPost::get_settings();

        if (!$settings['enable_sparkpost'] || empty($settings['password'])) {
            return;
        }

        $tracking_enabled = (bool) $settings['enable_tracking'];
        $x_msys_api = array(
            'options' => array (
<<<<<<< 23ff7110c4c175f7b92f47caf3662175c6fb88de
                'open_tracking' => (bool) apply_filters('wpsp_open_tracking', $tracking_enabled),
                'click_tracking' => (bool) apply_filters('wpsp_click_tracking', $tracking_enabled)
=======
                'open_tracking' => $tracking_enabled,
                'click_tracking' => $tracking_enabled,
                'transactional' => true
>>>>>>> Add transaction option
            )
        );

        $phpmailer->isSMTP();
        $phpmailer->SMTPSecure = 'tls';
        $phpmailer->Port = !empty($settings['port']) ? intval($settings['port']) : 587;
        $phpmailer->Host = 'smtp.sparkpostmail.com';
<<<<<<< 23ff7110c4c175f7b92f47caf3662175c6fb88de
=======

>>>>>>> Add transaction option
        $phpmailer->SMTPAuth = true;
        $phpmailer->Username = 'SMTP_Injection';
        $phpmailer->Password = apply_filters('wpsp_api_key', $settings['password']);

        $json_x_msys_api = apply_filters('wpsp_smtp_msys_api', json_encode($x_msys_api));
        $phpmailer->addCustomHeader('X-MSYS-API', $json_x_msys_api);
    }
}
