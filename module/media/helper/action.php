<?php
	$pref = $app->configGet('media');
	
	$src	= KROOT.$_GET['src'];
	$dst	= KROOT.$_GET['dst'];
	$debug	= isset($_GET['debug']);

	clearstatcache();

	#$app->pre($_GET);

/*[remove] => 1
[create] => 1
[rename] => 1
[move] => 1
[upload] => 1$app->userCan('media.root')*/

	switch($_REQUEST['action']){
		case 'duplicate':
            if($app->userCan('media.create')) {
                $ext = substr(strtolower($src), -3);
                $dst = substr($src, 0, -4).' - copy.'.$ext;

                if(!file_exists($src)){
                    $success 	= 'false';
                    $callBack 	= "log_('DUPLICATE FAILED : $src existe pas');";
                }else
                if(file_exists($dst)){
                    $success 	= 'false';
                    $callBack 	= "log_('DUPLICATE FAILED : $dst existe deja');";
                }else
                if(!copy($src, $dst)) {
                    $success 	= 'false';
                    $callBack 	= "log_('DUPLICATE FAILED : $src > $dst');";
                }else{
                    umask(0);
                    chmod($dst, 0755);
                    $success 	= 'true';
                    $callBack 	= "log_('DUPLICATE SUCCESS : $src > $dst');folderView();";
                }
            }
		break;

		case 'move':
            if($app->userCan('media.move')) {
                $dst = $dst.'/'.basename($src);

                umask(0);
                if(file_exists($dst)){
                    $success 	= 'false';
                    $callBack 	= "log_('MOVE FAILED : $dst existe deja');";
                }else
                if($app->mediaRename($src, $dst)){
                    $success	= 'true';
                    $callBack	= "log_('MOVE SUCCESS : $src > $dst')";
                    chmod($dst, 0755);
                }else{
                    $success	= 'false';
                    $callBack	= "log_('MOVE SUCCESS API : $src > $dst')";
                }


                /*if(file_exists($dst)){
                    $success 	= 'false';
                    $callBack 	= "log_('MOVE FAILED : $dst existe deja dans le dossier de destination');";
                }else
                if(!rename($src, $dst)) {
                    $success 	= 'false';
                    $callBack 	= "log_('MOVE FAILED : $src > $dst');";
                }else{
                    if(is_file($dst)){
                    //	$app->mediaChangeUrl($app->mediaNoRoot($src), $app->mediaNoRoot($dst));
                    }else
                    if(is_dir($dst)){
                    //	$app->mediaUpdateFolder($app->mediaNoRoot($src), $app->mediaNoRoot($dst));
                    }
                    $success 	= 'true';
                    $callBack 	= "log_('MOVE SUCCESS : $src > $dst');";
                }*/
            }
		break;

		case 'remove':
            if($app->userCan('media.remove')) {
                if($app->mediaRemove($src)){
                    $success 	= 'true';
                    if(is_file($src)){
                        $callBack = "log_('REMOVE SUCCESS FILE : $src');";
                    }else
                    if(is_dir($src)){
                        $callBack = "log_('REMOVE SUCCESS FOLDER : $src');";
                    }else{
                        $callBack = "log_('REMOVE : $src');";
                    }

                }else{
                    $success 	= 'false';
                    $callBack 	= "log_('REMOVE FAILEDD : $src');";
                }
            }

		break;

		case 'newdir':
            if($app->userCan('media.create')) {
                umask(0);

                // -- Supression caracteres interdits
                if($pref['urlEncode'] == 1) {
                    $srcinfo = pathinfo($src);
                    $data = $app->helperUrlEncode($srcinfo['basename']);
                    $src = $srcinfo['dirname'].'/'.$data;
                }else {
                    $srcinfo = pathinfo($src);
                    $data = $srcinfo['basename'];
                }
                // --

                if(file_exists($src)){
                    $success 	= 'false';
                    $callBack 	= "log_('NEWDIR FAILED : $src existe deja');";
                }else
                if(mkdir($src, 0755)){
                    $success 	= 'true';
                    $callBack 	= "log_('NEWDIR SUCCESS : $src');";
                }else{
                    $success 	= 'false';
                    $callBack 	= "log_('NEWDIR FAILED : $src');";
                }
            }
		break;
		
		case 'rename' :
            if($app->userCan('media.rename')) {
                umask(0);
                $newFile = $dst;
                $srcExt	 = substr(strtolower($src), -3);
                $newExt	 = substr(strtolower($newFile), -3);;

                if($srcExt != $newExt && $srcExt != NULL && $newExt != NULL && is_file($src) == 'file') $dst .= '.'.$srcExt;

                if(file_exists($dst)){
                    $success 	= 'false';
                    $callBack 	= "log_('RENAME FAILED : $dst existe deja');";
                }else
                if($app->mediaRename($src, $dst)){
                    $success	= 'true';
                    $callBack	= "log_('RENAME SUCCESS : $src > $dst')";
                    chmod($dst, 0755);
                }else{
                    $success	= 'false';
                    $callBack	= "log_('RENAME SUCCESS API : $src > $dst')";
                }
            }
		break;

		case 'customKeyword':
			$app->mediaCustomKeys($_GET['src'], $_GET['todo'], $_GET['key']);
		break;
		
		case 'metadata':
			$app->mediaCustom($_GET['src'], $_GET['custom']);
			$success	= 'true';
			$callBack	= "log_('MISE A JOUR DES VALEURS METADATA KAPPUCCINO')";
		break;

		case 'lock':
			if(file_exists($src.'/.lock')){
				unlink($src.'/.lock');
				$message = 'UNLOCK';
			}else{
				umask(0);
				touch($src.'/.lock');
				chmod($src.'/.lock', 0755);
				$message = 'LOCK';
			}
			
			$success = 'true';
		break;
		
		case 'resize' :
			$url = parse_url($_GET['src']);
			$prs = explode('/', $url['path']);
			unset($prs[1]);
			$url = implode('/', $prs);
			$url = KROOT.dirname($url).'/'.$_GET['name'];
			
			if(file_exists($url)) unlink($file);
			umask(0);
			file_put_contents($url, file_get_contents($_GET['src']), 0755);
			$success	= 'true';
		break;
		
		case 'pdfToImage' :
			if(file_exists($src)){
				$ext = pathinfo($src, PATHINFO_EXTENSION);
				$dst = str_replace('.'.$ext, '.png', $src);
				if(file_exists($dst)) unlink($dst);

				$app->helperPipeExec("convert -verbose \"".$src."[0]\" \"".$dst."\"");

				umask(0);
				chmod($dst, 0755);				

				$success = true;
				$message = 'Conversion OK';
			}else{
				$succces = false;
				$message = 'File not found';
			}
		break;
		
		case 'download' : 

			$success = true;
			$message = 'OK ...';

			$files = array_map('trim', explode("\n", trim($_POST['data'])));
			
			foreach($files as $e){
				if(trim($e) != ''){

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
				    	$contentType	= curl_getinfo($curlHandle, CURLINFO_CONTENT_TYPE);
				    #	$size			= curl_getinfo($curlHandle, CURLINFO_SIZE_DOWNLOAD);
				    	$size			= curl_getinfo($curlHandle, CURLINFO_HEADER_SIZE);
				    	$headers		= mb_substr($raw, 0, $size);
				    	$contents		= mb_substr($raw, $size);

						$final = MEDIA.'/'.basename($e);
						file_put_contents($final, $raw);
					}

					curl_close($curlHandle);
				}
			}
		break;
		
		default :
			$success	= 'false';
			$callBack	= "log_('ACTION NON RECONNUE')";
	}

	$out = array(
		'success' 	=> $success,
		'message'	=> $message,
		'callBack'	=> $callBack,
		'_get'		=> $_GET
	);
	
	if($debug){
		$app->pre($out);
	}else{
		echo json_encode($out);
	}


	$file = DBLOG.'/A.'.date("Y-m-d-H").'h.log';
	$fo   = fopen($file, 'a+');
	$raw  = date("Y-m-d H:i:s").' ip:'.$_SERVER['REMOTE_ADDR'].' id_user:'.$app->user['id_user'].' '.str_replace("\n", ' ', json_encode($out))."\n";
	$fw   = fwrite($fo, $raw, strlen($raw));
	$fc   = fclose($fo);

?>