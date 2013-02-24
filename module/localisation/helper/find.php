<?php

	$folder = KROOT.'/user/theme';
	$files	= $app->fsFile($folder, '*.php');
	$label	= $_GET['label'];

	if(sizeof($files) > 0){
		foreach($files as $e){
			$contents = file_get_contents($e);
			
			if(preg_match("#".$label."#", $contents)){
				$in[] = str_replace(KROOT, '', $e);
			}
			unset($contents);
		}
	}

	echo is_array($in) ? json_encode($in) : json_encode(array());

?>