<?php


class SiteOrigin_Settings_Webfont_Manager {

	private $fonts;

	function __construct(){
		$this->fonts = array();
	}

	function single() {
		static $single;

		if( empty($single) ) {
			$single = new SiteOrigin_Settings_Webfont_Manager();
		}
		return $single;
	}

	function add_font( $name, $weights = array() ) {
		if( empty( $this->fonts[$name] ) ) {
			$this->fonts[$name] = $weights;
		}
		else {
			$this->fonts[$name] = array_merge( $this->fonts[$name], $weights );
			$this->fonts[$name] = array_unique( $this->fonts[$name] );
		}
	}

	function remove_font( $name ){
		unset( $this->fonts[$name] );
	}

	function enqueue() {
		if( empty( $this->fonts ) ) return;

		$family = array();
		foreach($this->fonts as $name => $weights) {

			if( !empty($weights) ) {
				$family[] = $name . ':' . implode(',', $weights);
			}
			else {
				$family[] = $name;
			}
		}

		wp_enqueue_style(
			'siteorigin-google-web-fonts',
			add_query_arg('family', implode( '|', $family ), '//fonts.googleapis.com/css')
		);
	}

}

/**
 * Enqueue the Google web fonts.
 */
function siteorigin_settings_webfonts_enqueue(){

}
add_action('wp_enqueue_scripts', 'siteorigin_settings_webfonts_enqueue');