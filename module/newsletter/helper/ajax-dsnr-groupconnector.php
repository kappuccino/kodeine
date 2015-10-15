<?php

if (intval($_POST['id']) > 0) {

	$circle = $app->apiLoad('socialCircle')->socialCircleGet(array(
		'id_socialcircle' => $_POST['id']
	));

	$media = '';
	if (!empty($circle['socialCircleMedia'])) {
		$media = json_decode($circle['socialCircleMedia'], true);
		$media = $media[0]['url'];
	}

	$out = array(
		'media' => $media,
		'title' => $circle['socialCircleName'],
		'description' => utf8_decode($circle['socialCircleDescription']),
		'raw' => $circle
	);

	echo json_encode($out);

}

?>