## Plugin Hooks
SparkPost's WordPress plugin has a number of hooks that can be used to modify how it works and/or inject new functionality around its lifecycle.

### Convention
Hook names are prefixed with `wpsp_`.

### List of hooks

| Hook Name                    | Type |  Description (Purpose)
| -------------                |-------------|:----------------:|
| wpsp_get_settings            | Filter   | Tap into settings objects  
| wpsp_init_mailer             | Action   | Modify/replace http mailer instance
| wpsp_get_http_lib            | Filter   | Modify/replace http library
| wpsp_before_send             | Action   |
| wpsp_after_send              | Action   |   
| wpsp_handle_response         | Filter   |  Custom handler for http response. **Return a boolean to indicate the success or failure of the transmission and to stop further processing.**
| wpsp_recipients              | Filter   |
| wpsp_open_tracking           | Filter   |   
| wpsp_click_tracking          | Filter   |
| wpsp_template_id             | Filter   |  Use a different template ID
| wpsp_sender_email            | Filter   |
| wpsp_sender_name             | Filter   |
| wpsp_response_body           | Filter   |
| wpsp_api_key                 | Filter   |  Use different API Key/Password
| wpsp_request_headers         | Filter   |
| wpsp_reply_to                | Filter   |
| wpsp_body_headers            | Filter   |   
| wpsp_smtp_msys_api           | Filter   |
| wpsp_transactional           | Filter   | Set whether an email is transactional or not.
| wpsp_substitution_data       | Filter   | Modify substitution_data object
