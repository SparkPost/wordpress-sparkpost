<?php
/**
 * @package wp-sparkpost
 */
/*
Plugin Name: SparkPost
Plugin URI: http://sparkpost.com/
Description: Send all your email from WordPress through SparkPost, the world's most advanced email delivery service.
Version: 1.1.2
Author: The HungryCoder
Author URI: http://thehungrycoder.com
License: GPLv2 or later
Text Domain: wpsp
*/

defined('ABSPATH') or die('Damn you!');
define('WPSP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPSP_PLUGIN_PATH', WPSP_PLUGIN_DIR . basename(__FILE__));

require_once(WPSP_PLUGIN_DIR . 'mailer.class.php');
require_once(WPSP_PLUGIN_DIR . 'sparkpost.class.php');

new SparkPost();
new SparkPostMailer();

if (is_admin()) {
    require_once(WPSP_PLUGIN_DIR . 'widget.class.php');
    new SparkPostAdmin();
}
