<?php
defined('ABSPATH') or die('Damn you!');


/**
 * @package wp-sparkpost
 */
class SparkPostAdmin
{
    private $options;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'admin_page_init'));
    }

    public function add_plugin_page()
    {
        add_options_page(
            'SparkPost SMTP Settings',
            'SparkPost SMTP',
            'manage_options',
            'wpsp-setting-admin',
            array($this, 'wpsp_admin_page')
        );
    }

    public function wpsp_admin_page()
    {
        $this->options = get_option('sp_settings');
        ?>
        <div class="wrap">
            <form method="post" action="options.php">
                <?php
                settings_fields("sp_settings_group");
                do_settings_sections("sp-options");
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function admin_page_init()
    {

        register_setting('sp_settings_group', 'sp_settings', array($this, 'sanitize'));
        add_settings_section("general", "SparkPost SMTP Settings", null, "sp-options");
        add_settings_field("from_name", "From name*", array($this, 'render_from_name_field'), "sp-options", "general");
        add_settings_field("from_email", "From email*", array($this, 'render_from_email_field'), "sp-options", "general");
        add_settings_field("password", "SMTP password*", array($this, 'render_password_field'), "sp-options", "general");
        add_settings_field("use_tls", "Use TLS", array($this, 'render_use_tls_field'), "sp-options", "general");

    }

    public function sanitize($input)
    {

        $new_input = array();

        if (isset($input['from_email'])) {
            $new_input['from_email'] = trim($input['from_email']);
        }

        if (isset($input['from_name'])) {
            $new_input['from_name'] = trim($input['from_name']);
        }


        if (isset($input['password'])) {
            $new_input['password'] = trim($input['password']);
        }

        if(isset($input['use_tls'])) {
        	$new_input['use_tls'] = 1;
        } else {
        	$new_input['use_tls'] = 0;
        }


        return $new_input;
    }

    public function render_password_field()
    {
        printf(
            '<input type="text" id="password" name="sp_settings[password]" class="regular-text" value="%s" />',
            isset($this->options['password']) ? esc_attr($this->options['password']) : ''
        );
    }

    public function render_from_email_field()
    {
        printf(
            '<input type="email" id="from_email" name="sp_settings[from_email]" class="regular-text" value="%s" />',
            isset($this->options['from_email']) ? esc_attr($this->options['from_email']) : ''
        );
    }

	public function render_from_name_field()
    {
        printf(
            '<input type="text" id="from_name" name="sp_settings[from_name]" class="regular-text" value="%s" />',
            isset($this->options['from_name']) ? esc_attr($this->options['from_name']) : ''
        );
    }

    public function render_use_tls_field()
    {
        printf(
            '<label><input type="checkbox" id="use_tls" name="sp_settings[use_tls]" value="1" %s />Secure connection</label>', $this->options['use_tls'] ? 'checked' : ''
        );
    }


}
