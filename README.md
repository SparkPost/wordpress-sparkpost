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
* Clone this repository to the WordPress plugins directory:

```
git clone git@github.com:SparkPost/wordpress-sparkpost.git ~/src/wordpress/wp-content/plugins/wordpress-sparkpost
```

* Activate the plugin from admin panel

## Releasing

* Create a branch off master: `git checkout -b bump`
* Update the version in [wordpress-sparkpost.php](wordpress-sparkpost.php)
* Update the version and change log in [readme.txt](readme.txt)
* Commit the changes and push the branch
* Create a pull request
* Once the pull request is merged, run `./deploy.sh`
