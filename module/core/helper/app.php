<?php

	if($_SERVER['DOCUMENT_ROOT'] != ''){
		$DOCROOT = $_SERVER['DOCUMENT_ROOT'];

		// Prevents end slash (lighttpd)
		if(substr($DOCROOT, -1) == '/') $DOCROOT = substr($DOCROOT, 0, -1);
	}else{
		$DOCROOT = dirname(dirname(dirname(dirname(__DIR__))));
	}

	# CONSTANT
	#
	define('KROOT', 	$DOCROOT);
	define('KPROMPT', 	str_replace($DOCROOT, NULL, KROOT));
	define('APP',		KROOT.'/app');
	define('MEDIA',		KROOT.'/media');
	define('USER',		KROOT.'/user');
	define('PLUGIN',	APP.'/plugin');
	define('MODULE',	APP.'/module');
	define('CONFIG',	USER.'/config');

	# DEFAULT
	#
	date_default_timezone_set('Europe/Paris');
	ini_set('display_errors',  'On');
	ini_set('html_errors', 	   'On');
	ini_set('error_reporting', E_ALL ^ E_NOTICE ^ E_DEPRECATED);

	# CUSTOM VALUES
	#
	if(file_exists(CONFIG.'/config.php')) include(CONFIG.'/config.php');
	if(!defined('THEME'))	  define('THEME', 	   USER.'/theme');
	if(!defined('TEMPLATE'))  define('TEMPLATE',   USER.'/template');
	if(!defined('DBLOG'))	  define('DBLOG',	   USER.'/log');
	if(!defined('IMGENGINE')) define('IMGENGINE', 'GD');

	define('BENCHME', isset($_GET['bench']) && !empty($config['benchmark']['allow']));
	define('DEBUGME', isset($_GET['debug']) && !empty($config['debug']['allow']));

	# LOADER
	#
	require dirname(__DIR__).'/autoloader.php';
	autoloader::register();
