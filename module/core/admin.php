<?php

	# CORE (ADMIN)
	#
	define('COREINC',	MODULE.'/core/includes');
	define('COREUI',	KPROMPT.'/admin/core/ui');

	
#	$less = @stat(MODULE.'/core/ui/css/_style.less');
#	$css  = @stat(MODULE.'/core/ui/css/_style.css');

#	define('COREONAIR',	($css['mtime'] > $less['mtime']));

	function isMe($p){
		$url = parse_url($_SERVER['REQUEST_URI']);
		if(preg_match("#".$p."#", $url['path'])) return 'me';
	}

# -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

	$app	= new coreAdmin();

	$url	= parse_url($url);
	$part	= explode('/', substr($url['path'], 7), 2);

	$module	= $part[0];
	$file_	= ($part[1] == '') ? 'index' : trim($part[1]);

	$folder	= USER.'/module/'.$module;
	$file	= $folder.'/'.$file_;

	// Verifier s'il existe le module dans le dossier USER/module
	if(!file_exists($folder)){
		$folder = APP.'/module/'.$module;
		$file	= $folder.'/'.$file_;
	}

	if($module == '' && $url['path'] == '/admin/'){
		$app->go('/admin/core/login');
	}

	if(!file_exists($folder) && $module != NULL) die('module not found');

	$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
	if($ext == 'php') $file = substr($file, 0, -4);

	#
	# != '.php ' ???
	#
	/*if(in_array($ext, array('less', 'css', 'js', 'png', 'gif', 'jpg', 'jpeg', 'eot', 'woff', 'tff', 'svg', 'swf'))){
		if(file_exists($file)){

			$mime = $app->mediaMimeType($file);
			header("Content-Type: ".$mimie);
			echo file_get_contents($file);
			exit();

		#	header("Location: ".str_replace(KROOT, NULL, $file));
		#	$app->go(str_replace(KROOT, NULL, $file));
		#	exit();
		}else{
			header("HTTP/1.0 404 Not Found");
			echo 'module file not found: '.$file;
			exit();
		}*/

	if(file_exists($file.'.php')){
		if(!$app->userIsAdmin && !in_array($url['path'], array('/admin/core/login', '/admin/core/helper/lost'))){
			$app->go('/admin/core/login');
		}else{
			header("Content-Type: text/html; charset=UTF-8");
			include($file.'.php');
		}
	}else
	if(file_exists($file)){
	#	header("HTTP/1.1 301 Moved Permanently");
	#	header("Location: ".str_replace(KROOT, NULL, $file));
	#	exit();

		$stat = stat($file);
		$etag = sprintf('%x-%x-%x', $stat['ino'], $stat['size'], $stat['mtime'] * 1000000);
		
		$expires = 60*60*24*1;
		header("Pragma: public");
		header("Cache-Control: maxage=".$expires);
		header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');

		$mime = $app->mediaMimeType($file);
		

		header("Content-Type:   ".$mime);
		header("Content-Length: ".filesize($file));
		header('Etag: ' . $etag);
	
		if(isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
			header('HTTP/1.0 304 Not Modified');
			exit();
		}else
		if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $stat['mtime']) {
			header("Last-Modified: ".gmdate(DATE_RFC1123, filemtime($file)));
			header('HTTP/1.0 304 Not Modified');
		}

		readfile($file);
		exit();


		#header("Content-Type: ".$mimie);
		#echo file_get_contents($file);
		#exit();

	#	header("Location: ".str_replace(KROOT, NULL, $file));
	#	$app->go(str_replace(KROOT, NULL, $file));
	#	exit();
	
	}else{
		die('module file not found: '.$file);
	}













?>