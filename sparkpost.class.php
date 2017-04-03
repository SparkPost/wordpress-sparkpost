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
        'transactional' => false
    );

    var $settings;

    public function __construct()
    {
        register_activation_hook(WPSP_PLUGIN_PATH, array($this, 'sp_activate'));
        register_deactivation_hook(WPSP_PLUGIN_PATH, array($this, 'sp_deactivate'));

        add_filter('plugin_action_links_' . plugin_basename(WPSP_PLUGIN_PATH), array($this, 'add_settings_link'));

        $this->settings = self::get_settings();

        if (self::get_setting('enable_sparkpost')) { //no need to register this hooks if plugin is disabled
            add_filter('wp_mail_from', array($this, 'set_from_email'));
            add_filter('wp_mail_from_name', array($this, 'set_from_name'));
        }
    }

    public function sp_activate()
    {
      $settings = self::$settings_default;
      $settings['transactional'] = true; // setting it here to apply this default value to new installation only as this is breaking change
      update_option('sp_settings', $settings);
    }

    public function sp_deactivate()
    {
        delete_option('sp_settings');
    }

    static function get_settings($apply_filter = true)
    {
        $settings = array_merge(self::$settings_default, get_option('sp_settings', array()));

        if ($apply_filter) {
            return apply_filters('wpsp_get_settings', $settings);
        }
        else {
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

    static function obfuscate_api_key($api_key)
    {
        if(!empty($api_key)) {
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
        return $args;
    }

    static function is_sandbox($email)
    {
        $email_splitted = array_slice(explode('@', $email), -1);
        return $email_splitted[0] === 'sparkpostbox.com';
    }
}
