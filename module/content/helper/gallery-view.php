<?php

	header("Content-Type: text/plain");

	$data = array();

	// ALBUM ///////////////////////////////////////////////////////////////////////////////////////////////////////////
	if($_GET['id_album'] > 0){
		$album = $app->apiLoad('content')->contentGet(array(
			'raw'        => true,
			'id_content' => $_GET['id_album'],
		));

		if($album['id_alias'] > 0){
			$original = $app->apiLoad('content')->contentGet(array(
				'raw'        => true,
				'id_content' => $album['id_alias'],
			));
		}

		$id_poster = empty($original) ? $album['id_poster']  : $original['id_poster'];
		$id_album  = empty($original) ? $album['id_content'] : $original['id_content'];

	}else{
		$id_album  = 0;
		$id_poster = NULL;
	}




	// ALBUMS //////////////////////////////////////////////////////////////////////////////////////////////////////////
	$albums = $app->apiLoad('content')->contentGet(array(
		'language'	=> 'fr',
		'raw'		=> true,
		'id_type'	=> $_GET['id_type'],
		'id_album'	=> $id_album,
		'is_album'	=> true,
		'order'		=> 'contentAlbumPos',
		'direction'	=> 'ASC',
		'noLimit'	=> true,
		'debug'		=> isset($_GET['pre'])
	));

	foreach($albums as $idx => $e){

		$tmp = array(
			'is_album'      => true,
			'is_alias'      => (intval($e['id_alias']) != 0),
			'id_alias'      => intval($e['id_alias']),
			'id_album'      => intval($e['id_album']),
			'id_content'    => intval($e['id_content']),
			'contentName'   => $e['contentName'],
			'contentSee'    => $e['contentSee'],
			'hasPoster'     => false
		);

		$id_poster = $e['id_poster'];

		// ALIAS ?
		if($e['id_alias'] > 0){
			$org = $app->apiLoad('content')->contentGet(array(
				'raw'        => true,
				'id_content' => $e['id_alias']
			));

			$id_poster = $org['id_poster'];
		}

		// Verifier le poster du dossier
		if($id_poster > 0){

			$poster = $app->apiLoad('content')->contentGet(array(
				'id_content'=> $id_poster,
				'raw'		=> true
			));

			if(file_exists(KROOT.$poster['contentItemUrl']) && is_file(KROOT.$poster['contentItemUrl'])){
				$tmp['hasPoster'] = true;

				$opt = array(
					'url'	=> $poster['contentItemUrl'],
					'admin'	=> true,
					'debug'	=> false,
					'cache'	=> true
				);

				if($poster['contentItemWidth'] >= $poster['contentItemHeight']){
					$preview = $app->mediaUrlData(array_merge($opt, array(
						'mode'	=> 'width',
						'value'	=> 300
					)));
				}else{
					$preview = $app->mediaUrlData(array_merge($opt, array(
						'mode'	=> 'height',
						'value'	=> 300
					)));
				}

				$tmp['preview'] = array(
					'url'       => $preview['img'],
					'width'     => intval($preview['width']),
					'height'    => intval($preview['height']),
				);
			}
		}

		$data[] = $tmp;
	}




	// ITEMS ///////////////////////////////////////////////////////////////////////////////////////////////////////////
	$items = $app->apiLoad('content')->contentGet(array(
		'language'	=> 'fr',
		'raw'		=> true,
		'id_type'	=> $_GET['id_type'],
		'id_album'	=> $id_album,
		'is_item'	=> true,
		'order'		=> 'contentItemPos',
		'direction'	=> 'ASC',
		'noLimit'	=> true
	));

	foreach($items as $i => $e){

		$tmp = array(
			'is_item'           => true,
			'is_poster'         => ($id_poster == $e['id_content']),
			'id_content'        => intval($e['id_content']),
			'id_album'          => intval($e['id_album']),
			'contentName'       => $e['contentName'],
			'contentSee'        => $e['contentSee'],
			'contentItemType'   => $e['contentItemType'],

		);

		if($e['contentItemType'] == 'image'){

			if(file_exists(KROOT.$e['contentItemUrl'])){

				$opt  = array(
					'url'	=> $e['contentItemUrl'],
					'admin'	=> true,
					'debug'	=> false,
					'cache'	=> true
				);

				if($e['contentItemWidth'] >= $e['contentItemHeight']){
					$preview = $app->mediaUrlData(array_merge($opt, array(
						'mode'	=> 'width',
						'value'	=> 300
					)));
				}else{
					$preview = $app->mediaUrlData(array_merge($opt, array(
						'mode'	=> 'height',
						'value'	=> 300
					)));
				}

				$tmp['preview'] = array(
					'url'       => $preview['img'],
					'width'     => intval($preview['width']),
					'height'    => intval($preview['height']),
				);
			}
		}

		$data[] = $tmp;
	}




	// SORTIE //////////////////////////////////////////////////////////////////////////////////////////////////////////
	$json = $app->helperJsonEncode($data);
	echo $app->helperJsonBeautifier($json);