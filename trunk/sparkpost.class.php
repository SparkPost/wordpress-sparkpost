<?php
// If ABSPATH is defined, we assume WP is calling us.
// Otherwise, this could be an illicit direct request.
if (!defined('ABSPATH')) exit();

/**
 * @package wp-sparkpost
 */
class SparkPost
{

    public function __construct()
    {
        register_activation_hook(WPSP_PLUGIN_PATH, array($this, 'sp_activate'));
        register_deactivation_hook(WPSP_PLUGIN_PATH, array($this, 'sp_deactivate'));
        add_filter('plugin_action_links_' . plugin_basename(WPSP_PLUGIN_PATH), array($this, 'add_settings_link'));

    }


    public function sp_activate() {
        $settings = array(
            'password' => '',
            'from_name' => '',
            'from_email' => '',
            'port' => 587,
            'enable_sparkpost' => 0
            );

        add_option('sp_settings', $settings);
    }

    public function sp_deactivate() {
        delete_option('sp_settings');
    }

    public function add_settings_link($links) {
        $mylinks = array();
        if(current_user_can('manage_options')){
            $mylinks[] = '<a href="' . esc_url(admin_url( 'options-general.php?page=wpsp-setting-admin' )) . '">Settings</a>';
        }
        return array_merge( $links, $mylinks );
    }

}
