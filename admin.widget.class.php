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

    public function asset_url($relative_asset_path)
    {
        return plugins_url($relative_asset_path, __FILE__);
    }

    public function add_assets()
    {
        wp_enqueue_style('sp-admin-css', $this->asset_url('assets/styles.css'));
    }

    public function add_plugin_page()
    {
        $page = add_options_page(
            'SparkPost Settings',
            'SparkPost',
            'manage_options',
            'wpsp-setting-admin',
            array($this, 'wpsp_admin_page')
        );

        add_action("admin_print_styles-{$page}", array($this, 'add_assets'));
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


        if ($include_attachment) {
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

    function render_tabs($active = '')
    {
        $tabs = array(
            array(
                'slug' => 'basic',
                'href' => admin_url(add_query_arg(array('page' => 'wpsp-setting-admin', 'tab' => 'basic'), 'admin.php')),
                'name' => 'Basic Settings'
            ),
            array(
                'slug' => 'overrides',
                'href' => admin_url(add_query_arg(array('page' => 'wpsp-setting-admin', 'tab' => 'overrides'), 'admin.php')),
                'name' => 'Overrides'
            ),
            array(
                'slug' => 'test',
                'href' => admin_url(add_query_arg(array('page' => 'wpsp-setting-admin', 'tab' => 'test'), 'admin.php')),
                'name' => 'Test'
            )
        );

        $email_logs_tab = array(
            'slug' => 'logs',
            'href' => admin_url(add_query_arg(array('page' => 'wpsp-setting-admin', 'tab' => 'logs'), 'admin.php')),
            'name' => 'Email Logs'
        );

        if (SparkPost::is_logging_enabled()) {
            $tabs[] = $email_logs_tab;
        }

        $inactive_class = 'nav-tab';
        $active_class = 'nav-tab nav-tab-active';
        $markups = '<h2 class="nav-tab-wrapper">';
        foreach (array_values($tabs) as $tab_data) {
            $is_current = (bool)($tab_data['slug'] == $active);
            $tab_class = $is_current ? $active_class : $inactive_class;
            $markups .= '<a href="' . esc_url($tab_data['href']) . '" class="' . esc_attr($tab_class) . '">' . esc_html($tab_data['name']) . '</a>';
        }

        $markups .= '</h2>';
        echo $markups;
    }

    function render_basic_settings()
    {
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('sp_settings_group_basic');
            do_settings_sections('sp-options-basic');
            submit_button();
            ?>
        </form>
        <?php
    }

    function render_overrides()
    {
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('sp_settings_group_overrides');
            do_settings_sections('sp-options-overrides');
            submit_button();
            ?>
        </form>
        <hr/>
        <?php
    }

    protected function render_test_section()
    {
        if (isset($_POST['sp_test_email'])) {
            $this->test_email_sending($_POST['to_email'], !empty($_POST['enable_debugging']), !empty($_POST['include_attachment']));
        }
        ?>
        <form method="post" action="admin.php?page=wpsp-setting-admin&tab=test">
            <input type="hidden" name="sp_test_email" value=""/>

            <div>
                <?php
                do_settings_sections('sp-test-email');
                submit_button('Send Test Email');
                ?>
            </div>
        </form>
        <?php
    }

    protected function render_logs_section()
    {
        if (isset($_POST['action']) && $_POST['action'] === 'clearlogs') {
            SparkPost::clear_logs();
        }
        $logsTable = new SparkPostEmailLogs();
        $logsTable->prepare_items();

        $logsTable->display();

        if (count($logsTable->items) > 0) {
            ?>
            <form method="post" action="admin.php?page=wpsp-setting-admin&tab=logs">
                <input type="hidden" name="action" value="clearlogs"/>
                <input type="submit" class="button button-primary" value="Empty Logs"/>
            </form>
            <?php
        }
    }

    public function wpsp_admin_page()
    {
        $image_url = $this->asset_url('assets/logo-40.png');
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'basic';

        ?>
        <div class="sp-heading"><img src="<?php echo $image_url ?>" alt="SparkPost"> &nbsp;&nbsp;
            <h2>SparkPost</h2>
        </div>
        <div class="wrap sparkpost">

            <?php

            $this->render_tabs($active_tab);

            if ($active_tab == 'overrides') {
                $this->render_overrides();
            } else if ($active_tab == 'test') {
                $this->render_test_section();
            } else if ($active_tab == 'logs') {
                $this->render_logs_section();
            } else {
                $this->render_basic_settings();
            }
            ?>
        </div>
        <?php
    }


    public function admin_page_init()
    {
        register_setting('sp_settings_group_basic', 'sp_settings_basic', array($this, 'sanitize_basic'));
        register_setting('sp_settings_group_overrides', 'sp_settings_overrides', array($this, 'sanitize_overrides'));
        register_setting('sp-test-email', 'sp_settings_test', array($this, 'sanitize_test'));

        add_settings_section('general', 'Basic settings', null, 'sp-options-basic');
        add_settings_field('enable_sparkpost', 'Enable*', array($this, 'render_enable_sparkpost_field'), 'sp-options-basic', 'general');
        add_settings_field('sending_method', 'Method*', array($this, 'render_sending_method_field'), 'sp-options-basic', 'general');
        add_settings_field('password', 'API Key*', array($this, 'render_password_field'), 'sp-options-basic', 'general');
        add_settings_field('location', 'API Location', array($this, 'render_location_field'), 'sp-options-basic', 'general' );
        add_settings_field('template', 'Template', array($this, 'render_template_field'), 'sp-options-basic', 'general');

        add_settings_section('overrides', 'Overrides', null, 'sp-options-overrides');
        add_settings_field('from_name', 'From name', array($this, 'render_from_name_field'), 'sp-options-overrides', 'overrides');
        add_settings_field('from_email', 'From email', array($this, 'render_from_email_field'), 'sp-options-overrides', 'overrides');
        add_settings_field('transactional', 'Transactional', array($this, 'render_transactional_field'), 'sp-options-overrides', 'overrides');
        add_settings_field('enable_tracking', 'Enable tracking', array($this, 'render_enable_tracking_field'), 'sp-options-overrides', 'overrides');

        if ($this->settings['sending_method'] === 'api') {
            add_settings_field('logs_emails', 'Email Logging', array($this, 'render_log_emails_field'), 'sp-options-overrides', 'overrides');
        }

        add_settings_section('test_email', '', null, 'sp-test-email');
        add_settings_field('to_email', 'Recipient*', array($this, 'render_to_email_field'), 'sp-test-email', 'test_email');
        add_settings_field('include_attachment', '', array($this, 'render_include_attachment_field'), 'sp-test-email', 'test_email');
        add_settings_field('debug_messages', 'Debug', array($this, 'render_enable_debugging_field'), 'sp-test-email', 'test_email');
    }

    public function sanitize_basic($input)
    {
        $new_input = array();

        if (!empty($input['template'])) {
            $new_input['template'] = sanitize_text_field($input['template']);
        } else {
            $new_input['template'] = '';
        }

        if (isset($input['location'])) {
            $new_input['location'] = sanitize_text_field($input['location']);
        } else {
            $new_input['location'] = '';
        }

        if (empty($input['password'])) {
            add_settings_error('Password', esc_attr('password'), 'API Key is required', 'error');
        } else {
            if (SparkPost::is_key_obfuscated(esc_attr($input['password']))) { //do not change password
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

        //validate template with HTTP API only
        if($new_input['sending_method'] !== 'api' && !empty($new_input['template'])) {
          add_settings_error('template', esc_attr('template'), sprintf("Template is not supported with SMTP methods. So template <i>%s</i> is removed from your settings.", $input['template']), 'error');
          $new_input['template'] = '';
        }

        return $new_input;
    }


    public function sanitize_overrides($input)
    {
        $new_input = array();

        if (!empty($input['from_email'])) {
            $new_input['from_email'] = sanitize_text_field($input['from_email']);
        }

        if (!empty($input['from_name'])) {
            $new_input['from_name'] = sanitize_text_field($input['from_name']);
        }

        if (!empty($input['enable_tracking'])) {
            $new_input['enable_tracking'] = true;
        } else {
            $new_input['enable_tracking'] = false;
        }

        if (!empty($input['log_emails'])) {
            $new_input['log_emails'] = true;
        } else {
            $new_input['log_emails'] = false;
        }

        if (!empty($input['transactional'])) {
            $new_input['transactional'] = true;
        } else {
            $new_input['transactional'] = false;
        }

        return $new_input;
    }

    public function render_enable_sparkpost_field()
    {
        printf(
            '<label><input type="checkbox" id="enable_sparkpost" name="sp_settings_basic[enable_sparkpost]" value="1" %s />Send email using SparkPost</label>', $this->settings['enable_sparkpost'] ? 'checked' : ''
        );
    }

    public function render_password_field()
    {
        $api_key = SparkPost::obfuscate_api_key($this->settings['password']);

        printf(
            '<input type="text" id="password" name="sp_settings_basic[password]" class="regular-text" value="%s" /><br/>
            <small><ul><li>For SMTP, set up an API key with the <strong>Send via SMTP</strong> permission</li>
            <li>For HTTP API, set up an API Key with the <strong>Transmissions: Read/Write, Templates: Read/Write</strong> permissions</li><a href="https://support.sparkpost.com/customer/portal/articles/1933377-create-api-keys" target="_blank">Need help creating a SparkPost API key?</a></small>',
            isset($api_key) ? $api_key : ''
        );
    }

    public function render_template_field()
    {
        ?>
        <input type="text" id="template" name="sp_settings_basic[template]" class="regular-text"
            value="<?php echo esc_attr($this->settings['template']); ?>"/><br/>
        <small>
            <ul>
                <li>- Please see <a
                            href="https://support.sparkpost.com/customer/portal/articles/2409547-using-templates-with-the-sparkpost-wordpress-plugin"
                            target="_blank">this article</a> for detailed information about using templates with this
                    plugin.
                </li>
                <li>- Templates can only be used with the HTTP API.</li>
                <li>- Leave this field blank to disable use of a template. You can still specify it by <a
                            href="https://github.com/SparkPost/wordpress-sparkpost/blob/master/docs/hooks.md"
                            target="_blank">using hooks</a>.
                </li>
            </ul>
        </small>
        <?php
    }

    public function render_from_email_field()
    {
        $hint = '<strong>Important:</strong> Domain must match with one of your verified sending domains.';
        if (empty($this->settings['from_email'])) {
            $hostname = parse_url(get_bloginfo('url'), PHP_URL_HOST);
            $hint .= sprintf(' When left blank, <strong>%s</strong> will be used as email domain', $hostname);
        }

        $hint = sprintf('<small>%s</small>', $hint);
        printf(
            '<input type="email" id="from_email" name="sp_settings_overrides[from_email]" class="regular-text" value="%s" /><br/>%s',
            isset($this->settings['from_email']) ? esc_attr($this->settings['from_email']) : '', $hint
        );
    }

    public function render_from_name_field()
    {
        printf(
            '<input type="text" id="from_name" name="sp_settings_overrides[from_name]" class="regular-text" value="%s" />',
            isset($this->settings['from_name']) ? esc_attr($this->settings['from_name']) : ''
        );
    }

    public function render_sending_method_field()
    {
        $method = esc_attr($this->settings['sending_method']);
        $port = esc_attr($this->settings['port']);

        $selected_method = !empty($method) ? $method : 'api';
        $selected_port = !empty($port) ? $port : 587;

        echo '<select name="sp_settings_basic[sending_method]">
        <option value="api" ' . (($selected_method == 'api') ? 'selected' : '') . '>HTTP API (Default)</option>
        <option value="smtp587" ' . (($selected_method == 'smtp' && $selected_port == 587) ? 'selected' : '') . '>SMTP (Port 587)</option>
        <option value="smtp2525" ' . (($selected_method == 'smtp' && $selected_port == 2525) ? 'selected' : '') . '>SMTP (Port 2525)</option>
        </select>';
    }

    public function render_location_field()
    {
        $selected = !empty($this->settings['location']) ? esc_attr($this->settings['location']) : '';

        echo '<select name="sp_settings_basic[location]">
        <option value="" ' . (($selected === '') ? 'selected' : '') . '>Worldwide</option>
        <option value="eu" ' . (($selected === 'eu') ? 'selected' : '') . '>EU</option>
        </select>';
    }

    public function render_enable_tracking_field()
    {
        printf(
            '<label><input type="checkbox" name="sp_settings_overrides[enable_tracking]" value="1" %s />Track clicks/opens in SparkPost</label>', $this->settings['enable_tracking'] ? 'checked' : ''
        );
    }

    public function render_log_emails_field()
    {
        printf('<label><input type="checkbox" name="sp_settings_overrides[log_emails]" value="1" %s />Store log of generated emails</label>
      <br/><small>HTTP API only.</small>',
            $this->settings['log_emails'] ? 'checked' : '');
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
        printf('<label><input type="checkbox" id="transactional" name="sp_settings_overrides[transactional]" value="1" %s />Mark emails as transactional</label>
        <br/><small>Upon checked, by default, it\'ll set mark all emails as transactional. It should be set false (using hooks) for non-transactional emails.</small>',
            $this->settings['transactional'] ? 'checked' : '');

    }

    public function render_include_attachment_field()
    {
        echo '<label><input type="checkbox" id="include_attachment" name="include_attachment" value="1" />Include Attachment</label>';
    }
}
