<?php

	$files = @$app->fsFile(MEDIA.'/ui', '*.css');
	if(is_array($files)){
		sort($files);
	
		foreach($files as $e){
			if(preg_match("#css#", $e) && basename($e) != 'ie.css'){
				echo file_get_contents($e)."\n\n\n";
			}
		}
	}
	
?>