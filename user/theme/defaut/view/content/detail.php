<?php

	if($content['id_type'] != NULL){
		
		if($content['contentTemplate'] != NULL){
			$template = $content['contentTemplate'];
		}else
		if($type['typeTemplate'] != NULL){
			$template = $type['typeTemplate'];
		}

		$template = TEMPLATE.'/'.$template.'/detail.php';

		if(file_exists($template)){
			include($template);
		}else{
			$SHOW_404 = true;
		}

	}else{
		$SHOW_404 = true;
	}

?>