<?php

	$pref    = $app->configGet('media');
	$debug   = isset($_REQUEST['debug']);
	$success = false;

	$src     = KROOT.rawurldecode($_REQUEST['src']);
	$dst     = KROOT.rawurldecode($_REQUEST['dst']);

	clearstatcache();

	switch($_REQUEST['action']){

		// OK
		case 'duplicate':
            if($app->userCan('media.create')){

	            $ext = pathinfo($src, PATHINFO_EXTENSION);
                $dst = substr($src, 0, -4).'-copy.'.$ext;

                if(!file_exists($src) || file_exists($dst)){
                    $success = false;
                }else
                if(!copy($src, $dst)) {
                    $success = false;
                }else{
                    umask(0);
                    chmod($dst, 0755);
                    $success = true;
                }
            }else{
                $success = false;
            }
		break;

		// OK
		case 'move':
            if($app->userCan('media.move')) {
                $dst = $dst.'/'.basename($src);

                umask(0);
                if(file_exists($dst)){
                    $success = false;
                }else
                if($app->mediaRename($src, $dst)){
                    $success = true;
                }else{
                    $success = false;
                }
            }else{
                $success = false;
            }
		break;

		// OK
		case 'remove':
            if($app->userCan('media.remove')) {
                if($app->mediaRemove($src)){
                    $success = true;
                }else{
                    $success = false;
                }
            }else{
                $success = false;
            }
		break;

		// OK
		case 'newdir':
            if($app->userCan('media.create')) {

                // -- Supression caracteres interdits
               /*if($pref['urlEncode'] == 1) {
                    $srcinfo = pathinfo($src);
                    $data    = $app->helperUrlEncode($srcinfo['basename']);
                    $src     = $srcinfo['dirname'].'/'.$data;
                }*/

                if(file_exists($src)){
                    $success = false;
                }else{
                    umask(0);
                    $success = mkdir($src, 0755);
                }
			}else{
                $success = false;
            }
		break;

		// OK
		case 'rename' :
            if($app->userCan('media.rename')) {

	            $oldExt = pathinfo($src, PATHINFO_EXTENSION);
	            $newExt = pathinfo($dst, PATHINFO_EXTENSION);

               // if($oldExt != $newExt && $oldExt != NULL && $newExt != NULL && is_file($src) == 'file') $dst .= '.'.$srcExt;

                umask(0);
                if(file_exists($dst)){
                    $success = false;
                }else
                if($app->mediaRename($src, $dst)){
                    $success = true;
                    chmod($dst, 0755);
                }else{
                    $success = false;
                }
            }else{
                $success = false;
            }
		break;

		case 'customKeyword':
			$app->mediaCustomKeys($_GET['src'], $_GET['todo'], $_GET['key']);
		break;
		
		case 'metadata':
			$app->mediaCustom($_GET['src'], $_GET['custom']);
			$success	= true;
			$callBack	= "log_('MISE A JOUR DES VALEURS METADATA KAPPUCCINO')";
		break;

		// OK
		case 'lock':
			if(file_exists($src.'/.lock')){
				unlink($src.'/.lock');
			}else{
				umask(0);
				touch($src.'/.lock');
				chmod($src.'/.lock', 0755);
			}
			
			$success = true;
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
			$success	= true;
		break;

		// OK
		case 'pdfCover' :
			if(file_exists($src)){
				$ext = pathinfo($src, PATHINFO_EXTENSION);
				$dst = str_replace('.'.$ext, '.png', $src);
				if(file_exists($dst)) unlink($dst);

				$app->helperPipeExec("convert -verbose \"".$src."[0]\" \"".$dst."\"");

				umask(0);
				chmod($dst, 0755);				

				$success = true;
			}else{
				$succces = false;
			}
		break;

		// OK
		case 'download' : 

			$success = true;

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

						$final = $src.'/'.basename($e);

					    umask(0);
					    file_put_contents($final, $raw);
					    chmod($final, 0755);

					}

					curl_close($curlHandle);
				}
			}
		break;
	}

	$out = array(
		'success' 	=> $success,
		'src'       => $src,
		'dst'       => $dst,
		'_get'		=> $_GET
	);


	// Sortie
	$json = $app->helperJsonEncode($out);
	echo $app->helperJsonBeautifier($json);

