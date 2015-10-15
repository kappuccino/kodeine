<?php


class coreMedia extends coreDb{

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
 + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function __construct(){
	$this->canCreateJpeg = function_exists('imagecreatefromjpeg');
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function mediaMimeType($url){

	if(substr($url, 0, strlen(KROOT)) != KROOT) $url = KROOT.$url;
	if(!file_exists($url)) return false;

	$ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));

	if($ext == 'css'	){ return 'text/css';					}else
	if($ext == 'js'		){ return 'application/javascript';		}else
	if($ext == 'woff'	){ return 'application/x-font-woff';	}

	# If php provide finfo* functions
	#
	if(function_exists('finfo_open')){
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime  = finfo_file($finfo, $url);
				 finfo_close($finfo);

		// Double check for this odd result
		if($mime != 'application/octet-stream') return $mime;
	}

	if(!isset($GLOBALS['mimes-type'])){
		$txt = file_get_contents(__DIR__.'/helper/mime-type.txt');
		foreach(explode("\n", $txt) as $line){
			if(trim($line) != '' && substr_count($line, "\t") > 0){

				$p = explode("\t", trim($line));
				$a = $p[0];
				$b = strtolower($p[sizeof($p)-1]);

				$GLOBALS['mimes-type'][$a] = explode(' ', $b);
			}
		}
	}

	$myExt = strtolower(pathinfo($url, PATHINFO_EXTENSION));

	foreach($GLOBALS['mimes-type'] as $type => $exts){
		if(in_array($myExt, $exts)) return $type;
	}

