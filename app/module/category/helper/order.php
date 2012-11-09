<?php
	$master = substr($_GET['id_category'], 4);
	$ids	= explode('-', $_GET['ordered']);
	
	foreach($ids as $n => $id){
		$app->dbQuery("UPDATE k_category SET pos_category=".($n)." WHERE id_category=".$id);
	}
	
	echo json_encode(array());
	
?>