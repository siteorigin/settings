# SiteOrigin Settings CSS Parser

This is a script that helps you create custom CSS from your SCSS files. Please note the following requirements before running the settings parser.

1. There must be a `settings.conf.php` in the `inc` folder of your theme.
2. Your theme must use SASS, which should all be located in the `sass` folder of your theme.
3. Make sure you have SASS [installed](http://sass-lang.com/install).
4. Install CSS Beautify CLI https://www.npmjs.com/package/cssbeautify-cli
5. SiteOrigin Settings must be installed at `/inc/settings/settings.php` and the bin folder must be at `/inc/settings/bin/`

Run the parser script using `./create_css.php` in the command line. It should have a chmod of 775. You should remove the `bin` folder in SiteOrigin Settings before distributing your theme. Use `./create_css.php free` to generate the CSS for the free version of your theme.

On MacOS `./create_css.php | pbcopy` to copy to clipboard.

## Updating the Google Fonts Array

In the `bin` directory add a file named `google-key.php`. Include your Google Fonts API key in that file. To update the array, run `./fetch_google_fonts.php` from the same directory.

## Configuration File

This file should be located at `inc/settings.conf.php` and is checked every time you run the settings parser. It tells the parser which files should be processed and how to translate the variables.

For an example, see SiteOrigin North - https://github.com/siteorigin/siteorigin-north/blob/develop/inc/settings.conf.php

```php
<?php

// An array mapping SiteOrigin Settings variable name to a SCSS variable
return array(
	'variables' => array(
		'branding_primary_color' => 'color__link',
	),
	'free' => array(
		// Any SASS variables that are available in the free version
		'sass__variable_1',
	),
	'stylesheets' => array(
		'style', 'woocommerce'
	),
);
```

Where color__link is a SASS variable, and branding_primary_color is a SiteOrigin Setting key.