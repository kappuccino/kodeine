<?php

	header('Content-type: application/json');

	if($_REQUEST['name'] != '' && $_REQUEST['id_type'] > 0){

		$def['k_content'] = array(
			'contentSee'			=> array('value' 	=> 1),
			'contentDateCreation'	=> array('function' => 'NOW()'),
			'contentDateUpdate'		=> array('function' => 'NOW()'),
		);

		$dat['k_contentdata'] = array(
			'contentUrl'			=> array('value' => $app->helperUrlEncode($_GET['name'], 'fr')),
			'contentName' 			=> array('value' => $_GET['name'])
		);

		$opt = array(
			'id_type'		=> $_GET['id_type'],
			'language'		=> 'fr',
			'id_content'	=> NULL,
			'def'			=> $def,
			'data'			=> $dat,
			'debug'			=> false
		);

		$result = $app->apiLoad('content')->contentSet($opt);

		$m = $app->apiLoad('content')->contentGet(array(
			'id_content'	=> $app->apiLoad('content')->id_content,
			'raw'			=> true
		));

	}


	if(isset($_GET['pre'])){
		$app->pre($m);
	}else{
		echo json_encode($m);
	}
?>