<?php

	if (intval($_POST['id']) > 0) {

		$user = $app->apiLoad('user')->userGet(array(
			'id_user' => $_POST['id']
		));

		$media = '';
		if (!empty($user['userMedia'])) {

      $media = json_decode($user['userMedia'], true);

      $media = $app->mediaUrlData(array(
        'url'		=> $media[0]['url'],
        'mode'		=> 'square',
        'value'		=> 500,
        'cdn'		=> true,
      ));

			//$media = json_decode($user['userMedia'], true);
			$media = $media['img'];
		}

		$out = array(
			'media' => $media,
			'title' => $user['field']['userPseudo'],
			'description' => '',
			'raw' => $user
		);

		echo json_encode($out);

	}

?>