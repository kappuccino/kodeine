<?php
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
	#	'debug'		=> isset($_GET['pre'])
	));

	if(sizeof($albums) > 0){
		foreach($albums as $idx => $e){
			$albums[$idx]['contentName'] = $e['contentName'];
		}
	}	

	if(isset($_GET['pre'])){
		$app->pre($albums);
	}else{
		echo json_encode($albums);	
	}
?>