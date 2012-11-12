<?php
	if(!defined('COREINC')) die('@');

	header("Content-type: text/css");
	
	echo "<!-- ".str_replace(KROOT, NULL, USER).'/config/tinymce.php'." -->\n";

	if(file_exists(USER.'/config/tinymce.php')){
		include(USER.'/config/tinymce.php');
	}

?>