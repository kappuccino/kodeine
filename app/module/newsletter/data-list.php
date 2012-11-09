<?php
	$api	= $app->apiLoad('newsletter');
	$pref	= $app->configGet('newsletter');
	
	$data = $app->apiLoad('newsletter')->newsletterGet(array(
		'id_newsletter' 	=> $_REQUEST['id_newsletter']
	));
		
	if($data['newsletterConnector'] == 'cloudapp') {
		include('connector/cloudapp/data-list.php');
		die();
	}
	if($data['newsletterConnector'] == 'mailchimp') {
		include('connector/mailchimp/data-list.php');
		die();
	}
	
	if($pref['connector'] == 'cloudApp') {
		include('connector/cloudapp/data-list.php');
		die();
	}
	if($pref['connector'] == 'mailChimp') {
		include('connector/mailchimp/data-list.php');
		die();
	}
?>