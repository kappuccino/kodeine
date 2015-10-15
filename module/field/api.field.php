<?php

class field extends coreApp {

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
public function fieldGet($opt=array()){

	if($opt['debug']) $this->pre("OPT", $opt);

	$dbMode = 'dbMulti';
	if(isset($opt['is_column'])) 		$cond[] = "is_column=".$opt['is_column'];
	if(isset($opt['fieldShowForm']))	$cond[] = 'fieldShowForm='.(($opt['fieldShowForm']) ? '1' : '0');


	# On demande des FIELD associe (category, chapter, type etc...)
	#
	if($opt['category']){							$myIds 	= $this->fieldAffectGet('category');			}else
	if($opt['chapter']){							$myIds 	= $this->fieldAffectGet('chapter');				}else
	if($opt['user']){								$myIds 	= $this->fieldAffectGet('user');				}else
	if($opt['businessCart']){						$myIds	= $this->fieldAffectGet('businessCart');		}else
	if($opt['businessCartLine']){					$myIds	= $this->fieldAffectGet('businessCartLine');	}else
	if($opt['socialForum']){						$myIds 	= $this->fieldAffectGet('socialForum');			}else
	if($opt['socialCircle']){						$myIds 	= $this->fieldAffectGet('socialCircle');		}else
	if($opt['socialEvent']){						$myIds 	= $this->fieldAffectGet('socialEvent');			}else
	if($opt['socialAlert']){						$myIds 	= $this->fieldAffectGet('socialAlert');			}else
	if($opt['socialActivity']){						$myIds 	= $this->fieldAffectGet('socialActivity');		}else
	if($opt['socialMessage']){						$myIds 	= $this->fieldAffectGet('socialMessage');		}else
	if($opt['socialEventUserData']){				$myIds 	= $this->fieldAffectGet('socialEventUserData');	}else
	if($opt['id_group'] != NULL){					$myIds 	= $this->fieldAffectGet('usergroup', 	$opt['id_group']);	}else
	if($opt['itemField']  && $opt['id_type'] > 0){	$myIds 	= $this->fieldAffectGet('typeitem', 	$opt['id_type']);	}else
	if($opt['albumField'] && $opt['id_type'] > 0){	$myIds 	= $this->fieldAffectGet('typealbum', 	$opt['id_type']);	}else
	if($opt['id_type'] > 0){						$myIds 	= $this->fieldAffectGet('type', 		$opt['id_type']);	}else

	# On demandes des FIELD directement
	#
	if(is_array($opt['id_field'])){
		if(is_integer($opt['id_field'][0])){
			$idx = $opt['id_field'];
		}else{
			foreach($opt['id_field'] as $e){
				$idx[] = "'".$e."'";
			}
		}
		$cond[] = "id_field IN(".implode(',', $idx).")";
	}else
	if($opt['id_field'] > 0){
		$dbMode = 'dbOne';
		$cond[] = "k_field.id_field=".$opt['id_field'];
	}else
	if($opt['fieldKey'] != NULL){
		if(is_array($opt['fieldKey'])){
			foreach($opt['fieldKey'] as $e){
				$keys[] = "'".$e."'";
			}
			$cond[] = "fieldKey IN(".implode(',', $keys).")";
		}else{
			$dbMode = 'dbOne';
			$cond[] = "fieldKey='".$opt['fieldKey']."'";
		}
	}else{
		$order  = " ORDER BY `fieldName` ASC";
		$do	= true;
	}

	# Quand on demande un ARRAY de id_field
	#
	if(isset($myIds)){
		if(sizeof($myIds) == 0){
			if($opt['debug']) $this->pre("MYIDS IS EMPTY");
			return array();
		}
		$cond[]	= "k_field.id_field IN(".implode(',', $myIds).")";
		$order 	= "\nORDER BY FIND_IN_SET(k_field.id_field, '".implode(',', $myIds)."') ASC";
	}

	if(sizeof($cond) > 0) $where = "WHERE ".implode(" AND ", $cond)." ";

	$query	= "SELECT * FROM k_field\n".$where.$order;
	$field	= $this->$dbMode($query);

	/*if($opt['utf8']){
		if($dbMode == 'dbOne'){
			$field = array_map('utf8_encode', $field);
		}else
		if($dbMode == 'dbMulti'){
			foreach($field as $idx => $e){
				$field[$idx] = array_map('utf8_encode', $e);
			}
		}
	}*/
	
	if($opt['debug']) $this->pre("QUERY", $this->db_query, "ERROR", $this->db_error, "DATA", $field);

	return $field;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
public function fieldSet($id_field, $def){

	if(!$this->formValidation($def)) return false;

	$query = (intval($id_field) > 0)
		? $this->dbUpdate($def)." WHERE id_field=".$id_field
		: $this->dbInsert($def);

	$this->dbQuery($query);
	#$this->pre($this->db_query, $this->db_error);

	if($this->db_error != NULL) return false;
	$this->id_field = ($id_field > 0) ? $id_field : $this->db_insert_id;

	$this->apiLoad('field')->fieldAffectType($this->id_field);

	return true;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
public function fieldRemove($id_field){

	// Remove FIELD
	$this->dbQuery("DELETE FROM k_field			WHERE id_field=".$id_field);
	$this->dbQuery("DELETE FROM k_fieldchoice	WHERE id_field=".$id_field);

	// Remove AFFECT
	$this->fieldAffectRemove($id_field);
	
	// Clean CACHE
	$this->fieldCacheBuild();
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
public function fieldCacheBuild(){

	return true;

	# TYPES
	#
	$types	= $this->apiLoad('type')->typeGet();
	$fields	= array(
		'type'	=> array(),
		'item'	=> array(),
		'album'	=> array()
	);

	foreach($types as $n => $e){
		unset($types[$n]['typeFormLayout']);

		if($e['is_gallery']){
			$flds['item']  = $this->apiLoad('field')->fieldGet(array('id_type' => $e['id_type'], 'itemField'  => true));
			$flds['album'] = $this->apiLoad('field')->fieldGet(array('id_type' => $e['id_type'], 'albumField' => true));
		}else{
			$flds['type']  = $this->apiLoad('field')->fieldGet(array('id_type' => $e['id_type']));
		}

		foreach($flds as $type => $fld){
			foreach($fld as $nf => $f){
				unset($f['fieldParam'], $f['fieldStyle']);
				$f['fieldName'] = $f['fieldName'];
				$fields[$type][$e['id_type']][] = $f;
			}
		}
	}

	# CATEGORY + CHAPTER + USER
	#
	$category	= $this->fieldGet(array('category' => true));
	$chapter	= $this->fieldGet(array('chapter' => true));
	$user		= $this->fieldGet(array('user' => true));


	# SAVE ALL
	#
	$this->configSet('content', 'jsonCacheType',  			addslashes(json_encode($types)));
	$this->configSet('field',   'jsonCacheFieldType',		addslashes(json_encode($fields['type'])));
	$this->configSet('field',   'jsonCacheFieldItem', 		addslashes(json_encode($fields['item'])));
	$this->configSet('field',   'jsonCacheFieldAlbum',		addslashes(json_encode($fields['album'])));
	$this->configSet('field', 	'jsonCacheFieldCategory',	addslashes(json_encode($category)));
	$this->configSet('field', 	'jsonCacheFieldChapter',	addslashes(json_encode($chapter)));
	$this->configSet('field', 	'jsonCacheFieldUser',		addslashes(json_encode($user)));
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
public function fieldChoiceGet($opt){

	if($opt['id_field'] > 0){
		$dbMode = 'dbMulti';
		$order  = " ORDER BY choiceOrder ASC";
		$cond[] = "id_field=".$opt['id_field'];
	}else
	if($opt['id_fieldchoice'] > 0){
		$dbMode = 'dbOne';
		$cond[] = "id_fieldchoice=".$opt['id_fieldchoice'];
	}else{
		return array();
	}

	if(sizeof($cond) > 0) $where = "WHERE ".implode(" AND ", $cond)." ";

	$field = $this->$dbMode("SELECT * FROM k_fieldchoice ". $where . $order);
	
	if($opt['debug']) $this->pre($this->db_query, $this->db_error, $field);

	return $field;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
public function fieldChoiceSet($id_field, $es){

	if(sizeof($es) == 0) return true;

	$pos = 0;

	foreach($es as $id_fieldchoice => $e){
		if($id_fieldchoice == 'remove'){
			foreach($e as $r){
				$this->dbQuery("DELETE FROM k_fieldchoice WHERE id_fieldchoice=".$r);
			}
		}else
		if($id_fieldchoice == 'new'){

			$last = $this->dbOne("SELECT MAX(choiceOrder) AS dernier FROM k_fieldchoice WHERE id_field=".$id_field);
			$last = $last['dernier'] + 1;

			foreach($e as $n){
				if(trim($n) != ''){
					$last++;
					$def['k_fieldchoice'] = array(
						'id_field' 		=> array('value' => $id_field),
						'choiceOrder'	=> array('value' => $last),
						'choiceValue'	=> array('value' => trim($n))
					);
					$this->dbQuery($this->dbInsert($def));
					#$this->pre($this->db_query, $this->db_error);
				}
			}
		}else{
			$previous = $this->fieldChoiceGet(array('id_fieldchoice' => $id_fieldchoice));

			$def['k_fieldchoice'] = array(
				'choiceOrder'	=> array('value' => $pos),
				'choiceValue'	=> array('value' => $e)
			);

			if($previous['choiceValue'] == $e){
				unset($def['k_fieldchoice']['choiceValue']);
			}else{
				$this->apiLoad('field')->fieldAffectValue($id_field, $previous['choiceValue'], $e);
			}

			$this->dbQuery($this->dbUpdate($def)." WHERE id_fieldchoice=".$id_fieldchoice);
			#$this->pre($this->db_query, $this->db_error);
			$pos++;
		}
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
public function fieldSaveValue($id_field, $value, $opt=array()){

	$field_ 	= $this->fieldGet(array('id_field' => $id_field));
	$splitter	= $this->splitter;

	# Keyword
	#
	if($field_['fieldType'] == 'keyword'){
		$keys = array_map('trim', explode(',', trim($value)));
		$keys = array_unique($keys, SORT_STRING);
		$keys = array_filter($keys);

		if(sizeof($keys) > 0){
			$save = array();
			foreach($keys as $k){
				array_push($save, "(".$id_field.", ".$opt['id_content'].", '".$opt['language']."', '".addslashes($k)."')");
			}
		}

		// keyword for content
		if(intval($opt['id_content']) > 0 && $opt['language'] != ''){
			$this->dbQuery("DELETE FROM k_contentkeyword WHERE id_field=".$id_field." AND id_content=".$opt['id_content']." AND language='".$opt['language']."'");
			#$this->pre($this->db_query, $this->db_error);

			if(sizeof($save) > 0){
				$this->dbQuery("INSERT IGNORE INTO k_contentkeyword (id_field, id_content, language, keyword) VALUES ".implode(",\n", $save));
				#$this->pre($this->db_query, $this->db_error);
			}
		}
		
		// Clean
		$value = implode(', ', $keys);
	}else


	# Array (multi)
	#
	if(is_array($value)){

		// Clean empty array element and duplicate content
		$used = array();
		foreach($value as $e){
			$e = trim($e);
			if($e != '' && !in_array($e, $used)){
				$tmp[]  = $e;
				$used[] = $e;
			}
		}
		$value = is_array($tmp) ? $tmp : array();
		
		unset($built, $tmp, $used);

		if($field_['fieldType'] == 'multichoice'){
			foreach($value as $id_fieldchoice){
				if($id_fieldchoice > 0){
					$tmp 	 = $this->fieldChoiceGet(array('id_fieldchoice' => $id_fieldchoice));
					$built[] = $tmp['choiceValue'];
				}
			}
		}else
		if($field_['fieldType'] == 'category'){
			$built = $value;
			$param = json_decode($field_['fieldParam'], true);
			if($param['type'] == 'solo') $splitter = '';
		}else
		if($field_['fieldType'] == 'user'){

			if($opt['id_user'] != NULL){
				$this->apiLoad('user')->userAssoUserSet($opt['id_user'], $field_['id_field'], $value);
			}else
			if($opt['id_content'] != NULL){
				$content_ = $this->dbOne("SELECT id_type FROM k_content WHERE id_content=".$opt['id_content']);
				$this->apiLoad('content')->contentAssoUserSet($opt['id_content'], $content_['id_type'], $field_['id_field'], $value);
			}
			
			$built = $value;
			$param = json_decode($field_['fieldParam'], true);
			if($param['type'] == 'solo') $splitter = '';

		}else
		if($field_['fieldType'] == 'content'){
	
			if($opt['id_user'] != NULL){
				$this->apiLoad('user')->userAssoSet($opt['id_user'], $field_['id_field'], $field_['fieldContentType'], $value);
			}else
			if($opt['id_content'] != NULL){
				$content_ = $this->dbOne("SELECT id_type FROM k_content WHERE id_content=".$opt['id_content']);
				$this->apiLoad('content')->contentAssoSet($opt['id_content'], $content_['id_type'], $field_['id_field'], $field_['fieldContentType'], $value);
			}

			$built = $value;
			$param = json_decode($field_['fieldParam'], true);
			if($param['type'] != 'multi') $splitter = '';

		}else
		if($field_['fieldType'] == 'social-forum'){
			$built = $value;
			$param = json_decode($field_['fieldParam'], true);
			if($param['type'] != 'multi') $splitter = '';
	
		}else
		if($field_['fieldType'] == 'dbtable'){
			$built = $value;
			$param = json_decode($field_['fieldParam'], true);
			if($param['type'] != 'multi') $splitter = '';
		}


		// Merge (la valeur du champ de la table content_x et non la table liee)
		$value = (is_array($built) && sizeof($built) > 0) ? $splitter.implode($splitter, $built).$splitter : '';
	}
	
	return $value;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
public function fieldTypeGet(){

	$def = array(
		'texte'			=> array('name' => 'Texte sur plusieurs lignes', 			 'view' => 'texte',			'type' => 'MEDIUMTEXT'),
		'texte-line'	=> array('name' => 'Texte sur une  ligne (255 caracteres)',  'view' => 'texte',			'type' => 'VARCHAR(255)'),
		'integer'		=> array('name' => 'Nombre entier',							 'view' => 'integer',		'type' => 'MEDIUMINT(64)'),
		'boolean'		=> array('name' => 'Booleen',								 'view' => 'boolean',		'type' => 'TINYINT(1)'),
		'date' 			=> array('name' => 'Date',									 'view' => 'date',			'type' => 'DATE'),
		'onechoice' 	=> array('name' => 'Menu deroulant',						 'view' => 'choice',		'type' => 'MEDIUMTEXT'),
		'multichoice'	=> array('name' => 'Plusieurs choix selectionnables',		 'view' => 'choice', 		'type' => 'MEDIUMTEXT'),
		'content'		=> array('name' => 'Relier un Type',						 'view' => 'content', 		'type' => 'MEDIUMTEXT'),
		'user'			=> array('name' => 'Relier un Utilisateur',					 'view' => 'user', 			'type' => 'MEDIUMTEXT'),
		'media'			=> array('name' => 'Media',									 'view' => 'media',			'type' => 'MEDIUMTEXT'),
		'category'		=> array('name' => 'Categorie',								 'view' => 'category', 		'type' => 'MEDIUMINT(64)'),
		'chapter'		=> array('name' => 'Arborescence',							 'view' => 'chapter',		'type' => 'MEDIUMINT(64)'),
		'content-type'	=> array('name' => 'Liste des Types de contenu',			 'view' => 'contentType',	'type' => 'VARCHAR(255)'),
		'social-forum'	=> array('name' => 'Social - Forum',						 'view' => 'socialForum',	'type' => 'MEDIUMTEXT'),
		'dbtable'		=> array('name' => 'Table (bdd)',							 'view' => 'dbtable',		'type' => 'MEDIUMTEXT'),
		'keyword'		=> array('name' => 'Mot clé',   						     'view' => 'dbtable',		'type' => 'MEDIUMTEXT'),
		'code'          => array('name' => 'Code',   						         'view' => 'code',		    'type' => 'MEDIUMTEXT')
	);

	return $def;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
public function fieldAffectGet($map, $id=0){
	$def = $this->dbOne("SELECT fields FROM k_fieldaffect WHERE map='".$map."' AND id=".intval($id));
	$def = json_decode($def['fields']);
	return is_array($def) ? $def : array();
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
public function fieldAffectSet($map, $fields, $id=0){
	
	if($map == '')			die('map is null');
	if(!is_array($fields))	die('field must be an array');

	if(is_array($fields)){
		foreach($fields as $idx => $e){
			$fields[$idx] = intval($e);
		}
	}else{
		$fields = array();
	}

	$json = json_encode($fields);

	$this->dbQuery("INSERT INTO k_fieldaffect (map,id,fields) VALUES ('".$map."', ".intval($id).", '".$json."') ON DUPLICATE KEY UPDATE fields='".$json."'");
	#$this->pre($this->db_query, $this->db_error);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
public function fieldAffectPush($map, $field, $id=0){

	// Get
	$mymap		= $this->dbOne("SELECT * FROM k_fieldaffect WHERE map='".$map."' AND id='".$id."'");
	
	// Add
	$fields		= json_decode($mymap['fields']);
	$fields		= is_array($fields) ? $fields : array();
	$fields[]	= intval($field);
	$fields		= array_unique($fields);

	// Save
	$this->fieldAffectSet($map, $fields, $id);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
public function fieldAffectPop($map, $field, $id=0){

	// Get
	$map = $this->dbOne("SELECT * FROM k_fieldaffect WHERE map='".$map."' AND id='".$id."'");
	
	// Remove
	$tmp	= array(); 
	$fields	= json_decode($map['fields']);

	#$this->pre($fields);

	if(is_array($fields) && sizeof($fields) > 0){
		foreach($fields as $f){
			if($f != $field) $tmp[] = intval($f);
		}
	}
	
	#$this->pre("POPed", $tmp);

	// Save
	$this->fieldAffectSet($map['map'], $tmp, $map['id']);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
public function fieldAffectNew($map, $id_field, $id=0){

	$table = $this->fieldAffectMapToTable($map, $id);

	// Faire un ALTER sur la table
	$myFields	= $this->dbMulti("SHOW COLUMNS FROM ".$table);
	$found		= false;

	foreach($myFields as $myField){
		if($myField['Field'] == 'field'.$id_field) $found = true;
	}

	// Uniquement si NECESSAIRE
	if(!$found){
		$this->dbQuery("ALTER TABLE ".$table." ADD field".$id_field." VARCHAR(255) NOT NULL");
		#$this->pre($this->db_query, $this->db_error);
	}
	
	return true;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
public function fieldAffectRemove($id_field, $map='ALL', $id=0){

	#$this->pre($id_field, $map);

	# On recupere les MAP ou on utilise celle passe en argument
	#
	if($map == 'ALL'){
		$def = $this->dbMulti("SELECT * FROM k_fieldaffect WHERE map != 'usergroup'");
		
		foreach($def as $e){
			$e['fields'] = json_decode($e['fields'], true);
			if(in_array($id_field, $e['fields'])) $used[] = $e;
		}
	}else{
		$used[] = $this->dbOne("SELECT * FROM k_fieldaffect WHERE map = '".$map."' AND id=".intval($id));
	}


	#$this->pre("On trouve ce champs", $id_field, "dans", $used);

	# Si je n'ai aucune MAP qui utilise ce champs ...
	#
	if(sizeof($used) == 0) return false;


	# Pour chacune des MAP on supprime le FIELD pour les TABLE correspondante
	#
	foreach($used as $e){

		// Droper le FIELD
		$table = $this->fieldAffectMapToTable($e['map'], $e['id']);
		$this->dbQuery("ALTER TABLE ".$table." DROP field".$id_field);
		#$this->pre($this->db_query, $this->db_error);

		// Save in DB
		$this->fieldAffectPop($e['map'], $id_field, $e['id']);
	}
	
	return true;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
public function fieldAffectType($id_field, $table=NULL){

	# Obtenir notre base de type de champs
	#
	$types 	= $this->fieldTypeGet();
	$field 	= $this->fieldGet(array('id_field' => $id_field));
	$param 	= json_decode($field['fieldParam'], true);
	$type	= $types[$field['fieldType']]['type'];
	
	if($field['fieldType'] == 'category' && $param['type'] == 'multi'){
		$type = 'VARCHAR(255)';
	}

	# Si je suis un FIELD de type CONTENT alors je n'ai pas de CHAMP propre !
	# 
	//	if($field['fieldType'] == 'content') return true;
	//	Information a verifier pour CONTENT + USER le champs doit rester un MEDIUMTEXT


	# Trouver les TABLES concernees
	#
	if($table != NULL){
		$use[] = $table;
	}else{
		$def = $this->dbMulti("SELECT * FROM k_fieldaffect WHERE map != 'usergroup'");
		foreach($def as $e){
			$e['fields'] = json_decode($e['fields'], true);
			if(in_array($id_field, $e['fields'])){
				$use[] = $this->fieldAffectMapToTable($e['map'], $e['id']);
			}
		}
	}


	# Si je n'ai besoin de modifier aucune TABLES alors...
	#
	if(sizeof($use) == 0) return true;


	# Mettre a jour toute les TABLES qui sont concernees
	#
	foreach($use as $e){
		$fields = $this->dbMulti("SHOW COLUMNS FROM `".$e."`");
		foreach($fields as $f){
			if($f['Field'] == 'field'.$id_field){
				if(strtolower($f['Type']) != strtolower($type)){
					$this->dbQuery("ALTER TABLE `".$e."` CHANGE `field".$id_field."` `field".$id_field."` ".$type." NOT NULL");
					#$this->pre($this->db_query, $this->db_error);
				}
			}
		}
	}

	return true;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
public function fieldAffectValue($id_field, $oldValue, $newValue){

	# Trouver le FIELD
	#
	$field = $this->fieldGet(array('id_field' => $id_field));
	$multiChoice = ($field['fieldType'] == 'multichoice');


	# Trouver les tables qui utilisent ce champs
	#
	$use = $this->fieldAffectTableForField($id_field);


	# On a pas besoin d'aller plus loin si on a aucun champs a modifier
	#
	if(sizeof($use) == 0) return;

	# On y va
	#
	foreach($use as $e){

		if($multiChoice){
			$old = addslashes($oldValue);
			$new = addslashes($newValue);

			$def[$e] = array(
				'field'.$id_field => array('function' => "REPLACE(field".$id_field.", '".$this->splitter.$old.$this->splitter."', '".$this->splitter.$new.$this->splitter."')")
			);

			$q = $this->dbUpdate($def)." WHERE field".$id_field." LIKE '%".$this->splitter.mysql_escape_string($oldValue).$this->splitter."%'";
		}else{

			$def[$e] = array(
				'field'.$id_field => array('value' => $newValue)
			);
			$q = $this->dbUpdate($def)." WHERE field".$id_field."='".mysql_escape_string($oldValue)."'";
		}
		
		$this->dbQuery($q);
		#$this->pre($this->db_query, $this->db_error, $this->db_affected_rows);
		unset($def);
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
public function fieldAffectMapToTable($map, $id=0){

	if($map == 'user'){						return 'k_userdata';			}else
	if($map == 'category'){					return 'k_categorydata';		}else
	if($map == 'chapter'){					return 'k_chapterdata';			}else

	if($map == 'socialCircle'){				return 'k_socialcircle';		}else
	if($map == 'socialForum'){				return 'k_socialforum';			}else
	if($map == 'socialEvent'){				return 'k_socialevent';			}else
	if($map == 'socialAlert'){				return 'k_socialalert';			}else
	if($map == 'socialActivity'){			return 'k_socialactivity';		}else
	if($map == 'socialMessage'){			return 'k_socialmessage';		}else
	if($map == 'socialEventUserData'){		return 'k_socialeventuserdata';	}else

	if($map == 'businessCart'){				return 'k_businesscart';		}else
	if($map == 'businessCartLine'){			return 'k_businesscartline';	}else

	if($map == 'type' 		&& $id > 0){	return 'k_content'.$id;			}else
	if($map == 'typealbum' 	&& $id > 0){	return 'k_contentalbum'.$id;	}else
	if($map == 'typeitem' 	&& $id > 0){	return 'k_contentitem'.$id;

	}else{
		die('NO TABLE FOUND, FATAL ERROR ('.var_export(func_get_args(), true).')');
	}

}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
public function fieldAffectTableForField($id_field){

	// Get
	$def 	= $this->dbMulti("SELECT * FROM k_fieldaffect WHERE map != 'usergroup'");
	$tables	= array();

	// Extract
	foreach($def as $e){
		$fields = json_decode($e['fields'], true);
		$fields	 = is_array($fields) ? $fields : array();

		if(in_array($id_field, $fields)){
			$tables[] = $this->fieldAffectMapToTable($e['map'], $e['id']);
		}
	}
	
	return $tables;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
public function fieldValidation($form){

	if(sizeof($form) == 0) return true;

	$fields = $this->fieldGet(array(
		'fieldKey'	=> array_keys($form),
		'debug' 	=> false
	));
	
	$compare = false;
	foreach($fields as $f){
		$fieldKey[$f['fieldKey']] = $f;

		if($f['is_needed']){
			$compare = true;
			$need[$f['id_field']] = true;
		}
	}

	// Rien de necessaire => OK
	if(!$compare) return true;

	// Si on envois fieldKey => Value il faut me transformer en id_field => Value
	$keys = array_keys($form);	
	if(intval($keys[0]) == 0){ // String ?
		foreach($form as $k => $v){
			$f = $fieldKey[$k];
			if(is_array($f)){
				$formID[$f['id_field']] = $v;
			}else{
				$this->formFieldNotFound[$k] = 'unknown field';
			}
		}
		// Flip Key to Id
		$form = $formID;
	}

	// On attends id_field => value
	$good = true;
	foreach($form as $id_field => $f){
		if($need[$id_field]){
			if(is_array($f) && sizeof($f) == 1 && $f[0] == ''){
				$this->formError['field'.$id_field] = true;
				$good = false;
			}else
			if(trim($f) == NULL){
				$this->formError['field'.$id_field] = true;
				$good = false;
			}
		}
	}

	return $good;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
public function fieldForm($id_field, $value, $opt=array()){

	# Traiter les OPTIONS
	#
	$name 		= !empty($opt['name']) ? $opt['name'] : 'field['.$id_field.']';
	$id			= !empty($opt['name']) ? $opt['id']	  : 'form-field-'.$id_field;
	$key		= (preg_match("#([0-9]){1,}#", $id_field)) ? '' : $id_field;
	$disabled	= ($opt['disabled']) ? "disabled=\"disabled\"" : NULL;

	# Recuperer le FIELD
	#
	if($opt['field'] != NULL){
		$field = $opt['field'];
	}else{
		$field = ($key != NULL)
			? $this->fieldGet(array('debug' => false, 'fieldKey'	=> $key))
			: $this->fieldGet(array('debug' => false, 'id_field'	=> $id_field));

		if($field['id_field'] == NULL) return "Impossible de trouver le champ";
		$field['fieldParam'] = json_decode($field['fieldParam'], true);
	}


	### Gerer les valeurs CHOICES 
	#
	if(is_array($opt['field']['fieldChoices'])){
		$choices = $opt['field']['fieldChoices'];
	}else
	if(in_array($field['fieldType'], array('onechoice', 'multichoice'))){
		$choices = $this->fieldChoiceGet(array('id_field' => $field['id_field']));
	}
	// Trouver la valeurs par defaut
	if($value == '' && is_array($choices) && sizeof($choices) > 0){
		foreach($choices as $e){
			if($e['choiceDefault']) $value = $e['choiceValue'];
		}
	}

# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
	
	### choix unique SELECT
	#
	if($field['fieldType'] == 'onechoice'){
		$form= "<select name=\"".$name."\" id=\"".$id."\" ".$disabled.">";
		foreach($choices as $e){
			if(isset($e['choiceKey'])){
				$val = " value=\"".$e['choiceKey']."\" ";
				$sel = ($value == $e['choiceKey'])		? ' selected' : NULL;
			}else{
				$val = '';
				$sel = ($value == $e['choiceValue'])	? ' selected' : NULL;
			}
			$form  .= "<option".$val.$sel.">".$e['choiceValue']."</option>";
		}
		$form .="</select>";
	}else

	### choix multiple CHECKBOX
	#
	if($field['fieldType'] == 'multichoice'){
		// Reformer la chaine si on recoit un array() - erreur de form
		if(is_array($value)){
			unset($tmp);
			foreach($choices as $e){
				if(in_array($e['id_fieldchoice'], $value)) $tmp[] = $e['choiceValue'];
			}
			$value = (sizeof($tmp) > 0)
				? $this->splitter.implode($this->splitter, $tmp).$this->splitter
				: '';
		}

		// Former l'array des choix choisis
		$value = explode($this->splitter, $value);
		if(!is_array($value)) $value = array();

		$form = '';
		foreach($choices as $e){
			$sel   = in_array($e['choiceValue'], $value) ? " checked=\"checked\"" : NULL;
			$form .= "<input type=\"checkbox\" name=\"".$name."[]\" value=\"".$e['id_fieldchoice']."\" ".$sel." ".$disabled." /> ".$e['choiceValue']."<br />";
		}

		$form .= "<input type=\"hidden\" name=\"".$name."[]\" value=\"\" />";
	}else

	### choix multiple CONTENT + USER
	#
	if(($field['fieldType'] == 'content' && $field['fieldContentType'] > 0) OR in_array($field['fieldType'], array('user', 'dbtable'))){

		if($field['fieldType'] == 'dbtable'){
			if($field['fieldParam']['type'] == 'solo'){
				$value = is_array($value) ? $value : array($value);
			}else
			if(is_string($value)){
				$value = explode($this->splitter, $value);
			}
		}
		if(!is_array($value)) $value = array();

		// Clean empty values
		foreach($value as $nv => $vv){
			if(trim($vv) == '') unset($value[$nv]);
		}


		// Correspond a la maniere dont on ajoute un element, mutli = defaut, solo= on vide + on ajoute
		$addType = ($field['fieldParam']['type'] == 'solo') ? 'solo' : 'multi';

		// Si je demande du CONTENT
		if($field['fieldType'] == 'content' && $field['fieldContentType'] > 0){
			$type		= $this->apiLoad('type')->typeGet(array('id_type' => $field['fieldContentType']));
			/* mofifié, nx backoffice */
			# $open		= "<a href=\"content.".(($type['is_gallery']) ? "gallery." : NULL)."index.php?id_type=".$field['fieldContentType']."\" target=\"blank\" class=\"open\">Ouvrir: ".$type['typeName']."</a>";
			$open		= "<a href=\"".(($type['is_gallery']) ? "gallery-" : NULL)."index?id_type=".$field['fieldContentType']."\" target=\"blank\" class=\"open\">Ouvrir: ".$type['typeName']."</a>";
			$action		= "tagSearch(".$field['id_field'].", ".$type['id_type'].", '".$name."', '".$addType."')";
			$search		= "<a onClick=\"".$action."\">Chercher (".$type['typeName'].")</a>";
			$create		= "<a onClick=\"tagCreate(".$field['id_field'].", ".$type['id_type'].", '".$name."', '".$addType."')\">Créer cet élement</a>";
			$tags		= (sizeof($value) > 0)
				? $this->apiLoad('content')->contentGet(array(
					'id_content'	=> $value,
					'id_type'		=> $type['id_type'],
					'is_album'      => ($type['is_gallery'] == 1),
					'id_parent'		=> '*',
					'raw'			=> true,
					'debug'			=> false,
					'order'			=> "FIND_IN_SET(k_content.id_content, '".implode(',', $value)."')",
					'direction'		=> 'ASC'
				))
				: array();

			$tagId		= 'id_content';
			$tagView	= 'contentName';
			$tagPrompt	= 'ct';
			$lancer		= "Lancer une recherche pour afficher le contenu (".$type['typeName'].")";

			if($field['fieldParam']['type'] == 'select'){
				$selectId	= 'id_content';
				$selectView	= 'contentName';
				$selectData = $this->apiLoad('content')->contentGet(array(
					'id_type'		=> $type['id_type'],
					'raw'			=> true,
					'noLimit'		=> true,
					'order'			=> 'contentName',
					'direction'		=> 'ASC'
				));
			}
		}else

		// Si je demande un USER
		if($field['fieldType'] == 'user'){
			$open		= "<a href=\"/user/\" target=\"blank\" class=\"open\">Afficher les utilisateurs</a>";
			$action		= "tagSearch(".$field['id_field'].", 'user', '".$name."', '".$addType."')";
			$search		= "<a onClick=\"".$action."\">Chercher les utilisateurs</a>";
			$tags		= (sizeof($value) > 0)
				? $this->apiLoad('user')->userGet(array(
					'id_user'	=> $value,
					'debug'		=> false
				))
				: array();
			$tagId		= 'id_user';
			$tagView	= 'userMail';
			$tagPrompt	= 'us';
			$lancer		= "Lancer une recherche pour afficher les utilisateurs";
			
			if($field['fieldParam']['type'] == 'select'){
				$selectId	= 'id_user';
				$selectView	= 'userMail';
				$selectData = $this->apiLoad('user')->userGet(array(
					'noLimit'	=> true,
					'order'		=> 'userMail',
					'direction'	=> 'ASC',
					'debug'		=> false
				));
			}

		}else

		// Si je demande une table externe
		if($field['fieldType'] == 'dbtable'){
			$open		= NULL;
			$action		= "tagSearch(".$field['id_field'].", 'dbtable', '".$name."', '".$addType."')";
			$search		= "<a onClick=\"".$action."\">Chercher</a>";

			if(sizeof($value) > 0){
				$tags = $this->dbMulti(
					"SELECT ".$field['fieldParam']['field'].",".$field['fieldParam']['id']."\n".
					"FROM ".$field['fieldParam']['table']."\n".
					"WHERE ".$field['fieldParam']['id']." IN(".implode(',', $value).")"
				);
			}else{
				$tags = array();
			}
			$tagId		= $field['fieldParam']['id'];
			$tagView	= $field['fieldParam']['field'];
			$tagPrompt	= 'db';
			$lancer		= "Lancer une recherche pour afficher les valeurs";

			if($field['fieldParam']['type'] == 'select'){
				$selectId	= $field['fieldParam']['id'];
				$selectView	= $field['fieldParam']['field'];
				$selectData = $this->dbMulti(
					"SELECT ".$field['fieldParam']['field'].",".$field['fieldParam']['id']."\n".
					"FROM ".$field['fieldParam']['table']."\n".
                    " ".$field['fieldParam']['where']."\n".
					"ORDER BY ".$field['fieldParam']['field']." ASC"
				);
			}
		}

		// Si on demande un menu deroulant
		if($field['fieldParam']['type'] == 'select'){
			if(!is_array($selectData)) $selectData = array();

			$form  = "<select name=\"".$name."[]\" id=\"".$id."\">";
			$form .= "<option value=\"\"></option>";
			foreach($selectData as $d){
				$form .= "<option value=\"".$d[$selectId]."\"".(in_array($d[$selectId], $value) ? ' selected' : '').">".$d[$selectView]."</option>";
			}
			$form .= "</select>";
		}
		
		// Dans le cas ou l'on veut la table de donnee
		else{

			$form  = "<div class=\"contenttable-list\" id=\"contenttable-".$field['id_field']."\">";
	
			// Up panel
			$form .= "<div class=\"head clearfix\">";
			$form .= "<div class=\"search\"><input type=\"search\" class=\"field\" onkeyup=\"".$action."\" /> ".$search . $create."</div>".$open;
			$form .= "</div>";
	
			// Up values : cette ligne permet d'avoir la valeur du POST pour ce champs (contentSet ou userSet)
			$form .= "<input type=\"hidden\" name=\"".$name."[]\" value=\"\" />";
			$form .= "<ul class=\"keyword clearfix\" id=\"sort".$field['id_field']."\">";
			foreach($tags as $t){
				$form .= "<li class=\"key\" id=\"".$tagPrompt."-".$field['id_field']."-".$t[$tagId]."\">";
				$form .= "<input type=\"hidden\" name=\"".$name."[]\" value=\"".$t[$tagId]."\" /> ".$t[$tagView].' ('.$t[$tagId].')';
				$form .= "<a class=\"kill\" onClick=\"tagRemove('".$tagPrompt."', ".$field['id_field'].", ".$t[$tagId].")\"></a>";
				$form .= "<a class=\"edit\" onClick=\"tagOpen('".$tagId."', ".$t[$tagId].")\" target=\"_blank\"></a>";
				$form .= "</li>";
			}
			$form .= "</ul>";
	
			// Data
			$form .= "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" class=\"listing\" style=\"margin:0px; padding:0px;\">";
			$form .= "<thead><tr>";
			$form .= "<th width=\"25\">&nbsp;</th>";
			$form .= "<th width=\"25\">ID</th>";
			$form .= "<th>Nom</th>";
			if(sizeof($field['fieldParam']['id_field']) > 0){
				foreach($field['fieldParam']['id_field'] as $colField){
					$colField  = $this->fieldGet(array('id_field' => $colField));

					if($colField['id_field'] > 0) $form .= "<th width=\"20%\">".$colField['fieldName']."</th>";
				}
			}
			$form .= "</tr></thead>";
			$form .= "</table>";

			$form .= "<div class=\"table\" style=\"".(($field['fieldParam']['height'] > 0) ? $field['fieldParam']['height'] : NULL)."\">";
			$form .= "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" class=\"listing\" style=\"margin:0px; padding:0px;\">";
			$form .= "<tbody><tr><td class=\"tagListingSearch\">".$lancer."</td></tr></tbody>";
			$form .= "</table>";
			$form .= "</div>";
		}

	}else

	### Media
	#
	if($field['fieldType'] == 'media'){
	
		$form  = "<div class=\"media-list\">";
		$form .= "<textarea name=\"".$name."\" id=\"".$id."\" cols=\"60\" rows=\"6\" style=\"".$opt['style']."; margin-bottom:5px;\" class=\"display-off\">".$value."</textarea>";
		
		// bar du haut (action)
		$form .= "<div class=\"media-header clearfix\">";
		$form .= "<a onClick=\"mediaPicker('".$id."','sort')\" class=\"left\">Ajouter des média (selection dans une nouvelle fenêtre)</a>";
		$form .= "<a onClick=\"javascript:$('#".$id."').toggleClass('display-off');\" class=\"right\">Afficher / masquer la version texte</a>";
		$form .= "</div>";

		// edition des meta
		$form .= "<div class=\"editing\" style=\"display:none;\">";
		$form .= "<iframe id=\"".$id."-iframe\" src=\"../media/helper/metadata-fromcontent?off\" scrolling=\"no\" frameborder=\"0\" width=\"100%\" height=\"100%\"></iframe>";
		$form .= "</div>";

		// images
		$form .= "<ul id=\"".$id."-list\" class=\"clearfix\"><li class=\"noMedia\">Aucun media</li>";
		$values = is_array($value) ? $value : json_decode($value, true);

		if(sizeof($values) > 0){
			foreach($values as $e){
				$c = @$this->mediaUrlData(array(
					'url'   => $e['url'],
					'mode'  => 'width',
					'value' => 160
				));

				$form .= "<li id=\"".implode('@@', $e)."\" data-cache=\"".$c['img']."\" class=\"".((!file_exists(KROOT.$e['url']) && $e['url'] != '') ? 'notFound' : '')."\">";
					$form .= "<div class=\"action clearfix\">";
						$form .= "<span class=\"move\"></span>";
						$form .= "<span class=\"info\"></span>";
						$form .= "<span class=\"caption\"></span>";
						$form .= "<a class=\"remove\"></a>";
						$form .= "<span class=\"notfound\"></span>";
					$form .= "</div>";
					$form .= "<div class=\"media-view\"></div>";
				$form .= "</li>";
			}
		}
		$form .= "</ul>";

		// bar du bas (action)
		$form .= "<div class=\"media-footer clearfix\">";
		$form .= "<img src=\"../core/ui/img/_img/arrow-folder-close.png\" class=\"arrow\" />";
		$form .= "<a class=\"left media-picker-embed-choose\">Choisir des media</a>";
		$form .= "</div>";

		// choix des image par le bas...
		$form .= "<div class=\"choosing\" style=\"display:none;\">";
		$form .= "<iframe id=\"".$id."-choosing\" src=\"../media/index?embed&off\" scrolling=\"no\" frameborder=\"0\" width=\"100%\" height=\"100%\"></iframe>";
		$form .= "</div>";
		
		// upload
		$form .= 	'<div id="modal-upload" data-field="'.$id.'" style="display: none;">
						<div class="uploadcontainer clearfix">
							<div class="left clearfix">
								<p>Glissez des fichiers dans la fenetre pour les télécharger.</p>
								<p>Si votre navigateur ne supporte pas cette fonctionalité, cliquez sur le bouton "Parcourir".</p>
								<br /><br />
								<input id="file_upload" name="file_upload" type="file" multiple="true">
							</div>
							<div id="queue" class="clearfix"></div>
						</div>
					</div>';
		
		$form .= "</div>";
	}else

	### Date
	#
	if($field['fieldType'] == 'date'){
		$form = "<input type=\"text\" name=\"".$name."\" id=\"".$id."\" value=\"".(($value == '0000-00-00') ? '' : $value)."\" class=\"".$opt['class']." datePicker\" size=\"10\" ".$disabled." />";
	}else

	### Nombre entier
	#
	if($field['fieldType'] == 'integer'){
		$form = "<input type=\"text\" name=\"".$name."\" id=\"".$id."\" value=\"".$value."\" ".$disabled." />";
	}else

	### Boolean
	#
	if($field['fieldType'] == 'boolean'){
		$form  = "<input type=\"hidden\"   name=\"".$name."\" value=\"0\" />";
		$form .= "<input type=\"checkbox\" name=\"".$name."\" id=\"".$id."\" value=\"1\" ".$disabled." ".(($value == 1) ? 'checked' : '')." />";
	}else

	### Texte
	#
	if($field['fieldType'] == 'texte'){
		$class = ($field['is_editor']) ? 'richtext' : '';
		$form  = "<textarea name=\"".$name."\" id=\"".$id."\" cols=\"60\" rows=\"6\" class=\"".$opt['class']." ".$class."\" style=\"".$opt['style']."\" ".$disabled.">".$value."</textarea>";
	}else

	### Texte Line
	#
	if($field['fieldType'] == 'texte-line'){
		$form = '<input name="'.$name.'" id="'.$id.'" class="'.$opt['class'].'" style="'.$opt['style'].'" value="'.$value.'" '.$disabled.' />';
	}else

	### Category
	#
	if($field['fieldType'] == 'category'){

		$args = array(
			'id'		=> $id,
			'profile'	=> true,
			'language'	=> 'fr',
			'disabled'	=> $opt['disabled'],
			'style'		=> $opt['style']
		);
		
		if($field['fieldParam']['type'] == 'solo'){
			$args['name']	= $name;
			$args['one']	= true;
			$args['value']	= $value;
		}else{			
			$args['name']	= $name.'[]';
			$args['multi']	= true;
			
			$tmp = explode($this->splitter, $value);
			unset($tmp[sizeof($tmp)-1], $tmp[0]);
			$args['value']	= $tmp;
		}

		$form = $this->apiLoad('category')->categorySelector($args);
	}else

	### Chapter
	#
	if($field['fieldType'] == 'chapter'){
		$form = $this->apiLoad('chapter')->chapterSelector(array(
			'name'		=> $name,
			'id'		=> $id,
			'one' 		=> true,
			'profile'	=> false,
			'language'	=> 'fr',
			'value'		=> $value,
			'disabled'	=> $opt['disabled']
		));
	}else

	### Type
	#
	if($field['fieldType'] == 'content-type'){

		$form= "<select name=\"".$name."\" id=\"".$id."\" ".$disabled.">";
		foreach($this->apiLoad('type')->typeGet() as $e){
			$sel	= ($value == $e['id_type'])	? ' selected' : NULL;
			$form  .= "<option value=\"".$e['id_type']."\"".$sel.">".$e['typeName']."</option>";
		}
		$form .="</select>";

	}else

	### Social Forum
	#
	if($field['fieldType'] == 'social-forum'){

		$args = array(
			'name'		=> $name,
			'id'		=> $id,
			'profile'	=> false,
			'language'	=> 'fr',
			'value'		=> $value,
			'disabled'	=> $opt['disabled'],
			'style'		=> $opt['style']
		);
		
		if($field['fieldParam']['type'] == 'one'){
			$args['one']	= true;
		}else{
			$args['multi']	= true;
		}

		
		$form = $this->apiLoad('socialForum')->socialForumSelector($args);

	}else
	
	### Keyword
	#
	if($field['fieldType'] == 'keyword'){
		$form = '<input name="'.$name.'" id="'.$id.'" class="'.$opt['class'].'" style="'.$opt['style'].'" value="'.$value.'" '.$disabled.' />';
	}else

	### CODE
	#
	if($field['fieldType'] == 'code'){
		$form  = '<textarea name="'.$name.'" id="'.$id.'" cols="60" rows="6" class="'.$opt['class'].' '.$class.' codemirror" style="'.$opt['style'].'" '.$disabled.'>'.$value.'</textarea>';
	}
	
	else{
		$form = "Non traite : \"".$field['fieldType']."\"";
	}
	

	return $form;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
public function fieldTrace($data, $e, $f=array()){

#	$this->pre(func_get_args());

	$field	= $this->fieldForm(
		$e['id_field'],
		$this->formValue($data['field'.$e['id_field']], $_POST['field'][$e['id_field']]),
		array(
			'name'  => $f['name'] ? : '',
			'class' => 'field ',
			'style' => 'width:99%; ' . $e['fieldStyle']
		)
	);

	if(preg_match("#richtext#", 	$field)) $GLOBALS['textarea'][]	 = 'form-field-'.$e['id_field'];
	if(preg_match("#media-list#", 	$field)) $GLOBALS['mediaList'][] = "'form-field-".$e['id_field']."'";
	if(preg_match("#datePicker#", 	$field)) $GLOBALS['datePick'][]	 = "'form-field-".$e['id_field']."'";

	$close = '';
	if(!empty($f['close'])) $close = 'closed';

	echo "<li class=\"clearfix ".$close." ".$this->formError('field'.$e['id_field'], 'needToBeFilled')." form-item\" id=\"field".$e['id_field']."\">";

		echo "<div class=\"hand\">&nbsp;</div>";
		echo "<div class=\"toggle\">&nbsp;</div>";

		echo "<label>".$e['fieldName'];
			if($e['is_needed']) echo ' *';
			if(preg_match("#richtext#", $field)){
				echo "<br /><a href=\"javascript:toggleEditor('form-field-".$e['id_field']."');\">Activer/Désactiver l'éditeur</a>";
			}
		echo "</label>";

		echo "<div class=\"form\">".$field."</div>";

		if($e['fieldInstruction']){
			echo "<div class=\"instruction off\">".$e['fieldInstruction']."</div>";
		}

	echo "</li>";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
public function fieldFormating($opt){

	if($opt['debug']) $this->pre("OPT", $opt);

	$data	= $opt['data'];		if(sizeof($data) == 0)		return $opt['data'];
	$fields = $opt['fields'];	if(sizeof($fields) == 0)	return $opt['data'];

	# Si on utilise le KEY au lieu des ID
	foreach($fields as $e){
		$fieldsKey[$e['fieldKey']]	= $e;
		$fieldsIds[]				= $e['id_field'];
	}
	$fields = $fieldsKey;

	foreach($data as $id_field => $value){
		if(!is_integer($id_field)) $id_field = $fields[$id_field]['id_field'];

		if(in_array($id_field, $fieldsIds)){
			$def['field'.$id_field] = array(
				'value' => $this->fieldSaveValue($id_field, $value, $opt)
			);
		}
	}

	return $def;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
public function fieldMapping($opt){

	$data		= $opt['data'];		if(sizeof($data) == 0)		return $opt['data'];
	$fields 	= $opt['fields'];	if(sizeof($fields) == 0)	return $opt['data'];
	$language	= $opt['language'];

	foreach($fields as $f){
		$v = $data['field'.$f['id_field']];
		$p = json_decode($f['fieldParam'], true); 

		// MEDIA
		if($f['fieldType'] == 'media'){
			$v = json_decode($v, true); unset($media);

			if(sizeof($v) > 0 && is_array($v)){
				foreach($v as $e){
					$e_ = $this->mediaInfos($e['url']);
					$e_['caption'] = $e['caption'];
					$media[$e['type']][] = $e_;
				}
				$data['field'.$f['id_field']] = $media;
			}else{
				$data['field'.$f['id_field']] = array();
			}

		}else

		// CATEGORY
		if($f['fieldType'] == 'category'){
			$tmp = array();

			if($p['type'] == 'solo' && intval($v) > 0){
				$tmp = $this->dbOne("SELECT id_category, categoryName FROM k_categorydata WHERE id_category=".intval($v));
			}else
			if($p['type'] == 'multi'){
				$v = explode($this->splitter, $v);
				unset($v[sizeof($v)-1], $v[0]);

				foreach($v as $vCat){
					$tmp[] = $this->dbOne("SELECT id_category, categoryName FROM k_categorydata WHERE id_category=".intval($vCat));
				}
			}

			$data['field'.$f['id_field']] = $tmp;

		}else

		// USER
		if(is_array($v) && $f['fieldType'] == 'user'){
			unset($tmp);
			foreach($v as $bUser){
				$tmp[] = $this->dbOne("SELECT * FROM k_user WHERE k_user.id_user=".$bUser." AND is_deleted=0");
			}
			$data['field'.$f['id_field']] = $tmp;
		}else

		// CONTENT
		if($f['fieldType'] == 'content'){
			$v		= is_array($v) ? $v : array($v); 
			$param	= json_decode($f['fieldParam'], true); 

			unset($tmp);
			foreach($v as $bContent){
				$tmp[] = $this->dbOne("SELECT * FROM k_contentdata WHERE id_content=".$bContent." AND language='".$language."'");
			}
			$data['field'.$f['id_field']] = (($param['type'] == 'solo' && sizeof($v) == 1) ? $tmp[0] : $tmp);
		}else

		// CHOICE
		if(in_array($f['fieldType'], array('onechoice', 'multichoice')) && substr($v, 0, 2) == $this->splitter && substr($v, -2) == $this->splitter && $v != $this->splitter){
			$part = explode($this->splitter, substr($v, 2, -2));
			$data['field'.$f['id_field']] = implode("<br />", $part);
		}

		// NORMAL
		/*else{
			$data['field'.$f['id_field']] = $v;
		}*/

		$data['field'][$f['fieldKey']] = $data['field'.$f['id_field']];
		unset($data['field'.$f['id_field']]);
	}

	return $data;
}


}