<?php
	$template_info = @file_get_contents($_REQUEST['url'].'/info.xml');
	preg_match("#<file>(.*)</file>?#", $template_info, $file);
	$template_source = @file_get_contents($_REQUEST['url'].'/'.$file[1]);
    $template_source = str_replace("&nbsp;", " ", $template_source);
	die($template_source);
?>