<?php
	if(!$app->userIsAdmin) header("Location: ./");

	$data = $app->apiLoad('newsletter')->newsletterGet(array(
		'id_newsletter' 	=> $_REQUEST['id_newsletter']
	));
	
	$body = $app->apiLoad('newsletter')->newsletterPrepareBody($data['id_newsletter']);

	echo $body;	

?>