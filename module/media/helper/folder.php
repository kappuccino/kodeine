<?php

	if(!headers_sent()) header('Content-type: text/javascript');

	$debug		= isset($_GET['debug']);
	$folder     = rawurldecode($_GET['folder']);
	$prompt		= '/media';

	$pref		= $app->configGet('media');
	$cache		= ($pref['useCache'] == '1') ? true : false;
	if($app->userCan('media.root') != '') {
	    if(file_exists(KROOT.$app->userCan('media.root'))) $prompt = $app->userCan('media.root');
	}

	$folder 	= ($_GET['folder'] == NULL) ? $prompt : $folder;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	$files 		= $app->fsFile(KROOT.$folder, '', FLAT_NOHIDDEN);
	if(!is_array($files)) $files = array();

	$folders 	= $app->fsFolder(KROOT.$folder, '', FLAT_NOHIDDEN);
	if(!is_array($folders)) $folders = array();

	$elements	= array_merge($folders, $files);
	if(!is_array($elements)) $elements = array();

	sort($elements);

	$path = ''; $ignore = array(); $way = array();
	foreach(explode('/', substr($folder, 1)) as $f){
		$path .= '/'.$f;
		if(file_exists(KROOT.$path.'/.ignore')){
			$ignore_ = array_map('trim', explode("\n", trim(file_get_contents(KROOT.$path.'/.ignore'))));
			$ignore_ = array_map(function($a){ global $path; return $path.'/'.$a.'/'; }, $ignore_);
			$ignore  = array_merge($ignore, $ignore_);
		}
		$way[] = $path.'/';
	}

	foreach($elements as $n => $e){
		if(in_array(str_replace(KROOT,'',$e).'/', $ignore)) unset($elements[$n]);
	}

	$scan = (count(array_intersect($ignore, $way)) > 0) ? false : true;

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	$result = array();
	if($scan){
		foreach($elements as $n => $el){ unset($tmp);
			if(!in_array(basename($el), $ignore)){

				$tmp['url'] 	= basename($el); //str_replace(KROOT, '', $el);
			//	$tmp['name'] 	= basename(str_replace(KROOT, '', $el));
				$tmp['cache']	= $cache;

				if(is_dir($el)){
					$tmp['is_folder']   = true;
					$tmp['is_file'] 	= false;
					$tmp['is_locked']	= file_exists($el.'/.lock') ? true : false;
				}else
				if(is_file($el)){
					$tmp['is_folder']   = false;
					$tmp['is_file'] 	= true;
					$tmp['is_locked']	= false;

					$tmp['kind']		= $app->mediaType($el);
					$tmp['weight']		= filesize($el);

					$ext = strtolower(pathinfo($el, PATHINFO_EXTENSION));

					if($tmp['type'] == 'pdf'){
						$tmp['kind']	= 'pdf';
					}else
					if($tmp['kind'] == 'picture'){

						$size = getimagesize($el);
						$tmp['width']  	= $size[0];
						$tmp['height'] 	= $size[1];
						$tmp['mime']   	= $size['mime'];

						$opt = array(
							'url'	=> str_replace(KROOT, NULL, $el),
							'admin'	=> true,
							'debug'	=> false,
							'cache'	=> $cache
						);

						$test = $app->mediaDataGet($opt['url']);

						if($tmp['width'] >= $tmp['height']){
							$data = $app->mediaUrlData(array_merge($opt, array(
								'mode'	=> 'width',
								'value'	=> 300
							)));
						}else{
							$data = $app->mediaUrlData(array_merge($opt, array(
								'mode'	=> 'height',
								'value'	=> 300
							)));
						}

						$tmp['preview'] = array(
							'url' 		=> $data['img'],
							'height'	=> $data['height'],
							'width'		=> $data['width']
						);
					}
				}

				$result[] = $tmp;
			}
		}
	}

	// Sortie
	$json = $app->helperJsonEncode($result);
	echo $app->helperJsonBeautifier($json);

