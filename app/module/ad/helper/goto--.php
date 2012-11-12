<?php

	if(intval($_GET['id_ad']) > 0){
		$ad = $app->apiLoad('ad')->adGet(array('id_ad' => $_GET['id_ad']));

		if(intval($ad['id_ad']) > 0){
			$app->dbQuery("UPDATE k_ad SET adClick = adClick +1 WHERE id_ad=".$ad['id_ad']);
			header("Location: ".$ad['adTogo']);
			exit();
		}else{
			die("AD NOT FOUND");
		}

	}else{
		die("AD NOT FOUND");
	}

?>