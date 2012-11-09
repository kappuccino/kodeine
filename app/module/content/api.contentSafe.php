<?php

class contentSafe extends coreApp {

public function contentSafe(){}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Retourne TRUE si l'un des FIELD d'un CONTENT contient un lien qui donne un 404
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function contentBrokenLink($id_content, $opt){
	
	$content = $this->apiLoad('content')->contentGet(array(
		'id_content'	=> $id_content,
		'raw'			=> true
	));
	
	if(intval($content['id_content']) == 0) return false;
	
	$fields = $this->apiLoad('field')->fieldGet(array(
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


} ?>