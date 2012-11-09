<?php

	$obj = $app->apiLoad('content')->contentGet(array(
		'id_content'	=> $r[1],
		'useChapter'	=> false,
		'useGroup'		=> false
	));
	
	$file = KROOT.$obj['contentItemUrl'];

	if(!file_exists($file)){
		header("HTTP/1.0 404 Not Found");
		exit();
	}

	$stat = @stat($file);
	$etag = sprintf('%x-%x-%x', $stat['ino'], $stat['size'], $stat['mtime'] * 1000000);
	
	$expires = 60*60*24*1;
	header("Pragma: public");
	header("Cache-Control: maxage=".$expires);
	header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');

	header("Content-Type:   ".$obj['contentItemType'].'/'.$obj['contentItemMime']);
	header("Content-Length: ".$obj['contentItemWeight']);
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
?>