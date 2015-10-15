<?php

if (intval($_POST['id']) > 0) {

	$event = $app->apiLoad('socialEvent')->socialEventGet(array(
		'id_socialevent' => $_POST['id']
	));

	#$app->pre($event);

	$media = '';
	if (!empty($event['socialEventMedia'])) {
		$media = json_decode($event['socialEventMedia'], true);
		$media = $media[0]['url'];
	}

	$out = array(
		'media' => $media,
		'title' => $event['socialEventName'],
		'description' => utf8_decode($event['socialEventDescription']),
		'raw' => $event
	);

	echo json_encode($out);

}

?>