<?php

	header("Content-Type: text/plain");

	$data = array();

	// Album
	if($_GET['id_album'] > 0){
		$album = $app->apiLoad('content')->contentGet(array(
			'raw'			=> true,
			'id_content'	=> $_GET['id_album'],
		));

		$id_poster = $album['id_poster'];
	}else{
		$id_poster = NULL;
	}

	// Albums
	$albums = $app->apiLoad('content')->contentGet(array(
		'language'	=> 'fr',
		'raw'		=> true,
		'id_type'	=> $_GET['id_type'],
		'id_album'	=> $_GET['id_album'],
		'is_album'	=> true,
		'order'		=> 'contentAlbumPos',
		'direction'	=> 'ASC',
		'noLimit'	=> true,
		'debug'		=> isset($_GET['pre'])
	));

	foreach($albums as $idx => $e){

		$tmp = array(
			'is_album'      => true,
			'id_album'      => intval($e['id_album']),
			'id_content'    => intval($e['id_content']),
			'contentName'   => $e['contentName'],
			'contentSee'    => $e['contentSee'],
			'hasPoster'     => false
		);

		// Verifier le poster du dossier
		if($e['id_poster'] > 0){

			$poster = $app->apiLoad('content')->contentGet(array(
				'id_content'=> $e['id_poster'],
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

	// Items
	$items = $app->apiLoad('content')->contentGet(array(
		'language'	=> 'fr',
		'raw'		=> true,
		'id_type'	=> $_GET['id_type'],
		'id_album'	=> $_GET['id_album'],
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

	//$content = array_merge($albums, $items);

	// Subs
	/*foreach($content as $idx => $e){
		$content[$idx]['is_album']	= ($e['is_album']) ? true : false;
		$content[$idx]['is_item']	= ($e['is_item'])  ? true : false;
		$content[$idx]['is_poster']	= ($album['id_poster'] == $e['id_content']) ? true : false;
	}*/

	// Path
	/*function parents($app, $id_content, &$path){
		$p = $app->dbOne("
			SELECT * FROM k_contentalbum
			INNER JOIN k_contentdata ON k_contentalbum.id_content = k_contentdata.id_content
			WHERE language='fr' AND k_contentalbum.id_content=".$id_content
		);

		$path[] = array('id_content' => $p['id_content'], 'contentName' => $p['contentName']);
		if($p['id_album'] != '0') parents($app, $p['id_album'], $path);
	
	#	$app->pre($app->db_query, $path);
	}
	
	$path = array();#$_GET['id_album']);
	if($_GET['id_album'] != 0) parents($app, $_GET['id_album'], $path);

	$data['path']	= array_reverse($path);*/
#	$data['items']	= $content;

/*
	foreach($data['path'] as $idx => $e){
		$data['path'][$idx]['contentName'] = $e['contentName'];
	}
	
	foreach($data['items'] as $idx => $e){
		$data['items'][$idx]['contentName'] = $e['contentName'];
	}
*/

	// Sortie
	$json = $app->helperJsonEncode($data);
	echo $app->helperJsonBeautifier($json);