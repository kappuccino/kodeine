<?php

	if(intval($_GET['id_ad']) > 0){
		$ad = $app->apiLoad('content')->contentGet(array('id_content' => $_GET['id_ad'], 'useGroup' => false, 'debug' => false));
		if(intval($ad['id_content']) > 0){
            $app->apiLoad('ad')->adStat(array(
                'id_content'    => $ad['id_content'],
                'language'      => $ad['language'],
                'field'         => 'click',
                'debug'         => false
            ));
			header("Location: ".$ad['contentAdUrl']);
			exit();
		}else{
			die("AD NOT FOUND");
		}

	}else{
		die("AD NOT FOUND");
	}

?>