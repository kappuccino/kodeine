<?php

	$tableBloc = '@nlblocs';
	$tableWrap = '@nlwrap';

	$blocsCache = array();
	$htmlToWrite = array();
	foreach($_POST['blocs'] as $p) {
		if (empty($blocsCache[$p])) {
			$blocsCache[$p] = $app->dbOne('SELECT * FROM `'. $tableBloc .'` WHERE id_bloc='.intval($p));
		}
		$htmlToWrite[] = $blocsCache[$p];
	}

	$contents = array_map(function($bloc) {
		return $bloc['contents'];
	}, $htmlToWrite);

	## Wraps
	$wrap = $app->dbOne('SELECT * FROM `'. $tableWrap .'` WHERE id_wrap=1');

	## Save NL template
	$app->apiLoad('newsletter')->newsletterTemplateSet($_POST['id_template'], array(
		'k_newslettertemplate' => array(
			'templateData' => array('value' => $wrap['top'] .implode('', $contents). $wrap['bottom']),
			'templateName' => array('value' => $_POST['templatename'])
		)
	));

	echo json_encode( array('ok' => true ));
	exit(0);


