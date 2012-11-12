<?php
	require(dirname(dirname(__FILE__)).'/api/core.admin.php');
	$app = new coreAdmin();

	if(!$app->userIsAdmin) header("Location: ./");

	$data = $app->apiLoad('newsletter')->newsletterGet(array(
		'id_newsletter' 	=> $_REQUEST['id_newsletter']
	));
	
	$body = $app->apiLoad('newsletter')->newsletterPrepareBody($data['id_newsletter']);

	echo $body;	

?>