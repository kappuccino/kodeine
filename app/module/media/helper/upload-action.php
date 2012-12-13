<?php

if (isset($_GET['auto'])) {
	$_REQUEST['f'] = '/media/upload/content/'.date('Y/m/d');
}

function stripChars($string){
	return $string;
	/*return strtr($string,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ ',
						'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY_');*/
}

ini_set('upload_max_filesize',	'100M');
ini_set('post_max_size',		'100M');

ini_set('max_execution_time',	'1000');
ini_set('max_input_time',		'1000');

$uploadDir = $_REQUEST['f'].'/';

if (!empty($_FILES)) {
	$tempFile = $_FILES['Filedata']['tmp_name'][0];
	$tempName = $_FILES['Filedata']['name'][0];
	
	if (!is_array($_FILES['Filedata']['tmp_name'])) {
		$tempFile = $_FILES['Filedata']['tmp_name'];
	}
	
	if (!is_array($_FILES['Filedata']['name'])) {
		$tempName = $_FILES['Filedata']['name'];
	}
	
	$uploadDir  = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;
	if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
	
	$targetFile = stripChars($uploadDir . $tempName);



	$fileParts = pathinfo($tempName);
	umask(0);

	if(move_uploaded_file($tempFile, $targetFile)){

		$ext = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

		if($ext == 'jpg' OR $ext == 'jpeg'){
			$data = exif_read_data($targetFile);

			if($data['Orientation'] > 1){
				$size = GetImageSize($targetFile);
				
				if($data['Orientation'] == 6){
					$rot = -90;
				}else
				if($data['Orientation'] == 8){
					$rot = 90;
				}else
				if($data['Orientation'] == 3){
					$rot = 180;
				}

				$src = imagecreatefromjpeg($targetFile);
				$dst = imagerotate($src, $rot, 0);
				imagejpeg($dst, $targetFile, 90);
			}
		}

		echo json_encode(array(
			'success'	=> true,
			'ext'		=> $ext,
			'rot'		=> $rot,  
			'folder'	=> $_REQUEST['f']
		));	

	 	chmod($targetFile, 0755); 

	}else{
		echo "error :( ";
	}
	
}else{
	echo 'No files';
}

?>