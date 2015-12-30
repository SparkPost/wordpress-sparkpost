# WordPress SparkPost

Use SparkPost emails right from your WordPress site. 

## Usages

### Installation

**Option 1**

* Download the plugin from [WordPress's plugins repository](https://wordpress.org/plugins/sparkpost/). 
* Upload to plugins directory of your WordPress installation which, usually, is `wp-content/plugins`.
* Activate the plugin from admin panel. 

**Option 2**
* From your WordPress site's admin panel go to **Plugins -> Add New**. 
* Enter *sparkpost* in *Search Plugins* text field and hit Enter. 
* It should show pluging titled *SparkPost SMTP*. Click **Install Now** button. 
* In next page, upon successful downloading click **Activate Plugin**. 

### Configuration
Once plugin is installed, you need some quick **but important** configuration. Click **SparkPost SMTP** from Settings menu
* In the form put SparkPost API key, sender name and email. 
* Click **Save Changes**

### Test Email
From Test Email section, try sending a test email to yourself to make sure the credentials are working fine. 


## Development
* Install WordPress
* Clone this repository
* Create a symlink to the repository's `trunk` directory in WordPress plugins directory. 

For example, if you've cloned this repository to `~/src/wordpress-sparkpost` and your WordPress is installed in `~/src/wordpress`, you should create a symlink like following 

```
ln -s ~/src/wordpress-sparkpost/trunk ~/src/wordpress/wp-contents/plugins/wordpress-sparkpost
```
* Activate the plugin from admin panel
