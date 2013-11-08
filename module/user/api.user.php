<?php
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Last release		2011.07.21
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

class user extends coreApp {

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function __clone(){}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function user(){}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userGet($opt=array()){

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep='userGet() @='.json_encode($opt));


	if($opt['debug']) $this->pre("[OPT]", $opt);

	# Gérer les options
	#
	$useField	= isset($opt['useField']) 		? $opt['useField']		: true;
	$useMedia	= isset($opt['useMedia'])		? $opt['useMedia']		: false;
	$useSocial	= isset($opt['useSocial'])		? $opt['useSocial']		: false;
	$limit		= ($opt['limit'] != '') 		? $opt['limit']			: 30;
	$offset		= ($opt['offset'] != '') 		? $opt['offset']		: 0;
	$searchLink	= ($opt['searchLink'] != '') 	? $opt['searchLink']	: 'OR';
	$dbMode		= 'dbMulti';

	# GET id_user
	#
	if(array_key_exists('id_user', $opt)){
		if(is_array($opt['id_user']) && sizeof($opt['id_user']) > 0){
			$cond[] = "k_user.id_user IN(".@implode(',', $opt['id_user']).")";
		}else
		if(intval($opt['id_user']) > 0){
			$dbMode = 'dbOne';
			$cond[] = "k_user.id_user=".$opt['id_user'];
		}else{
			if($opt['debug']) $this->pre("ERROR: ID_USER (NUMERIC, ARRAY)", "GIVEN", var_export($opt['id_user'], true));
			return array();
		}
	}

	# GET not this ID
	#
	if(array_key_exists('not', $opt)){
		if(is_array($opt['not']) && sizeof($opt['not']) > 0){
			$cond[] = "k_user.id_user NOT IN(".@implode(',', $opt['not']).")";
		}else
		if(intval($opt['not']) > 0){
			$cond[] = "k_user.id_user != ".$opt['not'];
		}else{
			if($opt['debug']) $this->pre("ERROR: NOT (NUMERIC, ARRAY)", "GIVEN", var_export($opt['not'], true));
			return array();
		}
	}

	# GET userToken
	#
	if(array_key_exists('userToken', $opt)){
		if($opt['userToken'] != ''){
			$dbMode = 'dbOne';
			$cond[] = "k_user.userToken='".$opt['userToken']."'";
		}else{
			if($opt['debug']) $this->pre("ERROR: ID_USER (STRING)", "GIVEN", var_export($opt['userToken'], true));
			return array();
		}
	}

	# Gerer les USER FIELD
	#
	$field = $this->apiLoad('field')->fieldGet(array('user' => true));
	foreach($field as $f){
		$fieldKey[$f['fieldKey']] = $f;
		if($f['is_search'])												$fieldSearch[]		= $f;
		if($f['fieldType'] == 'content' && $f['fieldContentType'] > 0)	$fieldAssoContent[] = $f;
		if($f['fieldType'] == 'user')									$fieldAssoUser[]	= $f;
	}

	# Gerer la recherche
	#
	if($opt['id_search'] > 0){
		$search = $this->searchGet(array('id_search' => $opt['id_search']));
		if(sizeof($search['searchParam'])) $opt['searchParam'] = $search;
	}
	if(is_array($opt['searchParam'])){
		if(sizeof($search['searchParam'])){
			$cond[] = $this->apiLoad('content')->contentSearchSQL($search);
		}
	}
	if(is_array($opt['search'])){
		unset($tmp);

		foreach($opt['search'] as $e){
			if($e['searchField'] > 0){
				$tmp[] = $this->dbMatch("field".$e['searchField'], $e['searchValue'], $e['searchMode']);
			}else
			if($fieldKey[$e['searchField']]['id_field'] != NULL){
				$tmp[] = $this->dbMatch("field".$fieldKey[$e['searchField']]['id_field'], $e['searchValue'], $e['searchMode']);
			}else
			if($field[$e['searchField']]['id_field'] != NULL){
				$tmp[] = $this->dbMatch("field".$field[$e['searchField']]['id_field'], $e['searchValue'], $e['searchMode']);
			}else{
				$tmp[] = $this->dbMatch($e['searchField'], $e['searchValue'], $e['searchMode']);
			}
		}

		if(sizeof($tmp) > 0) $cond[] = "(".implode(' '.$searchLink.' ', $tmp).")";
	}else
	if($opt['search'] != ''){
		unset($tmp);

		$tmp[] = $this->dbMatch("userMail", $opt['search'], 'CT');

		if(sizeof($fieldSearch) > 0){
			foreach($fieldSearch as $e){
				$tmp[] = $this->dbMatch("field".$e['id_field'], $opt['search'], 'CT');
			}
		}
			
		if(sizeof($tmp) > 0) $cond[] = "(".implode(' '.$searchLink.' ', $tmp).")";
	}

	# Former les CONDITIONS
	#
	if($opt['is_deleted'] != '*')	$cond[] = "k_user.is_deleted=".(isset($opt['is_deleted']) ? $opt['is_deleted'] : 0);
	if($opt['id_group'] != NULL) 	$cond[] = "id_group ".(is_array($opt['id_group']) ? "IN(".implode(",", $opt['id_group']).")" : "=".$opt['id_group']);
	if(sizeof($cond) > 0) $where = "WHERE ".implode(" AND ", $cond);

	# Former les JOIN
	#
	$join[] = "INNER JOIN k_userdata ON k_user.id_user = k_userdata.id_user";
	if($useSocial) $join[] = "INNER JOIN k_usersocial ON k_user.id_user = k_usersocial.id_user";

	# JOIN tables + WHERE conditions set in OPTIONS
	#
	if($opt['sqlJoin'] != '') $join[] = $opt['sqlJoin'];
	if($opt['sqlWhere'] != ''){
		if(isset($where)){
			$where .= ' '.$opt['sqlWhere'];
		}else{
			$where  = "WHERE ".$opt['sqlWhere'];
		}
	}
	$sqlJoin = "\n".implode("\n", $join)."\n";

	# Former les LIMITATIONS et ORDRE
	#
	if($dbMode == 'dbMulti'){
		if(isset($opt['order']) && isset($opt['direction'])){
			if($fieldKey[$opt['order']]['id_field'] != NULL && $opt['direction'] != NULL){
				$sqlOrder = "\nORDER BY field".$fieldKey[$opt['order']]['id_field']." ".$opt['direction'];
			}else{
				$sqlOrder = "\nORDER BY ".$opt['order']." ".$opt['direction'];
			}
		}else{
			$sqlOrder = "\nORDER BY k_user.id_user ASC";
		}

		if(!$opt['noLimit']) $sqlLimit = "\nLIMIT ".$offset.",".$limit;

	}else{
		$flip  = true;	
	}

	# USER
	#
	$users = $this->$dbMode("SELECT ".$distinct." SQL_CALC_FOUND_ROWS * FROM k_user". $sqlJoin . $where . $sqlOrder . $sqlLimit);

	$this->total	= $this->db_num_total;
	$this->limit	= $limit;

	if($opt['debug']) $this->pre($this->db_query, $this->db_error, $users);

	if(sizeof($users) == 0) return array();

