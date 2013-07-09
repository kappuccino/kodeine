<?php

namespace Kodeine;

class appHelper{

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function pre(){

		echo '<pre style="text-align:left; background-color:#FFFFFF; color:#515151; padding:5px; border:1px solid #515151;">';

		for($i=0; $i<func_num_args(); $i++){
			(!is_array(func_get_arg($i)) && !is_object(func_get_arg($i)))
					? print(func_get_arg($i)."\n")
					: print_r(func_get_arg($i));
		}

		echo '</pre>';
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function go($url){
		header("Location: ".$url);
		exit();
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function folder($folder, $mask=NULL, $options=NULL, $recursive=false){

		static $myFolders;
		static $myPWD;

		# Recursive
		if(!$recursive){
			$myFolders 	= array();
			$myPWD 		= getcwd();
		}

		# Options
		$segs = explode('_', $options);
		if(in_array('FLAT', 	$segs)) $flat 		= true;
		if(in_array('NOROOT',	$segs)) $noRoot 	= true;
		if(in_array('NOHIDDEN', $segs)) $noHidden	= true;
		if(in_array('PREG', 	$segs)) $usePreg	= true;

		if(!file_exists($folder)) return false;

		$dh  	= opendir($folder);
		$files	= array();
		while(false !== ($filename = readdir($dh))){
			if($filename != '.' && $filename != '..'){
				if($noHidden){
					if(substr($filename, 0, 1) != '.') $files[] = $filename;
				}else{
					$files[] = $filename;
				}
			}

		}

		if($mask == NULL){
			foreach($files as $file){
				if(is_dir($folder.'/'.$file)){
					$myFolders[] = (($noRoot) ? str_replace(KROOT, NULL, $folder) : $folder).'/'.$file;
					if(!$flat) $this->folder($folder.'/'.$file, $mask, $options, true);
				}
			}
		}else{
			chdir($folder);
			$globFiles = glob($mask);
			if(!is_array($globFiles)) $globFiles = array();

			foreach($globFiles as $file){;
				if(is_dir($folder.'/'.$file)) $myFolders[] = (($noRoot) ? str_replace(KROOT, NULL, $folder) : $folder).'/'.$file;
				if(!$flat) $this->folder($folder.'/'.$file, $mask, $options, true);
			}
		}

		chdir($myPWD);

		return $myFolders;
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function file($folder, $mask=NULL, $options=NULL, $recursive=false){

		static $myFiles;
		static $myPWD;

		# Folder Watch
		#if(substr($folder, 0, strlen(KROOT)) != KROOT) $folder = KROOT.$folder;
		#echo $folder;
		if(!file_exists($folder)) return false;

		# Recursive
		if(!$recursive){
			$myFiles 	= array();
			$myPWD 		= getcwd();
		}

		# Options
		$segs	= explode('_', $options);
		if(in_array('FLAT', 	$segs)) $flat 		= true;
		if(in_array('NOROOT',	$segs)) $noRoot 	= true;
		if(in_array('NOHIDDEN', $segs)) $noHidden	= true;
		if(in_array('PREG', 	$segs)) $usePreg	= true;

		if(!file_exists($folder)) return false;

		$raw	= array();
		$files	= array();
		$dh		= opendir($folder);

		while(false !== ($filename = readdir($dh))){
			if($filename != '.' && $filename != '..') $raw[] = $filename;
		}

		foreach($raw as $file){
			if($noHidden){
				if(substr($file, 0, 1) != '.') $files[] = $file;
			}else{
				$files[] = $file;
			}
		}

		if($mask == NULL){
			foreach($files as $file){
				if(is_file($folder.'/'.$file)) $myFiles[] = (($noRoot) ? str_replace(KROOT, NULL, $folder) : $folder).'/'.$file;
				if(!$flat) if(is_dir($folder.'/'.$file))	$this->file($folder.'/'.$file, NULL, $options, true);
			}
		}else{
			chdir($folder);

			foreach($files as $file){
				if($usePreg){
					if(preg_match($mask, $file)){
						if(is_file($folder.'/'.$file))	$myFiles[] = (($noRoot) ? str_replace(KROOT, NULL, $folder) : $folder).'/'.$file;
					}
				}else{
					if(fnmatch($mask, $file, FNM_CASEFOLD)) {
						if(is_file($folder.'/'.$file))	$myFiles[] = (($noRoot) ? str_replace(KROOT, NULL, $folder) : $folder).'/'.$file;
					}
				}
			}

			foreach($files as $file){
				if(!$flat) if(is_dir($folder.'/'.$file) && !$flat) $this->file($folder.'/'.$file, $mask, $options, true);
			}
		}

		chdir($myPWD);

		return $myFiles;
	}

	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
	public function message($message){

		$message	= trim($message);
		$prompt		= substr($message, 0, 3);
		$text		= trim(substr($message, 3));

		if($prompt == 'OK:'){
			return array('valid', $text);
		}else
			if($prompt == 'KO:'){
				return array('error', $text);
			}else
				if($prompt == 'WA:'){
					return array('warning', $text);
				}

		return array('message', $message);
	}

	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
	public function date($date, $format=''){

		if(substr_count($date, ' ') > 0){
			list($date, $time) 	= explode(' ', $date);
			list($a, $m,  $j) 	= explode('-', $date);
			list($h, $mn, $s)	= explode(':', $time);
		}else{
			list($a, $m, $j) 	= explode('-', $date);
		}

		return ($format == TIMESTAMP)
				? mktime($h, $mn, $s, $m, $j, $a)
				: strftime($format, mktime($h, $mn, $s, $m, $j, $a));
	}

	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	 + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
	public function replace($source, $values, $del="{}"){

		if(sizeof($values) == 0) return $source;

		foreach($values as $k => $v){
			$source = str_replace($del{0}.$k.$del{1}, $v, $source);
		}

		return $source;
	}

	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
	public function urlEncode($str, $language=NULL, $id_content=NULL){

		$NewText 	 = strtolower($str);
		$sep 		 = "-";
		$NewTextTemp = '';

		for($pos=0; $pos<strlen($NewText); $pos++){

			$l = $NewText{$pos};
			$c = ord($l);

			if($c >= 32 && $c < 128){
				$NewTextTemp .= $l;
			}else{
				if($c == '223'){ $NewTextTemp .= 'ss'; }
				if($c == '224'){ $NewTextTemp .= 'a';  }
				if($c == '225'){ $NewTextTemp .= 'a';  }
				if($c == '226'){ $NewTextTemp .= 'a';  }
				if($c == '229'){ $NewTextTemp .= 'a';  }
				if($c == '227'){ $NewTextTemp .= 'ae'; }
				if($c == '230'){ $NewTextTemp .= 'ae'; }
				if($c == '228'){ $NewTextTemp .= 'ae'; }
				if($c == '231'){ $NewTextTemp .= 'c';  }
				if($c == '232'){ $NewTextTemp .= 'e';  }
				if($c == '233'){ $NewTextTemp .= 'e';  }
				if($c == '234'){ $NewTextTemp .= 'e';  }
				if($c == '235'){ $NewTextTemp .= 'e';  }
				if($c == '236'){ $NewTextTemp .= 'i';  }
				if($c == '237'){ $NewTextTemp .= 'i';  }
				if($c == '238'){ $NewTextTemp .= 'i';  }
				if($c == '239'){ $NewTextTemp .= 'i';  }
				if($c == '241'){ $NewTextTemp .= 'n';  }
				if($c == '242'){ $NewTextTemp .= 'o';  }
				if($c == '243'){ $NewTextTemp .= 'o';  }
				if($c == '244'){ $NewTextTemp .= 'o';  }
				if($c == '245'){ $NewTextTemp .= 'o';  }
				if($c == '246'){ $NewTextTemp .= 'oe'; }
				if($c == '249'){ $NewTextTemp .= 'u';  }
				if($c == '250'){ $NewTextTemp .= 'u';  }
				if($c == '251'){ $NewTextTemp .= 'u';  }
				if($c == '252'){ $NewTextTemp .= 'ue'; }
				if($c == '255'){ $NewTextTemp .= 'y';  }
				if($c == '257'){ $NewTextTemp .= 'aa'; }
				if($c == '269'){ $NewTextTemp .= 'ch'; }
				if($c == '275'){ $NewTextTemp .= 'ee'; }
				if($c == '291'){ $NewTextTemp .= 'gj'; }
				if($c == '299'){ $NewTextTemp .= 'ii'; }
				if($c == '311'){ $NewTextTemp .= 'kj'; }
				if($c == '316'){ $NewTextTemp .= 'lj'; }
				if($c == '326'){ $NewTextTemp .= 'nj'; }
				if($c == '353'){ $NewTextTemp .= 'sh'; }
				if($c == '363'){ $NewTextTemp .= 'uu'; }
				if($c == '382'){ $NewTextTemp .= 'zh'; }
				if($c == '256'){ $NewTextTemp .= 'aa'; }
				if($c == '268'){ $NewTextTemp .= 'ch'; }
				if($c == '274'){ $NewTextTemp .= 'ee'; }
				if($c == '290'){ $NewTextTemp .= 'gj'; }
				if($c == '298'){ $NewTextTemp .= 'ii'; }
				if($c == '310'){ $NewTextTemp .= 'kj'; }
				if($c == '315'){ $NewTextTemp .= 'lj'; }
				if($c == '325'){ $NewTextTemp .= 'nj'; }
				if($c == '352'){ $NewTextTemp .= 'sh'; }
				if($c == '362'){ $NewTextTemp .= 'uu'; }
				if($c == '381'){ $NewTextTemp .= 'zh'; }
			}
		}

		$NewText = $NewTextTemp;

		$NewText = preg_replace("/<(.*?)>/", 						'', 	$NewText);
		$NewText = preg_replace("/\&#\d+\;/", 						'', 	$NewText);
		$NewText = preg_replace("/\&\#\d+?\;/",						'',		$NewText);
		$NewText = preg_replace("/\&\S+?\;/",						'',		$NewText);
		$NewText = preg_replace("/['\"\?\.\!*$\#@%;:,=\(\)\[\]]/",	'',		$NewText);
		$NewText = preg_replace("/\s+/",							$sep,	$NewText);
		$NewText = preg_replace("/\//", 							$sep,	$NewText);
		$NewText = preg_replace("/[^a-z0-9-_]/",					'',		$NewText);
		$NewText = preg_replace("/\+/", 							$sep,	$NewText);
		$NewText = preg_replace("/[-_]+/",							$sep,	$NewText);
		$NewText = preg_replace("/\&/",								'',		$NewText);
		$NewText = preg_replace("/-$/",								'',		$NewText);
		$NewText = preg_replace("/_$/",								'',		$NewText);
		$NewText = preg_replace("/^_/",								'',		$NewText);
		$NewText = preg_replace("/^-/",								'',		$NewText);

		$url = $NewText;
		$found = false;
		if($language != NULL){

			if($id_content != NULL) $idc = " AND id_content != ".$id_content;

			$content = $this->mysql->multi("SELECT contentUrl FROM k_contentdata WHERE contentUrl='".$url."' AND language='".$language."'".$idc);

			if(sizeof($content) > 0){
				$i = 1;

				while(!$found && $i <= 100){
					$check = $this->mysql->one("SELECT 1 FROM k_contentdata WHERE contentUrl='".$url."-".$i."' AND language='".$language."'");

					if(!$check[1]){
						$url	= $url.'-'.$i;
						$found	= true;
					}

					$i++;
				}

			}
		}

		return $url;
	}

	/*+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
	+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-*/
	public function jsonEncode($arr){

		//convmap since 0x80 char codes so it takes all multibyte codes (above ASCII 127).
		// So such characters are being "hidden" from normal json_encoding
		array_walk_recursive($arr, function (&$item, $key) {
			if (is_string($item)) $item = mb_encode_numericentity($item, array (0x80, 0xffff, 0, 0xffff), 'UTF-8');
		});

		return mb_decode_numericentity(json_encode($arr), array (0x80, 0xffff, 0, 0xffff), 'UTF-8');

	}

	/*+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
	+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-*/
	public function jsonBeautifier($json){

		$tab          = "\t";
		$new_json     = '';
		$indent_level = 0;
		$in_string    = false;
		$json_obj     = json_decode($json);
		$len          = strlen($json);

		if($json_obj === false) return false;

#	$json = $this->helperJsonEncode($json_obj);

		for($c = 0; $c < $len; $c++){
			$char = $json[$c];
			switch($char){
				case '{':
				case '[':
					if(!$in_string){
						$new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
						$indent_level++;
					}else{
						$new_json .= $char;
					}
					break;

				case '}':
				case ']':
					if(!$in_string){
						$indent_level--;
						$new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
					}else{
						$new_json .= $char;
					}
					break;

				case ',':
					if(!$in_string){
						$new_json .= ",\n" . str_repeat($tab, $indent_level);
					}else{
						$new_json .= $char;
					}
					break;

				case ':':
					if(!$in_string){
						$new_json .= ": ";
					}else{
						$new_json .= $char;
					}
					break;

				case '"':
					if($c > 0 && $json[$c-1] != '\\'){
						$in_string = !$in_string;
					}

				default:
					$new_json .= $char;
					break;
			}
		}

		return $new_json;
	}

	/*+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
	+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-*/
	public function noAccent($string){

		return str_replace(
			array('à','á','â','ã','ä','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó',
				'ô','õ','ö','ù','ú','û','ü','ý','ÿ','À','Á','Â','Ã','Ä','Ç','È','É',
				'Ê','Ë','Ì','Í','Î','Ï','Ñ','Ò','Ó','Ô','Õ','Ö','Ù','Ú','Û','Ü','Ý'
			),
			array('a','a','a','a','a','c','e','e','e','e','i','i','i','i','n','o','o',
				'o','o','o','u','u','u','u','y','y','A','A','A','A','A','C','E','E',
				'E','E','I','I','I','I','N','O','O','O','O','O','U','U','U','U','Y'
			),
			$string
		);

	}

	/*+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
	+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-*/
	public function pipeExec($cmd, $input=''){

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

	/*+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
	+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-*/
	public function arrayWrapp($array, $glue){

		foreach($array as $n => $v){
			$array[$n] = $glue.$v.$glue;
		}

		return $array;
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
// V2: OK
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function kTalk($str){

		$binding = array(
			'r'	=> $_SERVER['DOCUMENT_ROOT'],
			'R'	=> KROOT,
			'p'	=> KPROMPT,
			'l'	=> strtolower($this->kodeine['language']),
			'L'	=> strtoupper($this->kodeine['language']),
			'C' => $this->kodeine['chapterUrl'],
			'm'	=> $this->kodeine['moduleFolder'],
			'f' => $this->kodeine['moduleFile'],
			'F' => $this->kodeine['moduleFile'].'.php',
			't' => $this->kodeine['themeFolder'],
			'T' => 'user/theme/'.$this->kodeine['themeFolder'],
		);

		$def = get_defined_constants(true);
		$def = $def['user'];

		foreach($def as $data => $value){
			if(constant($data) != NULL) $binding[$data] = $value;
		}

		$str = $this->helperReplace($str, $binding);

		return $str;
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
// V2: OK
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function kTalkCheck($str){
		return substr_count($str, '{') > 0 ? true : false;
	}

}
