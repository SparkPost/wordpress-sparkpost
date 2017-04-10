# Sending Attachments with Template

Currently, SparkPost API [does not support attachments with templates](https://support.sparkpost.com/customer/portal/articles/2458261-can-attachments-be-sent-when-using-templates-).
So the plugin can't send emails out-of-the-box with attachments when a template is specified.

SparkPost API has no immediate plan to add this feature.

## Interim solution
In WordPress plugin v3.0.0, we've added a work around. The plugin uses Templates Preview endpoint to generate an email from given template ID and then uses Transmissions endpoint to send that generated email with attachments. When we use Transmissions endpoint, we exclude Template ID (as the email is already created using template and substitution data). So at the end, the sent emails looks exactly the way it is supposed to look if it's sent with template and attachments.

## Caveats
- You'll need additional permission (`Templates: Read/Write`) to your API Key.
- Your metrics will be affected. Filtering by Template ID will not include the emails sent using this mechanism.

**Note: This is done ONLY WHEN a template is specified in plugin settings (or using hook) and the email contains attachments.** If you are never going to send attachment or never going to use Template, you don't need to do anything extra.
