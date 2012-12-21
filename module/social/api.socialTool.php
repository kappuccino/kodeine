<?php

class socialTool extends social {

function __clone(){}
function socialTool(){}


function socialToolExternal($opt){

	# GET
	#
	if($opt['type'] == 'post'){
		$data = $this->apiLoad('socialPost')->socialPostGet(array('id_socialpost' => $opt['id']));
		$data = $data['socialPostData'];
	}else
	if($opt['type'] == 'message'){
		$data = $this->apiLoad('socialMessage')->socialMessageGet(array('id_socialmessage' => $opt['id']));
		$data = $data['socialMessageData'];
	}else{
		return false;
	}

	# CHECK (recoder les blanc avant le data, hack, je devrai changer la regexp plutot
	#
	$view = $data;
	if(preg_match_all("#(http(s)?://(.*)\\s)#msU", ' '.$data.' ', $add, PREG_SET_ORDER)){

		foreach($add as $a){

			$myurl	= trim($a[0]);
			$url 	= parse_url($myurl);
			$view	= str_replace($myurl, "<a href=\"".$myurl."\" target=\"_blank\">".$myurl."</a>", $view);

			if($url['host'] == 'www.youtube.com'){
				parse_str($url['query'], $arr);
				if($arr['v'] != ''){
					$myurl	= 'http://gdata.youtube.com/feeds/api/videos/'.$arr['v'].'?alt=json';
					$yturl	= 'http://www.youtube.com/?v='.$arr['v'];
					$embed	= 'http://www.youtube.com/embed/'.$arr['v'].'?autoplay=1&rel=0';
				}else{
					unset($myurl); // avoiding useless curl action
				}
			}

			if(isset($myurl)){

				$curlHandle = curl_init();
				curl_setopt_array($curlHandle, array(
					CURLOPT_URL				=> $myurl,
					CURLOPT_HEADER 			=> false,
					CURLINFO_HEADER_OUT		=> true,
					CURLOPT_VERBOSE 		=> true,
					CURLOPT_RETURNTRANSFER 	=> true,
					CURLOPT_FOLLOWLOCATION 	=> true,
					CURLOPT_CONNECTTIMEOUT	=> 0.2,
				));

			    $raw = curl_exec($curlHandle);

			    if($raw !== false){
			    	$contentType	= curl_getinfo($curlHandle, CURLINFO_CONTENT_TYPE);
			    	$size			= curl_getinfo($curlHandle, CURLINFO_HEADER_SIZE);
			    	$headers		= mb_substr($raw, 0, $size);
			    	$contents		= mb_substr($raw, $size);
					$saveFolder		= '/media/upload/social/'.implode('/', str_split(rand(10000, 99999), 1));

					# YOU-TUBE.COM
					#
					if($url['host'] == 'www.youtube.com' && strlen($raw) > 50){
						$json = json_decode($raw, true);

						$opengraph[] = array(
							'og:site_name' 		=> 'Youtube',
				            'og:title'			=> $json['entry']['title']["\$t"],
				            'og:url'			=> $yturl,
				            'og:image'			=> $json['entry']["media\$group"]["media\$thumbnail"][0]['url'],
				            'og:video'			=> $embed,
				            'og:video:type'		=> 'application/x-shockwave-flash',
							'og:video:width'	=> $json['entry']["media\$group"]["media\$thumbnail"][0]['width'],
							'og:video:height'	=> $json['entry']["media\$group"]["media\$thumbnail"][0]['height'],
						);

					}else
					
					# GENERIC IMAGE
					#
					if(strpos($contentType, 'image/') !== false){
						umask(0);
						$saveFile	= KROOT.$saveFolder.'/'.uniqid('ext_').'.'.substr($contentType, 6);
						if(!file_exists(dirname($file))) mkdir(dirname($saveFile), 0755, true);
						file_put_contents($saveFile, $contents);
						$saveSizes	= GetImageSize($saveFile);

						$opengraph[] = array(
				            'og:url'			=> $myurl,
				            'og:image'			=> str_replace(KROOT, NULL, $saveFile),
							'og:image:height'	=> $saveSizes[1],
							'og:image:width'	=> $saveSizes[0]
						);

					}else
					
					# HTML Page
					#
					if(strpos($contentType, 'text/') !== false){

						libxml_use_internal_errors(true);
						$doc = new DomDocument();
						$doc->loadHTML($raw);
						$xpath = new DOMXPath($doc);
						$query = '//*/meta[starts-with(@property, \'og:\')]';
						$metas = $xpath->query($query);

						if(sizeof($metas) > 0){
							foreach($metas as $meta){
								$og[$meta->getAttribute('property')] = $meta->getAttribute('content');
							}
							$opengraph[] = $og;
							unset($og);
						}

					}
				}


				curl_close($curlHandle);
			}
		}
	}else{
		return false;
	}
	
#	die($this->pre($opengraph));

	# SAVE
	#
	if($opt['type'] == 'post'){
		$table	= 'k_socialpost';
		$ref	= 'id_socialpost';
		$field	= 'socialPostOpenGraph';
		$fview	= 'socialPostDataView';
	}else
	if($opt['type'] == 'message'){
		$table	= 'k_socialmessage';
		$ref	= 'id_socialmessage';
		$field	= 'socialMessageOpenGraph';
		$fview	= 'socialMessageDataView';
	}

	$def = array($table => array(
		$fview => array('value'	=> $view),
		$field => array('value' => addslashes(json_encode($opengraph)))
	));

	$this->dbQuery($this->dbUpdate($def)." WHERE ".$ref.'='.$opt['id']);
	#$this->pre($opengraph, $this->db_query, $this->db_error);	
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function indent($json) {

    $result      = '';
    $pos         = 0;
    $strLen      = strlen($json);
    $indentStr   = "\t";
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = true;

    for ($i=0; $i<=$strLen; $i++) {

        // Grab the next character in the string.
        $char = substr($json, $i, 1);

        // Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;
        
        // If this character is the end of an element, 
        // output a new line and indent the next line.
        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        // Add the character to the result string.
        $result .= $char;

        // If the last character was the beginning of an element, 
        // output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }
            
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        $prevChar = $char;
    }
    return str_replace("\/", "/", $result);
}


} ?>