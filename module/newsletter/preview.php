<?php

	if(!$app->userIsAdmin) header("Location: ./");

	/*$data = $app->apiLoad('newsletter')->newsletterTemplateGet(array(
		'id_newslettertemplate' 	=> $_REQUEST['id_newslettertemplate']
	));*/

	#$body = $app->apiLoad('newsletter')->newsletterPrepareBody($data['id_newsletter']);

	#echo $data['templateData'];

	$data = $app->apiLoad('newsletter')->newsletterGet(array(
		"id_newsletter" => $_REQUEST['id_newsletter']
	));

	echo $data['newsletterHtml'];

?>