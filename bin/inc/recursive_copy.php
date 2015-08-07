<?php

function tempdir($dir = false, $prefix = 'php') {
	$tempfile=tempnam(sys_get_temp_dir(),'');
	if (file_exists($tempfile)) { unlink($tempfile); }
	mkdir($tempfile);
	if (is_dir($tempfile)) { return $tempfile; }
}

function recurse_copy($src,$dst) {
	$dir = opendir($src);
	@mkdir($dst);
	while(false !== ( $file = readdir($dir)) ) {
		if (( $file != '.' ) && ( $file != '..' )) {
			if ( is_dir($src . '/' . $file) ) {
				recurse_copy($src . '/' . $file,$dst . '/' . $file);
			}
			else {
				copy($src . '/' . $file,$dst . '/' . $file);
			}
		}
	}
	closedir($dir);
}

function rsearch($folder, $pattern) {
	$dir = new RecursiveDirectoryIterator($folder);
	$ite = new RecursiveIteratorIterator($dir);
	$files = new RegexIterator($ite, $pattern, RegexIterator::GET_MATCH);
	$fileList = array();
	foreach($files as $file) {
		$fileList = array_merge($fileList, $file);
	}
	return $fileList;
}