<?php
namespace WPSparkPost;
// If ABSPATH is defined, we assume WP is calling us.
// Otherwise, this could be an illicit direct request.
if (!defined('ABSPATH')) exit();


/**
 * @package wp-sparkpost
 */
class SparkPostAdmin
{
    private $settings;

    public function __construct()
    {
        $this->settings = SparkPost::get_settings(false);
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'admin_page_init'));

    }

    public function add_plugin_page()
    {
        add_options_page(
            'SparkPost Settings',
            'SparkPost',
            'manage_options',
            'wpsp-setting-admin',
            array($this, 'wpsp_admin_page')
        );
    }

    protected function render_message($msg, $msg_type = 'error')
    {
        printf('<div class="%s notice is-dismissible"><p>%s</p></div>', $msg_type, $msg);
    }

    public function phpmailer_enable_debugging($phpmailer)
    {
        $phpmailer->SMTPDebug = 3;
        $phpmailer->Debugoutput = 'html';
    }


    public function set_html_content_type()
    {
        return 'text/html';
    }

    private function send_email($recipient, $attachments = array())
    {
        add_filter('wp_mail_content_type', array($this, 'set_html_content_type'));
        $headers = array();
        $result = wp_mail($recipient,
            'SparkPost email test',
            '<h3>Hurray!!</h3><p>You\'ve got mail! <br/><br> Regards,<br/><a href="https://www.sparkpost.com">SparkPost</a> WordPress plugin</p>',
            $headers,
            $attachments
        );
        remove_filter('wp_mail_content_type', array($this, 'set_html_content_type'));
        return $result;
    }

    public function test_email_sending($recipient, $debug = false, $include_attachment = false)
    {
        if (empty($recipient)) {
            return $this->render_message('Please enter a valid email address in the recipient field below.');
        }

        if (!is_email($recipient)) {
            return $this->render_message('Recipient is not a valid email address.');
        }


        if($include_attachment) {
            $attachments = array(__DIR__ . '/sample.txt');
        } else {
            $attachments = array();
        }

        if ($debug) {
            add_action('phpmailer_init', array($this, 'phpmailer_enable_debugging'));
            echo '<div class="notice is-dismissible">';
            echo '<h4>Debug Messages</h4>';
            $result = $this->send_email($recipient, $attachments);
            echo '</div>';
        } else {
            $result = $this->send_email($recipient, $attachments);
        }

        if ($result) {
            if (!$this->settings['enable_sparkpost']) {
                $this->render_message('Test email sent successfully but not through SparkPost.<br/>Note: the SparkPost plugin is not enabled.  To enable it, check "Send email using SparkPost" on the SparkPost settings page.', 'updated');
            } else {
                $this->render_message('Test email sent successfully', 'updated');
            }
        } else {
            $this->render_message('Test email could not be sent.  Please check your plugin settings and refer to <a href="https://support.sparkpost.com/customer/portal/articles/2670627-sparkpost-new-user-guide" target="_blank">Getting Started</a> in the <a href="https://support.sparkpost.com/" target="_blank">SparkPost Support Center</a>.');
        }
    }

    public function wpsp_admin_page()
    {
        ?>
        <div class="wrap">
            <form method="post" action="options.php">
                <?php
                settings_fields('sp_settings_group');
                do_settings_sections('sp-options');
                do_settings_sections('sp-overrides');
                submit_button();
                ?>
            </form>
            <hr/>
            <div>
                <h3>Test Email</h3>
                <?php
                if (isset($_POST['sp_test_email'])) {
                    $this->test_email_sending($_POST['to_email'], !empty($_POST['enable_debugging']), !empty($_POST['include_attachment']));
                }
                ?>

                <form method="post" action="">
                    <input type="hidden" name="sp_test_email" value=""/>
                    <?php
                    do_settings_sections('sp-test-email');
                    submit_button('Send Test Email', 'secondary');
                    ?>
                </form>
            </div>
        </div>
        <?php
    }

    public function admin_page_init()
    {
        register_setting('sp_settings_group', 'sp_settings', array($this, 'sanitize'));
        add_settings_section('general', 'SparkPost Settings', null, 'sp-options');
        add_settings_field('enable_sparkpost', '', array($this, 'render_enable_sparkpost_field'), 'sp-options', 'general');
        add_settings_field('sending_method', 'Method*', array($this, 'render_sending_method_field'), 'sp-options', 'general');
        add_settings_field('password', 'API Key*', array($this, 'render_password_field'), 'sp-options', 'general');
        add_settings_field('enable_tracking', 'Enable tracking*', array($this, 'render_enable_tracking_field'), 'sp-options', 'general');
        add_settings_field('template', 'Template', array($this, 'render_template_field'), 'sp-options', 'general');
        add_settings_field('transactional', 'Transactional', array($this, 'render_transactional_field'), 'sp-options', 'general');

        add_settings_section('overrides', 'Overrides', null, 'sp-overrides');
        add_settings_field('from_name', 'From name', array($this, 'render_from_name_field'), 'sp-overrides', 'overrides');
        add_settings_field('from_email', 'From email', array($this, 'render_from_email_field'), 'sp-overrides', 'overrides');

        add_settings_section('test_email', '', null, 'sp-test-email');
        add_settings_field('to_email', 'Recipient*', array($this, 'render_to_email_field'), 'sp-test-email', 'test_email');
        add_settings_field('include_attachment', '', array($this, 'render_include_attachment_field'), 'sp-test-email', 'test_email');
        add_settings_field('debug_messages', 'Debug', array($this, 'render_enable_debugging_field'), 'sp-test-email', 'test_email');
    }

    public function sanitize($input)
    {

        $new_input = array();

        if (!empty($input['from_email'])) {
            $new_input['from_email'] = sanitize_text_field($input['from_email']);
        }

        if (!empty($input['from_name'])) {
            $new_input['from_name'] = sanitize_text_field($input['from_name']);
        }

        if (!empty($input['template'])) {
            $new_input['template'] = sanitize_text_field($input['template']);
        }

        if (empty($input['password'])) {
            add_settings_error('Password', esc_attr('password'), 'API Key is required', 'error');
        } else {
            if(SparkPost::is_key_obfuscated(esc_attr($input['password']))) { //do not change password
                $new_input['password'] = $this->settings['password'];
            } else {
                $new_input['password'] = sanitize_text_field($input['password']);
            }
        }

        if (isset($input['enable_sparkpost'])) {
            $new_input['enable_sparkpost'] = true;
        } else {
            $new_input['enable_sparkpost'] = false;
        }

        if ((empty($input['password'])) && $new_input['enable_sparkpost']) {
            add_settings_error('Enable', esc_attr('enable_sparkpost'), 'You must enter API key to enable sending via SparkPost', 'error');
            $new_input['enable_sparkpost'] = false;
        }

        switch (esc_attr($input['sending_method'])) {
            case 'smtp587':
                $new_input['port'] = 587;
                $new_input['sending_method'] = 'smtp';
                break;
            case 'smtp2525':
                $new_input['port'] = 2525;
                $new_input['sending_method'] = 'smtp';
                break;
            default:
                unset($new_input['port']);
                $new_input['sending_method'] = 'api';
                break;
        }

        if(!empty($input['enable_tracking'])) {
            $new_input['enable_tracking'] = true;
        } else {
            $new_input['enable_tracking'] = false;
        }

        if(!empty($input['transactional'])) {
          $new_input['transactional'] = true;
        } else {
          $new_input['transactional'] = false;
        }

        return $new_input;
    }

    public function render_enable_sparkpost_field()
    {
        printf(
            '<label><input type="checkbox" id="enable_sparkpost" name="sp_settings[enable_sparkpost]" value="1" %s />Send email using SparkPost</label>', $this->settings['enable_sparkpost'] ? 'checked' : ''
        );
    }

    public function render_username_field()
    {
        echo '<input type="text" id="username" name="sp_settings[username]" class="regular-text" value="SMTP_Injection" readonly />';
    }

    public function render_password_field()
    {
        $api_key = SparkPost::obfuscate_api_key($this->settings['password']);

        printf(
            '<input type="text" id="password" name="sp_settings[password]" class="regular-text" value="%s" /><br/>
            <small><ul><li>For SMTP, set up an API key with the <strong>Send via SMTP</strong> permission</li>
            <li>For HTTP API, set up an API Key with the <strong>Transmissions: Read/Write, Templates: Read/Write</strong> permissions</li><a href="https://support.sparkpost.com/customer/portal/articles/1933377-create-api-keys" target="_blank">Need help creating a SparkPost API key?</a></small>',
            isset($api_key) ? $api_key : ''
        );
    }

    public function render_template_field()
    {
        ?>
        <input type="text" id="template" name="sp_settings[template]" class="regular-text"
               value="<?php echo $this->settings['template']; ?>"/><br/>
        <small>
            <ul>
                <li>- Please see <a href="https://support.sparkpost.com/customer/portal/articles/2409547-using-templates-with-the-sparkpost-wordpress-plugin" target="_blank">this article</a> for detailed information about using templates with this plugin.</li>
                <li>- Templates can only be used with the HTTP API.</li>
                <li>- Leave this field blank to disable use of a template. You can still specify it by <a href="https://github.com/SparkPost/wordpress-sparkpost/blob/master/docs/hooks.md" target="_blank">using hooks</a>.</li>
            </ul>
        </small>
    <?php
    }

    public function render_from_email_field()
    {
        $hint = '<strong>Important:</strong> Domain must match with one of your verified sending domains.';
        if(empty($this->settings['from_email'])){
            $hostname = parse_url(get_bloginfo('url'), PHP_URL_HOST);
            $hint .= sprintf(' When left blank, <strong>%s</strong> will be used as email domain', $hostname);
        }

        $hint = sprintf('<small>%s</small>', $hint);
        printf(
            '<input type="email" id="from_email" name="sp_settings[from_email]" class="regular-text" value="%s" /><br/>%s',
            isset($this->settings['from_email']) ? esc_attr($this->settings['from_email']) : '', $hint
        );
    }

    public function render_from_name_field()
    {
        printf(
            '<input type="text" id="from_name" name="sp_settings[from_name]" class="regular-text" value="%s" />',
            isset($this->settings['from_name']) ? esc_attr($this->settings['from_name']) : ''
        );
    }

    public function render_sending_method_field()
    {
        $method = esc_attr($this->settings['sending_method']);
        $port = esc_attr($this->settings['port']);

        $selected_method = !empty($method) ? $method : 'api';
        $selected_port = !empty($port) ? $port : 587;

        echo '<select name="sp_settings[sending_method]">
        <option value="api" ' . (($selected_method == 'api') ? 'selected' : '') . '>HTTP API (Default)</option>
        <option value="smtp587" ' . (($selected_method == 'smtp' && $selected_port == 587) ? 'selected' : '') . '>SMTP (Port 587)</option>
        <option value="smtp2525" ' . (($selected_method == 'smtp' && $selected_port == 2525) ? 'selected' : '') . '>SMTP (Port 2525)</option>
        </select>';
    }

    public function render_enable_tracking_field()
    {
        printf(
            '<label><input type="checkbox" id="enable_tracking" name="sp_settings[enable_tracking]" value="1" %s />Track clicks/opens in SparkPost</label>', $this->settings['enable_tracking'] ? 'checked' : ''
        );
    }

    public function render_to_email_field()
    {
        echo '<input type="email" id="to_email" name="to_email" class="regular-text" value="" />';
    }

    public function render_enable_debugging_field()
    {
        echo '<label><input type="checkbox" id="enable_debugging" name="enable_debugging" value="1" checked />Show email debugging messages</label>';
    }

    public function render_transactional_field()
    {
        printf('<label><input type="checkbox" id="transactional" name="sp_settings[transactional]" value="1" %s />Mark emails as transactional</label>
        <br/><small>Upon checked, by default, it\'ll set mark all emails as transactional. It should be set false (using hooks) for non-transactional emails.</small>',
         $this->settings['transactional'] ? 'checked' : '');

    }


    public function render_include_attachment_field()
    {
        echo '<label><input type="checkbox" id="include_attachment" name="include_attachment" value="1" />Include Attachment</label>';
    }
}
