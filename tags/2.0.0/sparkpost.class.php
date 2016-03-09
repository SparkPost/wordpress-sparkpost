<?php
// If ABSPATH is defined, we assume WP is calling us.
// Otherwise, this could be an illicit direct request.
if (!defined('ABSPATH')) exit();

/**
 * @package wp-sparkpost
 */
class SparkPost
{

    var $options_default = array(
        'port' => 587,
        'sending_method' => 'api',
        'enable' => false,
        'password' => '',
        'from_name' => '',
        'from_email' => '',
        'enable_sparkpost' => false
    );

    var $options;

    public function __construct()
    {
        register_activation_hook(WPSP_PLUGIN_PATH, array($this, 'sp_activate'));
        register_deactivation_hook(WPSP_PLUGIN_PATH, array($this, 'sp_deactivate'));
        add_filter('plugin_action_links_' . plugin_basename(WPSP_PLUGIN_PATH), array($this, 'add_settings_link'));

        $this->options = $this->get_options();

        if($this->get_option('enable_sparkpost')) { //no need to register this hooks if plugin is disabled
            add_filter('wp_mail_from', array($this, 'set_from_email'));
            add_filter('wp_mail_from_name', array($this, 'set_from_name'));
        }
    }


    public function sp_activate()
    {
        $settings = array(
            'password' => '',
            'from_name' => '',
            'from_email' => '',
            'port' => 587,
            'enable_sparkpost' => 0
        );

        add_option('sp_settings', $settings);
    }

    public function sp_deactivate()
    {
        delete_option('sp_settings');
    }

    public function get_options()
    {
        return get_option('sp_settings');
    }

    public function get_option($option)
    {
        if(!empty($this->options[$option])) {
            return $this->options[$option];
        } else {
            return $this->options_default[$option];
        }
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
        if(!empty($this->options['from_name'])) {
            return $this->options['from_name'];
        } else {
            return $name;
        }
    }

    public function set_from_email($email)
    {
        if(!empty($this->options['from_email'])) {
            return $this->options['from_email'];
        } else {
            return $email;
        }
    }

    static function obfuscate_api_key($api_key) {
        return str_replace(substr($api_key, 5), str_repeat('*', 35), $api_key);
    }

    static function is_key_obfuscated($api_key) {
        return (bool) stripos($api_key, '*');
    }
}
