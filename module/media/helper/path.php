<?php

	if(!headers_sent()) header('Content-type: text/javascript');

	$debug		= isset($_GET['debug']);
	$folder     = rawurldecode($_GET['folder']);
	$prompt		= '/media';

	$pref		= $app->configGet('media');
	$cache		= ($pref['useCache'] == '1') ? true : false;
	if($app->userCan('media.root') != '') {
	    if(file_exists(KROOT.$app->userCan('media.root'))) $prompt = $app->userCan('media.root');
	}

	$folder 	= ($_GET['folder'] == NULL) ? $prompt : $folder;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	if(substr($folder, 0, 1) == '/') $folder = substr($folder, 1);

	foreach(explode('/', $folder) as $f){
		$result[] = array(
			'url' => $f
		);
	}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	// Sortie
	$json = $app->helperJsonEncode($result);
	echo $app->helperJsonBeautifier($json);

