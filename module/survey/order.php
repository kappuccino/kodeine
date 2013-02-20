<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	# Ajouter un ITEM
	#
	if($_POST['todo'] == 'newItem' && trim($_POST['item']) != NULL){

		// trouver le dernier ORDER pour cette QUERY
		$next = $app->dbOne("SELECT MAX(surveyrQueryItemOrder) as n FROM k_surveyqueryitem WHERE id_surveyquery=".$_POST['id_surveyquery']);

		$def = array('k_surveyqueryitem' => array(
			'id_surveyquery'		=> array('value' => $_POST['id_surveyquery']),
			'surveyrQueryItemOrder'	=> array('value' => ($next['n'] + 1)),
			'surveyQueryItemName'	=> array('value' => $_POST['item']),
		));

		$app->apiLoad('survey')->surveyQueryItemSet(array(
			'def'	=> $def,
			'debug'	=> false
		));

		$out = array('success' => true, 'message' => 'creation de l\'item', 'id_surveyqueryitem' => $app->apiLoad('survey')->id_surveyqueryitem);
	}else



	# Changer l'order des ITEM
	#
	if($_GET['todo'] == 'orderItem' && $_GET['order'] != ''){

		$order = explode(',', $_GET['order']);

		foreach($order as $idx => $e){
			$app->dbQuery("UPDATE k_surveyqueryitem SET surveyrQueryItemOrder=".$idx." WHERE id_surveyqueryitem=".$e);
		}

		$out = array('success' => true, 'message' => 'mise a jour de l\'ordre des items ('.sizeof($order).')');
	}else



	# Changer l'order des GROUP
	#
	if($_GET['todo'] == 'orderGroup' && $_GET['order'] != ''){
	
		$order = explode(',', $_GET['order']);

		foreach($order as $idx => $e){
			$app->dbQuery("UPDATE k_surveygroup SET surveyGroupOrder=".$idx." WHERE id_surveygroup=".$e);
		}

		$out = array('success' => true, 'message' => 'mise a jour de l\'ordre des groupes ('.sizeof($order).')');
			
	}else



	# Classement des QUERY dans les GROUP
	#
	if($_GET['todo'] == 'orderQuery' && sizeof($_GET['order']) > 0){
		
		foreach($_GET['order'] as $g => $order){
			
			$order = explode(',', $order);

			if(sizeof($order) > 0){
				preg_match("#group([0-9]*)#", $g, $m);
				$id_surveygroup = $m[1];

				foreach($order as $i => $idq){
					$sql[] = "UPDATE k_surveyquery SET id_surveygroup=".$id_surveygroup.", surveyQueryOrder=".$i." WHERE id_surveyquery=".$idq;
				}
			}
			
		}

		if(sizeof($sql) > 0){
			foreach($sql as $q){
				$app->dbQuery($q);
			}
		}

		$out = array('success' => true, 'message' => 'mise a jour des ordre pour les queries ('.sizeof($sql).')');
		
	}else{
		$out = array('success' => false, 'message' => 'Unknown action');
	}



	if(isset($_GET['pre'])){
		$app->pre($out);
	}else{
		echo json_encode($out);
	}
?>