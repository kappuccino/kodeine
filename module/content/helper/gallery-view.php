<?php
	// Album
	if($_GET['id_album'] > 0){
		$album = $app->apiLoad('content')->contentGet(array(
			'raw'			=> true,
			'id_content'	=> $_GET['id_album'],
		));
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

	if(sizeof($albums) > 0){
		foreach($albums as $idx => $e){

			// Verifier le poster du dossier
			if($e['id_poster'] > 0){

				$poster = $app->apiLoad('content')->contentGet(array(
					'id_content'=> $e['id_poster'],
					'raw'		=> true,
					'debug'		=> false,
				));

				if(file_exists(KROOT.$poster['contentItemUrl']) && is_file(KROOT.$poster['contentItemUrl'])){
					$preview = $app->mediaUrlData(array(
						'url'		=> $poster['contentItemUrl'],
						'mode'		=> (($poster['contentItemWidth'] > $poster['contentItemHeight']) ? 'width' : 'height'),
						'value'		=> $_GET['size']
					));
	
					$albums[$idx]['poster'] = array('preview' => array(
						'contentItemUrl'	=> $preview['img'],
						'contentItemWidth'	=> $preview['width'],
						'contentItemHeight'	=> $preview['height'],
					));
				}else{
					$albums[$idx]['id_poster'] = 0;
				}
			}
		}
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
		'noLimit'	=> true,
#		'debug'		=> isset($_GET['pre'])
	));
#	die($app->pre("#2", $items));

	foreach($items as $i => $e){
		if($e['contentItemType'] == 'image'){
			$preview = $app->mediaUrlData(array(
				'url'	=> $e['contentItemUrl'],
				'mode'	=> (($e['contentItemWidth'] > $e['contentItemHeight']) ? 'width' : 'height'),
				'value'	=> $_GET['size']
			));

			$items[$i]['preview'] = array(
				'contentItemUrl'	=> $preview['img'],
				'contentItemWidth'	=> $preview['width'],
				'contentItemHeight'	=> $preview['height'],
			);
		}
	}

	$content = array_merge($albums, $items);

	// Subs
	foreach($content as $idx => $e){
		$content[$idx]['is_album']	= ($e['is_album']) ? true : false;
		$content[$idx]['is_item']	= ($e['is_item'])  ? true : false;
		$content[$idx]['is_poster']	= ($album['id_poster'] == $e['id_content']) ? true : false;
	}

	// Path
	function parents($app, $id_content, &$path){
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

	$data['path']	= array_reverse($path);	
	$data['items']	= $content;
	
	foreach($data['path'] as $idx => $e){
		$data['path'][$idx]['contentName'] = $e['contentName'];
	}
	
	foreach($data['items'] as $idx => $e){
		$data['items'][$idx]['contentName'] = $e['contentName'];
	}
	

	if(isset($_GET['pre'])){
		$app->pre($data);
	}else{
		echo json_encode($data);	
	}
?>