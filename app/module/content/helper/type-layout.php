<?php

	header('Content-type: text/html; charset=UTF-8');
	
	if($_POST['typeFormLayout'] != NULL && $_POST['id_type'] != NULL){
		$v = ($_POST['typeFormLayout']);

		$app->dbQuery("SET NAMES 'utf8'");
		$app->dbQuery("UPDATE k_type SET typeFormLayout='".$v."' WHERE id_type='".$_POST['id_type']."'");

		echo json_encode(array('result' => 'ok', 'v' => $v));
	}else{
		echo json_encode(array('result' => 'no'));
	}

?>