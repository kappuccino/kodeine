<?php

	if(!headers_sent()) header('Content-type: text/javascript');

	function encode_items(&$item, $key){
		$item = $item;
	}
	
	$pref		= $app->configGet('media');
	$cache		= ($pref['useCache'] == '1') ? true : false;
	$cache		= true;
#	var_dump($cache);

#	$prompt 	= (($k->userSettingGet('root') == NULL) ? KPROMPT.'/media' : $k->userSettingGet('root'));
	$prompt		= '/media';

$prompt = '/media';

if($app->userCan('media.root') != '') {
    if(file_exists(KROOT.$app->userCan('media.root'))) {
        $prompt = $app->userCan('media.root');
    }
}
//die($prompt);
#	$folder 	= urldecode($_GET['folder']);
	$folder 	= rawurldecode($_GET['folder']);
	

	$debug		= isset($_GET['debug']);

	$files 		= $app->fsFile(KROOT.$folder, '', FLAT_NOHIDDEN);
	if(!is_array($files)) $files = array();

	$folders 	= $app->fsFolder(KROOT.$folder, '', FLAT_NOHIDDEN);
	if(!is_array($folders)) $folders = array();

	$elements	= array_merge($folders, $files);
	if(!is_array($elements)) $elements = array();

	sort($elements);

	if($folder != $prompt){
		$result['parent'] = dirname($folder);
	}

	$ignore = file_exists(KROOT.$folder.'/.ignore')
		? array_map('trim', explode("\n", trim(file_get_contents(KROOT.$folder.'/.ignore'))))
		: array();

	$result['files'] = array();

	foreach($elements as $n => $el){ unset($tmp);
		if(!in_array(basename($el), $ignore)){

			$tmp['url'] 	= str_replace(KROOT, '', $el);
			$tmp['name'] 	= basename(str_replace(KROOT, '', $el));
			$tmp['cache']	= $cache;

			if(is_dir($el)){
				$tmp['type'] 		= 'dir';
				$tmp['tag']			= 'isDir';
				$tmp['locked']		= file_exists($el.'/.lock') ? true : false;
			}else
			if(is_file($el)){
				$tmp['type'] 		= 'file';
				$tmp['locked']		= false;
				$tmp['kind']		= $app->mediaType($el);
				$tmp['weight']		= filesize($el);
				$ext 				= $app->mediaParser($el);

				if(strtolower($ext['type']) == 'pdf'){
					$tmp['kind']	= 'pdf';
					$tmp['tag']		= 'isFil';
				}else
				if($tmp['kind'] == 'picture'){
	
					$size			= getimagesize($el);
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
					
					if($tmp['width'] > 300 OR $tmp['height'] > 300){
						if($tmp['width'] >= $tmp['height']){
							$data = $app->mediaUrlData(array_merge($opt, array(
								'mode'	=> 'width',
								'value'	=> 300
							#	'value'	=> $_GET['width']
							)));
							$tag = 'w';
						}else{
							$tag = 'h';
							$data = $app->mediaUrlData(array_merge($opt, array(
								'mode'	=> 'height',
								'value'	=> round(300 * $_GET['factor'])
							#	'value'	=> $_GET['height']
							)));
						}
							/*$app->pre(array_merge($opt, array(
								'mode'	=> 'height',
								'value'	=> round(300 * $_GET['factor'])
							#	'value'	=> $_GET['height']
							)));*/
							
						$tmp['thumbnail'] = array(
							'exists'	=> true,
							'tag'		=> $tag,
							'url' 		=> $data['img'],
							'height'	=> $data['height'],
							'width'		=> $data['width']
						);
	
					}else{
						$tmp['thumbnail'] = array(
							'exists'	=> false
						);
					}
	
					$tmp['tag'] .= ' isOrg';
				}else{
					$tmp['tag']	= 'isFil';
				}
			}

			$result['files'][] = $tmp;
		}
	}

	if($debug){
		echo "DEBUG RESULT\n";
		$app->pre($result);
	}else{
		#$app->pre($result);
		array_walk_recursive($result, 'encode_items');
		echo json_encode($result);
	}

?>