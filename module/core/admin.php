<?php
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
// KODEINE ADMIN PROXY
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -

	define('COREINC',	    MODULE.'/core/includes');
	define('COREUI',	    KPROMPT.'/admin/core/ui');
	define('COREVENDOR',    KPROMPT.'/admin/core/vendor');

	function isMe($p){
		$url = parse_url($_SERVER['REQUEST_URI']);
		if(preg_match("#".$p."#", $url['path'])) return 'me';
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -

	$app	= new coreAdmin();

	if(file_exists(CONFIG.'/app.php')) include(CONFIG.'/app.php');

	$url	= parse_url($url);
	$part	= explode('/', substr($url['path'], 7), 2);
	$module	= $part[0];

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -

	if($module == '' && $url['path'] == '/admin/'){
		$app->go('core/login');
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -

	# /admin/module/file
	#
	if(strpos($part[1], '/') === false){
		$folder	= '/module/'.$module;
		$file_  = ($part[1] == '') ? 'index' : trim($part[1]);
	}else

	# /admin/module/folder/
	#
	if(substr($part[1], -1) == '/'){
		$folder	= '/module/'.$module.'/'.substr($part[1], 0, -1);
		$file_  = 'index';
	}

	# /admin/module/folder/file
	#
	else{
		$folder	= '/module/'.$module.'/'.dirname($part[1]);
		$file_  = ($part[1] == '') ? 'index' : basename(trim($part[1]));
	}

	$file = $folder.'/'.$file_;
	$ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
	if($ext == ''){
		$ext   = 'php';
		$file .= '.'.$ext;
	}

#	echo 'MODULE='.$module.'<br />';
#	echo 'FOLDER='.$folder.'<br />';
#	echo 'FILE_ ='.$file_.'<br />';
#	echo 'FILE  ='.$file.'<br />';
#	die();

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -

	// Verifier s'il existe le module dans le dossier USER/module
	if(file_exists(USER.$file)){
		$file = USER.$file;
	}else
	// Verifier s'il existe le module dans le dossier APP/module
	if(file_exists(APP.$file)){
		$file = APP.$file;
	}
	// Si j'ai ni l'un ni l'autre...
	else{
		die('module not found');
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -

	if(file_exists($file) && $ext == 'php'){
		if(!$app->userIsAdmin && !in_array($url['path'], array('/admin/core/login', '/admin/core/helper/lost'))){
			$app->go(KPROMPT.'/admin/core/login');
		}else{
			header("Content-Type: text/html; charset=UTF-8");
			include($file);
		}
	}else
	if(file_exists($file)){

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

	}else{
		die('module file not found: '.$file);
	}













?>