<?php

if (intval($_POST['id']) > 0) {

	$marque = $app->apiLoad('socialEvent')->socialEventGet(array(
		'id_socialevent' => $_POST['id']
	));

	#$app->pre($marque);

	$media = '';
	if (!empty($marque['socialEventMedia'])) {
		$media = json_decode($marque['socialEventMedia'], true);
		$media = $media[0]['url'];
	}

	$out = array(
		'media' => $media,
		'title' => $marque['socialEventName'],
		'description' => utf8_decode($marque['socialEventDescription']),
		'raw' => $marque
	);

	echo json_encode($out);

}

?>