<?php

namespace WPSparkPost;
/*
Plugin Name: SparkPost
Plugin URI: http://sparkpost.com/
Description: Send all your email from Wordpress through SparkPost, the world's most advanced email delivery service.
Version: 3.2.6
Author: SparkPost
Author URI: http://sparkpost.com
License: GPLv2 or later
Text Domain: wpsp
*/

// If ABSPATH is defined, we assume WP is calling us.
// Otherwise, this could be an illicit direct request.
if (!defined('ABSPATH')) exit();

define('WPSP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPSP_PLUGIN_PATH', WPSP_PLUGIN_DIR . basename(__FILE__));
define('WPSP_PLUGIN_VERSION', '3.2.6');

require_once(WPSP_PLUGIN_DIR . 'sparkpost.class.php');

if (is_admin()) {
    require_once(WPSP_PLUGIN_DIR . 'admin.widget.class.php');
    require_once(WPSP_PLUGIN_DIR . 'email-logs.class.php');
    new SparkPostAdmin();
}
$sp = new SparkPost();
if (SparkPost::get_setting('enable_sparkpost')) {
    if (SparkPost::get_setting('sending_method') == 'smtp') {
        require_once(WPSP_PLUGIN_DIR . 'mailer.smtp.class.php');
        new SparkPostSMTPMailer();
    } else {
        require_once(WPSP_PLUGIN_DIR . 'mailer.http.class.php');
        add_filter('wp_mail', array($sp, 'init_sp_http_mailer'));
    }
}
