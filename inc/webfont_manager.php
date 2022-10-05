<?php

class SiteOrigin_Settings_Webfont_Manager {

	private $fonts;
	private $websafe;

	function __construct() {
		$this->fonts = array();
		$this->websafe = apply_filters( 'siteorigin_settings_websafe', include dirname( __FILE__ ) . '/../data/websafe.php' );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	static function single() {
		static $single;

		if ( empty( $single ) ) {
			$single = new SiteOrigin_Settings_Webfont_Manager();
		}
		return $single;
	}

	function add_font( $name, $weights = array(), $subset = 'latin' ) {
		// This is a websafe font? If so, don't add it.
		if ( ! empty( $this->websafe[ $name ] ) ) {
			return;
		}
		if ( empty( $this->fonts[$name] ) ) {
			$this->fonts[ $name ] = array(
				'variants' => $weights,
				'subset' => $subset,
			);
		} else {
			if ( isset( $this->fonts[ $name ]['variants'] ) ) {
				$this->fonts[ $name ]['variants'] = array_merge( $this->fonts[ $name ]['variants'], $weights );
				$this->fonts[ $name ]['variants'] = array_unique( $this->fonts[ $name ]['variants'] );
			} else {
				$this->fonts[ $name ]['variants'] = $weights;
			}
			$this->fonts[ $name ] = array_unique( $this->fonts[ $name ], SORT_REGULAR );
		}
	}

	function remove_font( $name ) {
		unset( $this->fonts[$name] );
	}

	function enqueue() {
		$default_font_settings = apply_filters( 'siteorigin_settings_font_settings', array() );
		if ( !empty($default_font_settings) ) {
			$settings = SiteOrigin_Settings::single();
			foreach( $default_font_settings as $setting => $webfont ) {
				$value = json_decode( $settings->get( $setting ), true );
				if ( empty( $value ) || empty( $value['font'] ) ) {
					// No font set, load default.
					$this->add_font( $webfont['name'], $webfont['weights'], 'all' );
				} else {
					$this->add_font( $value['font'], array( $value['variant'] ), $value['subset'] );
				}
			}
		}

		if ( empty( $this->fonts ) ) return;

		$family = array();
		$subset = array();
		foreach ( $this->fonts as $name => $font ) {
			if ( ! empty( $font['variants'] ) ) {
				$family[] = $name . ':' . implode( ',', $font['variants'] );
			} else {
				$family[] = $name;
			}
			if ( ! empty( $font['subset'] ) ) {
				$subset[ $font['subset'] ] = $font['subset'];
			}
		}

		// If all is set, load all subsets.
		if ( isset( $subset['all'] ) ) {
			$subset = array();
		}

		wp_enqueue_style(
			'siteorigin-google-web-fonts',
			add_query_arg(
				array(
					'family' => implode( '|', $family ),
					'subset' => implode( ',', $subset ),
					'display' => 'block',
				),
				esc_url( apply_filters( 'siteorigin_web_font_url', 'https://fonts.googleapis.com/css' ) )
			)
		);
	}

}
SiteOrigin_Settings_Webfont_Manager::single();
