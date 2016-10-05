## Plugin Hooks
SparkPost's WordPress plugin has a number of hooks that can be used to modify how it works and/or inject new functionality around its lifecycle.

### Convention
Hook names are prefixed with `wpsp_`.There are some hooks that are common across mailers (http and smtp). Unless the hook applies to both mailer, it has either `smtp_` or `smtp_` after the initial prefix (`wpsp_`).

### List of hooks

| Hook Name                         | Applicable Mailer          | Description (Purpose)  |
| -------------                     |:----------------:| -----:|
| wpsp_get_settings                 | Both | Tap into settings objects  
| wpsp_init_http_mailer*            | HTTP | Modify/replace http mailer instance
| wpsp_http_get_lib                 | HTTP | Modify/replace http library
| wpsp_http_before_send*            | HTTP |
| wpsp_http_after_send*             | HTTP |  
| wpsp_http_handle_response         | HTTP | Custom handler for http response. **Should return boolean to stop further processing.**
| wpsp_http_recipients              | HTTP |
| wpsp_open_tracking                | Both |  
| wpsp_click_tracking               | Both |
| wpsp_http_template_id             | HTTP | Use a different template ID
| wpsp_substitution_content_tag_name| HTTP | Use a different tag for content substitution
| wpsp_http_body                    | HTTP |  
| wpsp_http_sender_email            | Both |
| wpsp_http_sender_name             | Both |
| wpsp_http_response_body           | HTTP |
| wpsp_http_recipients              | HTTP |  
| wpsp_api_key                      | BOTH | Use different API Key/Password
| wpsp_http_request_headers         | HTTP |
| wpsp_http_reply_to                | HTTP |
| spwp_http_body_headers            | HTTP |  
| wpsp_smtp_msys_api                | SMTP |

\* These are action hooks. So return value is irrelevant. 
