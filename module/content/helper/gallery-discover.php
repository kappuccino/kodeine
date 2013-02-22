<?php

#	ini_set('upload_max_filesize',	'100M');
#	ini_set('post_max_size',		'100M');
	ini_set('max_execution_time',	'1000');
	ini_set('max_input_time',		'1000');

	$debug		= isset($_REQUEST['debug']);
	$action     = $_REQUEST['action'];
	$id_album   = $_REQUEST['id_album'];
	$id_type    = $_REQUEST['id_type'];
	$success	= false;

	clearstatcache();

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
	function createItem($e, $id_album, $id_type){
		global $app;

		$e_		= str_replace(KROOT, NULL, $e);
		$indb	= $app->dbOne("SELECT * FROM k_contentitem WHERE id_album='".$id_album."' AND contentItemUrl='".addslashes($e_)."'");
		$opt	= array(
			'id_type'	=> $id_type,
			'language'	=> 'fr',
			'debug'		=> false,
			'def'		=> array('k_content' => array(
				'is_item'			=> array('value' => 1),
				'id_user'			=> array('value' => $app->user['id_user']),
				'contentSee'		=> array('value' => 1)
			)),
			'data'		=> array('k_contentdata' => array(
				'contentName'		=> array('value' => basename($e)),
			)),
			'item'		=> array('k_contentitem' => array(
				'id_album'			=> array('value' => $id_album),
				'contentItemUrl'	=> array('value' => addslashes($e_))
			)
		));

		list($type, $mime) = explode('/', $app->mediaMimeType($e));

		$opt['item']['k_contentitem']['contentItemType']	= array('value' => $type);
		$opt['item']['k_contentitem']['contentItemMime']	= array('value' => $mime);
		$opt['item']['k_contentitem']['contentItemWeight']	= array('value' => filesize($e));

		if($type == 'image'){
			$size = getimagesize($e);
			$opt['item']['k_contentitem']['contentItemHeight']	= array('value' => $size[1]);
			$opt['item']['k_contentitem']['contentItemWidth']	= array('value' => $size[0]);
		}

		if($indb['id_content'] > 0){
			$opt['id_content'] = $indb['id_content'];
		}else{
			$last = $app->dbOne("SELECT MAX(contentItemPos) AS la FROM k_contentitem WHERE id_album=".$id_album);
			$last = ($last['la'] + 1);
			$opt['item']['k_contentitem']['contentItemPos']	= array('value' => $last);
		}

		$app->apiLoad('content')->contentSet($opt);
	}

	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
	function createAlbum($folder, $id_album, $id_type){
		global $app;

		$indb = $app->dbOne("
			SELECT k_contentdata.id_content FROM k_contentdata
			INNER JOIN k_contentalbum ON k_contentdata.id_content = k_contentalbum.id_content
			WHERE id_album=".$id_album." AND contentName='".basename($folder)."'"
		);

		if($indb['id_content'] == ''){
			$last 	= $app->dbOne("SELECT MAX(contentAlbumPos) AS la FROM k_contentalbum WHERE id_album");
			$result = $app->apiLoad('content')->contentSet(array(
				'id_type'	=> $id_type,
				'language'	=> 'fr',
				'debug'		=> false,
				'def'		=> array('k_content' => array(
					'id_user'		=> array('value' => $app->user['id_user']),
					'is_album'		=> array('value' => 1),
					'contentSee'	=> array('value' => 1)
				)),
				'data'		=> array('k_contentdata' => array(
					'contentUrl'	=> array('value' => basename($folder)),
					'contentName' 	=> array('value' => basename($folder))
				)),
				'album'		=> array('k_contentalbum' => array(
					'id_album'			=> array('value' => $id_album),
					'contentAlbumPos'	=> array('vlaue' => $last['la'] + 1)
				))
			));

			return $app->apiLoad('content')->id_content;
		}else{
			return $indb['id_content'];
		}
	}

	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
	function fld($f, $id_album, $id_type, $into='create'){
		global $app, $out;

		$subs	= $app->fsFolder($f, NULL, 'FLAT');
		$myid	= ($into == 'inside') ? $id_album : createAlbum($f, $id_album, $id_type);
		$out[]	= array(
			'folder' 	=> str_replace(KROOT, NULL, $f),
			'id_album'	=> intval($myid)
		);

		if(sizeof($subs) > 0){
			sort($subs);
			foreach($subs as $e){
				fld($e, $myid, $id_type);
			}
		}

	}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	$success	= false;
	$folder_    = rawurldecode($_REQUEST['folder']);
	$folder 	= KROOT.$folder_;

	#$app->pre($_REQUEST);

	switch($action){

		case 'folder' :
			if(file_exists($folder)){

				// On sauve les dossier/album pour resynchroniser au besoin
				if($id_album > 0){
					$app->dbQuery("UPDATE k_contentalbum SET contentAlbumSyncFolder='".$folder_."' WHERE id_content=".$id_album);
				}

				// Balancer la sauce sur les dossier
				fld($folder, $id_album, $id_type, $_REQUEST['into']);

				// Sortie (r = par reference)
				$r 		 = $out;
				$success = true;

			}else{
				$more = 'folder do not exists: '.$folder;
			}
		break;


		case 'item' :
			if(file_exists($folder)){
				$files = $app->fsFile($folder, NULL, FLAT_NOHIDDEN);
				$r = 0;
				$success = true;
				if(sizeof($files) > 0){
					sort($files);
					foreach($files as $e){
						createItem($e, $_REQUEST['id_album'], $_REQUEST['id_type']);
						$r++;
					}
				}
			}
		break;


		default :
			$success	= 'false';
			$callBack	= "log_('ACTION NON RECONNUE')";
	}

	$out = array(
		'success' 	=> $success,
		'data'		=> $r,
		'more'		=> $more
	);


	// Sortie
	$json = $app->helperJsonEncode($out);
	echo $app->helperJsonBeautifier($json);

