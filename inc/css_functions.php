<?php

class SiteOrigin_Settings_CSS_Functions {
	public function __construct() {
	}

	public static function single() {
		static $single;

		if ( empty( $single ) ) {
			$single = new self();
		}

		return $single;
	}

	public function font( $match ) {
		if ( empty( $match[2] ) ) {
			return '';
		}

		$return = '';
		$args = json_decode( trim( $match[2] ), true );

		if ( empty( $args['font'] ) ) {
			return '';
		}

		$return .= 'font-family: "' . esc_attr( $args['font'] ) . '"' . ( ! empty( $args['category'] ) ? ', ' . $args['category'] : '' ) . '; ';

		if ( ! empty( $args['variant'] ) && strpos( $args['variant'], 'italic' ) !== false ) {
			$weight = str_replace( 'italic', '', $args['variant'] );
			$return .= 'font-style: italic; ';
		} else {
			$weight = $args['variant'];
		}

		if ( empty( $weight ) ) {
			$weight = 'normal';
		}

		if ( $weight == 'regular' ) {
			$weight = 'normal';
		}
		$return .= 'font-weight: ' . esc_attr( $weight ) . '; ';

		return $return;
	}

	public function rgba( $match ) {
		if ( empty( $match[2] ) ) {
			return '';
		}
		$args = explode( ',', $match[2] );

		$rgb = trim( $args[0] );
		// If no color is set, $rgb will be empty
		if ( empty( $rgb ) ) {
			return 'transparent';
		}

		$rgb = SiteOrigin_Settings_Color::hex2rgb( trim( $args[0] ) );

		return 'rgba(' . implode( ',', array_merge( $rgb, array( floatval( $args[1] ) ) ) ) . ');';
	}

	public function lighten( $match ) {
		$args = explode( ',', $match[2] );
		$rgb = SiteOrigin_Settings_Color::hex2rgb( trim( $args[0] ) );
		$hsv = SiteOrigin_Settings_Color::rgb2hsv( $rgb );

		if ( strpos( $args[1], '%' ) !== false ) {
			$percent = intval( trim( $args[1] ) ) / 100;
		} else {
			$percent = floatval( trim( $args[1] ) );
		}

		$hsv[2] += $percent;

		return SiteOrigin_Settings_Color::rgb2hex( SiteOrigin_Settings_Color::hsv2rgb( $hsv ) );
	}

	public function darken( $match ) {
		$args = explode( ',', $match[2] );
		$rgb = SiteOrigin_Settings_Color::hex2rgb( trim( $args[0] ) );
		$hsv = SiteOrigin_Settings_Color::rgb2hsv( $rgb );

		if ( strpos( $args[1], '%' ) !== false ) {
			$percent = intval( trim( $args[1] ) ) / 100;
		} else {
			$percent = floatval( trim( $args[1] ) );
		}

		$hsv[2] -= $percent;

		return SiteOrigin_Settings_Color::rgb2hex( SiteOrigin_Settings_Color::hsv2rgb( $hsv ) );
	}
}
