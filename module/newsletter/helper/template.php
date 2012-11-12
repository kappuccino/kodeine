<?php
	$template = $app->apiLoad('newsletter')->newsletterTemplateGet(array(
		'id_newslettertemplate' => $_GET['id_newslettertemplate']
	));

	if(isset($_GET['pre'])){
		$app->pre($template);
	}else{
		header('Content-type: application/json');
		echo json_encode($template);
	}

?>