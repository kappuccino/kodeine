<?php	
		$id_newsletter = base64_decode($_GET['preview-newsletter']);
		if(intval($id_newsletter) == 0) exit();

		$data = $app->apiLoad('newsletter')->newsletterGet(array(
			'id_newsletter' 	=> $id_newsletter
		));

		die($data['newsletterHtml']);
?>