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

    protected function render_message($msg, $msg_type='error') {
        echo "<div class='$msg_type notice is-dismissible'><p>$msg</p></div>";
    }

    public function phpmailer_enable_debugging($phpmailer) {
        $phpmailer->SMTPDebug = 3;
        $phpmailer->Debugoutput = 'html';
    }


    public function set_html_content_type() {
        return 'text/html';
    }

    private function send_email($recipient) {
        add_filter( 'wp_mail_content_type', array($this, 'set_html_content_type'));
        $result = wp_mail($recipient, 'SparkPost email test', '<h3>Hurray!!</h3><p>SparkPost email is working!</p>');
        remove_filter( 'wp_mail_content_type', array($this, 'set_html_content_type'));
        return $result;
    }

    public function test_email_sending($recipient, $debug=false) {
        if(empty($recipient)) {
            return $this->render_message('Recipient can\'t be empty!');
        }

        if(!is_email($recipient)) {
            return $this->render_message('Recipient is not valid!');
        }
        
        if($debug) {
            add_action('phpmailer_init', array($this, 'phpmailer_enable_debugging'));
            echo '<div class="notice is-dismissible">';
            echo '<h4>Debug Messages</h4>';
            $result = $this->send_email($recipient);
            echo '</div>';
        } else {
            $result = $this->send_email($recipient);
        }
        
        if ($result) {
           $this->render_message('Email sent successfully', 'updated'); 
        } else {
            $this->render_message('Email could not sent');
        }
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
            <div>
                <h2>Test Email</h2>
                <?php 
                if(isset($_POST['sp_test_email'])) {
                    $this->test_email_sending($_POST['to_email'], !empty($_POST['enable_debugging']));
                }
                ?>

                <form method="post" action="">
                    <input type="hidden" name="sp_test_email" value="" />
                    <?php 
                    do_settings_sections("sp-test-email");
                    submit_button('Send Test Email');
                    ?>
                </form>
            </div>
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

        add_settings_section('test_email', '', null, 'sp-test-email');
        add_settings_field('to_email', 'Recipient*', array($this, 'render_to_email_field'), 'sp-test-email', 'test_email');
        add_settings_field('debug_messages', 'Debug', array($this, 'render_enable_debugging_field'), 'sp-test-email', 'test_email');
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

    public function render_to_email_field()
    {
        echo '<input type="email" id="to_email" name="to_email" class="regular-text" value="" />';
    }

    public function render_enable_debugging_field()
    {
        echo '<label><input type="checkbox" id="enable_debugging" name="enable_debugging" value="1" %s />Show email debugging messages</label>';
    }
}
