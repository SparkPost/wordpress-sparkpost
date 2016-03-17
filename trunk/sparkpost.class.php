<?php
// If ABSPATH is defined, we assume WP is calling us.
// Otherwise, this could be an illicit direct request.
if (!defined('ABSPATH')) exit();

/**
 * @package wp-sparkpost
 */
class SparkPost
{

    protected static $options_default = array(
        'port' => 587,
        'sending_method' => 'api',
        'password' => '',
        'from_name' => '',
        'from_email' => '',
        'enable_sparkpost' => false,
        'enable_tracking' => true
    );

    var $options;

    public function __construct()
    {
        register_activation_hook(WPSP_PLUGIN_PATH, array($this, 'sp_activate'));
        register_deactivation_hook(WPSP_PLUGIN_PATH, array($this, 'sp_deactivate'));
        add_filter('plugin_action_links_' . plugin_basename(WPSP_PLUGIN_PATH), array($this, 'add_settings_link'));

        $this->options = self::get_options();

        if ($this->get_option('enable_sparkpost')) { //no need to register this hooks if plugin is disabled
            add_filter('wp_mail_from', array($this, 'set_from_email'));
            add_filter('wp_mail_from_name', array($this, 'set_from_name'));
        }
    }


    public function sp_activate()
    {
        add_option('sp_settings', self::$options_default);
    }

    public function sp_deactivate()
    {
        delete_option('sp_settings');
    }

    static function get_options()
    {
        return array_merge(self::$options_default, get_option('sp_settings'));
    }

    static function get_option($option)
    {
        $options = self::get_options();
        return $options[$option];
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
        if (!empty($this->options['from_name'])) {
            return $this->options['from_name'];
        } else {
            return $name;
        }
    }

    public function set_from_email($email)
    {
        if (!empty($this->options['from_email'])) {
            return $this->options['from_email'];
        } else {
            return $email;
        }
    }

    static function obfuscate_api_key($api_key)
    {
        return substr($api_key, 0, 4) . str_repeat('*', 36);
    }

    static function is_key_obfuscated($api_key)
    {
        return strpos($api_key, '*') !== false;
    }
}
