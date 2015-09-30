# SiteOrigin Settings CSS Parser

This is a script that helps you create custom CSS from your SCSS files. Please note the following requirements before running the settings parser.

1. There must be a `settings.conf.php` in the `inc` folder of your theme.
2. Your theme must use SASS, which should all be located in the `sass` folder of your theme.
3. Make sure you have SASS [installed](http://sass-lang.com/install).
4. SiteOrigin Settings must be installed at `/inc/settings/settings.php` and the bin folder must be at `/inc/settings/bin/`

Run the parser script using `/create_css.php` in the command line. You should remove the `bin` folder in SiteOrigin Settings before distributing your theme.

## Configuration File

This file is checked every time you run the settings parser. It tells the parser which files should be processed and how to translate the variables.

```php
<?php

// An array mapping SCSS variable to a SiteOrigin Settings variable name
return array(
	'variables' => array(
		'color__link' => 'branding_primary_color',
	),
	'stylesheets' => array(
		'style', 'woocommerce'
	),
);
```

Where color__link is a SASS variable, and branding_primary_color is a SiteOrigin Setting key.