	if($flip) $users = array($users);

	# Gerer les ASSOCIATIONS
	#
	if(sizeof($fieldAssoContent) > 0){
		foreach($users as $idx => $c){				
			foreach($fieldAssoContent as $f){
				$users[$idx]['field'.$f['id_field']] = $this->userAssoGet($c['id_user'], $f['id_field'], $f['fieldContentType']);
			}
		}
	}
	if(sizeof($fieldAssoUser) > 0){
		foreach($users as $idx => $c){				
			foreach($fieldAssoUser as $f){
				$users[$idx]['field'.$f['id_field']] = $this->userAssoUserGet($c['id_user'], $f['id_field']);
			}
		}
	}

	# Mapping
	#
	$users = $this->userMapping(array(
		'data'		=> $users,
		'useMedia'	=> $useMedia,
		'useField'	=> $useField,
		'fields'	=> $field,
	));

	if($flip) $users = $users[0];

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep);

	return $users;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userSet($opt){

	if($opt['debug']) $this->pre("[opt]", $opt);

	$id_user		= $opt['id_user'];
	$def			= $opt['def'];
	$field			= $opt['field'];
	$addressbook	= $opt['addressbook'];
	$newsletter		= $opt['newsletter'];

	if(sizeof($def) > 0){
		if($id_user > 0){
			$q = $this->dbUpdate($def)." WHERE id_user=".$id_user;
		}else{
			$def['k_user']['userToken'] = array('value' => md5(uniqid(NULL, true)));
			$q = $this->dbInsert($def);
		}

		@$this->dbQuery($q);
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
		if($this->db_error != NULL) return false;
	}

	$this->id_user = ($id_user > 0) ? $id_user : $this->db_insert_id;

	if($id_user == NULL){
		$this->dbQuery("UPDATE k_user SET userDateCreate=NOW(), userDateUpdate=NOW() WHERE id_user=".$this->id_user);
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);

		$this->dbQuery("INSERT INTO k_userdata (id_user) VALUES (".$this->id_user.")");
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);

		$this->dbQuery(
			"INSERT INTO k_useraddressbook ".
			"(addressbookIsMain, addressbookIsProtected, addressbookIsDelivery, addressbookIsBilling, addressbookTitle, id_user) ".
			"VALUES ".
			"(1, 1, 1, 1, 'Defaut', ".$this->id_user.")"
		);
		$id_addressbook = $this->db_insert_id;
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);

		$hook = 'userInsert';
	}else{
		$hook = 'userUpdate';
	}

	# FIELD
	#
	if(sizeof($field) > 0){

		# Si on utilise le KEY au lieu des ID
		$fields = $this->apiLoad('field')->fieldGet(array('user' => true, 'debug' => false));
		foreach($fields as $e){
			$fieldsKey[$e['fieldKey']] = $e;
		} $fields = $fieldsKey;


		unset($def);
		$apiField = $this->apiLoad('field');
		$apiField->id_user = $this->id_user;

		foreach($field as $id_field => $value){
			if(!is_integer($id_field)) $id_field = $fields[$id_field]['id_field'];
			$value = $apiField->fieldSaveValue($id_field, $value, array('id_user' => $this->id_user));
			$def['k_userdata']['field'.$id_field] = array('value' => $value); 
		}
		$this->dbQuery($this->dbUpdate($def)." WHERE id_user=".$this->id_user);
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	}
	
	# ADDRESSBOOK
	#
	if(sizeof($addressbook) > 0){
		$this->userAddressBookSet(array(
			'id_user'        => $this->id_user,
			'id_addressbook' => $id_addressbook,
			'def'            => $addressbook,
			'debug'          => $opt['debug']
		));
	}

	if($this->id_user > 0){
		// Update Group
		$this->userSearchCache(array(
			'id_user' => $this->id_user
		));

		// Update Circle
		$this->userSocialCircle($this->id_user);

		// Hook
		$this->hookAction($hook, $this->id_user);
	}

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userRemove($id_user){

	if(empty($id_user)) return false;

	$this->dbQuery("UPDATE k_user SET is_deleted=1 WHERE id_user=".$id_user);
	$this->hookAction('userRemove', $id_user);

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userFieldSet($id_field, $def){

	if(!$this->formValidation($def)) return false;

	if($id_field > 0){
		$q = $this->dbUpdate($def)." WHERE id_field=".$id_field;
	}else{
		$q = $this->dbInsert($def);
	}

	@$this->dbQuery($q);

	if($this->db_error != NULL) return false;
	$this->id_field = ($id_field > 0) ? $id_field : $this->db_insert_id;
	
	if($id_field == NULL){
		$this->dbQuery("ALTER TABLE `k_userdata` ADD `field".$this->id_field."` VARCHAR(255) NOT NULL");
	}

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userMediaLink($opt){

	if(!is_array($opt['url'])) $opt['url'] = array($opt['url']);

	# Get user
	#
	$user = $this->userGet(array(
		'id_user' => $opt['id_user'],
	));
	if($user['id_user'] == NULL){
		if($opt['debug']) $this->pre("user not found with id_user", $opt['id_user']);
		return false;
	}
	
	# CLEAR and Exit 
	#
	if($opt['clear']){
		$this->dbQuery("UPDATE k_user SET userMedia='' WHERE id_user=".$opt['id_user']);
		if($opt['debug']) $this->pre("CLEAR", $this->db_query, $this->db_error);
		return true;
	}

	// Check if file EXIST
/*
	if(!file_exists(KROOT.$opt['url'])){
		if($opt['debug']) $this->pre("file not found : ".KROOT.$opt['url']);
		return false;
	}
*/

	# Update ARRAY
	#
	$media = json_decode($user['userMedia'], true);
	$media = is_array($media) ? $media : array();
	
	// Si on souhait conserver les autres element, verifier s'il n'y a pas de doublon
	if(!$opt['onlyMe']){
		foreach($opt['url'] as $n => $e){
			foreach($media as $m){
				if($e == $m['url']) unset($opt['url'][$n]);
			}
		}
	}
	
	# Update BDD (if needed)
	#
	if(sizeof($opt['url']) > 0){
		if($opt['onlyMe']) $media = array();
		
		foreach($opt['url'] as $e){
			// Type (image=picture -- JS type corruption ?)
			$type		= ($opt['type'] == NULL) ? $this->mediaType($e) : $opt['type'];
			$type		= ($type == 'picture') ? 'image' : $type;

			$media[]	= array('type' => $type, 'url' => $e);
		}
	
		$def = array('k_user' => array(
			'userMedia' => array('value' => json_encode($media))
		));
	
		$this->dbQuery($this->dbUpdate($def)." WHERE id_user=".$opt['id_user']);
		if($opt['debug']) $this->pre("UPDATE", $this->db_query, $this->db_error);

		return true;
	}
	
	return false;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userMapping($opt){

	$users	= $opt['data'];
	$fields	= $opt['fields'];

	foreach($users as $idx => $c){
			
		// Media
		if($opt['useMedia']){
			$userMedia = json_decode(stripslashes($c['userMedia']), true);
			if(sizeof($userMedia) > 0){
				unset($media);
				foreach($userMedia as $e){
					$v = $this->mediaInfos($e['url']);
					$media[$e['type']][] = $v;
				}
				$users[$idx]['userMedia'] = $media;
			}else{
				$users[$idx]['userMedia'] = array();
			}
		}

		// Field
		if($opt['useField']){
			foreach($fields as $f){
				$v = $c['field'.$f['id_field']];
				
				if($f['fieldType'] == 'media'){
					$v = json_decode($v, true); unset($media);
					if(sizeof($v) > 0 && is_array($v)){
						foreach($v as $e){
							$e_ = $this->mediaInfos($e['url']);
							$e_['caption'] = $e['caption'];
							$media[$e['type']][] = $e_;
						}
						$users[$idx]['field'][$f['fieldKey']] = $media;
					}
				}else

				if(is_array($v) && $f['fieldType'] == 'user'){
					unset($tmp);
					foreach($v as $id_user){
						$tmp[] = $this->dbOne("SELECT * FROM k_user WHERE id_user=".$id_user);
					}
					$users[$idx]['field'][$f['fieldKey']] = $tmp;
				}else

				if(is_array($v) && $f['fieldType'] == 'content'){
					unset($tmp);
					foreach($v as $id_content){
						$tmp[] = $this->dbOne("SELECT * FROM k_contentdata WHERE id_content=".$id_content);
					}
					$users[$idx]['field'][$f['fieldKey']] = $tmp;
				}else

				if(in_array($f['fieldType'], array('onechoice', 'multichoice')) && substr($v, 0, 2) == $this->splitter && substr($v, -2) == $this->splitter && $v != $this->splitter){
			#	if(substr($v, 0, 2) == $this->splitter && substr($v, -2) == $this->splitter && $v != $this->splitter){
					$part = explode($this->splitter, substr($v, 2, -2));
					$users[$idx]['field'][$f['fieldKey']] = implode("<br />", $part);

				}else{
					$users[$idx]['field'][$f['fieldKey']] = $v;
				}

				unset($users[$idx]['field'.$f['id_field']]);
			}
		}
		
		// Search Cache
		$userSearchCache = json_decode($c['userSearchCache'], true);
		$users[$idx]['userSearchCache'] = is_array($userSearchCache) ? $userSearchCache : array();
	}

	return $users;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userSearchCache($opt){

	# update pour 1 USER
	#	
	if(intval($opt['id_user']) > 0){

		$id_user	= $opt['id_user'];
		$id_search	= $opt['id_search'];
		$in 		= array();

		$search 	= ($id_search == NULL)
			? $this->dbMulti("SELECT * FROM k_search WHERE searchType='user'")
			: $this->dbMulti("SELECT * FROM k_search WHERE id_search=".$id_search);
	
		if(sizeof($search) == 0) return false;
		
		foreach($search as $s){
			$s['searchParam'] = unserialize($s['searchParam']);
			if($s['searchParam'] != ''){
				$query	= "SELECT k_user.id_user FROM k_user INNER JOIN k_userdata on k_user.id_user = k_userdata.id_user WHERE\n1 AND ".$this->userSearchSQL($s);
				$data	= @$this->dbMulti($query);
				$data	= $this->dbKey($data, 'id_user');
	
				if(in_array($id_user, $data)) $in[] = intval($s['id_search']);
			}
		}
	
		$this->dbQuery("UPDATE k_user SET userSearchCache='".json_encode($in)."' WHERE id_user=".$id_user);
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	
	}else

	# update pour 1 SEARCH
	#
	if(intval($opt['id_search']) > 0){

		$search = $this->dbOne("SELECT * FROM k_search WHERE id_search=".$opt['id_search']);
		if(intval($search['id_search']) == 0) return false;

		$search['searchParam'] = unserialize($search['searchParam']);

		$query	= "SELECT k_user.id_user FROM k_user INNER JOIN k_userdata WHERE\n1 AND ".$this->userSearchSQL($search);
		$data	= @$this->dbMulti($query);
		$data	= $this->dbKey($data, 'id_user');

		if(sizeof($data) == 0) return false;

		$all = $this->dbMulti("SELECT id_user, userSearchCache FROM k_user WHERE userSearchCache != '' AND id_user IN(".implode(',', $data).")");

		foreach($all as $u){

			$userSearchCache = json_decode($u['userSearchCache'], true);
			$userSearchCache = is_array($userSearchCache) ? $userSearchCache : array();

			if($opt['clean']){

				$tmp = array();
				if(in_array($opt['id_search'], $userSearchCache)){
					foreach($userSearchCache as $s){
						if($s != $opt['id_search']) $tmp[] = $s;
					}

					$this->dbQuery("UPDATE k_user SET userSearchCache='".json_encode($tmp)."' WHERE id_user=".$u['id_user']);
					if($opt['debug']) $this->pre($this->db_query, $this->db_error);
				}

			}else{
				$userSearchCache[]	= intval($opt['id_search']);
				$userSearchCache	= array_unique($userSearchCache);

				$this->dbQuery("UPDATE k_user SET userSearchCache='".json_encode($userSearchCache)."' WHERE id_user=".$u['id_user']);
				if($opt['debug']) $this->pre($this->db_query, $this->db_error);
			}
		}
	}

	return false;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userSearch($opt){

	$search = $this->dbOne("SELECT * FROM k_search WHERE id_search=".$opt['id_search']);
	$search['searchParam'] = unserialize($search['searchParam']);

	# Gérer les options
	#
	$limit		= ($opt['limit'] != '') 	? $opt['limit']		: 30;
	$offset		= ($opt['offset'] != '') 	? $opt['offset']	: 0;

	# Former les LIMITATIONS et ORDRE
	#
	if(isset($opt['order']) && isset($opt['direction'])){
		$sqlOrder = "\nORDER BY ".$opt['order']." ".$opt['direction'];
	}else{
		$sqlOrder = "\nORDER BY k_user.id_user ASC";
	}

	if(!$opt['noLimit']) $sqlLimit = " LIMIT ".$offset.",".$limit;

	$c = array();
	$this->total = 0;
	if(is_array($search['searchParam']) && sizeof($search['searchParam']) > 0){
		$q = "SELECT SQL_CALC_FOUND_ROWS * FROM k_user \n".
			 "INNER JOIN k_userdata ON k_user.id_user = k_userdata.id_user\n".
			 "WHERE \n".$this->userSearchSQL($search)."\n".
			 $sqlOrder . $sqlLimit;
	
		$c = $this->dbMulti($q);
		$this->total = $this->db_num_total;
	}
	if($opt['debug']) $this->pre($this->db_query, $this->db_error, $c);


	return $c;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userSearchSQL($param, $level=0){
#$this->pre($param);

	$prompt  = str_repeat("\t", $level);
	$prompt_ = $prompt."\t";

	$q .= $prompt."(\n";

	if(is_array($param['searchParam']) && sizeof($param['searchParam']) > 0){
		foreach($param['searchParam'] as $i => $e){
			$last 	= ($i == sizeof($param['searchParam'])-1);
			$field	= preg_match("#[a-z]#", $e['searchField']) ? $e['searchField'] : 'field'.$e['searchField'];

			if(is_array($e['searchValue'])){
				unset($tmp);
				foreach($e['searchValue'] as $n){
					$tmp[] = "`".$field."` = '".$n."'";
				}
				$q .= $prompt_."(".implode(" AND ", $tmp).")\n";
			}else{
				$q .= $prompt_.$this->dbMatch($field, $e['searchValue'], $e['searchMode'])."\n";
			}
			
			if(sizeof($e['searchParam']) > 0){
				if($last) $q .= $prompt_.$param['searchChain']."\n";
				$q .= $this->userSearchSQL($e, ($level+1));
			}

			if(!$last) $q .= $prompt_.$param['searchChain']."\n";
		}
	}
	
	#if(sizeof($c) > 0) $q .= implode($prompt_.$param['searchChain']."\n", $c);

	$q .= $prompt.")\n";

	return $q;
	
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userProfileGet($opt=array()){

	if($opt['id_profile'] > 0){
		$dbMode = 'dbOne';
		$cond[] = "id_profile=".$opt['id_profile'];
	}else{
		$dbMode = 'dbMulti';
	}


	# Former les conditions
	#
	if(sizeof($cond) > 0) $where = "WHERE ".implode(" AND ", $cond);


	# PROFILE
	#
	$profile = $this->$dbMode("SELECT * FROM k_userprofile ".$where." ORDER BY profileName ASC");
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);


	#  PARAM
	#
	if($dbMode == 'dbMulti'){
		foreach($profile as $idx => $c){
			$profile[$idx]['profileRule'] = unserialize($profile[$idx]['profileRule']);
			if(!is_array($profile[$idx]['profileRule'])) $profile[$idx]['profileRule'] = array();
		}
	}else
	if($dbMode == 'dbOne'){
		$profile['profileRule'] = unserialize($profile['profileRule']);
		if(!is_array($profile['profileRule'])) $profile['profileRule'] = array();
	}

	return $profile;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userProfileSet($id_profile, $def){

	if($id_profile > 0){
		$q = $this->dbUpdate($def)." WHERE id_profile=".$id_profile;
	}else{
		$q = $this->dbInsert($def);
	}

	@$this->dbQuery($q);
	if($this->db_error != NULL) return false;

	$this->id_profile = ($id_profile > 0) ? $id_profile : $this->db_insert_id;

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userProfileCheckChapter($chapter){

	if(!is_array($chapter)) return array();
	
	foreach($chapter as $e){
		unset($autre);

		foreach($chapter as $a){
			if($a != $e) $autre[] = $a;
		}

		$me = $this->apiLoad('chapter')->chapterGet(array(
			'language'		=> 'fr',
			'id_chapter'	=> $e
		));

		foreach(explode(',', $me['chapterChildren']) as $c){
			if(@in_array($c, $autre)) $louche[] = $c;
		}
	}

	foreach($chapter as $e){
		if(!@in_array($e, $louche)) $rest[] = $e;
	}

	return is_array($rest) ? $rest : array();
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userProfileCheckCategory($category){

	if(!is_array($category)) return array();
	
	foreach($category as $e){
		unset($autre);

		foreach($category as $a){
			if($a != $e) $autre[] = $a;
		}
		
		$me = $this->apiLoad('category')->categoryGet(array(
			'language' 		=> 'fr',
			'id_category' 	=> $e
		));

		foreach(explode(',', $me['categoryChildren']) as $c){
			if(@in_array($c, $autre)) $louche[] = $c;
		}
	}

	foreach($category as $e){
		if(!@in_array($e, $louche)) $rest[] = $e;
	}

	return is_array($rest) ? $rest : array();
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userProfileCheckGroup($group){

	if(!is_array($group)) return array();
	
	foreach($group as $e){
		unset($autre);

		foreach($group as $a){
			if($a != $e) $autre[] = $a;
		}
		
		$me = $this->userGroupGet(array('id_group' => $e));
		
		foreach(explode(',', $me['groupChildren']) as $c){
			if(@in_array($c, $autre)) $louche[] = $c;
		}
	}

	foreach($group as $e){
		if(!@in_array($e, $louche)) $rest[] = $e;
	}

	return is_array($rest) ? $rest : array();
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userGroupGet($opt=array()){

	if($opt['distinctParent'] && is_array($opt['id_group'])){
		foreach($opt['id_group'] as $e){
			$list[] = $this->userGroupGet(array('id_group' => $e));
		}
	
		$parent = array();
		foreach($list as $e){
			$parent = array_merge($parent, explode(',', $e['groupParent']));
		}

		foreach($parent as $idx => $e){
			if($e == '0') unset($parent[$idx]);
		}

		return $parent;
	}else
	if($opt['distinctChildren'] && is_array($opt['id_group'])){
		foreach($opt['id_group'] as $e){
			$list[] = $this->userGroupGet(array('id_group' => $e));
		}
		foreach($list as $e){
			$str .= $e['groupChildren'].',';
		}
		$children = explode(',', $str);
		return $children;
	}else
	if($opt['threadFlat']){

		$group = $this->userGroupGet(array(
			'profile'			=> $opt['profile'],
			'thread'			=> true,
			'mid_group'			=> $opt['mid_group'],
			'noid_group'		=> $opt['noid_group'],
		));

		$this->threadFlatWork = array();

		$group = $this->userGroupGet(array(
			'threadFlatWork'	=> true,
			'mid_group'			=> $opt['mid_group'],
			'noid_group'		=> $opt['noid_group'],
			'group'				=> $group,
			'level'				=> 0
		));

		return $this->threadFlatWork;

	}else
	if($opt['threadFlatWork']){

		foreach($opt['group'] as $e){
			$e['level'] = $opt['level'];
			$tmp = $e; unset($tmp['sub']);

			$this->threadFlatWork[] = $tmp;
			
			if(is_array($e['sub'])){
				$this->userGroupGet(array(
					'profile'			=> $opt['profile'],
					'threadFlatWork'	=> true,
					'mid_group'			=> $opt['mid_group'],
					'noid_group'		=> $opt['noid_group'],
					'group'				=> $e['sub'],
					'level'				=> ($opt['level'] + 1)
				));
			}
		}

		return true;
	}else
	if($opt['thread']){

		if($opt['noid_group'] > 0) $cond[] = " id_group != ".$opt['noid_group'];

		$mid 	= isset($opt['mid_group']) ? $opt['mid_group'] : 0;
		$cond[] = ($opt['profile']) ? "id_group IN(".$this->profile['group'].")" : "mid_group=".$mid;

		if(sizeof($cond) > 0) $where = "WHERE ".implode(' AND ', $cond);

		$group = $this->dbmulti("SELECT * FROM k_group ".$where." ORDER by pos_group");

		foreach($group as $idx => $e){
			if($e['id_group'] != $opt['noid_group']){
	
				$group[$idx]['sub'] = $this->userGroupGet(array(
					'thread'		=> true,
					'mid_group'		=> $e['id_group'],
					'noid_group'	=> $opt['noid_group']
				));
			}
		}
		
		return $group;

	}else
	if($opt['id_group'] != ''){
		$dbMode = 'dbOne';
		$cond[] = "id_group=".$opt['id_group'];
	}else
	if($opt['mid_group'] != ''){
		$dbMode = 'dbMulti';
		$cond[] = "mid_group=".$opt['mid_group'];
	}else{
		$dbMode = 'dbMulti';
	}

	if(sizeof($cond) > 0) $where = "WHERE ".implode(" AND ", $cond)." ";

	$group = $this->$dbMode("SELECT * FROM k_group ".$where);

	if($dbMode == 'dbOne' && $group['id_group'] != ''){
		$group['groupFormLayout'] = json_decode(utf8_decode($group['groupFormLayout']), true);

		if(!is_array($group['groupFormLayout'])){

			$group['groupFormLayout'] = array(
				'tab' => array(
					'view0' => array(
						'label' => 'Defaut',
						'field' => array()
					)
				),
				'bottom' => array(
					
				)
			);
		}
	}



	if($opt['debug']) $this->pre($this->db_query, $this->db_error, $group);

	return $group;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userGroupSet($id_group, $def){

	if(!$this->formValidation($def)) return false;

	if($id_group != NULL){
		$q = $this->dbUpdate($def)." WHERE id_group=".$id_group;
	}else{
		$last = $this->dbOne("SELECT MAX(pos_group) AS m FROM k_group WHERE mid_group=".$def['k_group']['mid_group']['value']);
		$def['k_group']['pos_group'] = array('value' => ($last['m'] + 1));
		$q = $this->dbInsert($def);
	}

	@$this->dbQuery($q);
	if($this->db_error != NULL) return false;
	$this->id_group = ($id_group > 0) ? $id_group : $this->db_insert_id;

	$this->userGroupFamily();

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userGroupRemove($id_group){
	$this->dbQuery("DELETE FROM k_group WHERE id_group=".$id_group);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userGroupSelector($opt){

	$form  = '';
	$group = $this->userGroupGet(array(
		'profile'		=> $opt['profile'],
		'mid_group'		=> 0,
		'threadFlat'	=> true
	));

	if($opt['multi']){
		$value = is_array($opt['value']) ? $opt['value'] : array();

		$form .= "<select name=\"".$opt['name']."\" id=\"".$opt['id']."\" size=\"".$opt['size']."\" multiple style=\"".$opt['style']."\" ".$opt['events'].">";
		foreach($group as $e){
			$selected = in_array($e['id_group'], $value) ? ' selected' : NULL;
			$form .= "<option value=\"".$e['id_group']."\"".$selected.">".str_repeat(' &nbsp; &nbsp;', $e['level']).'&nbsp;'.$e['groupName']."</option>";
		}
		$form .= "</select>";
	}else
	if($opt['one']){
		$value = is_array($opt['value']) ? $opt['value'][0] : $opt['value'];

		$form .= "<select name=\"".$opt['name']."\" id=\"".$opt['id']."\" style=\"".$opt['style']."\" ".$opt['events'].">";
		foreach($group as $e){
			$selected = ($e['id_group'] == $value) ? ' selected' : NULL;
			$form .= "<option value=\"".$e['id_group']."\"".$selected.">".str_repeat(' &nbsp; &nbsp;', $e['level']).'&nbsp;'.$e['groupName']."</option>";
		}
		$form .= "</select>";
	}
	
	return $form;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	Mettre a jour les PARENT et CHILDREN et les sauver en base
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userGroupFamily(){

	$group = $this->userGroupGet(array(
		'threadFlat'	=> true,
		'debug'			=> true
	));

	foreach($group as $e){
		$tree = $this->userGroupFamilyParent($e);
		$tree = sizeof($tree) > 0 ? implode(',', array_reverse($tree)) : '';
		
		$this->dbQuery("UPDATE k_group SET groupParent='".$tree."' WHERE id_group=".$e['id_group']);
	}
	
	foreach($group as $e){
		$tree = $this->userGroupFamilyChildren($e);
		$tree = (sizeof($tree) > 0) ? implode(',', $tree) : '';

		$this->dbQuery("UPDATE k_group SET groupChildren='".$tree."' WHERE id_group=".$e['id_group']);
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	Trouver tous les PARENTS pour un GROUP
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userGroupFamilyParent($e, $line=array()){

	if(abs(intval($e['mid_group'])) > 0){
		$next = $this->userGroupGet(array(
			'id_group' => $e['mid_group']
		));
	
		$line[] = $e['mid_group'];
		return $this->userGroupFamilyParent($next, $line);
	}else{
		return $line;
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	Trouver tous les CHILDREN pour un GROUP
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userGroupFamilyChildren($e, &$line=array()){

	$children = $this->userGroupGet(array(
		'mid_group' => $e['id_group']
	));

	foreach($children as $child){
		$line[] = $child['id_group'];
		$this->userGroupFamilyChildren($child, $line);
	}
	
	return $line;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
 - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */
public  function userAddressBookCheck($id_user){

	$books = $this->userAddressBookGet(array('id_user' => $id_user), false);

	if(sizeof($books) == 0){
		$this->dbQuery("INSERT INTO k_useraddressbook (id_user, addressbookIsMain, addressbookIsDelivery, addressbookIsBilling, addressbookIsProtected) VALUES (".$id_user.", 1, 1, 1, 1)");
	}else{
		foreach($books as $book){
			if($book['addressbookIsMain']) 		$main 		= $book['id_addressbook'];
			if($book['addressbookIsDelivery']) 	$delivery 	= $book['id_addressbook'];
			if($book['addressbookIsBilling']) 	$billing 	= $book['id_addressbook'];
		}
	
		if($main == '') 			$sql[] = "UPDATE k_useraddressbook SET addressbookIsMain=1 		WHERE id_addressbook=".$books[0]['id_addressbook'];
		if($delivery == '') 		$sql[] = "UPDATE k_useraddressbook SET addressbookIsDelivery=1 	WHERE id_addressbook=".$books[0]['id_addressbook'];
		if($billing == '') 			$sql[] = "UPDATE k_useraddressbook SET addressbookIsBilling=1	WHERE id_addressbook=".$books[0]['id_addressbook'];
		if(sizeof($books) == '1')	$sql[] = "UPDATE k_useraddressbook SET addressbookIsProtected=1	WHERE id_addressbook=".$books[0]['id_addressbook'];
	
		if(sizeof($sql) > 0){
			foreach($sql as $q){
				$this->dbQuery($q);
			}
		}
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userAddressBookGet($opt=array(), $check=true){

	if($opt['id_user'] == NULL) return array();
	if($check) $this->userAddressBookCheck($opt['id_user']);

	$cond[] = "id_user=".$opt['id_user'];

	if($opt['delivery'] != ''){
		$dbMode = 'dbOne';
		$cond[] = 'addressbookIsDelivery=1';
	}else
	if($opt['billing'] != ''){
		$dbMode = 'dbOne';
		$cond[] = 'addressbookIsBilling=1';
	}else
	if($opt['id_addressbook'] != ''){
		$dbMode = 'dbOne';
		$cond[] = 'id_addressbook='.$opt['id_addressbook'];
	}else{
		$dbMode = 'dbMulti';
	}

	if(sizeof($cond) > 0) $where = "WHERE ".implode(" AND ", $cond);

	$ab = $this->$dbMode("SELECT * FROM k_useraddressbook ".$where);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error, $ab);

	return $ab;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
 - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */
public  function userAddressBookSet($opt=array()){

    $id_user		= $opt['id_user'];
    $id_addressbook	= $opt['id_addressbook'];
    $def			= $opt['def'];

    if(!$this->formValidation($def)) return false;

    if($id_addressbook != NULL){
        $q = $this->dbUpdate($def)." WHERE id_addressbook=".$id_addressbook;
    }else{
        $q = $this->dbInsert($def);
    }

    @$this->dbQuery($q);
    if($opt['debug']) $this->pre($this->db_query, $this->db_error);
    if($this->db_error != NULL) return false;

    $this->id_addressbook = ($id_addressbook > 0) ? $id_addressbook : $this->db_insert_id;

    if($opt['is_delivery']) $this->userAddressBookDeliverySet($this->id_addressbook, $id_user);
    if($opt['is_billing']) $this->userAddressBookBillingSet($this->id_addressbook, $id_user);

    $this->hookAction('userAddressBookSet', $this->id_addressbook);

    return true;
}
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
 - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */
public  function userAddressBookDeliverySet($id_addressbook, $id_user){

    if(intval($id_addressbook) == 0 || intval($id_user) == 0) return false;

    $this->dbQuery("UPDATE k_useraddressbook SET addressbookIsProtected=0 WHERE addressbookIsDelivery = '1' AND id_user='".$id_user."'");
    $this->dbQuery("UPDATE k_useraddressbook SET addressbookIsDelivery=0,addressbookIsMain=0 WHERE id_user='".$id_user."'");
    $this->dbQuery("UPDATE k_useraddressbook SET addressbookIsDelivery=1,addressbookIsProtected=1,addressbookIsMain=1 WHERE id_addressbook='".$id_addressbook."'");

    return true;
}
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
 - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */
public  function userAddressBookBillingSet($id_addressbook, $id_user){

    if(intval($id_addressbook) == 0 || intval($id_user) == 0) return false;

    $this->dbQuery("UPDATE k_useraddressbook SET addressbookIsProtected=0 WHERE addressbookIsBilling = '1' AND id_user='".$id_user."'");
    $this->dbQuery("UPDATE k_useraddressbook SET addressbookIsBilling=0 WHERE id_user='".$id_user."'");
    $this->dbQuery("UPDATE k_useraddressbook SET addressbookIsBilling=1,addressbookIsProtected=1 WHERE id_addressbook='".$id_addressbook."'");

    return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userAddressBookDefine($id_user, $id_addressbook, $field, $value, $erase=true){

	if($erase){
		$this->dbQuery("UPDATE ".$this->tableUserAddressBook." SET ".$field."=0 WHERE id_user=".$id_user);
	}

	$this->dbQuery("UPDATE ".$this->tableUserAddressBook." SET ".$field."=".$value." WHERE id_user=".$id_user." AND id_addressbook=".$id_addressbook);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function userAddressBookFormat($data, $opt=array()){

    $out = '';
	if($opt['name']){
		$out = $data['addressbookCivility'].' '.$data['addressbookFirstName'].' '.$data['addressbookLastName']."\n";
	}

	if($data['addressbookCompanyName']) 	$out.= $data['addressbookCompanyName']."\n";
	if($data['addressbookAddresse1']) 		$out.= $data['addressbookAddresse1']."\n";
	if($data['addressbookAddresse2']) 		$out.= $data['addressbookAddresse2']."\n";
	if($data['addressbookAddresse3']) 		$out.= $data['addressbookAddresse3']."\n";
	if($data['addressbookCityCode']) 		$out.= $data['addressbookCityCode'].' '.$data['addressbookCityName']."\n";
	if($data['addressbookStateName']) 		$out.= $data['addressbookStateName'].' ';
	if($data['addressbookCountryCode']) 	$out.= strtoupper($data['addressbookCountryCode']);

	if($opt['html']) $out = nl2br($out);

    $ret = array('out'  => $out, 'data' => $data, 'opt' => $opt);
    $ret  = $this->hookFilter('userAddressBookFormat', $ret);

	return $ret['out'];
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userImportCSV($file, $post=NULL){


	$headers 		= $post['headers'];
	$id_group		= $post['id_group'];
	$is_activate	= $post['activate'];
	$is_subscribed	= $post['userMailing'];
	$removeFirst	= $post['removeFirst'];
	$checkDoublon	= $post['checkDoublon'];
	$offset			= $post['offset'];
	$length			= $post['length'];
	$content 		= file_get_contents($file);


	# Trouver les lignes du fichiers
	$sepLigne	= "\n";
	$lignes 	= explode($sepLigne, $content);
	if(sizeof($lignes) < 2){
		$sepLigne 	= "\r"; 
		$lignes 	= explode($sepLigne, $content);
	}
	if(sizeof($lignes) < 2) return USER_IMPORT_ERRORLINE;


	
	# Trouver les colonnes du fichier
	$sepColonne = "\t";
	$colonnes 	= explode($sepColonne, $lignes[0]);
	$colonnes	= array_map('trim', $colonnes);

	if(sizeof($colonnes) == 1){
		$sepColonne = ';';
		$colonnes 	= explode($sepColonne,  $lignes[0]);
	}
	if(sizeof($colonnes) == 1){
		$sepColonne	= ','; 
		$colonnes 	= explode($sepColonne,  $lignes[0]);
	}
#	if(sizeof($colonnes) == 1) 		return USER_IMPORT_ERRORCOLUMN;
#	if(in_array(NULL, $colonnes)) 	return USER_IMPORT_EMPTYCELL;

	
	# Trouver s'il existe un caractere de protection de donnee
	$same			= 0;
	$testColonnes	= explode($sepColonne, $lignes[1]);
	foreach($testColonnes as $testColonne){
		if(substr($testColonne, 0, 1) == substr($testColonne, -1)) $same++;
	}
	if($same == sizeof($testColonnes)){
		foreach($lignes as $iLigne => $ligne){
			$tmpLigne 		 = array();
			foreach(explode($sepColonne, $ligne) as $iColonne => $colonne){
				$tmpLigne[]  = substr($colonne, 1, -1);
			}
			$lignes[$iLigne] = implode($sepColonne, $tmpLigne);
		}
	}


	# Construir la réponse
	$build = array(
		'lignes' 		=> $lignes,
		'sepLigne' 		=> $sepLigne,
		'colonnes'		=> $colonnes,
		'sepColonne'	=> $sepColonne	
	);


	# On s'occupe des headers du fichier
	if(sizeof($headers) == 0) return array('needHeaders', $build);
	foreach($headers as $index => $header){
		if($header == NULL){
		//	$labels[] = '';
		}else
		if(ereg("[0-9]{1,}", $header)){
			$labels[$index] = $header;
		}else{
			$system[$header] = $index;
		}
	}

#	if(sizeof($labels) == 0 || sizeof($system) == 0) return USER_IMPORT_HEADERS;
	if(sizeof($system) == 0) return USER_IMPORT_HEADERS;

	#$this->pre($headers, $system, $labels);	
	if($removeFirst) unset($lignes[0]);

	foreach($lignes as $nLigne => $ligne){
		$myUser = array();
		$myCol	= explode($sepColonne, $ligne);

		foreach($system as $field => $nColonne){
			$myUser[$field] = $myCol[$nColonne];
		}

		if(sizeof($labels) > 0){
			foreach($labels as $nColonne => $id_field){
				if($myCol[$nColonne] != NULL) $myUser['id_field'][$id_field] = $myCol[$nColonne];
			}
		}

		$users[] = $myUser;
	}

	if($id_group == NULL) return array('needID', $build);

	foreach($users as $index => $user){
		if(!isset($users[$index]['id_group']))		$users[$index]['id_group'] 		= $id_group;
		if(!isset($users[$index]['activate']))		$users[$index]['activate'] 		= $is_activate;
		if(!isset($users[$index]['userMailing']))	$users[$index]['userMailing']  	= $is_subscribed;
	}

	$usersInDb = array();
	foreach($this->dbMulti("SELECT id_user, userMail FROM k_user WHERE id_group = ".$id_group) as $userInDb){
		$usersInDb[$userInDb['id_user']] = $userInDb['userMail'];
	}

	$doublon = array();
	$errors	 = array();
	$done	 = array();
	$todo	 = sizeof($users);

	if($offset >= 0 && $length > 0) $users = array_splice($users, $offset, $length);

	foreach($users as $user){
		$ajoutable = true;

		if($checkDoublon && in_array($user['user'], $usersInDb)) 						$ajoutable = false; // Tri sur l'email
		if($user['id_user'] != NULL && array_key_exists($user['id_user'], $usersInDb))	$ajoutable = false; // Tri sur l'ID
		
		if($ajoutable){
			unset($def);

			if($user['userMail'] != NULL) $def['k_user']['userMail']	= array('value' => $user['userMail']);
			if($user['id_group'] != NULL) $def['k_user']['id_group']	= array('value' => $user['id_group']);

			$success = $this->userSet(array(
				'debug'			=> false,
				'def'			=> $def,
				'field'			=> $user['id_field']
			));

			if(!$success){
				$errors[] = $user;
			}else{
				$done[]   = $user;
			}

		}else{
			$doublon[] = $user;
		}
	}

	return array('imported', array('todo' => $todo, 'done' => $done, 'error' => $errors, 'doublon' => $doublon));
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userImportAddressBookCSV($file, $post=NULL){

    $headers        = $post['headers'];
    $id_group       = $post['id_group'];
    $is_activate    = $post['activate'];
    $is_subscribed  = $post['userMailing'];
    $removeFirst    = $post['removeFirst'];
    $checkDoublon   = $post['checkDoublon'];
    $sepColonne     = $post['sepColonne'];
    $deliveryDefault    = $post['deliveryDefault'];
    $billingDefault     = $post['billingDefault'];
    $offset         = $post['offset'];
    $length         = $post['length'];
    $content        = file_get_contents($file);
    
    # Trouver les lignes du fichiers
    $sepLigne   = "\n";
    $lignes     = explode($sepLigne, $content);
    if(sizeof($lignes) < 2){
        $sepLigne   = "\r"; 
        $lignes     = explode($sepLigne, $content);
    }
    if(sizeof($lignes) < 2) return USER_IMPORT_ERRORLINE;

    # Trouver les colonnes du fichier
    if($sepColonne == '') {
	    $sepColonne = "\t";
	    $colonnes   = explode($sepColonne, $lignes[0]);
	    $colonnes   = array_map('trim', $colonnes);
	
	    if(sizeof($colonnes) == 1){
	        $sepColonne = ';';
	        $colonnes   = explode($sepColonne,  $lignes[0]);
	    }
	    if(sizeof($colonnes) == 1){
	        $sepColonne = ','; 
	        $colonnes   = explode($sepColonne,  $lignes[0]);
	    }
    }else {
    	$colonnes   = explode($sepColonne,  $lignes[0]);
    }
   //if(sizeof($colonnes) == 1)      return USER_IMPORT_ERRORCOLUMN;
   //if(in_array(NULL, $colonnes))   return USER_IMPORT_EMPTYCELL;

    
    # Trouver s'il existe un caractere de protection de donnee
    $same           = 0;
    $testColonnes   = explode($sepColonne, $lignes[1]);
    foreach($testColonnes as $testColonne){
        if(substr($testColonne, 0, 1) == substr($testColonne, -1)) $same++;
    }
    if($same == sizeof($testColonnes)){
        foreach($lignes as $iLigne => $ligne){
            $tmpLigne        = array();
            foreach(explode($sepColonne, $ligne) as $iColonne => $colonne){
                $tmpLigne[]  = substr($colonne, 1, -1);
            }
            $lignes[$iLigne] = implode($sepColonne, $tmpLigne);
        }
    }


    # Construir la réponse
    $build = array(
        'lignes'        => $lignes,
        'sepLigne'      => $sepLigne,
        'colonnes'      => $colonnes,
        'sepColonne'    => $sepColonne  
    );

    # On s'occupe des headers du fichier
    if(sizeof($headers) == 0) return array('needHeaders', $build);
    foreach($headers as $index => $header){
        //echo substr($header,0,6);
        if($header == NULL){
        //  $labels[] = '';
        }else
        if(substr($header,0,6) == 'label-'){
            $labels[$index] = substr($header,6,strlen($header));
        }else{
            $system[$header] = $index;
        }
    }
    
    //die($this->pre($headers, $system, $labels));    
    

#   if(sizeof($labels) == 0 || sizeof($system) == 0) return USER_IMPORT_HEADERS;
    //if(sizeof($system) == 0) return USER_IMPORT_HEADERS;

    #$this->pre($headers, $system, $labels);    
    if($removeFirst) unset($lignes[0]);

    foreach($lignes as $nLigne => $ligne){
        $myUser = array();
        $myCol  = explode($sepColonne, $ligne);

        if(sizeof($system) > 0)foreach($system as $field => $nColonne){
            $myUser[$field] = $myCol[$nColonne];
        }

        if(sizeof($labels) > 0){
            foreach($labels as $nColonne => $id_field){
                if($myCol[$nColonne] != NULL) $myUser['id_field'][$id_field] = $myCol[$nColonne];
            }
        }

        $users[] = $myUser;
    }

    //die($this->pre($users));
    if($id_group == NULL) return array('needID', $build);

    foreach($users as $index => $user){
        if(!isset($users[$index]['id_group']))      $users[$index]['id_group']      = $id_group;
        if(!isset($users[$index]['activate']))      $users[$index]['activate']      = $is_activate;
        if(!isset($users[$index]['userMailing']))   $users[$index]['userMailing']   = $is_subscribed;
    }

    $usersInDb = array();
    foreach($this->dbMulti("SELECT id_user, userMail FROM k_user WHERE is_deleted=0 AND id_group = ".$id_group) as $userInDb){
        $usersInDb[$userInDb['id_user']] = $userInDb['userMail'];
    }

    $doublon = array();
    $errors  = array();
    $done    = array();
    $todo    = sizeof($users);

    if($offset >= 0 && $length > 0) $users = array_splice($users, $offset, $length);

    foreach($users as $user){
        $create_user = false;
		$id_user = $user['id_user'];
        if($user['id_user'] == '' && $user['userMail'] == '')  $create_user = true;
        if($user['id_user'] == '' && $user['userMail'] != ''){
            if(in_array($user['userMail'], $usersInDb)){
                $recup_id_user = array_keys($usersInDb, $user['userMail']);
                $id_user = $recup_id_user[0];
            }else
                $create_user = true;
        }  
        
        if($create_user){
            unset($def);
            $def['k_user']['userMail']  = array('value' => $user['userMail']);
            $def['k_user']['id_group']  = array('value' => $user['id_group']);
            $def['k_user']['is_active']  = array('value' => $is_activate);
            //$q = $this->dbInsert($def);
            //@$this->dbQuery($q);
			$this->userSet(array(
                'debug'         => false,
                'def'           => $def
            ));
            
            //$id_user = $this->db_insert_id;
            $id_user = $this->id_user;
        }
   

        if($id_user > 0){
            
            unset($def);
            $fields = array();
            
            $fields['id_user'] = array('value' => $id_user, 'query' => 1);
            foreach($user['id_field'] as $k=>$v){
                $fields[$k] = array('value' => $v, 'query' => 1);
            }
            
            $def['k_useraddressbook'] = $fields;
			if($deliveryDefault == 1 || $deliveryDefault == 1) {
				$this->dbQuery("UPDATE k_useraddressbook SET addressbookIsMain=0,addressbookIsProtected=0 WHERE id_user='".$id_user."'");	
				$def['k_useraddressbook']['addressbookIsMain'] 		= array('value' => '1', 'query' => 1);
				$def['k_useraddressbook']['addressbookIsProtected'] 	= array('value' => '1', 'query' => 1);
			}
			if($deliveryDefault == 1) {
				$def['k_useraddressbook']['addressbookIsDelivery'] 	= array('value' => '1', 'query' => 1);
				$this->dbQuery("UPDATE k_useraddressbook SET addressbookIsDelivery=0 WHERE id_user='".$id_user."'");				
			}
			if($billingDefault == 1) {	
				$def['k_useraddressbook']['addressbookIsBilling'] = array('value' => '1', 'query' => 1);			
				$this->dbQuery("UPDATE k_useraddressbook SET addressbookIsBilling=0 WHERE id_user='".$id_user."'");
			}

            unset($defuser);
            if($user['userMail'] != NULL) $defuser['k_user']['userMail']    = array('value' => $user['userMail']);
            if($user['id_group'] != NULL) $defuser['k_user']['id_group']    = array('value' => $user['id_group']);
            
            $success = $this->userSet(array(
                'debug'         => false,
                'id_user'       => $id_user,
                'def'           => $defuser,
                'addressbook'   => $def
            ));
            if(!$success){
                $errors[] = $user;
            }else{
                $done[]   = $user;
            }

        }else{
            $doublon[] = $user;
        }
    }

    return array('imported', array('todo' => $todo, 'done' => $done, 'error' => $errors, 'doublon' => $doublon));
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userAssoGet($id_user, $id_field, $id_type){

	$asso = $this->dbMulti("
		SELECT * FROM k_userasso
		WHERE id_user=".$id_user." AND id_field=".$id_field." AND id_type=".$id_type
	);

	foreach($asso as $e){
		$r[] = $e['id_content'];
	}
	
	return is_array($r) ? $r : array();
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
 + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userAssoSet($id_user, $id_field, $id_type, $value){

	if(!is_array($value)) $value = array();
	$this->dbQuery("DELETE FROM k_userasso WHERE id_user=".$id_user." AND id_field=".$id_field);
	#$this->pre($this->db_query, $this->db_error);

	if(sizeof($value) > 0){
		foreach($value as $id_content){
			if($id_content > 0) $added[] = "(".$id_user.", ".$id_field.", ".$id_type.", ".$id_content.")";
			
		}
		
		if(sizeof($added) > 0){
			$this->dbQuery("INSERT IGNORE INTO k_userasso (id_user, id_field, id_type, id_content) VALUES ".implode(',', $added));
			#$this->pre($this->db_query, $this->db_error);
			#dbQuery
		}
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userAssoUserGet($id_user, $id_field){

	$asso = $this->dbMulti("SELECT * FROM k_userasso WHERE id_user=".$id_user." AND id_field=".$id_field);

	foreach($asso as $e){
		$r[] = $e['id_userb'];
	}

	return is_array($r) ? $r : array();
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
 + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userAssoUserSet($id_user, $id_field, $ids_user){

	$this->dbQuery("DELETE FROM k_userasso WHERE id_user=".$id_user." AND id_field=".$id_field);
	#$this->pre($this->db_query, $this->db_error);

	if(sizeof($ids_user) > 0){
		foreach($ids_user as $e){
			if($id_user > 0) $added[] = "(".$id_user.", ".$id_field.", ".$e.")";
		}

		if(sizeof($added) > 0){
			$this->dbQuery("INSERT IGNORE INTO k_userasso (id_user, id_field, id_userb) VALUES ".implode(',', $added));
		#	$this->pre($this->db_query, $this->db_error);
		}
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
 + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public  function userSocialCircle($id_user){

	# Security
	#
	$found = false;
	foreach($this->dbMulti("SHOW TABLES") as $t){
		$t = array_values($t);
		if($t[0] == 'k_usersocial'){ $found = true; break; }
	}
	if(!$found) return false;


	# Circles
	#
	$circles = $this->dbMulti("SELECT * FROM k_socialcircle WHERE id_user IS NULL AND socialCircleMemberQuery != ''");
	if(sizeof($circles) == 0) return false;

	foreach($circles as $e){

		$q = json_decode($e['socialCircleMemberQuery'], true);
		if(is_array($q)){
			$sql = $this->userSearchSQL($q);

			
			$sql = "SELECT id_user FROM k_userdata WHERE id_user=".$id_user." AND ".$sql;
			$raw = $this->dbOne($sql);
			#$this->pre($sql, $raw);
			
			if($raw['id_user'] == $id_user){
				$in[] = "(".$id_user.",".$e['id_socialcircle'].")";
			}else{
				$out[]= $e['id_socialcircle'];
			}
			
			$upd[] = $e['id_socialcircle'];
		}
	}

	if(sizeof($upd) > 0){	
	
		if(sizeof($in) > 0){
			$this->dbQuery("INSERT IGNORE INTO  k_socialcircleuser (id_user, id_socialcircle) VALUES \n".implode(",\n", $in));
			#$this->pre($this->db_query, $this->db_error);
		}
		
		if(sizeof($out) > 0){
			$this->dbQuery("DELETE FROM k_socialcircleuser WHERE id_user=".$id_user." AND id_socialcircle IN(".implode(',', $out).")");
			#$this->pre($this->db_query, $this->db_error);
		}
		
		foreach($upd as $e){
			$this->apiLoad('socialCircle')->socialCircleMemberCount($e);
		}
	}

}

}