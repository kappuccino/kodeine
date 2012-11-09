<?php
	$template_info = @file_get_contents($_REQUEST['url'].'/info.xml');
	preg_match("#<file>(.*)</file>?#", $template_info, $file);
	$template_source = @file_get_contents($_REQUEST['url'].'/'.$file[1]);
    //echo htmlspecialchars('&eacute; - é');
    $template_source = str_replace("&nbsp;", " ", $template_source);
	die(utf8_encode($template_source));
?>