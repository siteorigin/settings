<?php

class SiteOrigin_Settings_Localization {

	public $terms;

	function __construct() {
		// These terms must all be passed through a siteorigin_settings_localization filter in your theme
		// There, they must be passed through proper WordPress localization functions
		// https://codex.wordpress.org/I18n_for_WordPress_Developers
		// Copy the content from localization.txt into your theme and change the translation domain.

		$terms = array(
			'section_title'            => 'Theme Settings',
			'section_description'      => 'Change settings for your theme.',
			'premium_only'             => 'Available in Premium',
			'premium_url'              => 'https://siteorigin.com/premium/',
			'close'                    => 'Close',
			'edit_widget'              => 'Edit Widget',

			// For the controls
			'variant'                  => 'Variant',
			'subset'                   => 'Subset',

			// For the settings metabox
			'meta_box'                 => 'Page settings',

			// For archives section
			'page_section_title'       => 'Page Template Settings',
			'page_section_description' => 'Change layouts for various pages on your site.',

			// For all the different temples and template types
			'template_home'            => 'Blog Page',
			'template_search'          => 'Search Results',
			'template_date'            => 'Date Archives',
			'template_404'             => 'Not Found',
			'template_author'          => 'Author Archives',
			'templates_post_type'      => 'Type',
			'templates_taxonomy'       => 'Taxonomy',

			// Widgets bundle field
			'requires_widgets_bundle'  => 'This field requires the Widgets Bundle plugin.',
			'install_widgets_bundle'   => '<a href="%s">Install</a> the Widgets Bundle now.',

			// Everything for the about pages
			'about_theme'              => 'About %s',
			'get_updates'              => 'Get Updates',
			'watch_video'              => 'Watch The Video',
			'share_theme'              => 'If you like %s, please share it!',
			'created_by'               => 'Proudly Created By',
			'free_wordpress_theme'     => 'Free WordPress Theme',

			// The message after activating the theme
			'thanks_for_choosing'      => 'Thanks for choosing %s!',
			'learn_more'               => 'You can learn more about it %shere%s, or head straight to the %scustomizer%s to start setting it up.',
			'learn_button'             => 'Learn About %s'
		);

		$this->terms = apply_filters( 'siteorigin_settings_localization', $terms );
	}

	static function single(){
		static $single;
		if( empty( $single ) ) {
			$single = new self();
		}
		return $single;
	}

	static function get( $term = false ) {
		$loc = self::single();

		if( empty( $term ) ) {
			return $loc->terms;
		}

		if( !empty( $loc->terms[ $term ] ) ) {
			return  $loc->terms[ $term ];
		}
		else return $term;
	}

}
