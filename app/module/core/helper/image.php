<?php

/*-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
	Description du fonctionnement de la cache de fichier

Le chemin de la cache est donnee par mediaUrlCache(),

Le fichier de cache contient une cle et le nom du fichier source, la cle est un nombre qui est
genere avec les parametre de generation (taille, mode etc...) ce qui permet d'avoir deux fichiers
de cache de la meme source mais avec des tailles differents (ou toute autre option), par exempe
1234-toto.jpg et 3442-toto.jpg

Pour chaque cache en base, on verifit si la cache est encore valide (cache.lastMod < source.lastMod)

On nettoit les vieux fichiers ou les lignes de la bdd trop anciennes

-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-*/
	
	require_once(dirname(__FILE__).'/app.php');

	if(preg_match("#([0-9]*)([\-,])([0-9]*)#", $_GET['value'], $match)){
		$value	= $match[1];
		$second = $match[3];
	}else{
		$value	= $_GET['value'];
	}

	# Construir la definition de l'image
	#
	$file 	= rawurldecode($_GET['file']);
	$file8 	= rawurldecode(utf8_decode($_GET['file']));
	if (!file_exists(KROOT.$file)) $file = $file8;
	
	$modes 	= array('w' => 'width', 'h' => 'height', 's' => 'square', 'c' => 'crop');
	$mode	= $modes[$_GET['mode']];
	$opt 	= array(
		'mode'			=> $mode,
		'value' 		=> $value,
		'second'		=> $second
	);


	# Watermark
	#
	if($_GET['water'] != null){
		$useWater = true;
				
		$opt['watermark'] = array(
			'position'	=> ($_GET['pos'] != NULL ? $_GET['pos'] : 'rb'),
			'source'	=> $_GET['water']
		);

		$stamp = $mediaWaterMark[$_GET['water']];

		if(!file_exists($stamp)) $useWater = false;
	} 

	# Determine si on utilise la cache (read|write);
	#
	$save		= isset($_GET['noCache']) ? false : true;
	$cache		= $app->mediaUrlCache($opt, $file);
	$magickArgs = '-unsharp 0x.5 -quality 90';


	# Si ImageMagick + No Cache, simuler un lastMod expire
	#
	if(IMGENGINE == 'IMAGEMAGICK' && !$save){
		$save		= true;
		$lastMod 	= 0;
	}


	# 404 si la SOURCE n'est plus accessible => nettoyer la BDD + DISK
	# Si plus de source = plus de cache !!
	#
	if(!file_exists(KROOT.$file)){
		$old = $app->dbMulti("SELECT * FROM k_cachemedia WHERE cacheSource='".$file."'");

		if(sizeof($old) > 0){
			foreach($old as $e){
				if(file_exists(KROOT.$e['cacheUrl'])){
					unlink(KROOT.$e['cacheUrl']);
				}
				$del[] = $e['id_cache'];
			}

			if(sizeof($del) > 0){
				$app->dbQuery("DELETE FROM k_cachemedia WHERE id_cache IN(".implode(',', $del).")");
			}
		}
	
		header("HTTP/1.0 404 Not Found");
		exit();
 	}
 
  
	# Mettre a jour la cache / fichier trop vieux et bdd
	#
	if($save){

		// Pour toutes les cache en BDD, verifier si les caches sont a jour
		$old = $app->dbMulti("SELECT * FROM k_cachemedia WHERE cacheSource='".$file."'");
		#$app->pre($app->db_query, $app->db_error, $old);

		if(sizeof($old) > 0){

			if(!isset($lastMod)){
				$lastMod = filemtime(KROOT.$file);
			}

			foreach($old as $e){
				// Si le fichier existe sur le disk
				if(file_exists(KROOT.$e['cacheUrl'])){

					// Si la date en BDD est EXPIRE (< SOURCE) => KILL
					if($e['cacheLastMod'] < $lastMod){
						unlink(KROOT.$e['cacheUrl']);
						$del[] = $e['id_cache'];
					}else{
					// Si la date sur le DISK est EXPIRE (< SOURCE) => KILL
						if(filemtime(KROOT.$e['cacheUrl']) < $lastMod){
							unlink(KROOT.$e['cacheUrl']);
							$del[] = $e['id_cache'];
						}
					}
				}

				// Si le fichier n'existe plus, alors on CLEAN la cache
				else{
					$del[] = $e['id_cache'];
				}
			}

			// Deep clean
			if(sizeof($del) > 0){
				$app->dbQuery("DELETE FROM k_cachemedia WHERE id_cache IN(".implode(',', $del).")");
			#	$app->pre($app->db_query, $app->db_error);
			}
		}


		// Preparer le dossier pour la cache
		if(!file_exists(dirname(KROOT.$cache))){
			umask(0);
			mkdir(dirname(KROOT.$cache), 0775, true);
		}
	}


	# Si le fichier cache existe encore (apres le clean de la cache)
	# ET que je veux l'utiliser > utiliser la cache
	#
	if(file_exists(KROOT.$cache) && $save){
		$tmp = @GetImageSize(KROOT.$cache);
		header("Content-Length: ".filesize(KROOT.$cache));
		header("Content-Type: ".$tmp['mime']);
		header("HTTP/1.0 200 OK");
		header("Location: ".$cache);
		exit();
	}


	# On commence le travail sur l'image source
	#
	$s = GetImageSize(KROOT.$file);
	list($orgWidth, $orgHeight) = $s;

	if($s['mime'] == 'image/png'){
		$isPNG = true;
		if(IMGENGINE == 'GD') $src = imagecreatefrompng(KROOT.$file);
	}else
	if($s['mime'] == 'image/gif'){
		$isGIF = true;
		if(IMGENGINE == 'GD') $src = imagecreatefromgif(KROOT.$file);
	}else
	if($s['mime'] == 'image/jpeg'){
		$isJPG = true;
		if(IMGENGINE == 'GD') $src = imagecreatefromjpeg(KROOT.$file);
	}

	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
		@FUNCTION
	+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */


	# Couleur du fond
	#
	function setBackgroundColor($img, $opt, $h, $w){
		$opt['background'] = '255,255,255';
		list($r, $g, $b) = explode(',', $opt['background']);
		$color = imagecolorallocate($img, $r, $g, $b);
		imagefilledrectangle($img, 0, 0, $h, $w, $color);
	}

	# Shell exec advanced (for ImageMagick)
	#
	function pipeExec($cmd, $input='', $debug=false){
		global $app;

		if($debug) $app->pre($cmd);

	    $proc = proc_open($cmd, array(
	    	0 => array('pipe', 'r'),
	    	1 => array('pipe', 'w'),
	    	2 => array('pipe', 'w')),
	    	$pipes
	    );
	    
	    fwrite($pipes[0], $input);
	    fclose($pipes[0]);
	    $stdout = stream_get_contents($pipes[1]);

	    fclose($pipes[1]);
	    $stderr = stream_get_contents($pipes[2]);

	    fclose($pipes[2]);
	    $rtn = proc_close($proc);

	    return array(
	        'stdout' => $stdout,
	        'stderr' => $stderr,
	        'return' => $rtn
	    );
	}

	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

	# HEIGHT fixed
	#
	if($mode == 'height'){
		$ratio = $opt['value'] / $orgHeight;

		if($ratio >= 1){
			$width_		= $orgWidth;
			$height_	= $orgHeight;
		}else{
			$width_		= round(($orgWidth * $ratio));
			$height_	= $opt['value'];
		}

		if(IMGENGINE == 'GD'){

			$dst = imagecreatetruecolor($width_, $height_);
 			setBackgroundColor($dst, $opt, $width_, $height_);

 			if($isPNG){
				imagealphablending($dst, false);
				imagesavealpha($dst, true);
 			}
 
	 		imagecopyresampled($dst, $src, 0, 0, 0, 0, $width_, $height_, $orgWidth, $orgHeight);

	 	}else
	 	if(IMGENGINE == 'IMAGEMAGICK'){
 			if($isPNG) $magickArgs .= ' -alpha transparent';
			pipeExec("convert -resize ".$opt['value']."x ".$magickArgs." \"".KROOT.$file."\" \"".KROOT.$cache."\"");
	 	}
	}else
	

	# Width fixed
	#
	if($mode == 'width'){

		$ratio = $opt['value'] / $orgWidth;

		if($ratio >= 1){
			$width_		= $orgWidth;
			$height_	= $orgHeight;
		}else{
			$width_		= $opt['value'];
			$height_	= round(($orgHeight * $ratio));
		}

		// Dans le cas ou j'ai du blanc en haut et en bas..
		if($top > 0){
			$r 			= $opt['second']  / $orgHeight;
			$width_		= round($orgWidth  * $r);
			$height_	= round($orgHeight * $r);
			$top		= 0;
			$left		= round(($width__ - $width_) / 2);
		}

		if(IMGENGINE == 'GD'){

	 		$dst = imagecreatetruecolor($width_, $height_);
	 		setBackgroundColor($dst, $opt, $width_, $height_);
	
	 		if($isPNG){
				imagealphablending($dst, false);
				imagesavealpha($dst, true);
	 		}
	
		 	imagecopyresampled($dst, $src, 0, 0, 0, 0, $width_, $height_, $orgWidth, $orgHeight);

		}else
	 	if(IMGENGINE == 'IMAGEMAGICK'){
 			if($isPNG) $magickArgs .= ' -alpha transparent';
			pipeExec("convert -resize x".$opt['value']." ".$magickArgs." \"".KROOT.$file."\" \"".KROOT.$cache."\"");
	 	}		 
	}else


	# Crop
	#
	if($mode == 'crop'){
		if($opt['second'] == NULL) die("manque le second parametre, Exemple c:800,400 (largeur,hauteur)");
		
		// Protection contre la sur-pixelisation
		if($orgWidth < $opt['value'])	$opt['value']	= $orgWidth;
		if($orgHeight < $opt['second'])	$opt['second']	= $orgHeight;

		$ratio		= $opt['value']  / $orgWidth;
		$ratioW		= $opt['value']  / $orgWidth;
		$ratioH		= $opt['second'] / $orgHeight;

		$width_		= ($ratio >= 1)		? $orgWidth	 : $opt['value'];
		$height_	= ($ratio >= 1)		? $orgHeight : round($orgHeight * $ratio);

		$width__	= ($ratioW >= 1)	? $orgWidth  : $opt['value'];
		$height__	= ($ratioH >= 1)	? $orgHeight : $opt['second'];
 		$top		= round(($height__ - $height_) / 2);
		$left		= 0;

		// Dans le cas ou j'ai du blanc en haut et en bas..
		if($top > 0){
			$r 			= $opt['second']  / $orgHeight;
			$width_		= round($orgWidth  * $r);
			$height_	= round($orgHeight * $r);
			$top		= 0;
			$left		= round(($width__ - $width_) / 2);
		}

		if(IMGENGINE == 'GD'){

	 		$dst = imagecreatetruecolor($width__, $height__);
	 			   setBackgroundColor($dst, $opt, $width__, $height__);
	
	 		if($isPNG){
				imagealphablending($dst, false);
				imagesavealpha($dst, true);
	 		}	
	
			#$app->pre($dst, $src, $left, $top, 0, 0, $width_, $height_, $orgWidth, $orgHeight);
		 	imagecopyresampled($dst, $src, $left, $top, 0, 0, $width_, $height_, $orgWidth, $orgHeight);

		}else
	 	if(IMGENGINE == 'IMAGEMAGICK'){
 			
 			$newHaut = round($orgHeight * ($opt['value'] / $orgWidth));
			$delta   = round(($newHaut - $opt['second']) / 2);
	
			pipeExec("convert -resize ".$opt['value']."x -crop ".$opt['value']."x".$opt['second']."+0+".$delta." ".$magickArgs."  \"".KROOT.$file."\" \"".KROOT.$cache."\"");
	 	}
	}else


	# Square
	#
	if($mode == 'square'){
	
		$ratio = $opt['value'] / $orgWidth;

		if($orgHeight > $orgWidth){ # Portrait
			$longeur = ($ratio >= 1) ? $orgWidth  : $opt['value'];
			$f = round(	($orgHeight - $orgWidth) / 2);
			$x = $orgWidth / $opt['value'];
			
			$a = 0;
			$b = $f;
		}else{ 
			$longeur = ($ratio >= 1) ? $orgHeight : $opt['value'];
			$f = round(	($orgWidth - $orgHeight) / 2);
			$x = $orgHeight / $opt['value'];

			$a = $f;
			$b = 0;
		}

		if(IMGENGINE == 'GD'){
	
	 		$dst = imagecreatetruecolor($longeur, $longeur);
			setBackgroundColor($dst, $opt, $longeur, $longeur);

	 		if($isPNG){
				imagealphablending($dst, false);
				imagesavealpha($dst, true);
	 		}

		 	imagecopyresampled($dst, $src, 	0,0, 	$a,$b, 	 ($orgWidth/$x),($orgHeight/$x), 	($orgWidth),($orgHeight));
			
		}else
	 	if(IMGENGINE == 'IMAGEMAGICK'){
			if($isPNG) $magickArgs .= ' -alpha transparent';
			pipeExec("convert -crop ".$orgHeight."x".$orgWidth."+".$a."+".$b." -resize ".$longeur."x ".$magickArgs." \"".KROOT.$file."\" \"".KROOT.$cache."\"");
	 	}

	}

	# Watermark avant de SAUVER
	#
 	if($useWater){

		$stamp = imagecreatefrompng($stamp);
		imagealphablending($stamp, false);
		imagesavealpha($stamp, true);

		$sx	= imagesx($stamp);
		$sy	= imagesy($stamp);
		$po = $opt['watermark']['position'];

		if($po == 'rb'){
			$posx = imagesx($dst) - $sx;
			$posy = imagesy($dst) - $sy;
		} else
		if($po == 'lb'){
			$posx = 0;
			$posy = imagesy($dst) - $sy;
		} else
		if($po == 'lt'){
			$posx = 0;
			$posy = 0;
		} else
		if($po == 'rt'){
			$posx = imagesx($dst) - $sx;
			$posy = 0;
		}

		imagecopy($dst, $stamp,  $posx, $posy, 0, 0,  $sx, $sy);
 	}
 	
 	
	# Enregistrer
	#
	if($save){

		// sur le disk
	 	if(IMGENGINE == 'GD'){
			if($isJPG){
				imagejpeg($dst, KROOT.$cache, 85);
			}else
			if($isPNG){
				imagepng($dst, KROOT.$cache);
			}else
			if($isGIF){
				imagegif($dst, KROOT.$cache);
			}
		}

		// Puisqu'on demande de sauve le fichier, alors 755
		if(file_exists(KROOT.$cache) && $cache != NULL){
			umask(0);
			chmod(KROOT.$cache, 0775);
		}

		// dans la base les infos, lastMod = on vient de creer le fichier
		$jpt = json_encode($opt);
		$ext = $app->dbOne(
			"SELECT id_cache FROM k_cachemedia\n".
			"WHERE cacheOpt = '".$jpt."' AND cacheSource = '".$file."' AND  cacheUrl = '".$cache."'"
		);
		
		if(sizeof($ext) == 0){
			$app->dbQuery($app->dbInsert(array('k_cachemedia' => array(
				'cacheLastMod'	=> array('value' => filemtime(KROOT.$cache)),
				'cacheOpt'		=> array('value' => $jpt),
				'cacheSource'	=> array('value' => $file),
				'cacheUrl'		=> array('value' => $cache)
			))));
		#	$app->pre($app->db_query, $app->db_error, date("Y-m-d H:i:s", time()));
		}

		// Afficher l'image
		header("Location: ".$cache);

	}
	
	# Pas de cache
	#
	else{
		if($isJPG){
			header("Content-Type: image/jpeg");
			imagejpeg($dst);
		}else
		if($isPNG){
			header("Content-Type: image/png");
			imagepng($dst);
		}else
		if($isGIF){
			header("Content-Type: image/gif");
			imagegif($dst);
		}
	}

?>