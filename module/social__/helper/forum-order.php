<?php

	$ids = explode('-', $_GET['ordered']);
	
	foreach($ids as $n => $id){
		$app->dbQuery("UPDATE k_socialforum SET pos_forum=".$n." WHERE id_socialforum=".$id);
		$q[] = array($app->db_query, $app->db_error);
	}

	echo json_encode($q);
?>