=== SparkPost ===
Contributors: sparkpost, rajuru
Tags: sparkpost, smtp, wp_mail, mail, email
Requires at least: 4.3
Tested up to: 4.7.2
Stable tag: 3.0.1
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

== Frequently Asked Questions ==

= What do I need to start using this plugin? =
You'll need to create an account on SparkPost.com and then generate an API Key with *Send via SMTP* and *Transmission Read/Write* permissions. Creating an account is completely free. Visit [SparkPost](https://app.sparkpost.com/sign-up) to signup.

= How do I create an API key? =
Follow [this tutorial](https://support.sparkpost.com/customer/portal/articles/1933377) for creating an API key. **Remember:** your API key must have *Send via SMTP* and *Transmission Read/Write* permissions to be usable by this plugin.

= How do I get further help? =
Visit plugin's [official issue tracker](https://github.com/SparkPost/wordpress-sparkpost/issues) and create new issue, if appropriate.


== Changelog ==
= 3.0.1 =
- Fix error with older php version ([#113](https://github.com/SparkPost/wordpress-sparkpost/issues/113))

= 3.0.0 =
- Support attachments in template ([#97](https://github.com/SparkPost/wordpress-sparkpost/issues/97)). Add `Templates: Read/Write` permission to API Key for this to work!
- Tested in WordPress v4.7.3
- Fix sending email with sandbox ([#109](https://github.com/SparkPost/wordpress-sparkpost/issues/109))

= 2.6.4 =
- Fix the issue to use template hook when not set in settings ([#95](https://github.com/SparkPost/wordpress-sparkpost/issues/95))
- Clarify attachment can't be sent with template and include workaround example ([#96](https://github.com/SparkPost/wordpress-sparkpost/issues/96))
- Include attachment in test email

= 2.6.3 =
- Add plugin name to XMailer (for SMTP)
- Tested in WordPress v4.7.2

= 2.6.2 =
- Tested in WordPress v4.7.1

= 2.6.0 =
- Handle multiple recipients correctly
- Fix getting started link
- Send assoc array to `wpsp_smtp_msys_api` filter

= 2.5.0 =
- Add support for [Transactional email](https://github.com/SparkPost/wordpress-sparkpost/blob/master/docs/transactional.md)
- Add support for [hooks](https://github.com/SparkPost/wordpress-sparkpost/blob/master/docs/hooks.md)

= 2.4.1 =
- Fix Reply-To header issue with WordPress 4.6

= 2.4.0 =
- Add supports for CC and BCC using HTTP API

= 2.3.0 =

- Fixed issue [#33](https://github.com/SparkPost/wordpress-sparkpost/issues/33) where from email and reply to were being overridden by templates: see [this article](https://support.sparkpost.com/customer/portal/articles/2409547-using-templates-with-the-sparkpost-wordpress-plugin) for detailed information

= 2.2.1 =
- Fix issue for previous version of WordPress

= 2.2.0 =
- Add template field for selecting a SparkPost template when using HTTP API
- Allow substituion of Subject, From name in HTTP API
- Replaced anonymous function for compatibility with older versions of PHP

= 2.1.0 =
- Enable/disable tracking option
- Add support for Reply-To in HTTP Mailer

= 2.0.1 =
- Fix email content type problem

= 2.0.0 =
- Support sending using HTTP API
- UI Tweak
- Hide API Key from UI
- Misc code improvements

= 1.1.5 =
- Support alternate port
- Use filter to set sender info
- Clearer settings panel

= 1.1.4 =
- Update copy

= 1.1.3 =
- Richer plugin settings error messages and help text, TLS now permanently enabled

= 1.1.2 =
- Shortened the plugin name to just SparkPost, added more readme copy, renamed 'SMTP password' setting to 'API key'

= 1.1.1 =
- Add link to `Settings` in plugins list page

= 1.1.0 =
- Add support for sending test email
- Add support for enable/disable sending via SparkPost

= 1.0.0 =
- Initial version

== Upgrade Notice ==

From WordPress plugins list, click `update now`.
