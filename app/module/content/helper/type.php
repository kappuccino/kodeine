<?php

	$order = explode('-', $_GET['order']);

	if(sizeof($order)  > 0){
		foreach($order as $i => $e){
			$app->dbQuery("UPDATE k_type SET typePos=".$i." WHERE id_type=".$e);
		}

		echo json_encode(array('success' => true));
	}else{
		echo json_encode(array('success' => false));
	}

?>