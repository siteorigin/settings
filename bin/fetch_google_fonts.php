#!/usr/bin/php -q
<?php

function get_data($url) {
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

// Add your Google webfont key here <https://developers.google.com/fonts/docs/developer_api?hl=en#APIKey>
$key = file_get_contents( __DIR__ . '/google-key.php' );
$response = get_data( 'https://www.googleapis.com/webfonts/v1/webfonts?key=' . urlencode( $key ) );

$fonts = json_decode( $response, true )['items'];
$return = array();

foreach( $fonts as $font ) {
	$family = $font['family'];
	foreach( array_keys($font) as $key ) {
		if( !in_array( $key, array('variants', 'subsets', 'category') ) )  {
			unset( $font[$key] );
		}
	}

	$return[$family] = $font;
}

// Sort the keys, just in case
ksort( $return );

echo 'Writing fonts to: ' . realpath( dirname(__FILE__). '/../data/fonts.php' ) . "\n";
$contents = "<?php\n\n";
$contents .= 'return ' . str_replace('  ', "\t", var_export( $return, true ) ) . ';';
file_put_contents( realpath( dirname(__FILE__). '/../data/fonts.php' ), $contents );