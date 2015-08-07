#!/usr/bin/php -q
<?php

include dirname( __FILE__ ) . '/inc/recursive_copy.php';
include dirname( __FILE__ ) . '/inc/cssmin.php';

$conf = include dirname( __FILE__ ) . '/../../inc/settings.conf.php';
if( empty($conf) ) {
	echo 'Empty config file';
	exit();
}

// Create a temporary directory
$temp_dir = tempdir();

// Copy everything to the temporary directory
recurse_copy( dirname(__FILE__) . '/../../sass/', $temp_dir . '/sass/' );

$files = rsearch( $temp_dir . '/sass/', '/.*\.scss/' );
foreach( $files as $file ) {
	$content = file_get_contents( $file );

	foreach( $conf['variables'] as $sass_name => $so_name ) {
		$content = preg_replace(
			'/\$' . preg_quote($sass_name) . ': [^;]*;/',
			'$' . $sass_name . ': "${' . $so_name . '}";',
			$content
		);
	}

	file_put_contents( $file, $content );
}

$output = array();
foreach( $conf['stylesheets'] as $s ) {
	$output[$s] = array();
	// Compile the SASS
	exec('sass --style expanded ' . $temp_dir . '/sass/' . $s . '.scss ' . $temp_dir . '/sass/' . $s . '.css' );

	// Remove any lines that aren't important
	$contents = file( $temp_dir . '/sass/' . $s . '.css' );

	foreach( $contents as $i => $line ) {
		if( preg_match('/ [A-Za-z0-9\-_]+ *\: *[^;]+;/', $line) && !preg_match('/"\$\{[A-Za-z0-9\-_]+\}"/', $line) ) {
			continue;
		}
		$output[$s][] = $line;
	}
}

foreach( $conf['stylesheets'] as $s ) {
	$css = CssMin::minify( implode( $output[$s], "" ), array
	(
		"ImportImports"                 => false,
		"RemoveComments"                => true,
		"RemoveEmptyRulesets"           => true,
		"RemoveEmptyAtBlocks"           => true,
		"ConvertLevel3AtKeyframes"      => false,
		"ConvertLevel3Properties"       => false,
		"Variables"                     => true,
		"RemoveLastDelarationSemiColon" => false
	) );
	$css = preg_replace( '/"(\$\{[A-Za-z0-9\-_]+\})"/', '$1', $css );
	echo "=========\n";
	echo "$s\n";
	echo "=========\n";
	echo $css;
	echo "\n\n=========\n\n";
}


// var_dump($temp_dir);
exec('rm -rf ' . $temp_dir);