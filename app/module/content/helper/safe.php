<?php
	require(dirname(dirname(dirname(dirname(__FILE__)))).'/api/core.admin.php');
	$app = new coreAdmin();

	if(!$app->userIsAdmin) die("--");

	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
		Retourne TRUE si l'un des FIELD d'un CONTENT contient un lien qui donne un 404
	+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
	function contentBrokenLink($app, $id_content, $opt){
		
		$content = $app->apiLoad('content')->contentGet(array(
			'id_content'	=> $id_content,
			'raw'			=> true
		));
		
		if(intval($content['id_content']) == 0) return false;
		
		$fields = $app->apiLoad('field')->fieldGet(array(
			'id_type'		=> $content['id_type']
		));
	
		$broken = false;
		foreach($fields as $e){
	
			$raw = $content['field'.$e['id_field']];
	
			if(is_string($raw)){
	
				# Rechercher les liens
				#
				preg_match_all("#href=\"(.*?)\"#", $raw, $ms, PREG_SET_ORDER);
		
				if(sizeof($ms) > 0){
					foreach($ms as $m){
		
						$curlHandle = curl_init();
						curl_setopt_array($curlHandle, array(
							CURLOPT_URL				=> $m[1],
							CURLOPT_HEADER 			=> true,
							CURLOPT_VERBOSE 		=> false,
							CURLOPT_RETURNTRANSFER 	=> true,
							CURLOPT_FOLLOWLOCATION 	=> true,
							CURLOPT_CONNECTTIMEOUT	=> 1
						));
		
					    $result = @curl_exec($curlHandle);
					    $lines  = explode("\n", $result);
		
					    if(preg_match("#HTTP/1.1 404#", $lines[0])){
					    	$broken = true;
					    }
					}
				}
			}
		}
	
		return $broken;
	}
	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */


	if($_GET['todo'] == 'list'){
		if($_GET['what'] == 'internalLink' OR $_GET['what'] == 'contentCache'){
			$tmp = $app->dbMulti("SELECT id_content FROM k_content");
			
			foreach($tmp as $e){
				$list[] = $e['id_content'];
			}
		}else
		if($_GET['what'] == 'mediaCache'){
			$list = $app->fsFolder(KROOT.'/media', '', 'NOROOT');
		}

		if(!is_array($list)) $list = array();
		$out = array('list' => $list, 'what' => $_GET['what']);
	}else


	# Verifier les liens morts dans les pages
	#
	if($_GET['what'] == 'internalLink'){
		$isBroken = contentBrokenLink($app, $_GET['element'], array('debug' => false));
		sleep(1);
		
		if($isBroken){
			$c = $app->apiLoad('content')->contentGet(array(
				'id_content'	=> $_GET['element'],
				'raw'			=> true
			));

			$out = array(
				'isBroken'		=> $isBroken,
				'id_content'	=> $_GET['element'],
				'contentName'	=> $c['contentName'],
				'what'			=> $_GET['what']
			);
		}else{
			$out = array('isBroken' => false, 'what' => $_GET['what']);
		}
	}else


	# Generer la cache pour toute les pages
	#
	if($_GET['what'] == 'contentCache'){
		$app->apiLoad('content')->contentCacheBuild($_GET['element'], array('debug' => false));
		$out = array('done' => true, 'what' => $_GET['what']);
	}else


	# Gerer les cache morte des media
	#
	if($_GET['what'] == 'mediaCache'){
	
		$folder = KROOT.str_replace('/media/', '/media/.cache/', $_GET['element']);

		if(file_exists($folder)){
			$files = $app->fsFile($folder, '', 'FLAT');
			
			foreach($files as $e){
				$indb = $app->dbOne("SELECT * FROM k_cachemedia WHERE cacheUrl='".str_replace(KROOT, '', $e)."'");
				
				# Le fichier a bien sa reference dans la BDD
				if($indb['id_cache'] > 0){
					$source = KROOT.$indb['cacheSource'];

					if(file_exists($source)){
						$lastMod = filemtime($source);

						// Si la date en BDD est EXPIRE (< SOURCE) => KILL
						if($indb['cacheLastMod'] < $lastMod){
							$unlink[]	= $indb['cacheUrl'];
							$del[]		= $indb['id_cache'];
						}else
						// Si la date sur le DISK est EXPIRE (< SOURCE) => KILL
						if(filemtime(KROOT.$e['cacheUrl']) < $lastMod){
							$unlink[]	= $indb['cacheUrl'];
							$del[] 		= $indb['id_cache'];
						}
					}else{
						$unlink[] = $e;
					}
				}else{
					$unlink[] = $e;
				}
			}

			if(sizeof($unlink) > 0){
				foreach($unlink as $e){
					unlink(KROOT.$e);
				}
			}

			if(sizeof($del) > 0){
				$app->dbQuery("DELETE FROM k_cachemedia WHERE id_cache IN(".implode(',', $del).")");
			}
		}
	
		$out = array('del' => sizeof($del), 'unlink' => sizeof($unlink));
	}




	if(isset($_GET['pre'])){
		$app->pre($_GET);
	}else{
		echo json_encode($out);
	}
	
?>