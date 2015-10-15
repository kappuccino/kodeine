<?php

	if (isset($_POST['id_newsletter']) && isset($_POST['id_template'])) {

		$template = $app->apiLoad('newsletter')->newsletterTemplateGet(array(
			'id_newslettertemplate' => $_POST['id_template']
		));
		#$app->pre($template);
		$newsletter = $app->dbQuery('UPDATE `k_newsletter` SET `newsletterHtml` = "'. addslashes($template['templateData']) .'" WHERE `k_newsletter`.`id_newsletter`='. $_POST['id_newsletter'] );

		$newsletterData = $app->apiLoad('newsletter')->newsletterGet(array(
			'id_newsletter' => $_POST['id_newsletter']
		));

		echo json_encode(array("ok" => true, "newsletter" => $newsletterData));
		exit();
	}
	echo json_encode(array("ok" => false));
	exit();

?>