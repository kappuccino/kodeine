<?php

	$data = array();

	if(!isset($_GET['root'])){
		$albums = $app->apiLoad('content')->contentGet(array(
			'language'	=> 'fr',
			'raw'		=> true,
			'id_type'	=> $_GET['id_type'],
			'id_album'	=> $_GET['id_album'],
			'is_album'	=> true,
			'order'		=> 'contentAlbumPos',
			'direction'	=> 'ASC',
			'noLimit'	=> true,
		#	'debug'		=> isset($_GET['pre'])
		));


		foreach($albums as $idx => $e){

			$tmp = array(
				'id_content'  => intval($e['id_content']),
				'is_album'    => true,
				'contentName' => $e['contentName']
			);

			$data[] = $tmp;
		}

	}else{

		$data[] = array(
			'id_content'  => 0,
			'is_album'    => true,
			'contentName' => _('Root')
		);
	}

	// Sortie
	$json = $app->helperJsonEncode($data);
	echo $app->helperJsonBeautifier($json);