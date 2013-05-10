<?php

	$data = array();

	// Albums
	if($_GET['id_album'] > 0){

		$album = $app->apiLoad('content')->contentGet(array(
			'language'	=> 'fr',
			'raw'		=> true,
			'id_type'	=> $_GET['id_type'],
			'id_content'=> $_GET['id_album'],
			'is_album'	=> true,
			'debug'     => false
		));

		$id_album   = $album['id_content'];
		$working    = true;
		$count      = 0;

		while($working){

			$tmp = $app->apiLoad('content')->contentGet(array(
				'language'	=> 'fr',
				'raw'		=> true,
				'id_type'	=> intval($_GET['id_type']),
				'id_content'=> intval($id_album),
				'is_album'	=> true,
				'debug'     => false
			));

			$data[] = array(
				'is_album'    => true,
				'id_content'  => intval($tmp['id_content']),
				'contentName' => $tmp['contentName']
			);

			$id_album = $tmp['id_album'];
			$count++;
			if($tmp['id_album'] == 0) $working = false;
			if($count > 5)            $working = false;
		}

	}

	$data[] = array(
		'is_album'      => true,
		'id_content'    => 0,
		'contentName'   => _('Root')
	);

	$data = array_reverse($data);

	// Sortie
	$json = $app->helperJsonEncode($data);
	echo $app->helperJsonBeautifier($json);

