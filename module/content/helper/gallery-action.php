<?php
	require(dirname(dirname(dirname(dirname(__FILE__)))).'/api/core.admin.php');
	$app = new coreAdmin();

	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
		subs = la liste de tous les albums
	+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
	function sub($app, $id_album, $subs){
		$items = $app->dbMulti("SELECT * FROM k_contentalbum WHERE id_album=".$id_album);
		foreach($items as $e){
			$subs[] = $e['id_content'];
			sub($app, $e['id_content'], &$subs);
		}
	}



	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

	# On supprime un element (ALBUM / ITEM);
	#
	if($_GET['action'] == 'remove'){

		$subs = array($_GET['id_content']);
		sub($app, $_GET['id_content'], &$subs);


		// Suppression des sous-albums
		foreach($subs as $e){
			$app->apiLoad('content')->contentRemove($_GET['id_type'], $e, 'fr');
		}
		
		// Supprimer les id_content de tous ces albums
		$items = $app->dbMulti("SELECT id_content FROM k_contentitem WHERE id_album IN(".implode(',', $subs).")");
		if(sizeof($items) > 0){
			foreach($items as $e){
				$app->apiLoad('content')->contentRemove($_GET['id_type'], $e['id_content'], 'fr');
			}
		}
		
		$app->apiLoad('content')->contentAlbumFamily();

		$data['message'] = ($app->db_error == '') ? 'OK' : 'removeError';		

	}else


	# Gerer la positions des elements (ALBUM / ITEMS)
	#
	if($_GET['action'] == 'positions'){
		foreach(explode(',', $_GET['items']) as $idx => $id_content){
			if($id_content != NULL){
				$app->dbQuery("UPDATE k_contentitem SET contentItemPos=".$idx." WHERE id_content=".$id_content." AND id_album=".$_GET['id_album']);
				$app->pre($app->db_query, $app->db_error);
			}
		}
		foreach(explode(',', $_GET['albums']) as $idx => $id_content){
			if($id_content != NULL){
				$app->dbQuery("UPDATE k_contentalbum SET contentAlbumPos=".$idx." WHERE id_content=".$id_content." AND id_album=".$_GET['id_album']);
				$app->pre($app->db_query, $app->db_error);
			}
		}
		$data['message'] = 'OK';
	}else


	# Deplacement d'un ITEM
	#
	if($_GET['action'] == 'moveItem'){
		$last = $app->dbOne("SELECT MAX(contentItemPos) AS la FROM k_contentitem WHERE id_album=".$_GET['goto']);
		$app->dbQuery("UPDATE k_contentitem SET id_album=".$_GET['goto'].", contentItemPos=".($last['la']+1)." WHERE id_content=".$_GET['me']);
		$data['message'] = '@';
	}else


	# Deplacement d'un ALBUM
	#
	if($_GET['action'] == 'moveAlbum'){
		$last = $app->dbOne("SELECT MAX(contentAlbumPos) AS la FROM k_contentalbum WHERE id_album=".$_GET['goto']);
		$app->dbQuery("UPDATE k_contentalbum SET id_album=".$_GET['goto'].", contentAlbumPos=".($last['la']+1)." WHERE id_content=".$_GET['me']);
		$data['message'] = '@';

		$app->apiLoad('content')->contentAlbumFamily();
	
	}else
	

	# Modifier la visibilite d'un element (ALBUM / ITEM)
	#
	if($_GET['action'] == 'toggleView'){

		$see = ($_GET['contentSee'] == 'true') ? '1' : '0';
		$app->dbQuery("UPDATE k_content SET contentSee=".$see." WHERE id_content=".$_GET['id_content']);

		$data = array(
			'message'		=> (($app->db_error == '') ? 'OK' : 'toggleViewError'),
			'newContentSee'	=> $see,
			'id_content' 	=> $_GET['id_content'] 
		);
	}else


	# Modifier la visibilite d'un element (ALBUM / ITEM)
	#
	if($_GET['action'] == 'togglePoster'){

		$poster = ($_GET['is_poster'] == 'true') ? $_GET['id_content'] : 0;
		$app->dbQuery("UPDATE k_contentalbum SET id_poster=".$poster." WHERE id_content=".$_GET['id_album']);

		$data = array(
			'message'		=> (($app->db_error == '') ? 'OK' : 'togglePosterError'),
			'is_poster'		=> $_GET['is_poster'] == 'true',
			'id_content' 	=> $_GET['id_content'],
			'query'			=> $app->db_query,
			'error'			=> $app->db_error
		);

	}else{
		$data['message'] = 'Action non reconnue';
	}

	if(isset($_GET['pre'])){
		$app->pre($data);
	}else{
		echo json_encode($data);	
	}
?>