## Plugin Hooks
SparkPost's WordPress plugin has a number of hooks that can be used to modify how it works and/or inject new functionality around its lifecycle.

### Convention
Hook names are prefixed with `wpsp_`.

### List of hooks

| Hook Name                    | Description (Purpose)
| -------------                |:----------------:|
| wpsp_get_settings            | Tap into settings objects  
| wpsp_init_mailer*            | Modify/replace http mailer instance
| wpsp_get_http_lib            | Modify/replace http library
| wpsp_before_send*            |
| wpsp_after_send*             |  
| wpsp_handle_response         | Custom handler for http response. **Should return boolean to stop further processing.**
| wpsp_recipients              |
| wpsp_open_tracking           |  
| wpsp_click_tracking          |
| wpsp_template_id             | Use a different template ID
| wpsp_substitution_content_tag_name| Use a different tag for content substitution
| wpsp_sender_email            |
| wpsp_sender_name             |
| wpsp_response_body           |
| wpsp_api_key                 | Use different API Key/Password
| wpsp_request_headers         |
| wpsp_reply_to                |
| wpsp_body_headers            |  
| wpsp_smtp_msys_api            

\* These are action hooks. So return value is irrelevant.
