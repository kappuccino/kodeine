<?php
	
	ini_set('upload_max_filesize',	'100M');
	ini_set('post_max_size',		'100M');
	
	ini_set('max_execution_time',	'1000');
	ini_set('max_input_time',		'1000');
	
	require(dirname(dirname(dirname(dirname(__FILE__)))).'/api/core.admin.php');
	$app = new coreAdmin();
	$result = array();

	# Upload
	#
	$result['time']		= date('r');
	$result['addr']		= substr_replace(gethostbyaddr($_SERVER['REMOTE_ADDR']), '******', 0, 6);
	$result['agent']	= $_SERVER['HTTP_USER_AGENT'];

	if(count($_GET))	$result['get'] 		= $_GET;
	if(count($_POST)) 	$result['post'] 	= $_POST;
	if(count($_FILES))	$result['files']	= $_FILES;
	
	// we kill an old file to keep the size small
	$log  = DBLOG.'/U.'.date("Y-m-d-H").'h.log';
	$fo   = fopen($log, 'a+');
	$raw  = date("Y-m-d H:i:s").' ip:'.$_SERVER['REMOTE_ADDR'].' id_user:'.$app->user['id_user'].' '.print_r($result, true)."\n";
	$fw   = fwrite($fo, $raw, strlen($raw));
	$fc   = fclose($fo);
	
	
	// Validation
	$error = false;
	
	if(!isset($_FILES['Filedata']) || !is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
		$error = 'Invalid Upload post_max_size='.ini_get('post_max_size');
	}

	if($error) {
		$return = array(
			'status' => '0',
			'error' => $error
		);
	}else{

		$ext = strtolower(substr(strrchr($_FILES['Filedata']['name'], '.'), 1));

		$final = KROOT.'/media/upload/gallery/'.uniqid('up_').'.'.$ext;
		move_uploaded_file($_FILES['Filedata']['tmp_name'], $final);

		################################################################################################################################
		$album = $app->apiLoad('content')->contentGet(array(
			'id_content' 	=> $_GET['id_album'],
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
				'contentName'	=> array('value' => basename($final)),
			)),
			'item'		=> array('k_contentitem' => array(
				'id_album'			=> array('value' => $album['id_content']),
			)
		));

		list($type, $mime) = explode('/', $app->mediaMimeType($final));
	
		$opt['item']['k_contentitem']['contentItemType']	= array('value' => $type);
		$opt['item']['k_contentitem']['contentItemMime']	= array('value' => $mime);
		$opt['item']['k_contentitem']['contentItemWeight']	= array('value' => filesize($final));
	
		if($type == 'image'){
			$size = getimagesize($final);
			$opt['item']['k_contentitem']['contentItemHeight']	= array('value' => $size[1]);
			$opt['item']['k_contentitem']['contentItemWidth']	= array('value' => $size[0]);
		}
	
		$last = $app->dbOne("SELECT MAX(contentItemPos) AS la FROM k_contentitem WHERE id_album=".$album['id_album']);
		$last = ($last['la'] + 1);
		$opt['item']['k_contentitem']['contentItemPos']	= array('value' => $last);

		$app->apiLoad('content')->contentSet($opt);
		$myID = $app->apiLoad('content')->id_content;


		/////
		$pad	= str_pad($myID, 8, "0", STR_PAD_LEFT);
		$final_ = dirname($final).'/'.implode('/', str_split($pad, 1)).'/'.$pad.'.'.$ext;
		
		umask(0);
		if(!file_exists(dirname($final_))) mkdir(dirname($final_), 0755, true);
		rename($final, $final_);
		umask(0);
	 	chmod($final_, 0755);

		$app->apiLoad('content')->contentSet(array(
			'id_content'	=> $myID,
			'is_item'		=> true,
			'debug'			=> false,
			'item'			=> array('k_contentitem' => array(
				'contentItemUrl'	=> array('value' => str_replace(KROOT, '', $final_)),
			))
		));

		################################################################################################################################
		
		$return['src'] = $final_;

		$return = array(
			'status' 	=> '1',
			'name' 		=> $_FILES['Filedata']['name']
		);
	
		// Our processing, we get a hash value from the file
		$return['hash'] = md5_file($final_);
	}
	
	
	// Output
	
	/**
	 * Again, a demo case. We can switch here, for different showcases
	 * between different formats. You can also return plain data, like an URL
	 * or whatever you want.
	 *
	 * The Content-type headers are uncommented, since Flash doesn't care for them
	 * anyway. This way also the IFrame-based uploader sees the content.
	 */
	
	if (isset($_REQUEST['response']) && $_REQUEST['response'] == 'xml') {
		// header('Content-type: text/xml');
	
		// Really dirty, use DOM and CDATA section!
		echo '<response>';
		foreach ($return as $key => $value) {
			echo "<$key><![CDATA[$value]]></$key>";
		}
		echo '</response>';
	} else {
		// header('Content-type: application/json');
	
		echo json_encode($return);
	}
	
?>