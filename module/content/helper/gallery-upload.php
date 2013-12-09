<?php

	$escape    = true;
	$uploadDir = '/media/upload/'.date("Y/m/d");

	if (!is_dir(KROOT.$uploadDir)){
		umask(0);
		mkdir(KROOT.$uploadDir, 0755, true);
	}

	ini_set('upload_max_filesize',	'100M');
	ini_set('post_max_size',		'100M');

	ini_set('max_execution_time',	'1000');
	ini_set('max_input_time',		'1000');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	function import($app, $file){

		chmod(KROOT.$file, 0755);
		$ext = strtolower(pathinfo(KROOT.$file, PATHINFO_EXTENSION));

		if($ext == 'jpg' OR $ext == 'jpeg'){
			$data = exif_read_data(KROOT.$file);

			if($data['Orientation'] > 1){
				if($data['Orientation'] == 6){ $rot = -90; }else
				if($data['Orientation'] == 8){ $rot =  90; }else
				if($data['Orientation'] == 3){ $rot = 180; }

				$src = imagecreatefromjpeg(KROOT.$file);
				$dst = imagerotate($src, $rot, 0);
				imagejpeg($dst, KROOT.$file, 90);
			}
		}

		$album = $app->apiLoad('content')->contentGet(array(
			'id_content' => $_POST['id_album'],
			'raw'		 => true
		));

		$opt = array(
			'id_type'	=> $album['id_type'],
			'language'	=> 'fr',
			'debug'		=> false,
			'def'		=> array('k_content' => array(
				'is_item'		=> array('value' => 1),
				'id_user'		=> array('value' => $app->user['id_user']),
				'contentSee'	=> array('value' => 1)
			)),
			'data'		=> array('k_contentdata' => array(
				'contentName'	=> array('value' => basename($file)),
			)),
			'item'		=> array('k_contentitem' => array(
				'id_album'		=> array('value' => $album['id_content']),
			)
			));

		list($type, $mime) = explode('/', $app->mediaMimeType(KROOT.$file));

		$opt['item']['k_contentitem']['contentItemType']   = array('value' => $type);
		$opt['item']['k_contentitem']['contentItemMime']   = array('value' => $mime);
		$opt['item']['k_contentitem']['contentItemWeight'] = array('value' => filesize(KROOT.$file));

		if($type == 'image'){
			$size = getimagesize(KROOT.$file);
			$opt['item']['k_contentitem']['contentItemHeight'] = array('value' => $size[1]);
			$opt['item']['k_contentitem']['contentItemWidth']  = array('value' => $size[0]);
		}

		$last = $app->dbOne("SELECT MAX(contentItemPos) AS la FROM k_contentitem WHERE id_album=".$album['id_album']);
		$last = ($last['la'] + 1);
		$opt['item']['k_contentitem']['contentItemPos']	= array('value' => $last);

		$app->apiLoad('content')->contentSet($opt);
		$myID = $app->apiLoad('content')->id_content;

		$app->apiLoad('content')->contentSet(array(
			'id_content'	=> $myID,
			'is_item'		=> true,
			'debug'			=> false,
			'item'			=> array('k_contentitem' => array(
				'contentItemUrl' => array('value' => $file),
			))
		));

		$preview = $app->mediaUrlData(array(
			'url'	=> $file,
			'admin'	=> true,
			'debug'	=> false,
			'cache'	=> true,
			'mode'	=> 'width',
			'value'	=> 300
		));

		$model = array(
			'is_item'         => true,
			'is_poster'       => false,
			'id_content'      => $myID,
			'id_album'        => $album['id_content'],
			'contentName'     => basename($file),
			"contentSee"      => "1",
			"contentItemType" => $type,
			"preview"         => array(
				"url"    => $preview['img'],
				"width"  => $preview['width'],
				"height" => $preview['height']
			)
		);

		return array(
			'success' => true,
			'ext'     => $ext,
			'rot'     => $rot,
			'folder'  => $_REQUEST['f'],
			'model'   => $model
		);
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	if(!empty($_POST['remoteUrl'])){

		$urls = explode("\n", trim($_POST['remoteUrl']));
		$out  = array(
			'success' => true,
			'remote'  => array()
		);

		foreach($urls as $e){

			$curlHandle = curl_init();
			curl_setopt_array($curlHandle, array(
				CURLOPT_URL				=> $e,
				CURLOPT_HEADER 			=> false,
				CURLINFO_HEADER_OUT		=> true,
				CURLOPT_VERBOSE 		=> true,
				CURLOPT_RETURNTRANSFER 	=> true,
				CURLOPT_FOLLOWLOCATION 	=> true,
				CURLOPT_CONNECTTIMEOUT	=> 0.9,
			));

			$raw = curl_exec($curlHandle);

			if($raw !== false){
				$targetFile = time().'_'.uniqid().'_'.basename($e);

				if($escape){
					$ext        = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
					$targetFile = substr($targetFile, 0, strlen($ext)*-1);
					$targetFile = $app->helperUrlEncode($targetFile).'.'.$ext;
				}

				$targetFile = $uploadDir.'/'.$targetFile;

				if(file_put_contents(KROOT.$targetFile, $raw)){
					$out['remote'][] = import($app, $targetFile);
				}else{
					$out['remote'][] = array('err' => 'download issue');
				}
			}

			curl_close($curlHandle);
		}

	}else
	if(!empty($_FILES)){

		$tempFile = is_array($_FILES['Filedata']['tmp_name'])
			? $_FILES['Filedata']['tmp_name'][0]
			: $_FILES['Filedata']['tmp_name'];

		$tempName = is_array($_FILES['Filedata']['name'])
			? $_FILES['Filedata']['name'][0]
			: $_FILES['Filedata']['name'];

		$pref  = $app->configGet('content');
		$ext   = strtolower(pathinfo($tempName, PATHINFO_EXTENSION));

		if($pref['galleryUploadChao'] == 'after'){
			$noExt      = substr($tempName, 0, ( strlen($ext)+1 )*-1);
			$targetFile = $noExt.'_'.time().'_'.uniqid().'.'.$ext;
		}else
		if($pref['galleryUploadChao'] == 'none'){
			$targetFile = $tempName;
		}else{
			$targetFile = time().'_'.uniqid().'_'.$tempName;
		}

		if($escape){
			$targetFile = substr($targetFile, 0, strlen($ext)*-1);
			$targetFile = $app->helperUrlEncode($targetFile).'.'.$ext;
		}

		$targetFile = $uploadDir.'/'.$targetFile;

		if(move_uploaded_file($tempFile, KROOT.$targetFile)){
			$out = import($app, $targetFile);
		}else{
			$out = array('success' => false, 'err' => "error :( ");
		}

	}else{
		$out = array('success' => false, 'err' => 'no files');
	}

	echo json_encode($out);

