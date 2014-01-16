<?php
	$api	= $app->apiLoad('newsletter');
	$pref	= $app->configGet('newsletter');

	$data = $app->apiLoad('newsletter')->newsletterGet(array(
		'id_newsletter' 	=> $_REQUEST['id_newsletter']
	));
		
	if($pref['connector'] == 'cloudApp') {
		include('connector/cloudapp/data-list.php');
		die();
	}else
	if($pref['connector'] == 'mailChimp') {
		include('connector/mailchimp/push.php');
		die();
	}else{
		echo "No connector";
	}
