<?php

class coreOpengraph extends coreApp{

function openGraphVideo($view){

	# CHECK (recoder les blanc avant le data, hack, je devrai changer la regexp plutot
	#
	if(preg_match_all("#(http(s)?://(.*)\\s)#msU", ' '.$view.' ', $add, PREG_SET_ORDER)){

		foreach($add as $a){

			$myurl	= trim($a[0]);
			$url 	= parse_url($myurl);
			$view	= str_replace($myurl, "<a href=\"".$myurl."\" target=\"_blank\">".$myurl."</a>", $view);

			if($url['host'] == 'www.youtube.com'){
				parse_str($url['query'], $arr);
				if($arr['v'] != ''){
					$ytid   = $arr['v'];
					$myurl	= 'http://gdata.youtube.com/feeds/api/videos/'.$ytid.'?alt=json';
					$yturl	= 'http://www.youtube.com/?v='.$ytid;
					$embed	= 'http://www.youtube.com/embed/'.$ytid.'?autoplay=1&rel=0';
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
				#	$saveFolder		= '/media/upload/social/'.implode('/', str_split(rand(10000, 99999), 1));

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
							'yt:id'             => $ytid
						);

					}else

					# GENERIC IMAGE
					#
					/*if(strpos($contentType, 'image/') !== false){
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

					}else*/

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

					        // VIMEO
						    if($url['host'] == 'www.vimeo.com' OR $url['host'] == 'vimeo.com'){
							    if(preg_match("#/([0-9]*)$#", $og['og:url'], $m)) $og['vimeo:id'] = $m[1];
							}

							$opengraph[] = $og;
							unset($og);
						}

				    }


				curl_close($curlHandle);
			}
			}
		}
	}else{
		return array();
	}

	return $opengraph;
}

}