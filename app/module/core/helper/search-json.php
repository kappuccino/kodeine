<?php

	if(!defined('COREINC')) die('@');

	if($_GET['id_field'] == 'userMail'){
		$field['fieldType'] = 'texte';
		$field['fieldName']	= 'userMail';
	}else{
		$field = $app->apiLoad('field')->fieldGet(array(
			'id_field' => $_GET['id_field']
		));
	}
	
	$out['field'] = $field;
	
	if($field['fieldType'] == 'texte'){
		$out['mode']	= array('CT', 'BW', 'ED');
	}else
	if($field['fieldType'] == 'integer'){
		$out['mode']	= array('MT', 'LT', 'ME', 'LE');
	}else
	if($field['fieldType'] == 'multichoice'){
		$out['choice']	= $app->apiLoad('field')->fieldChoiceGet(array('id_field' => $field['id_field']));
		$out['mode']	= false;
	}else
	if($field['fieldType'] == 'onechoice'){
		$out['choice'] 	= $app->apiLoad('field')->fieldChoiceGet(array('id_field' => $field['id_field']));
		$out['mode'] 	= array('EG', 'NE');
	}else{
		$out['mode'] 	= false;
	}
		
	echo isset($_GET['debug']) ? $app->pre($out) : json_encode($out);
?>