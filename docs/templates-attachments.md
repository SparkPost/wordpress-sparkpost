# Sending Attachments with Template

Currently, you [can't send attachments](https://support.sparkpost.com/customer/portal/articles/2458261-can-attachments-be-sent-when-using-templates-) if you are you using a template. There are [technical work arounds](https://www.sparkpost.com/blog/advanced-email-templates/) but this is not still supported by SparkPost plugin yet.

## Interim solution
If all of your emails has attachments, easiest solution is not to use any template at all (i.e. Keep `Template` field empty).

However, if some of your emails use template (and they do not have attachments), you can specify a template ID in settings and then use the hook to remove the template just before creating that email that has attachment(s).

Here is an example:

```
function remove_template() {
  return false;
}

//register the filter
add_filter('wpsp_template_id', remove_template);

// call wp_mail with attachments
remove_filter('wpsp_template_id', remove_template); //so other emails use template as usual.
```

## Change in future
In future SparkPost may support sending attachments with Templates. Also, we can consider the implementing the alternative that is mentioned above directly inside this plugin.

Track [this issue](https://github.com/SparkPost/wordpress-sparkpost/issues/97) to know the latest regarding this.
