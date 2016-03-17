=== SparkPost ===
Contributors: sparkpost, rajuru
Tags: sparkpost, smtp, wp_mail, mail, email
Requires at least: 4.0
Tested up to: 4.4.2
Stable tag: 1.1.5
License: GPLv2 or later

Send all your email from WordPress through SparkPost, the most advanced email delivery service.

== Description ==
The [SparkPost](https://www.sparkpost.com/) email delivery service offers best in class deliverability to ensure your mail hits the inbox, live analytics to review, track and optimize your email activities, as well as highest performance when you need it most: always.

When the SparkPost plugin is enabled, all outgoing email from your WordPress installation is sent through your SparkPost service.  From within [the SparkPost UI](https://app.sparkpost.com/), you can then watch your campaigns unfold live by tracking your engagement metrics, learn what your audience responds to and even integrate more deeply with your app using the SparkPost API.

== Installation ==

Option 1: Install using the WordPress Admin Panel:

1. From your WordPress site's Admin Panel go to _Plugins -> Add New_.
1. Enter 'sparkpost' in the _Search Plugins_ text field and hit Enter.
1. Your search results will include a plugin named SparkPost.
1. Click the _Install Now_ button to install the SparkPost plugin.
1. Upon successful installation, the SparkPost plugin will appear in _Plugins -> Installed Plugins_.
1. Finally, click _Activate Plugin_ to activate your new plugin.

Option 2: Install manually:

1. Download the plugin zip file by clicking on the _Download_ button on [this page](https://wordpress.org/plugins/sparkpost).
1. Unzip the plugin zip file into your WordPress plugins directory (usually `wp-content/plugins`)
1. In the WordPress Admin Panel, go to the _Plugins_ page.  In the list, you should see your new **SparkPost** plugin.
1. Click **Activate** to activate your new plugin.

Upon successful activation, **SparkPost** will appear on the _Settings_ menu in the WordPress Admin Panel. Click on _Settings -> SparkPost_ to open the SparkPost plugin configuration page and complete setup.

Fill in each plugin configuration field:

* **enable?**: Check this box to enable your plugin :)
* **from name**: a human-friendly name to show in 'From' headers
  * e.g. your name or your site's name

* **from email**: your 'From' email address
  * e.g. yourname@yourdomain.com

* **SMTP password**: A SparkPost API key with *Send via SMTP* permission
  * Hint: they look like this: 39fb780c182927cde6baddab00f67676feed1beef17

For information on how to create an API key, follow the [official documentation](https://support.sparkpost.com/customer/portal/articles/1933377-create-api-keys).

Ensure your [sending domain](https://app.sparkpost.com/#/configuration/sending-domains) is properly configured within SparkPost.

== Frequently Asked Questions ==
= What do I need to start using this plugin? =
You'll need to create an account on SparkPost.com and then generate an API Key with *Send via SMTP* permission. Creating an account is completely free. Visit [SparkPost](https://app.sparkpost.com/sign-up) to signup.

= How do I create an API key? =
Follow [this tutorial](https://support.sparkpost.com/customer/portal/articles/1933377) for creating an API key. **Remember:** your API key must have 'Send via SMTP' permission to be usable by this plugin.

= How do I get further help? =
Visit our [support portal](https://support.sparkpost.com/) for help.


== Changelog ==
= 1.1.5 =
- Support alternate port
- Use filter to set sender info
- Clearer settings panel

= 1.1.4 =
Update copy

= 1.1.3 =
Richer plugin settings error messages and help text, TLS now permanently enabled

= 1.1.2 =
Shortened the plugin name to just SparkPost, added more readme copy, renamed 'SMTP password' setting to 'API key'

= 1.1.1 =
- Add link to `Settings` in plugins list page

= 1.1.0 =
- Add support for sending test email
- Add support for enable/disable sending via SparkPost

= 1.0.0 =
Initial version

== Upgrade Notice ==
This is initial version.
