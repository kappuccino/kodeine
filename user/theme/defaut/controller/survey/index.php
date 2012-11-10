<?php

	$api = $this->apiLoad('survey');

	$survey = $api->surveyGet(array(
		'id_survey' => $_REQUEST['id_survey']
	));
	
	# Soit j'ai cette infos via l'URL (mail) ou depuis le rechargement du FORM
	#
	if($_REQUEST['id_surveyslot'] > 0){
		$id_surveyslot = $_REQUEST['id_surveyslot'];

		// Ne pas faire confiance a cet ID, il pourait ne pas exister
		$tmp = $api->surveySlotGet(array(
			'id_surveyslot' => $id_surveyslot
		));

		if($tmp['id_surveyslot'] == '') die('ID SLOT introuvable');
	}else

	# L'URL demande la creation d'un SLOT
	#
	if(isset($_REQUEST['autoSlot'])){
		$id_surveyslot = $api->surveySlotInit(array(	
			'id_survey'			=> $survey['id_survey'],
			'id_content'		=> $_REQUEST['id_content'],
			'surveySlotEmail'	=> $_REQUEST['userMail']
		));
	}

	# Ce cas ne devrait pas existe
	#
	else{
		die('cas impossible ! - impossible de trouver le survey... dommage !!!');
	}

	$slot = $api->surveySlotGet(array(
		'id_surveyslot' => $id_surveyslot
	));

	if(intval($_POST['id_survey']) > 0){
		$api->surveyFormSubmit(array(
			'id_survey' 	=> $_POST['id_survey'],
			'id_surveyslot'	=> $id_surveyslot,
			'query'			=> $_POST['query']
		));
	}

	$next = $api->surveySlotNext($id_surveyslot);

	if(is_bool($next)){
		$api->surveySlotFinished($id_surveyslot);
		header("Location: ended?id_survey=".$survey['id_survey']);
	}else{
		$group = $api->surveyGroupGet(array('id_surveygroup' => $next));
	}

?>