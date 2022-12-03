#!/usr/bin/php -q
<?php

include dirname( __FILE__ ) . '/inc/recursive_copy.php';

// PHP 8 Compatibility function required by cssmin.
// https://www.php.net/manual/en/function.each.php#126076
if ( ! function_exists( 'each' ) ) {
	function each( $array ) {
		$key = key( $array );
		$value = current( $array );
		$each = is_null( $key ) ? false : array(
			1        => $value,
			'value'  => $value,
			0        => $key,
			'key'    => $key,
		);
		next( $array );
		return $each;
	}
}
include dirname( __FILE__ ) . '/inc/cssmin.php';

$conf = include dirname( __FILE__ ) . '/../../settings.conf.php';
if( empty($conf) ) {
	echo 'Empty config file';
	exit();
}

if( !empty($conf['free']) && isset($argv[1]) ) {
	if( $argv[1] == 'free' ) {
		// Only have the free variables
		foreach( $conf['variables'] as $setting_name => $sass_name ) {
			if( !in_array($setting_name, $conf['free']) ) {
				unset( $conf['variables'][$setting_name] );
			}
		}
	}
	else if( $argv[1] == 'premium' ) {
		// Exclude the free variables
		foreach( $conf['free'] as $setting_name ) {
			unset($conf['variables'][$setting_name]);
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

	// Replace the variables where they're defined.
	foreach( $conf['variables'] as $setting_name => $sass_name ) {
		$content = preg_replace(
			'/\$' . preg_quote($sass_name) . ': [^;]*;/',
			'$' . $sass_name . ': "${' . $setting_name . '}";',
			$content
		);
	}


	$GLOBALS['sass_vars'] = array();
	foreach( $conf['variables'] as $setting_name => $sass_name ) {
		$GLOBALS['sass_vars'][ '$' . $sass_name ] = $setting_name;
	}
	
	foreach( $conf['variables'] as $setting_name => $sass_name ) {
		$content = preg_replace_callback( '/([a-zA-Z_\-]+) *\(([^\)]*\$' . preg_quote( $sass_name ) . '[^\)]*)\)/', function ( $match ) {
			$args = array_map( 'trim',  preg_split('/ *, */', $match[2] ) );
			$args_replaced = 0;

			foreach( $args as $i => $arg ) {
				if( $arg[0] !== '$' ) continue;
				if( isset( $GLOBALS['sass_vars'][ $arg ] ) ) {
					$args[$i] = '${' . $GLOBALS['sass_vars'][$arg] . '}';
					$args_replaced++;
				}
			}

			$fn = array_merge( array( $match[1] ), $args );
			return '"#FUNCTION#:' . urlencode( json_encode( $fn  ) ) . '#END_FUNCTION#"';
		}, $content );
	}

	file_put_contents( $file, $content );
}

$output = array();
foreach( $conf['stylesheets'] as $s ) {
	$output[$s] = array();
	// Compile the SASS
	exec('sass --style expanded ' . $temp_dir . '/sass/' . $s . '.scss ' . $temp_dir . '/sass/' . $s . '.css' );

	// Remove any lines that aren't relevant
	$contents = file( $temp_dir . '/sass/' . $s . '.css' );
	foreach( $contents as $i => $line ) {
		if(
			preg_match('/ [A-Za-z0-9\-_]+ *\: *[^;]+;/', $line) &&
			!preg_match('/"\$\{[A-Za-z0-9\-_]+\}"/', $line) &&
		    strpos( $line, '#FUNCTION#' ) === false
		) {
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

	// Now lets replace all the function calls
	$css = preg_replace_callback( '/\"#FUNCTION#:(.*)#END_FUNCTION#\"/', function( $match ){
		$args = json_decode( urldecode( $match[1] ) );

		$function = array_shift( $args );
		$return = '';
		if( $function !== 'calc' ) {
			$return .= '.';
		}
		$return .= $function . '( ';
		if( !empty( $args ) ) {
			$return .= implode( ', ', $args );
		}
		$return .= ')';
		return $return;
	}, $css );
	
	foreach( $conf['variables'] as $setting_name => $sass_name ) {
		$css = preg_replace( '/#\{\$' . preg_quote( $sass_name ) . '\}/', '${' . $setting_name . '}', $css );
	}

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
		$code_lines[] = addcslashes( $line, "'" );
	}


	if( !empty($code_lines) ) {
		echo "==============\n\n";
		echo '// Custom CSS Code' . "\n";
		echo '$css .= ' . "'";
		echo implode( "\n	", $code_lines );
		echo "'" . ';';
		echo "\n\n";
		echo "==============\n\n";
	}
}

exec('rm -rf ' . $temp_dir);
