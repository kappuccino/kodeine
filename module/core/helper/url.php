<?php

	$trace 	= false;
#	$url 	= $_SERVER['REDIRECT_URL'];
#	$url	= ($url == '') ? str_replace('?'.$_SERVER['QUERY_STRING'], NULL, $_SERVER['REQUEST_URI']) : $url;

	$url	= $_GET['rewrite'];
	$url	= (substr($url, 0, 1) == '/') ? $url : '/'.$url;
	$url	= (substr($url, -1) == '?')   ? substr($url, 0, -1) : $url; # lighttpd rule !!!! a refaire proprement
	$url_	= parse_url($_SERVER['REQUEST_URI']);

	parse_str($_SERVER['QUERY_STRING'], $get);

#	die($url);
#	if($trace) $app->pre("url", $url, "get", $get, "server", $_SERVER);


	// IMAGE ///////////////////////////////////////////////////////////////////////////////////////////////////////////
	if(substr($url, 2, 1) == ':'){
		preg_match("#^/([w|h|s|c]):([0-9,]{1,})(,([0-9]{1,}))?(.*)#", $url, $r);

		$_GET['image'] 	= true;
		$_GET['mode']  	= $r[1];
		$_GET['value']	= $r[2];
		$_GET['second']	= $r[3];
		$_GET['file']	= urldecode($r[5]);

		if($trace) $app->pre("IMG", $r, "GET", $_GET);

		include(__DIR__.'/image.php');
		exit();
	}else

	// AD //////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if(preg_match("#^/ad([0-9]{1,})#", $url, $r)){
		$_GET['id_ad'] = $r[1];
		if($trace) $app->pre("AD", $r);

		include(APP.'/module/ad/helper/goto.php');
		exit();
	}else

	// PREVIEW NEWSLETTER //////////////////////////////////////////////////////////////////////////////////////////////
	if($_GET['preview-newsletter'] != ''){
		include(APP.'/module/newsletter/helper/preview.php');
		exit();
	}else

	// ROBOTS.TXT //////////////////////////////////////////////////////////////////////////////////////////////////////
	if($url_['path'] == '/robots.txt'){
		header("Content-Type: text/plain");
		$config = $app->configGet('robots.txt');

		echo $config['contentFile'];
		exit();
	}else

	// ADMIN ///////////////////////////////////////////////////////////////////////////////////////////////////////////
	if(substr($url, 0, 7) == '/admin/'){
		include(APP.'/module/core/admin.php');
		exit();
	}else

	// vFS /////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/*if(preg_match("#v/([0-9]{1,})/(.*)#", $url, $r)){
		include(dirname(__FILE__).'/vfs.php');
		exit();
	}else*/

	// AUTH ////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/*if(preg_match("#^/externalauth#", $url, $r)){
		include(dirname(__FILE__).'/externalauth.php');
		exit();
	}else*/

	// MAIN ////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if(preg_match('#^/(([a-z]{2})(-([a-z]{2}))?/)?(.*)?$#', $url, $r)){

		#print_r($r);

		if(strlen($r[2]) > 2){
			$_GET['urlLanguage'] = $r[2];
			$_GET['urlCountry']  = $r[4];
		} else {
			$_GET['urlLanguage'] = $r[2];
			$_GET['urlCountry']  = $r[2];
		}

		$_GET['urlRequest']		= $r[5];
		
		if($trace) $app->pre("MAIN", $r);
	}

	unset($url, $url_, $r);
