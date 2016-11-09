# Send Buddypress emails using SparkPost 

Buddypress plugin implements their own mailer (`bb_mail`) by creating a seperate mailer object of phpMailer class; same way SparkPost plugin does for HTTP Mailer. That's why they don't work nicely with each other out-of-the-box. However, Buddypress developers also made it extremely easy to fallback to default `wp_mail`. 

To fix the problem, just add the following snippet in your theme's `functions.php` (feel free to create, if one doesn't exist already). 

```
add_filter('bp_email_use_wp_mail', function() {
  return true;
});
```
