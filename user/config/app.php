<?php

	# General PHP config
	#
	ini_set('display_errors',	'On');
	ini_set('html_errors', 		'On');
	ini_set('error_reporting',	E_ALL ^ E_NOTICE ^ E_DEPRECATED);


	# External auth
	#
	#define('AUTHTOKEN',		'123456');
	#define('AUTHKEY', 			'hjfzeuhgaerugierugfeirzhgiaermpygearu');


	# Memcache config
	#
	#define('MEMCACHE_PREFIX',	'KODEINE:');
	#define('MEMCACHE_SERVER',	'mem1.kappuccino.net');
	#define('MEMCACHE_LOG',		false);


	# Dump
	#
	define('DUMPDIR',			USER.'/dump');
	define('DBLOG',				USER.'/log');
	
	define('ISUTF8', 			false);


?>