<?php

	# Prevents end slash in document root path (lighttpd)
	$DOCROOT = $_SERVER['DOCUMENT_ROOT'];
	if(substr($DOCROOT, -1) == '/') $DOCROOT = substr($DOCROOT, 0, -1);

	define('KROOT', 	$DOCROOT);
	define('KPROMPT', 	str_replace($DOCROOT, NULL, KROOT));

	# MAIN
	#
	define('APP',		KROOT.'/app');
	define('MEDIA',		KROOT.'/media');
	define('USER',		KROOT.'/user');
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

	# AUTOLOAD
	#
	function kodeineAutoload($api){

		$first = substr(strtolower($api), 0, 4);

		if(strpos($api, ".php") !== false){
			$direct	= true;
			$class	= $api;
		}else
		if($first == 'core'){
			$class	= APP.'/module/core/core.'.substr(strtolower($api), 4).'.php';
			$alter	= USER.'/api/core.'.substr(strtolower($api), 4).'.php';
		}else
		if($first == 'data'){
			$parts	= array_map('strtolower', explode(' ', preg_replace('/(?!^)[[:upper:]]/',' \0', $api)));
			$file   = lcfirst(substr(implode('', array_map('ucfirst', $parts)), 4));

			$class	= APP.'/module/core/data.'.$file.'.php';
			$alter	= USER.'/api/data.'.$file.'.php';
		}else{
			$parts	= array_map('strtolower', explode(' ', preg_replace('/(?!^)[[:upper:]]/',' \0', $api)));
			$mod   	= $parts[0];

			if(count($parts) > 1){
				unset($parts[0]);
				$file   = $mod.implode('', array_map('ucfirst', $parts));

				$class	= APP.'/module/'.$mod.'/api.'.$file.'.php';
				$alter	= USER.'/module/'.$mod.'/api.'.$file.'.php';
				$custom	= USER.'/api/api.'.$file.'.php';
			}else{
				$class	= APP.'/module/'.$mod.'/api.'.$api.'.php';
				$alter	= USER.'/module/'.$mod.'/api.'.$api.'.php';
				$custom	= USER.'/api/api.'.$api.'.php';
			}
		}
	
		$alter	= (isset($custom) && file_exists($custom)) ? $custom : $alter;
		$class	= (isset($alter)  && file_exists($alter))  ? $alter	 : $class;

	#	echo $class."\n";
		if(file_exists($class)){
			try{
				require_once($class);
			}
			catch(Exception $e){
				throw new Exception("Class file not found : ".$api);
			}
		}
	}

	spl_autoload_register('kodeineAutoload');

?>