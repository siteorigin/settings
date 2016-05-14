<?php

class SiteOrigin_Settings_Localization {

	public $terms;

	function __construct() {
		$terms = array(
			'section_title'            => __( 'Theme Settings', 'siteorigin' ),
			'section_description'      => __( 'Change settings for your theme.', 'siteorigin' ),
			'premium_only'             => __( 'Available in Premium', 'siteorigin' ),
			'premium_url'              => 'https://siteorigin.com/premium/?target=theme_north',
			// For the controls
			'variant'                  => __( 'Variant', 'siteorigin' ),
			'subset'                   => __( 'Subset', 'siteorigin' ),
			// For the settings metabox
			'meta_box'                 => __( 'Page settings', 'siteorigin' ),
			// For archives section
			'page_section_title'       => __( 'Page Template Settings', 'siteorigin' ),
			'page_section_description' => __( 'Change layouts for various pages on your site.', 'siteorigin' ),
			// For all the different temples and template types
			'template_home'            => __( 'Blog Page', 'siteorigin' ),
			'template_search'          => __( 'Search Results', 'siteorigin' ),
			'template_date'            => __( 'Date Archives', 'siteorigin' ),
			'template_404'             => __( 'Not Found', 'siteorigin' ),
			'template_author'          => __( 'Author Archives', 'siteorigin' ),
			'templates_post_type'      => __( 'Type', 'siteorigin' ),
			'templates_taxonomy'       => __( 'Taxonomy', 'siteorigin' ),
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