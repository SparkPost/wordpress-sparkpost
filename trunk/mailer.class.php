<?php
// If ABSPATH is defined, we assume WP is calling us.
// Otherwise, this could be an illicit direct request.
if (!defined('ABSPATH')) exit();


/**
 * @package wp-sparkpost
 */
class SparkPostMailer
{

    public function __construct()
    {
        add_action('phpmailer_init', array($this, 'configure_phpmailer'), 2);
    }

    public function configure_phpmailer($phpmailer) {
    	$options = get_option('sp_settings');

        if(!$options['enable_sparkpost'] || empty($options['password']) || empty($options['from_email']) || empty($options['from_name'])) {
            return;
        }

        $phpmailer->isSMTP();
        $phpmailer->SMTPSecure = 'tls';
        $phpmailer->Port = !empty($options['port']) ? intval($options['port']) : 587;
        $phpmailer->Host = 'smtp.sparkpostmail.com';
        $phpmailer->setFrom($options['from_email'], $options['from_name']);
        $phpmailer->AddReplyTo($options['from_email'], $options['from_name']); 
        
        $phpmailer->SMTPAuth = true;
        $phpmailer->Username = 'SMTP_Injection';
        $phpmailer->Password = $options['password'];
    }
}
