<?php

	$escape = true;

	ini_set('upload_max_filesize',	'100M');
	ini_set('post_max_size',		'100M');

	ini_set('max_execution_time',	'1000');
	ini_set('max_input_time',		'1000');

	if(!empty($_FILES)){

		$tempFile = $_FILES['Filedata']['tmp_name'][0];
		$tempName = $_FILES['Filedata']['name'][0];

		if(!is_array($_FILES['Filedata']['tmp_name'])) {
			$tempFile = $_FILES['Filedata']['tmp_name'];
		}

		if(!is_array($_FILES['Filedata']['name'])) {
			$tempName = $_FILES['Filedata']['name'];
		}

		$uploadDir = '/media/upload/'.date("Y/m/d");
		if (!is_dir(KROOT.$uploadDir)){
			umask(0);
			mkdir(KROOT.$uploadDir, 0755, true);
		}

		$targetFile = uniqid().'_'.$tempName;
		$ext        = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

		if($escape){
			$targetFile = substr($targetFile, 0, strlen($ext)*-1);
			$targetFile = $app->helperUrlEncode($targetFile).'.'.$ext;
		}

		$targetFile = $uploadDir.'/'.$targetFile;

		umask(0);
		if(move_uploaded_file($tempFile, KROOT.$targetFile)){

			chmod(KROOT.$targetFile, 0755);

			if($ext == 'jpg' OR $ext == 'jpeg'){
				$data = exif_read_data(KROOT.$targetFile);

				if($data['Orientation'] > 1){
					$size = getImageSize(KROOT.$targetFile);

					if($data['Orientation'] == 6){
						$rot = -90;
					}else
					if($data['Orientation'] == 8){
						$rot = 90;
					}else
					if($data['Orientation'] == 3){
						$rot = 180;
					}

					$src = imagecreatefromjpeg(KROOT.$targetFile);
					$dst = imagerotate($src, $rot, 0);
						   imagejpeg($dst, KROOT.$targetFile, 90);
				}
			}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

			$import = true;
			if($import){

				$album = $app->apiLoad('content')->contentGet(array(
					'id_content' 	=> $_POST['id_album'],
					'raw'			=> true
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
						'contentName'	=> array('value' => basename($targetFile)),
					)),
					'item'		=> array('k_contentitem' => array(
						'id_album'		=> array('value' => $album['id_content']),
					)
					));

				list($type, $mime) = explode('/', $app->mediaMimeType(KROOT.$targetFile));

				$opt['item']['k_contentitem']['contentItemType']	= array('value' => $type);
				$opt['item']['k_contentitem']['contentItemMime']	= array('value' => $mime);
				$opt['item']['k_contentitem']['contentItemWeight']	= array('value' => filesize(KROOT.$targetFile));

				if($type == 'image'){
					$size = getimagesize(KROOT.$targetFile);
					$opt['item']['k_contentitem']['contentItemHeight']	= array('value' => $size[1]);
					$opt['item']['k_contentitem']['contentItemWidth']	= array('value' => $size[0]);
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
						'contentItemUrl'	=> array('value' => $targetFile),
					))
				));
			}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

			echo json_encode(array(
				'success'	=> true,
				'ext'		=> $ext,
				'rot'		=> $rot,
				'folder'	=> $_REQUEST['f']
			));


		}else{
			echo "error :( ";
		}

	}else{
		echo 'No files';
	}


