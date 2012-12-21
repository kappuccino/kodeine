<?php    
    if(!$app->userIsAdmin) header("Location: ./");
    
	$data	= $app->apiLoad('newsletter')->newsletterGet(array('id_newsletter' => $_REQUEST['id_newsletter']));
	
    $html = '';
    $html = str_replace("&nbsp;", " ", $data['newsletterHtmlDesigner']);
	die($html);
?>