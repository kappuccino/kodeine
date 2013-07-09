<?php

	$trace 	= false;
#   $url 	= $_SERVER['REDIRECT_URL'];
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

	// ROBOTS.TXT //////////////////////////////////////////////////////////////////////////////////////////////////////
	if($url_['path'] == '/robots.txt'){
		header("Content-Type: text/plain");
		$config = $app->configGet('robots.txt');

		echo $config['contentFile'];
		exit();
	}else

	// ADMIN ///////////////////////////////////////////////////////////////////////////////////////////////////////////
	if(substr($url, 0, 7) == '/admin/'){
		include(APP.'/module/admin/helper/proxy.php');
		exit();
	}else

	// MAIN ////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if(preg_match("#^/(([a-zA-Z]{2})/)?(.*)?$#", $url, $r)){
		$_GET['urlLanguage']	= $r[2];
		$_GET['urlRequest']		= $r[3];

		if($trace) $app->pre("MAIN", $r);
	}

	unset($url, $url_, $r);