<?php

	# Web Context
	if($_SERVER['DOCUMENT_ROOT'] != ''){
		$DOCROOT = $_SERVER['DOCUMENT_ROOT'];

		// Prevents end slash in document root path (lighttpd)
		if(substr($DOCROOT, -1) == '/') $DOCROOT = substr($DOCROOT, 0, -1);
	}else
	# Term Context
	if($_SERVER['TERM'] != ''){
		$DOCROOT = dirname(dirname(dirname(dirname(__DIR__))));
	}

	define('KROOT', 	$DOCROOT);
	define('KPROMPT', 	str_replace($DOCROOT, NULL, KROOT));

	# MAIN
	#
	define('APP',		KROOT.'/app');
	define('MEDIA',		KROOT.'/media');
	define('USER',		KROOT.'/user');

	# APP
	#
	define('PLUGIN',	APP.'/plugin');
	define('MODULE',	APP.'/module');

	# USER
	#
	define('CONFIG',	USER.'/config');

	# DEFAULT
	#
	date_default_timezone_set('Europe/Paris');
	define('FLAT',		'FLAT');
	define('NOROOT',	'NOROOT');
	define('NOHIDDEN',	'NOHIDDEN');
	define('PREG',		'PREG');

	# CUSTOM VALUES
	#
	if(file_exists(CONFIG.'/app.php')) include(CONFIG.'/app.php');
	if(!defined('THEME'))		define('THEME', 		USER.'/theme');
	if(!defined('TEMPLATE'))	define('TEMPLATE',		USER.'/template');
	if(!defined('EVENT'))		define('EVENT',			USER.'/event');
	if(!defined('IMGENGINE'))	define('IMGENGINE',		'GD');
	if(!defined('DBLOG'))		define('DBLOG',			USER.'/log');
	if(!defined('DUMPDIR'))		define('DUMPDIR',		USER.'/dump');
	if(!defined('DUMPBIN'))		define('DUMPBIN',		'mysqldump');

	# BENCH
	#
	define('BENCHME', isset($_GET['bench']));

	# LOADER
	#
	require dirname(__DIR__).'/autoloader.php';
	autoloader::register();