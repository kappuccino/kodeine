<?php

	header('Content-type: text/html; charset=UTF-8');		
	
	if($_POST['groupFormLayout'] != NULL && $_POST['id_group'] != NULL){

		#require(dirname(dirname(dirname(dirname(__FILE__)))).'/api/core.admin.php');
		#$app = new coreAdmin();
	
		if(!$app->userIsAdmin) die(json_encode(array('result' => 'logout')));

		$v = ($_POST['groupFormLayout']);

		$app->dbQuery("SET NAMES 'utf8'");
		$app->dbQuery("UPDATE k_group SET groupFormLayout='".$v."' WHERE id_group='".$_POST['id_group']."'");

		echo json_encode(array('result' => 'ok', 'id_group' => $_POST['id_group'], 'v' => $v));
	}else{
		echo json_encode(array('result' => 'no'));
	}

?>