#!/usr/bin/php -q
<?php

include dirname( __FILE__ ) . '/inc/recursive_copy.php';
include dirname( __FILE__ ) . '/inc/cssmin.php';

$conf = include dirname( __FILE__ ) . '/../../settings.conf.php';
if( empty($conf) ) {
	echo 'Empty config file';
	exit();
}

if( !empty($conf['free']) ) {
	if( isset($argv[1]) && $argv[1] == 'free' ) {
		// Only have the free variables
		foreach( $conf['variables'] as $sass_name => $so_name ) {
			if( !in_array($sass_name, $conf['free']) ) {
				unset( $conf['variables'][$sass_name] );
			}
		}
	}
	else {
		// Exclude the free variables
		foreach( $conf['free'] as $v ) {
			unset($conf['variables'][$v]);
		}
	}
}

// Create a temporary directory
$temp_dir = tempdir();

// Copy everything to the temporary directory
recurse_copy( dirname(__FILE__) . '/../../../sass/', $temp_dir . '/sass/' );

// Replace variable values
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

$all_css = '';
foreach( $conf['stylesheets'] as $s ) {
	$css = CssMin::minify( implode( $output[$s], "\n\n" ), array
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

	file_put_contents( $temp_dir . '/sass/' . $s . '.css', $css );
	ob_start();
	passthru('cssbeautify-cli -f ' . $temp_dir . '/sass/' . $s . '.css' );
	$css = ob_get_clean();

	$css = preg_replace( '/"(\$\{[A-Za-z0-9\-_]+\})"/', '$1', $css );
	$css = preg_replace('/font-family: (\$\{[a-z0-9_]+\});/', '.font( $1 );', $css);

	if( trim($css) != '' ) {
		$all_css .= "/* $s */\n\n";
		$all_css .= $css."\n\n";
	}
}

$lines = explode("\n", $all_css);
if( !empty($lines) ) {

	$code_lines = array();
	foreach( $lines as $line ) {
		$line = trim($line);
		if( empty($line) ) continue;
		$code_lines[] = "'" . addcslashes( $line, "'" ) . "'";
	}

	if( !empty($code_lines) ) {
		echo "==============\n\n";
		echo '// Custom CSS Code' . "\n";
		echo '$css .= ';
		echo implode( ' . "\n" .' . "\n", $code_lines );
		echo ';';
		echo "\n\n";
		echo "==============\n\n";
	}
}

exec('rm -rf ' . $temp_dir);