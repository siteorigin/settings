#!/usr/bin/php -q
<?php

// Add your Google webfont key here <https://developers.google.com/fonts/docs/developer_api?hl=en#APIKey>
$key = trim( file_get_contents( __DIR__ . '/google-key.php' ) );
$response = file_get_contents( 'https://www.googleapis.com/webfonts/v1/webfonts?key=' . urlencode( $key ) );

$fonts = json_decode( $response, true )['items'];
$return = array();

foreach( $fonts as $font ) {
	$family = $font['family'];
	foreach( array_keys($font) as $key ) {
		if( ! in_array( $key, array('variants', 'subsets', 'category') ) )  {
			unset( $font[$key] );
		}
	}
	sort( $font['subsets'] );

	$return[$family] = $font;
}

// Sort the keys, just in case
ksort( $return );

echo 'Writing fonts to: ' . realpath( dirname(__FILE__). '/../data/fonts.php' ) . "\n";
$contents = "<?php\n\n";
$contents .= 'return ' . str_replace('  ', "\t", var_export( $return, true ) ) . ';';
file_put_contents( realpath( dirname(__FILE__). '/../data/fonts.php' ), $contents );
