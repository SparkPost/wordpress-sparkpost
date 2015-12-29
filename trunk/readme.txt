=== SparkPost SMTP ===
Contributors: sparkpost, rajuru
Tags: sparkpost, smtp, wp_mail, mail, email
Requires at least: 4.0
Tested up to: 4.4
Stable tag: trunk
License: GPLv2 or later

Send emails via SparkPost

== Description ==
Patch wp_mail to send emails via SparkPost using SMTP. 

== Installation ==
* Upload this plugin to WordPress (WP) plugins directory (usually wp-content/plugins)
* Go to WP Admin Panel -> Plugins. In the list, you should see this as \'SparkPost SMTP\'. 
* Click \'Activate\'. 

* Upon successful activation, it\'ll add a new item in Settings menu titled \'SparkPost SMTP\'. Click there to open configuration page. 
* Complete form with sending email, sending name and password (SparkPost API key with \'Send via SMTP\' permission\'). For more information on how to create an API key, follow official documentation on https://support.sparkpost.com/customer/portal/articles/1933377-create-api-keys

== Frequently Asked Questions ==
= What do I need to start using this plugin? =
You\'ll need to create an account in SparkPost.com and then generate an API Key with \'Send via SMTP\' permission. Creating an account is completely free. Visit https://app.sparkpost.com/sign-up to signup. 

= How do I create an API key? = 
Follow [this tutorial](https://support.sparkpost.com/customer/portal/articles/1933377) for creating an API key. *Remember:* your API key must have \'Send via SMTP\' permission to be usable by this plugin. 

= How do I get further help? = 
Visit our [support portal](https://support.sparkpost.com/) for help. 


== Changelog ==
= 1.0.0 =
Initial version

== Upgrade Notice ==
This is initial version.