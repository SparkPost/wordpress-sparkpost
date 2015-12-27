<?php
defined('ABSPATH') or die('Damn you!');


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

        if(empty($options['password']) || empty($options['from_email']) || empty($options['from_name'])) {
            return;
        }

        $phpmailer->isSMTP();
        if ($options["use_tls"] == 1) {
            $phpmailer->SMTPSecure = 'tls';
        }
        $phpmailer->Port = 587;
        $phpmailer->Host = 'smtp.sparkpostmail.com';
        $phpmailer->setFrom($options['from_email'], $options['from_name']);
        $phpmailer->AddReplyTo($options['from_email'], $options['from_name']); 
        
        $phpmailer->SMTPAuth = true;
        $phpmailer->Username = 'SMTP_Injection';
        $phpmailer->Password = $options['password'];
    }
}