	throw new Exception("Sorry, we can't extract MimeType.");
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function mediaType($f){

	switch(strtolower(substr(strrchr($f, '.'), 1))){
		case 'jpg' :
		case 'jpeg': 
		case 'gif' :
		case 'png' : 
		case 'psd' :
		case 'tif' :
		case 'eps' : $type = 'picture'; 	break;
		
		case 'zip' :
		case 'rar' :
		case 'gz'  :
		case 'tar' :
		case 'tgz' :
		case 'sit' : $type = 'archive'; 	break;
		
		case 'mov' : 
		case 'avi' : 
		case 'mpg' :
		case 'mpeg':
		case 'flv' :
		case 'mp4' :
		case 'wmv' :
		case 'mkv' :
		case 'm4v' : $type = 'video'; 		break;

		case 'fla' : 
		case 'swf' : $type = 'flash'; 		break;
		
		case 'mp3' :
		case 'wav' : 
		case 'aiff': 
		case 'ogg' : 
		case 'wav' : $type = 'audio'; 		break;
		
		case 'pdf' : $type = 'pdf';			break;
		case 'doc' : $type = 'word';		break;
		case 'xls' : $type = 'excel';		break;
		case 'pwp' : $type = 'powerpoint';	break;

		default    : $type = 'file';
	}
	
	return $type;
}


/*+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
	Function		mediaInfos
	DocTag			kMedia.mediaInfos-1.0.0
	Description		
	Parameters		url(string)
+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-*/
function mediaInfos($url){

	if(substr($url, 0, strlen(KROOT)) == KROOT) $url = str_replace(KROOT, NULL, $url);

	$path	= KROOT.$url;
	$out 	= array();

	if(file_exists($path)){
		$out['exists']	= true;
		$out['url'] 	= $url;
		$out['path'] 	= $path;

		if(is_file($path)){
			$out['weight']	= filesize($out['path']);
		}

		switch($this->mediaType($path)){
			case 'picture' :
				$sizes 			= GetImageSize($path);
			#	$parse			= $this->mediaParser($path);
				$out['height']	= $sizes[1];
				$out['width']	= $sizes[0];
			break;
		}

	}else{
		$out['exists']	= false;		
		$out['error']	= 'File '.$path.' does not exists';
	}

	return $out;
}


/*+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-*/
function mediaNoRoot($url){
	return str_replace(KROOT, KPROMPT, $url);
}


/*+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-*/
function mediaParser($url){

	# WITH _X
	preg_match("#(.*)[_](([xsdlm]{1,}))[\.]([a-z]{1,})$#", $url, $regs);
	
	# WITH OUT _X
	if(sizeof($regs) <= 1) preg_match("#(.*)([_]([xsdlmp]*|[0-9]{1,}))?[\.]([A-Za-z]{1,})$#", $url, $regs);

	# DATA
	$out = array(
		'clean' => $regs[1],
		'size'	=> $regs[3],
		'type'	=> $regs[4] 
	);

	return $out;
}

/*+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
	Function		mdGetKeywords
	DocTag			kMedia.mediaGetKeywords-1.0.0
	Description		
	Parameters		option(array)
+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-*/
function mediaGetKeywords($options=NULL){

	# Options
	$offset			= ($options['offset'] != NULL)  		? $options['offset'] 		: 0;
	$limit			= ($options['limit'] != NULL)   		? $options['limit']  		: 10;
	$orderBy		= ($options['orderBy'] != NULL) 		? $options['orderBy'] 		: 'occurence';
	$orderTo		= ($options['orderTo'] != NULL) 		? $options['orderTo'] 		: 'DESC';
	$minOccurence	= ($options['minOccurence'] != NULL) 	? $options['minOccurence']	: 0;
	$keyword		=  $options['keyword'];
	$search			=  $options['search'];

	if($minOccurence > 0) $w[] = " occurence >= ".$minOccurence;
	if($keyword != NULL)  $w[] = " dataValue = '".addslashes($keyword)."'";
	if($search != NULL)   $w[] = " dataValue LIKE '%".addslashes($search)."%'";
	if(sizeof($w) > 0)  $where = "\nWHERE ".implode(" AND ", $w);

	# Options treatment
	$sqlOrder	= " ORDER BY ".$orderBy. " ".$orderTo."\n";
	$sqlLimit	= " LIMIT ".$offset.", ".$limit;

	# Querying
	$r = $this->dbMulti("SELECT occurence, dataValue FROM ".$this->viewKeywords . $where . $sqlOrder . $sqlLimit);

	return $r;	
}

/*+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
	Function		mdGetKeywords
	DocTag			kMedia.mediaGet-1.0.0
	Description		
	Parameters		option(array)
+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-*/
function mediaGet($options=NULL){

	# Options
	$offset			= ($options['offset'] != NULL)  		? $options['offset'] 		: 0;
	$limit			= ($options['limit'] != NULL)   		? $options['limit']  		: 10;
	$orderBy		= ($options['orderBy'] != NULL) 		? $options['orderBy'] 		: 'occurence';
	$orderTo		= ($options['orderTo'] != NULL) 		? $options['orderTo'] 		: 'DESC';
	$dataName		=  $options['dataName'];
	$dataValue		=  $options['dataValue'];

	# Options treatment
	$sqlOrder	= " ORDER BY ".$orderBy. " ".$orderTo."\n";
	$sqlLimit	= " LIMIT ".$offset.", ".$limit;

	# Querying
	$q = "SELECT SQL_CACHE DISTINCT SQL_CALC_FOUND_ROWS  * FROM ".$this->tableMedia . "\n".
		 "INNER JOIN ".$this->tableMediaData." ON ".$this->tableMedia.".id_media = ".$this->tableMediaData.".id_media\n".
		 "WHERE dataName='".$dataName."' AND dataValue='".addslashes($dataValue)."' " . $sqlOrder . $sqlLimit;
	
	$r = $this->dbMulti($q);

#	$this->pre($q);

	$this->myTotal = $this->db_num_total;

	return $r;	
}

/*+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-*/
function mediaDataGet($file){
	$re = $this->dbOne("SELECT * FROM k_media WHERE mediaUrl='".addslashes($file)."'");
	return $re;
}


/*+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-*/
function mediaDataSet($url, $def){

	$exists = $this->dbOne("SELECT 1 FROM k_media WHERE mediaUrl='".$url."'");

	if($exists[1]){
		$q = $this->dbUpdate($def)." WHERE mediaUrl='".$url."'";
	}else{
		$q = $this->dbInsert($def);
	}

	if(!file_exists(DBLOG)) mkdir(DBLOG, 755, true);

	$file = DBLOG.'/A.'.date("Y-m-d-H").'h.log';
	$fo   = fopen($file, 'a+');
	$raw  = date("Y-m-d H:i:s").' ip:'.$_SERVER['REMOTE_ADDR'].' id_user:'.$this->user['id_user'].' '.str_replace("\n", ' ', $q)."\n";

	fwrite($fo, $raw, strlen($raw));
	fclose($fo);

	@$this->dbQuery($q);
	if($this->db_error != NULL) return false;

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function mediaRename($src, $dst){

	$src_ 		= str_replace(KROOT, NULL, $src);
	$dst_ 		= str_replace(KROOT, NULL, $dst);
	$cacheSrc	= str_replace('/media/', '/media/.cache/', $src_);
	$cacheDst	= str_replace('/media/', '/media/.cache/', $dst_);

	umask(0);
	if(is_dir($src)){
		$r = rename($src, $dst);

		if($r){
			chmod($dst, 0755);

			$media = $this->dbMulti("SELECT * FROM k_media WHERE mediaUrl LIKE '".$src_."%'");
			foreach($media as $e){
				$this->dbQuery("UPDATE k_media SET mediaUrL='".str_replace($src_, $dst_, $e['mediaUrl'])."' WHERE mediaUrl='".$e['mediaUrl']."'");
				$this->pre($this->db_query, $this->db_error);
			}

			if(file_exists(KROOT.$cacheSrc) && is_dir(KROOT.$cacheSrc) && ! file_exists(KROOT.$cacheDst)){

				if(!file_exists(dirname(KROOT.$cacheDst))){
					mkdir(dirname(KROOT.$cacheDst), 0755, true);
				}

				rename(KROOT.$cacheSrc, KROOT.$cacheDst);
			}

			$cache = $this->dbMulti("SELECT * FROM k_cachemedia WHERE cacheSource LIKE '".$src_."/%'");
			#$this->pre($cache);

			foreach($cache as $e){
				$this->dbQuery("
					UPDATE k_cachemedia SET
					cacheSource='".str_replace($src_,     $dst_,     $e['cacheSource'])."', 
					cacheUrl   ='".str_replace($cacheSrc, $cacheDst, $e['cacheUrl'])."'
					WHERE id_cache=".$e['id_cache']
				);
				$this->pre($this->db_query, $this->db_error);
			}
		}

	}else
	if(is_file($src)){
		$r = copy($src, $dst);

		if($r){
			chmod($dst, 0755);

			# Verifier dans la base de media si je dois bouger des choses
			#
			$media = $this->dbOne("SELECT * FROM k_media WHERE mediaUrl='".$src_."'");
			if($media['mediaUrl'] != ''){
				$this->dbQuery("UPDATE k_media SET mediaUrl='".$dst_."' WHERE mediaUrl='".$src_."'");
			}

			# Recuperer la version en cache
			#
			$cache 		= $this->dbOne("SELECT * FROM k_cachemedia WHERE cacheSource = '".$src_."'");
			$cacheSrc	= $cache['cacheUrl'];
			$cacheDst	= dirname($dst_).'/'.basename($cacheSrc);
			$cacheDst	= str_replace('/media/', '/media/.cache/', $cacheDst);

			if(file_exists(KROOT.$cacheSrc) && is_file(KROOT.$cacheSrc) && !file_exists(KROOT.$cacheDst)){
				if(!file_exists(dirname(KROOT.$cacheDst))){
					mkdir(dirname(KROOT.$cacheDst), 0755, true);
				}
				rename(KROOT.$cacheSrc, KROOT.$cacheDst);

				$this->dbQuery("UPDATE k_cachemedia SET cacheSource='".$dst_."',  cacheUrl   ='".$cacheDst."' WHERE cacheSource='".$src_."'");
			}

			# Supprimer l'ancien fichier (BDD + Cache)
			#
			$this->mediaRemove($src);
		}

	}else{
		$r = false;
	}

	return $r;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function mediaRemove($src){

	if(is_dir($src)){

		$cache = str_replace('/media/', '/media/.cache/', $src);

		# Parcourir les sous-dossier et supprimer tous les fichirs (protection .lock)
		#
		$files = $this->fsFile($src);
		if(file_exists($cache)){
			$cacheFiles = $this->fsFile($cache);
			if(is_array($cacheFiles)) $files = array_merge($files, $cacheFiles);			
		}

	#	echo "Files";
	#	print_r($files);

		if(sizeof($files) > 0){
			sort($files);
			foreach($files as $f){
				if(basename($f) == '.lock'){
					$check[] = dirname($f);
					$check[] = str_replace('/media/', '/media/.cache/', dirname($f));
				}
			}
			#if(is_array($check)) sort($check);

		#	echo "Check\n";
		#	print_r($check);

			if(sizeof($check) > 0){
				foreach($files as $f){
					$do = false;
					foreach($check as $c){
					#	if(substr($f, 0, strlen($c)) != $c) $do = true;
						$p = "#^".$c."#";
						
					#	echo "\n";
					#	echo "\tPatern: ".$p."\n";
					#	echo "\tCheck : ".$c."\n";
					#	echo "\tFile  : ".$f."\n";

						if(preg_match($p, $f)){
					#		echo "\tPreg  : OK\n";
							$do = true;
						}else{
					#		echo "\tPreg  : Pas OK\n";
						}

					}
					if(!$do){
						$tmp[] = $f;
					}else{
						$keep[] = $f;
					}
				}
				$files = $tmp;
			}

			#echo "Finally\n";
			#print_r($files);

			#echo "Non supprime\n";
			#print_r($keep);
		
			if(is_array($files)){
				foreach($files as $f){
				#	echo "Suppression de ".$f."\n";
					unlink($f);
				}
			}
		}
	
		unset($check, $lock, $tmp, $keep);

		# Supprimer tous les dossier puisqu'ils sont maintenant vides
		#
		$folders 	= $this->fsFolder($src);
		$folders[]	= $src;
		if(file_exists(dirname($cache))){
			$cacheFolder =  $this->fsFolder($cache);
			if(is_array($cacheFolder)){
				$cacheFolder[]	= $cache;
				$folders = array_merge($folders, $cacheFolder);

			}
		}

		#echo "Folder\n";
		#print_r($folders);

		foreach($folders as $f){
			if(file_exists($f.'/.lock')){
				$check[] = $f;
				$check[] = str_replace('/media/', '/media/.cache/', $f);
			}
		}

		#echo "Check\n";
		#print_r($check);


		if(sizeof($check) > 0){
			foreach($folders as $f){
				$kill = true;
				foreach($check as $c){
					
					if($kill){
					#	echo "\n";
					#	echo "\tCheck : ".$c."\n";
					#	echo "\tFolder: ".$f."\n";
	
						if(preg_match("#^".$c."#", $f)){
						#	echo "\t      : Garde 1\n";
							$kill = false;
						}else
						if(substr($c, 0, strlen($f)) == $f){
						#	echo "\t      : Garde 2\n";
							$kill = false;
						}else{
						#	echo "\t      : Supprime 1\n";
						}
					}
				}

				if($kill){
					$keep[] = $f;
				}else{
					$del[] = $f;
				}
				
			#	echo "\n\n\n\n";
			}
				
			// Keep correspond a ceux qui restent
			$folders = $keep;
		}


		#echo "Finally Folder\n";
		#print_r($folders);

		#echo "Non supprime\n";
		#print_r($keep);

		if(is_array($folders)){
			foreach($folders as $f){
			#	echo "Suppression de ".$f."\n";
				rmdir($f);

				$this->dbQuery("DELETE FROM k_media 		WHERE mediaUrl 	LIKE '".str_replace(KROOT, NULL, $f)."/%'");
				$this->dbQuery("DELETE FROM k_cachemedia WHERE cacheSource 	LIKE '".str_replace(KROOT, NULL, $f)."/%'");
			}
		}

		$r = true;


	}else
	if(is_file($src)){
		$r = unlink($src);
		$this->dbQuery("DELETE FROM k_media 		WHERE mediaUrl	= '".str_replace(KROOT, NULL, $src)."'");
		$this->dbQuery("DELETE FROM k_cachemedia WHERE cacheSource	= '".str_replace(KROOT, NULL, $src)."'");
	}

	$r = true;

	return $r;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function mediaIndexRemove($file){
	$this->dbQuery("DELETE FROM k_media WHERE mediaUrl='".$file."'");
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function mediaUrlCache($opt, $file, $raw=false){

	if(is_array($opt)){
		unset(
			$opt['cache'], $opt['debug'], $opt['url'], $opt['file'], $opt['admin'],
			$opt['generate'], $opt['ssl'], $opt['cdn'], $opt['domain']
		);
	}

	if(is_array($opt)){
		ksort($opt);
	}else{
		$opt = array();
	}
	
	foreach($opt as $k => $v){
		if($v == NULL){
			unset($opt[$k]);
		}else{
			$opt[$k] = strval($v);
		}
	}

	$key	= json_encode($opt);
	$key	= crc32($key);
	$key	= sprintf("%u", $key);
	$cache	= '/media/.cache'.dirname($file).'/'.$key.'-'.basename($file);
 	$cache	= str_replace('.cache/media/', '.cache/', $cache);
		
	if($raw){
		return array(
			'key' => $key,
			'url' => $cache
		);
	}else{
		return $cache;
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function mediaUrlData($opt){

	global $mediaWaterMark, $mediaCDN;

	$url = $opt['url'];
	if(substr($url, 0, strlen(KROOT)) != KROOT) $url = KROOT.$url;
	if(!file_exists($url)) return false;
	list($width_, $height_) = getimagesize($url);

	// Trouver
	if(preg_match("#([0-9]*)([\-,])([0-9]*)#", $opt['value'], $match)){
		$opt['value']  = $match[1];
		$opt['second'] = $match[3];
	}

	if($opt['mode'] == 'box'){
		$ratioF  = $opt['value']  / $opt['second'];
		$ratio = $width_ / $height_;
		if($ratio >= $ratioF){
			$opt['mode'] = 'width';
		}else{
			$opt['mode'] = 'height';
			$opt['value'] = $opt['second']; // reverse
		}
		unset($opt['second']);
	}
	
	if($opt['mode'] == 'height'){
		$ratio	= $opt['value'] / $height_;
		$ratio	= ($ratio < 1) ? $ratio : 1;
		$code	= 'h';
		$width	= round($width_ * $ratio);
		$height	= round($height_ * $ratio);
	}else
	if($opt['mode'] == 'width'){
		$ratio	= $opt['value'] / $width_;
		$ratio	= ($ratio < 1) ? $ratio : 1;
		$code	= 'w';
		$width	= round($width_ * $ratio);
		$height	= round($height_ * $ratio);
	}else
	if($opt['mode'] == 'square'){
		$max	= ($width_ > $height_) ? $height_ : $width_;
		$long	= ($opt['value'] >= $max) ? $max : $opt['value'];
		$ratio	= $long / $max;
		$code	= 's';
		$height	= $long;
		$width	= $long;
	}else
	if($opt['mode'] == 'crop'){
		$code	= 'c';
		$ratio  = $opt['value']  / $width_;
		$ratioW = $opt['value']  / $width_;
		$ratioH = $opt['second'] / $height_;

		if($ratio >= 1){
			$width	= $width_;
			$height	= $height_;
		}else{
			$width	= $opt['value'];
			$height	= round(($height_ * $ratio));
		}

		$width  = ($ratioW >= 1) ? $width_  : $opt['value'];
		$height = ($ratioH >= 1) ? $height_ : $opt['second'];
	}
	
	
	
	$cache	= isset($opt['cache']) ? $opt['cache'] : true;
 	$render	= ($opt['mode'] == 'crop')
 		? '/'.$code.':'.$opt['value'].','.$opt['second'].$opt['url']
 		: '/'.$code.':'.$opt['value'].$opt['url'];

	if(!$cache) $render .= '?noCache';

	# Watermark
	#
	if($opt['watermark'] != NULL){
		$useWater = false;		
		if(file_exists($mediaWaterMark[$opt['watermark']['source']])) {
			$useWater = true;
			if ($cache) $render .= '?';
			$render .= '&water='.$opt['watermark']['source'];
			$render .= '&pos='.$opt['watermark']['position'];
		}		
	}

 	# Suppression des valeurs ne servant pas a definir la KEY du cache
	#$this->pre($opt);
	$store	= $this->mediaUrlCache($opt, str_replace(KROOT, NULL, $url));
	#$this->pre($store);
	
	# Si source > cache (on supprime la cache)
	if(file_exists(KROOT.$store)){
		$srcLastMod 	= filemtime($url);
		$cacheLastMod	= filemtime(KROOT.$store);

		if($srcLastMod > $cacheLastMod) unlink(KROOT.$store);
	}

	# La comande qui sera utilisé par la balise <IMG />
	$img = (file_exists(KROOT.$store) && $cache) ? $store : $render;

	# Gerer le domaine pour CDN ou autre
	if($opt['cdn'] === true && isset($mediaCDN)){
		$domain		= is_array($mediaCDN) ? $mediaCDN[array_rand($mediaCDN, 1)] : $mediaCDN;
		$storeDom	= 'http://'.$domain.$store;

		if($img == $store) $img = $storeDom;
		$store = $storeDom;
	}

	# Si on demande l'ENREGISTREMENT, et la GENERATION
	if($cache && $opt['generate'] && $img != $store){
	
		$curlHandle = curl_init();
		curl_setopt_array($curlHandle, array(
			CURLOPT_URL				=> 'http://'.$_SERVER['HTTP_HOST'].$render,
			CURLOPT_HEADER 			=> true,
			CURLOPT_VERBOSE 		=> true,
			CURLOPT_RETURNTRANSFER 	=> true,
			CURLOPT_FOLLOWLOCATION 	=> true,
			CURLOPT_CONNECTTIMEOUT	=> 0.2
		));
	    $result = @curl_exec($curlHandle);

	    if($result === false) throw new Exception('System error: ' . curl_error($curlHandle));
		curl_close($curlHandle);
		
		// Mettre a jour du coup les valeurs du tableau puisque l'image existe
		$img = $store;
	}

	$out 	= array(
		'url'		=> $url,
		'src'		=> str_replace(KROOT, NULL, $url),
		'ratio'		=> $ratio,
		'value'		=> $opt['value'],
		'source'	=> array(
			'url' 		=> $url,
			'src'		=> str_replace(KROOT, NULL, $url),
			'height'	=> $height_,
			'width'		=> $width_
		),
		'height'	=> $height,
		'width'		=> $width,
		'cache'		=> $cache,
		'render'	=> $render,
		'store'		=> $store,
		'img'		=> $img
	);
	
	if($useWater) {
		$out['watermark'] = array(
			'source'	=> $opt['watermark']['source'],
			'position' 	=> $opt['watermark']['position']
		);
	}
	
	// Simplifier le code derriere
	$out['html'] = "src=\"".$out['img']."\" width=\"".$out['width']."\" height=\"".$out['height']."\" ";

	if($opt['debug']) $this->pre($out);

	return $out;
}


}