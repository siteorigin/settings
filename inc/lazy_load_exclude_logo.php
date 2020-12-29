<?php
/**
 * Exclude Logo from Lazy Load plugins.
 */
class SiteOrigin_Settings_Lazy_Load_Exclude_Logo {

	function __construct() {
		add_filter( 'wp_get_attachment_image_attributes', array( $this, 'exclude_logo' ), 10, 2 );
	}

	static function single() {
		static $single;
		if ( empty( $single ) ) {
			$single = new self();
		}
		return $single;
	}

	public function exclude_logo( $attr, $attachment ) {
		$logo_setting = apply_filters( 'siteorigin_settings_lazy_load_exclude_logo_setting', 'branding_logo' );
		if ( ! empty( $logo_setting ) ) {
			$custom_logo_id = siteorigin_setting( $logo_setting );
		}

		if ( empty( $custom_logo_id ) ) {
			$custom_logo_id = get_theme_mod( 'custom_logo' );
		}

		if ( ! empty( $custom_logo_id ) && $attachment->ID == $custom_logo_id ) {
			// Jetpack Lazy Load
			if ( class_exists( 'Jetpack_Lazy_Images' ) || class_exists( 'Automattic\\Jetpack\\Jetpack_Lazy_Images' ) ) {
				$attr['class'] .= ' skip-lazy';
			}
			// Smush Lazy Load
			if ( class_exists( 'Smush\Core\Modules\Lazy' ) ) {
				$attr['class'] .= ' no-lazyload';
			}
			// LiteSpeed Cache Lazy Load
			if ( class_exists( 'LiteSpeed_Cache' ) || class_exists( 'LiteSpeed\Media' ) ) {
				$attr['data-no-lazy'] = 1;
			}
			// WP 5.5
			$attr['loading'] = 'eager';
		}
		return $attr;
	}
}
