<?php

namespace WPSparkPost;

// If ABSPATH is defined, we assume WP is calling us.
// Otherwise, this could be an illicit direct request.
if (!defined('ABSPATH')) exit();

/**
 * @package wp-sparkpost
 */
class SparkPost
{
    protected static $settings_default = array(
        'port' => 587,
        'sending_method' => 'api',
        'password' => '',
        'from_name' => '',
        'from_email' => '',
        'enable_sparkpost' => false,
        'enable_tracking' => true,
        'template' => '',
        'transactional' => false,
        'log_emails' => false,
        'location' => 'us'
    );

    protected $hostnames = array(
        'us' => array(
            'api' => 'https://api.sparkpost.com',
            'smtp' => 'smtp.sparkpostmail.com'
        ),
        'eu' => array(
            'api' => 'https://api.eu.sparkpost.com',
            'smtp' => 'smtp.eu.sparkpostmail.com'
        )
    );

    var $settings, $db_version;

    public function __construct()
    {
        register_activation_hook(WPSP_PLUGIN_PATH, array($this, 'sp_activate'));
        register_deactivation_hook(WPSP_PLUGIN_PATH, array($this, 'sp_deactivate'));

        add_filter('plugin_action_links_' . plugin_basename(WPSP_PLUGIN_PATH), array($this, 'add_settings_link'));
        add_action('plugins_loaded', array($this, 'db_update_check'));


        $this->settings = self::get_settings();

        if (self::get_setting('enable_sparkpost')) { //no need to register this hooks if plugin is disabled
            add_filter('wp_mail_from', array($this, 'set_from_email'));
            add_filter('wp_mail_from_name', array($this, 'set_from_name'));
            add_filter('sp_hostname', array($this, 'get_hostname'));
        }

        $this->db_version = '1.0.0';
    }

    public function sp_activate()
    {
        $settings = self::$settings_default;
        $settings['transactional'] = true; // setting it here to apply this default value to new installation only as this is breaking change
        update_option('sp_settings', $settings);
        $this->install_db();
    }

    protected function install_email_log_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sp_email_logs';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
        id SERIAL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        wp_mail_args text NOT NULL,
        subject varchar(255) NOT NULL,
        content text NOT NULL,
        response text NOT NULL,
        PRIMARY KEY  (id)
      ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $result = dbDelta($sql);
        update_option('sp_db_version', $this->db_version);

        return $result;
    }

    protected function install_db()
    {
        return $this->install_email_log_table();
    }

    function db_update_check()
    {
        //no need to check db version if email logging is not enabled
        $mailer = self::get_setting('sending_method');
        $email_logging_enabled = self::get_setting('log_emails');
        if($mailer != 'api' ||  !$email_logging_enabled) {
          return false;
        }

        if (get_site_option('sp_db_version') != $this->db_version) {
            return $this->install_db();
        }
        return false;
    }

    public function sp_deactivate()
    {
        delete_option('sp_settings');
    }

    static function get_settings($apply_filter = true)
    {
        $settings = array_merge(
            self::$settings_default,
            get_option('sp_settings', array()),
            get_option('sp_settings_basic', array()),
            get_option('sp_settings_overrides', array())
        );

        if ($apply_filter) {
            return apply_filters('wpsp_get_settings', $settings);
        } else {
            return $settings;
        }

    }

    static function get_setting($setting)
    {
        $settings = self::get_settings();
        return $settings[$setting];
    }

    public function add_settings_link($links)
    {
        $mylinks = array();
        if (current_user_can('manage_options')) {
            $mylinks[] = '<a href="' . esc_url(admin_url('options-general.php?page=wpsp-setting-admin')) . '">Settings</a>';
        }
        return array_merge($links, $mylinks);
    }

    public function set_from_name($name)
    {
        if (!empty($this->settings['from_name'])) {
            $name = $this->settings['from_name'];
        }

        return apply_filters('wpsp_sender_name', $name);
    }

    public function set_from_email($email)
    {
        if (!empty($this->settings['from_email'])) {
            $email = $this->settings['from_email'];
        }

        return apply_filters('wpsp_sender_email', $email);
    }

    public function get_hostname($type = 'api')
    {
        $location = !empty($this->settings['location']) ? esc_attr($this->settings['location']) : 'us';
        return $this->hostnames[$location][$type];
    }

    static function obfuscate_api_key($api_key)
    {
        if (!empty($api_key)) {
            return substr($api_key, 0, 4) . str_repeat('*', 36);
        }

        return $api_key;
    }

    static function is_key_obfuscated($api_key)
    {
        return strpos($api_key, '*') !== false;
    }

    public function init_sp_http_mailer($args)
    {
        global $phpmailer;
        if (!$phpmailer instanceof SparkPostHTTPMailer) {
            $phpmailer = new SparkPostHTTPMailer();
        }
        $phpmailer->wp_mail_args = $args;
        return $args;
    }

    static function is_sandbox($email)
    {
        $email_splitted = array_slice(explode('@', $email), -1);
        return $email_splitted[0] === 'sparkpostbox.com';
    }

    static function add_log($wp_mail_args, $content, $response)
    {
        if (!self::is_logging_enabled()) {
            return false;
        }

        global $wpdb;
        $wpdb->show_errors();
        $content = json_decode($content);
        $subject = '';

        //get subject
        if (isset($content->content) && property_exists($content->content, 'subject')) {
            $subject = $content->content->subject;
        } else if (isset($content->substitution_data) && property_exists($content->substitution_data, 'subject')) {
            $subject = $content->substitution_data->subject;
        }

        return $wpdb->insert($wpdb->prefix . 'sp_email_logs', array(
            'subject' => $subject,
            'content' => json_encode($content),
            'response' => json_encode($response),
            'wp_mail_args' => json_encode($wp_mail_args)
        ));
    }

    static function clear_logs()
    {
        global $wpdb;
        $wpdb->show_errors();
        $wpdb->query('TRUNCATE ' . $wpdb->prefix . 'sp_email_logs');
    }

    static function is_logging_enabled()
    {
        $settings = self::get_settings();

        return $settings['sending_method'] === 'api' && $settings['log_emails'];
    }
}
