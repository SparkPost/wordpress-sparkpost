<?php
defined('ABSPATH') or die('Damn you!');

/**
 * @package wp-sparkpost
 */
class SparkPost
{

    public function __construct()
    {
        register_activation_hook(WPSP_PLUGIN_PATH, array($this, 'sp_activate'));
        register_deactivation_hook(WPSP_PLUGIN_PATH, array($this, 'sp_deactivate'));
    }


    public function sp_activate() {
        $settings = array(
            'password' => '',
            'from_name' => '',
            'from_email' => '',
            'enable_sparkpost' => 0,
            'use_tls' => 0
            );

        add_option('sp_settings', $settings);
    }

    public function sp_deactivate() {
        delete_option('sp_settings');
    }

}
