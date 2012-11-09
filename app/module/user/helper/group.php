<?php
	header('Content-type: application/json');
	if($_GET['todo'] == 'order'){
		
		$order  = explode(',', $_GET['order']);
		$parent = $app->apiLoad('user')->userGroupGet(array(
			'id_group' => $order[0]
		));
	
		foreach($order as $idx => $e){
			$app->dbQuery("UPDATE k_group SET pos_group=".$idx." WHERE id_group=".$e);
		}
		
		$out = array('success' => true, 'message' => 'update group order');

	}else{
		$out = array('success' => false, 'message' => 'unknown action');
	}
	
	if($_GET['pre']){
		$app->pre($out);
	}else{
		echo json_encode($out);
	}

?>