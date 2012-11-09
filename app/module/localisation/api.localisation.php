<?php
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Last release		2011.01.06
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

class localisation extends coreApp {

function __clone(){}
public function localisation(){}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function localisationGet($opt=array()){

	if($opt['label'] != NULL){
		$dbMode	= 'dbMulti';
		$cond[]	= "label = '".$opt['label']."'";
	}else
	if($opt['getMaster']){
		$dbMode	= 'dbMulti';
	}else
	if($opt['getSlave'] && $opt['master'] != NULL){
		$dbMode	= 'dbMulti';
		$cond[]	= "label LIKE '".$opt['master']."%'";
	}else{
		$dbMode = 'dbMulti';
	}


	if(sizeof($cond) > 0) $where = " WHERE ".implode(" AND ", $cond)." ";

	$localisation = $this->$dbMode("SELECT * FROM k_localisation ".$where."  ORDER BY label ASC");

	if($opt['debug']) $this->pre($this->db_query, $this->db_error, $localisation);

	if($opt['getMaster']){
		foreach($localisation as $e){
			list($master, $slave) = explode('_', $e['label']);
			$out[$master] = '';
		}
		if(is_array($out)) $localisation = array_keys($out);
	}else
	
	if($opt['getSlave']){
		foreach($localisation as $e){
			if(preg_match("#^".$opt['master']."_(.*)#i", $e['label'], $r)){
				$out[$r[1]] = '';
			}
		#	$out[substr($e['label'], strlen($opt['master'])+1)] = '';
		}
		$localisation = array_keys($out);
		sort($localisation);
	}else
	if($opt['empty']){
		foreach($localisation as $e){
			$sort[$e['language']] = $e;
		}

		$label		= $e['label'];
		$country 	= $this->countryGet(array('is_used' => true));

		foreach($country as $e){
			if($sort[$e['iso']] != NULL){
				$sort[$e['iso']]['countryLanguage'] = $e['countryLanguage'];
				$out[] = $sort[$e['iso']];
			}else{
				$out[] = array(
					'countryLanguage'	=> $e['countryLanguage'],
					'language' 			=> $e['iso'],
					'label'				=> $label,
					'translation'		=> ''
				);
			}
		}
		
		$localisation = $out;
		sort($localisation);
	}

	
	if($opt['debug']) $this->pre($localisation);
	
	return $localisation;

} } ?>