<?php

class socialAlert extends social{

function __clone(){}
function socialActivity(){}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialAlertGet($opt){

	if($opt['debug']) $this->pre("OPTION", $opt);

	$dbMode = 'dbMulti';

	// GET id_socialalert
	if(array_key_exists('id_socialalert', $opt)){
		if(intval($opt['id_socialalert']) > 0){
			$dbMode = 'dbOne';
			$cond[] = "id_socialalert=".$opt['id_socialalert'];
		}else{
			if($opt['debug']) $this->pre("ERROR: ID_SOCIALALERT (NUMERIC)", "GIVEN", var_export($opt['id_socialalert'], true));
			return array();
		}		
	}

	// GET id_user
	if(array_key_exists('id_user', $opt)){
		if(intval($opt['id_user']) > 0){
			$cond[] = "id_user=".$opt['id_user'];
		}else{
			if($opt['debug']) $this->pre("ERROR: ID_USER (NUMERIC)", "GIVEN", var_export($opt['id_user'], true));
			return array();
		}		
	}


	# Field
	#
	$fields = $this->apiLoad('field')->fieldGet(array('socialAlert' => true));
	foreach($fields as $f){
		$fieldKey[$f['fieldKey']] = $f;
		if($f['is_search'])												$fieldSearch[]		= $f;
		if($f['fieldType'] == 'content' && $f['fieldContentType'] > 0)	$fieldAssoContent[] = $f;
		if($f['fieldType'] == 'user')									$fieldAssoUser[]	= $f;
	}

#	$this->pre($fields, $fieldKey);


	# Search (version simplifie)
	#
	if(is_array($opt['search'])){
		foreach($opt['search'] as $e){
			if($e['searchField'] > 0){
				$tmp[] = $this->dbMatch("k_socialalert.field".$e['searchField'],	$e['searchValue'], $e['searchMode']);
			}else
			if($fieldKey[$e['searchField']]['id_field'] != NULL){
				$tmp[] = $this->dbMatch("k_socialalert.field".$fieldKey[$e['searchField']]['id_field'], $e['searchValue'], $e['searchMode']);
			}
		}
		if(sizeof($tmp) > 0) $cond[] = "(".implode(' '.$searchLink.' ', $tmp).")";
	}


	# LIMITATION & ORDER
	#
	if($dbMode == 'dbMulti'){
		$order = "\nORDER BY ".(($opt['order'] != '' && $opt['direction'] != '')
			? $opt['order']." ".$opt['direction']
			: "k_socialalert.id_socialalert DESC");

		$limit = "\nLIMIT ".(($opt['offset']  != '' && $opt['limit'] != '')
			? $opt['offset'].",".$opt['limit']
			: "0,50");

		if($opt['noLimit'] == true) unset($limit);
	}else{
		$flip = true;
	}
	

	# EXECUTE
	#
	$field	= "k_socialalert.*";
	$where	= is_array($cond) ? "\nWHERE\n".implode(" AND ", $cond) : NULL;
	$inner	= is_array($join) ? "\n".implode("\n", $join)."\n" : NULL;

	$alert	= $this->$dbMode("SELECT ".$field." FROM k_socialalert ". $inner . $where .$order . $limit);
	if($opt['debug']) $this->pre("QUERY", $this->db_query, "ERROR", $this->db_error, "DATA", $alert);


	# FORMAT
	#
	if(sizeof($alert) > 0){
		if($flip) $alert = array($alert);
		
		$alert = $this->socialAlertMapping(array(
			'data'		=> $alert,
			'fields'	=> $fields
		));

		if($flip) $alert = $alert[0];
	}

	return $alert;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialAlertSet($opt){

	if($opt['debug']) $this->pre($opt);

	# NEW !
	#
	if($opt['id_socialalert'] == NULL){
		$this->dbQuery("INSERT INTO k_socialalert (socialAlertName) VALUES ('TEMP+NAME')");
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	
		$id_socialalert = $this->db_insert_id;
	}else{
		$id_socialalert = $opt['id_socialalert'];
	}
	$this->id_socialalert	= $id_socialalert;


	# CORE
	#
	$query = $this->dbUpdate(array('k_socialalert' => $opt['core']))." WHERE id_socialalert=".$id_socialalert;
	$this->dbQuery($query);
	if($opt['debug']) $this->pre("QUERY", $this->db_query, "ERROR", $this->db_error);

	# FIELD
	#
	if(sizeof($opt['field']) > 0){

		# Si on utilise le KEY au lieu des ID
		$fields = $this->apiLoad('field')->fieldGet(array('socialAlert' => true));
		foreach($fields as $e){
			$fieldsKey[$e['fieldKey']] = $e;
		} $fields = $fieldsKey;

		unset($def);
		$apiField = $this->apiLoad('field');

		foreach($opt['field'] as $id_field => $value){
			if(!is_integer($id_field)) $id_field = $fields[$id_field]['id_field'];
			
			if(intval($id_field) > 0){
				$value = $apiField->fieldSaveValue($id_field, $value);
				$def['k_socialalert']['field'.$id_field] = array('value' => $value);
			}
		}

		$this->dbQuery($this->dbUpdate($def)." WHERE id_socialalert=".$id_socialalert);
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	}

	return true;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialAlertRemove($opt){

	$id_socialalert = $opt['id_socialalert'];
	if(intval($id_socialalert) == 0) return false;
	
	$this->dbQuery("DELETE FROM k_socialalert WHERE id_socialalert=".$id_socialalert);
	return true;
}



/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialAlertMapping($opt){

	$data = $opt['data'];

	foreach($data as $n => $e){
	
		$data[$n]['socialAlertData'] = json_decode($e['socialAlertData'], true);
		if(!is_array($data[$n]['socialAlertData'])) $data[$n]['socialAlertData'] = array();

		if(sizeof($opt['fields']) > 0){
			foreach($opt['fields'] as $f){

				$param	= json_decode($f['fieldParam'], true);
				$v		= $e['field'.$f['id_field']];
				if(substr($v, 0, 2) == $this->splitter && substr($v, -2) == $this->splitter && $v != $this->splitter){
					$v = explode($this->splitter, substr($v, 2, -2));
				}

				if($f['fieldType'] == 'media'){
					$v = json_decode($v, true);
					if(sizeof($v) > 0 && is_array($v)){
						foreach($v as $e){
							$media[$e['type']][] = $this->mediaInfos($e['url']);
						}
						$data[$n]['field'][$f['fieldKey']] = $media;
					}
				}else
				if($f['fieldType'] == 'user'){
					$v = is_array($v) ? $v : array(intval($v));
					foreach($v as $id_user){
						$tmp_ = $this->dbOne("SELECT id_user FROM k_user WHERE id_user=".$id_user);
						if($tmp_['id_user'] != '') $tmp[] = $tmp_;
					}

					$data[$n]['field'.$f['id_field']] = (($param['type'] == 'solo' && sizeof($v) == 1) ? $tmp[0] : $tmp);
				}else
				if($f['fieldType'] == 'content'){
					$v = is_array($v) ? $v : array(intval($v));
					foreach($v as $id_content){
						$tmp_ = $this->dbOne("SELECT * FROM k_contentdata WHERE id_content=".$id_content);
						if($tmp_['id_content'] != '') $tmp[] = $tmp_;
					}

					$data[$n]['field'][$f['fieldKey']] = (($param['type'] == 'solo' && sizeof($v) == 1) ? $tmp[0] : $tmp);
				}else{
					$data[$n]['field'][$f['fieldKey']] = $v;
				}

				unset($data[$n]['field'.$f['id_field']], $media, $tmp, $tmp_, $v, $param);
			}
		}

	}

	return $data;
}




































} ?>