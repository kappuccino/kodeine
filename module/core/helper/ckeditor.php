<?php

	if(!defined('COREINC')) die('@');

	header("Content-type: text/css");

	#echo "<!-- ".str_replace(KROOT, NULL, USER).'/config/ckeditor.php'." -->\n";

	if(file_exists(USER.'/config/ckeditor.php')){
		include(USER.'/config/ckeditor.php');
	}

?>