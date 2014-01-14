<?php

class socialEvent extends social{

function __clone(){}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
function socialEventGet($opt=array()){

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep='socialEventGet() @='.json_encode($opt));

	if($opt['debug']) $this->pre("[OPT]", $opt);

	# Gerer les OPTIONS
	#
	$dbMode		= 'dbMulti';
	$cond[]     = 'k_socialevent.socialEventHide=0';
	$searchLink	= ($opt['searchLink'] != '') ? $opt['searchLink'] : 'OR';

	// GET id_socialevent
	if(array_key_exists('id_socialevent', $opt)){
		
		if(is_array($opt['id_socialevent']) && sizeof($opt['id_socialevent']) > 0){
			$cond[] = "k_socialevent.id_socialevent IN(".implode(',', $opt['id_socialevent']).")";
		}else
		if(intval($opt['id_socialevent']) > 0){
			$cond[] = "k_socialevent.id_socialevent=".$opt['id_socialevent'];
			$dbMode = 'dbOne';
		}else{
			if($opt['debug']) $this->pre("ERROR: ID_SOCIALEVENT (NUMERIC,ARRAY)", "GIVEN", var_export($opt['id_socialevent'], true));
			return array();
		}
	}

	// GET id_user
	if(array_key_exists('id_user', $opt)){

		if(intval($opt['id_user']) > 0){
			if($opt['is_into']){
				$cond[] = "(k_socialeventuser.id_user=".$opt['id_user']." OR k_socialevent.id_user=".$opt['id_user'].")";
				$join[] = "INNER JOIN k_socialeventuser ON k_socialevent.id_socialevent = k_socialeventuser.id_socialevent";
			}else
			if($opt['public']){
				$cond[] = "(k_socialevent.id_user IS NULL OR k_socialevent.id_user=".$opt['id_user'].")";
				$public = true;
			}else{
				$cond[] = "k_socialevent.id_user=".$opt['id_user'];
			}
		}else{
			if($opt['debug']) $this->pre("ERROR: ID_USER (NUMERIC)", "GIVEN", var_export($opt['id_user'], true));
			return array();
		}
	}


	# FIELD
	#
	$fields = $this->apiLoad('field')->fieldGet(array('socialEvent' => true));
	foreach($fields as $f){
		$fieldKey[$f['fieldKey']] = $f;
		if($f['is_search'])												$fieldSearch[]		= $f;
		if($f['fieldType'] == 'content' && $f['fieldContentType'] > 0)	$fieldAssoContent[] = $f;
		if($f['fieldType'] == 'user')									$fieldAssoUser[]	= $f;
	}


	# RECHECHE
	#
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
		$cond[] = $this->dbMatch("socialEventName", $opt['search'], 'CT');
	}


	# Former les CONDITIONS
	#		
	if(sizeof($cond) > 0) $where = "WHERE ".implode(" AND ", $cond);
	if(sizeof($join) > 0) $join	 = "\n".implode("\n", $join)."\n";


	# Former les LIMITATIONS et ORDRE
	#
	if($dbMode == 'dbMulti'){
		$order = "\nORDER BY ".(($opt['order'] != '' && $opt['direction'] != '')
			? $opt['order']." ".$opt['direction']
			: "k_socialevent.id_socialevent ASC");

		if($opt['offset'] >= 0 && $opt['limit'] > 0) $limit = "\nLIMIT ".$opt['offset'].",".$opt['limit'];
		if($opt['noLimit']) unset($limit);
	}else{
		$flip = true;
	}


	# EVENTS
	#
	$events 		= $this->$dbMode("SELECT SQL_CALC_FOUND_ROWS * FROM k_socialevent\n" . $join . $where . $order . $limit);
	$this->total	= $this->db_num_total;
	if($opt['debug']) $this->pre("[QUERY]", $this->db_query, "[ERROR]", $this->db_error, "[DATA]", $events);


	# FORMAT
	#
	if(sizeof($events) > 0){

		if($flip) $events = array($events);
		
		$events = $this->socialEventMapping(array(
			'data'		=> $events,
			'fields'	=> $this->apiLoad('field')->fieldGet(array('socialEvent' => true)),
			'withOwner'	=> $opt['withOwner'],
			'withMedia'	=> $opt['withMedia']
		));

		if($flip) $events = $events[0];
	}

	if($opt['debug']) $this->pre("[FORMAT]", $events);

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep);

	return $events;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialEventSet($opt){

	# NEW !
	#
	if($opt['id_socialevent'] == NULL){
		$this->dbQuery("INSERT INTO k_socialevent (socialEventDateCreation, socialEventDateUpdate) VALUES (NOW(), NOW())");
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
		$id_socialevent = $this->db_insert_id;
		
		$opt['core']['id_socialevent'] = array('value' => $id_socialevent);

		unset($opt['core']['id_socialevent']);
	}else{
		$opt['core']['socialEventDateUpdate'] = array('function' => 'NOW()');
		$id_socialevent = $opt['id_socialevent'];
	}

	$this->id_socialevent = $id_socialevent;

	# CORE
	#
	$query = $this->dbUpdate(array('k_socialevent' => $opt['core']))." WHERE id_socialevent=".$id_socialevent;
	$this->dbQuery($query);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);

	# FIELD
	#
	if(sizeof($opt['field']) > 0){

		# Si on utilise le KEY au lieu des ID
		$fields = $this->apiLoad('field')->fieldGet(array('socialEvent' => true));
		foreach($fields as $e){
			$fieldsKey[$e['fieldKey']] = $e;
		} $fields = $fieldsKey;

		unset($def);
		$apiField = $this->apiLoad('field');

		foreach($opt['field'] as $id_field => $value){
			if(!is_integer($id_field)) $id_field = $fields[$id_field]['id_field'];
			
			if(intval($id_field) > 0){
				$value = $apiField->fieldSaveValue($id_field, $value);
				$def['k_socialevent']['field'.$id_field] = array('value' => $value);
			}
		}

		$this->dbQuery($this->dbUpdate($def)." WHERE id_socialevent=".$id_socialevent);
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	}

	# ... NEW => Member
	#
	if($opt['id_socialevent'] == NULL && intval($opt['core']['id_user']['value']) > 0){
		$this->socialEventMemberAdd(array(
			'debug'				=> $opt['debug'],
			'id_socialevent'	=> $id_socialevent,
			'user'				=> $opt['core']['id_user']['value'],
			'force'				=> true
		));
	}

	# SANDBOXING
	#
	/*$this->apiLoad('socialSandbox')->socialSandboxPush(array(
		'debug'				=> false,
		'socialSandboxType'	=> 'id_socialevent',
		'socialSandboxId'	=> $id_socialevent
	));*/

	# NOTIFICATION
	#
	if($opt['notification']){
		$this->apiLoad('socialActivity')->socialActivitySet(array(
			'debug'					=> $opt['debug'],
			'id_user'				=> $opt['core']['id_user']['value'],
			'notification'			=> true,
			'socialActivityKey'		=> 'id_socialevent',
			'socialActivityId'		=> $id_socialevent,
			'socialActivityFlag'	=> 'UPDATE'
		));
	}

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialEventMapping($opt){

	$data = $opt['data'];

	foreach($data as $n => $e){

		# OWNER
		#
		if($opt['withOwner'] == true){
			$data[$n]['owner'] = $this->apiLoad('user')->userGet(array(
				'id_user' 	=> $e['id_user'],
				'useMedia'	=> $opt['withMedia'],
			));
		}
		
		# MEDIA
		#
		if($opt['withMedia'] == true){
			$media = json_decode(($e['socialEventMedia']), true);
			if(sizeof($media) > 0){
				unset($media_);
				foreach($media as $m){
					$v = $this->mediaInfos($m['url']);
					$media_[$m['type']][] = $v;
				}
				$data[$n]['socialEventMedia'] = $media_;
			}else{
				$data[$n]['socialEventMedia'] = array();
			}
		}

		# FIELD
		#
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
					$v = is_array($v) ? $v : array();
					foreach($v as $id_user){
						$tmp_ = $this->dbOne("SELECT id_user FROM k_user WHERE id_user=".$id_user);
						if($tmp_['id_user'] != '') $tmp[] = $tmp_;
					}

					$data[$n]['field'.$f['id_field']] = (($param['type'] == 'solo' && sizeof($v) == 1) ? $tmp[0] : $tmp);
				}else
				if($f['fieldType'] == 'content'){
					$v = is_array($v) ? $v : array();
					foreach($v as $id_content){
						$tmp_ = $this->dbOne("SELECT * FROM k_contentdata WHERE id_content=".$id_content);
						if($tmp_['id_content'] != '') $tmp[] = $tmp_;
					}

					$data[$n]['field'][$f['fieldKey']] = (($param['type'] == 'solo' && sizeof($v) == 1) ? $tmp[0] : $tmp);
				}else{
					$data[$n]['field'][$f['fieldKey']] = $v;
				}

				unset($data[$n]['field'.$f['id_field']], $media, $tmp, $tmp_);
			}
		}		
	}

	return $data;
}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function socialEventRemove($id_socialevent){

			if(intval($id_socialevent) == 0) return false;

			// USER
			$users = $this->dbMulti("SELECT id_user FROM k_socialeventuser WHERE id_socialevent=".$id_socialevent);
			$users = $this->dbKey($users, 'id_user');
			foreach($users as $e){
				$this->socialUserCacheClean($e);
			}

			// POST
			$posts = $this->apiLoad('socialPost')->socialPostGet(array(
				'id_socialevent'	=> $id_socialevent,
				'noLimit'			=> true
			));
			foreach($posts as $p){
				$this->apiLoad('socialPost')->socialPostHide(array(
					'id_socialpost' => $p['id_socialpost']
				));
			}

			// REMOVE
			$this->dbQuery("DELETE FROM k_socialeventpending 	WHERE id_socialevent=".$id_socialevent);
			$this->dbQuery("DELETE FROM k_socialeventuser 		WHERE id_socialevent=".$id_socialevent);
			$this->dbQuery("DELETE FROM k_socialeventuserdata	WHERE id_socialevent=".$id_socialevent);
			$this->dbQuery("DELETE FROM k_socialevent 			WHERE id_socialevent=".$id_socialevent);
			$this->dbQuery("DELETE FROM k_socialpostevent		WHERE id_socialevent=".$id_socialevent);

			$this->dbQuery("DELETE FROM k_socialsandbox 		WHERE socialSandboxId=".$id_socialevent." AND socialSandboxType='id_socialevent'");
		}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function socialEventHide($opt){

		$id_socialevent = $opt['id_socialevent'];
		if(intval($id_socialevent) == 0) return false;

		// USER
		$users = $this->dbMulti("SELECT id_user FROM k_socialeventuser WHERE id_socialevent=".$id_socialevent);
		foreach($users as $e){
			$this->socialUserCacheClean($e['id_user']);
		}

		// POST -> HIDE
		$posts = $this->apiLoad('socialPost')->socialPostGet(array(
			'id_socialevent' => $id_socialevent,
			'noLimit'        => true
		));
		foreach($posts as $p){
			$this->apiLoad('socialPost')->socialPostHide(array(
				'id_socialpost' => $p['id_socialpost']
			));
		}

		// HIDE
		$this->dbQuery("UPDATE k_socialevent SET socialEventHide=1 WHERE id_socialevent=".$id_socialevent);

		// REMOVE
		$this->dbQuery("DELETE FROM k_socialsandbox WHERE socialSandboxId=".$id_socialevent." AND socialSandboxType='id_socialevent'");
	}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialEventMediaLink($opt){

	if(!is_array($opt['url'])) $opt['url'] = array($opt['url']);

	# Get content
	#
	$event = $this->socialEventGet(array(
		'id_socialevent'	=> $opt['id_socialevent'],
		'raw'				=> true
	));
	if($event['id_socialevent'] == NULL){
		if($opt['debug']) $this->pre("Event not found with id_socialevent", $opt['id_socialevent']);
		return false;
	}
	
	# CLEAR and Exit 
	#
	if($opt['clear']){
		$this->dbQuery("UPDATE k_socialevent SET socialEventMedia='' WHERE id_socialevent=".$opt['id_socialevent']);
		if($opt['debug']) $this->pre("CLEAR", $this->db_query, $this->db_error);
		return true;
	}

	# Update ARRAY
	#
	$media = json_decode($event['socialEventMedia'], true);
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
		
		// Type (image=picture -- JS type corruption ?)
		$type		= ($opt['type'] == NULL) ? $this->mediaType($e) : $opt['type'];
		$type		= ($type == 'picture') ? 'image' : $type;

		foreach($opt['url'] as $e){
			$media[]	= array('type' => $type, 'url' => $e);
		}
	
		$def = array('k_socialevent' => array(
			'socialEventMedia' => array('value' => json_encode($media))
		));
	
		$this->dbQuery($this->dbUpdate($def)." WHERE id_socialevent=".$opt['id_socialevent']);
		if($opt['debug']) $this->pre("UPDATE", $this->db_query, $this->db_error);

		return true;
	}

	return false;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialEventPendingAccept($opt){

	$id_socialevent	= $opt['id_socialevent'];
	$user			= is_array($opt['user']) ? $opt['user'] : array($opt['user']);
	
	foreach($user as $id_user){
		if(intval($id_user) > 0){
			$del[] = $id_user;
			$add[] = "(".$id_socialevent.", ".$id_user.", ".time().")";
		}
	}

	if(sizeof($add) > 0){
		$this->dbQuery("DELETE FROM k_socialeventpending WHERE id_socialevent=".$id_socialevent." AND id_user IN(".implode(', ', $del).")");
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);

		$this->dbQuery("INSERT IGNORE INTO k_socialeventuser (id_socialevent, id_user, timeline) VALUES ".implode(', ', $add));
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	}

	$this->socialEventMemberCount(array(
		'debug'				=> false,
		'id_socialevent'	=> $id_socialevent
	));

	$this->socialEventMemberFix(array(
		'debug'				=> false,
		'users'				=> $user
	));
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialEventPendingDismiss($opt){

	$id_socialevent	= $opt['id_socialevent'];
	$user			= is_array($opt['user']) ? $opt['user'] : array($opt['user']);
	$where			= "WHERE id_socialevent=".$id_socialevent." AND id_user IN(".implode(',', $user).")";

	$this->dbQuery("DELETE FROM k_socialeventuser  ".$where);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);

	$this->dbQuery("DELETE FROM k_socialeventuserdata ".$where);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);

	$this->dbQuery("DELETE FROM k_socialeventpending ".$where);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);

	$this->socialEventMemberCount(array(
		'debug'				=> false,
		'id_socialevent'	=> $id_socialevent
	));
	
	$this->socialEventMemberFix(array(
		'debug'				=> false,
		'users'				=> $user
	));
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialEventPendingGet($opt){

	if($opt['debug']) $this->pre("OPTION", $opt);

	$pushId = true;

	// GET id_socialevent (les MEMBRE EN ATTENTE pour cercle)
	if(array_key_exists('id_socialevent', $opt)){

		if(is_array($opt['id_socialevent'])){
			$cond[] = "id_socialevent IN(".implode(', ', $opt['id_socialevent']).")";
		}else
		if(intval($opt['id_socialevent']) > 0){
			$cond[] = "id_socialevent=".$opt['id_socialevent'];
		}else{
			if($opt['debug']) $this->pre("ERROR: id_socialevent (NUMERIC,ARRAY)", "GIVEN", var_export($opt['id_socialevent'], true));
			return array();
		}

		$select = 'k_socialeventpending.id_user';

		if(array_key_exists('withUser', $opt)){
			if($opt['withUser'] === true){
				$join[] = "INNER JOIN k_user 	 ON k_socialeventpending.id_user = k_user.id_user";
				$join[] = "INNER JOIN k_userdata ON k_socialeventpending.id_user = k_userdata.id_user";
				$select	= '*';
				$pushId	= false;
			}else{
				if($opt['debug']) $this->pre("ERROR: WITH_USER (BOOLEAN:TRUE)", "GIVEN", var_export($opt['withUser'], true));
				return array();
			}
		}

	}else

	// GET id_user (les cercle dont JE SUIS EN ATTENTE)
	if(array_key_exists('id_user', $opt)){

		if(intval($opt['id_user']) > 0){
			$cond[] = "id_user=".$opt['id_user'];
		}else{
			if($opt['debug']) $this->pre("ERROR: ID_USER (NUMERIC)", "GIVEN", var_export($opt['id_user'], true));
			return array();
		}

		$select = 'k_socialeventpending.id_socialevent';
	}

	if(sizeof($cond) > 0) $where = "\n\nWHERE\n\t".implode("\n\tAND\n\t", $cond)."\n";
	if(sizeof($join) > 0) $join	 = "\n".implode("\n", $join)."\n";

	$ids = $this->dbMulti("SELECT ".$select." FROM k_socialeventpending ". $join . $where);
	if($opt['debug']) $this->pre("[QUERY]", $this->db_query, "[ERROR]", $this->db_error, "[DATA]", $ids);

	if(sizeof($ids) == 0) return array();

	if(!$pushId) return $ids;


	foreach($ids as $id){
		list($t,$f) = explode('.', $select);
		if($f == NULL) $f = $t;
		$tmp[] = $id[$f];
	}

	return $tmp;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialEventMemberGet($opt){

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep='socialEventMemberGet() @='.json_encode($opt));

	if($opt['debug']) $this->pre("OPTION", $opt);

	$pushId = true;

	// GET id_socialevent (les membre d'un event)
	if(array_key_exists('id_socialevent', $opt)){

		if(is_array($opt['id_socialevent'])){
			$cond[] = "id_socialevent IN(".implode(', ', $opt['id_socialevent']).")";
		}else
		if(intval($opt['id_socialevent']) > 0){
			$cond[] = "id_socialevent=".$opt['id_socialevent'];
		}else{
			if($opt['debug']) $this->pre("ERROR: id_socialevent (NUMERIC,ARRAY)", "GIVEN", var_export($opt['id_socialevent'], true));
			return array();
		}

		$select = 'k_socialeventuser.id_user';

		if(array_key_exists('withUser', $opt)){
			if($opt['withUser'] === true){
				$join[] = "INNER JOIN k_user 	 ON k_socialeventuser.id_user = k_user.id_user";
				$join[] = "INNER JOIN k_userdata ON k_socialeventuser.id_user = k_userdata.id_user";
				$select	= '*';
				$pushId	= false;
			}else{
				if($opt['debug']) $this->pre("ERROR: WITH_USER (BOOLEAN:TRUE)", "GIVEN", var_export($opt['withUser'], true));
				return array();
			}
		}

	}else

	// GET id_user (les event dont un user est participant)
	if(array_key_exists('id_user', $opt)){

		if(intval($opt['id_user']) > 0){
			$cond[] = "id_user=".$opt['id_user'];
		}else{
			if($opt['debug']) $this->pre("ERROR: ID_USER (NUMERIC)", "GIVEN", var_export($opt['id_user'], true));
			return array();
		}

		$select = 'k_socialeventuser.id_socialevent';
	}

	if(sizeof($cond) > 0) $where = "\n\nWHERE\n\t".implode("\n\tAND\n\t", $cond)."\n";
	if(sizeof($join) > 0) $join	 = "\n".implode("\n", $join)."\n";

	$ids = $this->dbMulti("SELECT ".$select." FROM k_socialeventuser ". $join . $where);
	if($opt['debug']) $this->pre("[QUERY]", $this->db_query, "[ERROR]", $this->db_error, "[DATA]", $ids);

	if(sizeof($ids) == 0) return array();

	if(!$pushId) return $ids;

	foreach($ids as $id){
		list($t,$f) = explode('.', $select);
		if($f == NULL) $f = $t;
		$tmp[] = $id[$f];
	}

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep);

	return $tmp;
}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function socialEventMemberAdd($opt){

		$id_socialevent	= $opt['id_socialevent'];
		$user			= $opt['user'];

		$event	= $this->socialEventGet(array(
			'id_socialevent' => $id_socialevent
		));

		$table	= ($event['is_moderate'] == '1') ? 'k_socialeventpending' : 'k_socialeventuser';
		$flag   = ($table == 'k_socialeventpending') ? 'PENDING' : 'ENTER';
		$user	= is_array($user) ? $user : array($user);

		if($opt['force']) $table = 'k_socialeventuser';

		foreach($user as $id_user){
			if(intval($id_user) > 0) $tmp[] = "(".$id_socialevent.", ".$id_user.", ".time().")";
		}

		if(sizeof($tmp) > 0){
			$this->dbQuery("INSERT IGNORE INTO ".$table." (id_socialevent, id_user, timeline) VALUES ".implode(', ', $tmp));
			if($opt['debug']) $this->pre($this->db_query, $this->db_error);
		}

		$this->socialEventMemberCount(array(
			'debug'          => $opt['debug'],
			'id_socialevent' => $id_socialevent
		));

		$this->socialEventMemberFix(array(
			'debug' => $opt['debug'],
			'users' => $user
		));

		# SANDBOXING
		#
		$this->apiLoad('socialSandbox')->socialSandboxPush(array(
			'debug'				=> false,
			'socialSandboxType'	=> 'id_socialevent',
			'socialSandboxId'	=> $id_socialevent
		));

		# ACTIVITY + NOTIFICATION
		#
		foreach($user as $e){
			$this->apiLoad('socialActivity')->socialActivitySet(array(
				'debug'              => $opt['debug'],
				'id_user'            => $e,
				'notification'       => true,
				'socialActivityKey'  => 'id_socialevent',
				'socialActivityId'   => $id_socialevent,
				'socialActivityFlag' => $flag
			));
		}
	}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialEventMemberRemove($opt){

	$id_socialevent	= $opt['id_socialevent'];
	$user			= is_array($opt['user']) ? $opt['user'] : array($opt['user']);
	$where 			= "WHERE id_socialevent=".$id_socialevent." AND id_user IN(".implode(',', $user).")";

	$this->dbQuery("DELETE FROM k_socialeventpending ".$where);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);

	$this->dbQuery("DELETE FROM k_socialeventuser ".$where);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);

	$this->dbQuery("DELETE FROM k_socialeventuserdata ".$where);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);

	$this->socialEventMemberCount(array(
		'debug'				=> $opt['debug'],
		'id_socialevent'	=> $id_socialevent
	));
	
	$this->socialEventMemberFix(array(
		'debug'				=> $opt['debug'],
		'users'				=> $user
	));
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialEventMemberCount($opt){

	$id_socialevent = $opt['id_socialevent'];

	// The Event
	$event	= $this->socialEventGet(array(
		'id_socialevent' => $id_socialevent
	));
	if(intval($event['id_socialevent']) == 0){
		if($opt['debug']) $this->pre("ERROR: id_socialevent (NUMERIC)", "GIVEN", var_export($opt['id_socialevent'], true));
		return false;
	}

	// Member
	$c = $this->dbOne("SELECT COUNT(*) AS c FROM k_socialeventuser WHERE id_socialevent=".$id_socialevent);
	$this->dbQuery("UPDATE k_socialevent SET socialEventMemberCount=".intval($c['c'])." WHERE id_socialevent=".$id_socialevent);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	

	// Pending
	$c = $this->dbOne("SELECT COUNT(*) AS c FROM k_socialeventpending WHERE id_socialevent=".$id_socialevent);
	$this->dbQuery("UPDATE k_socialevent SET socialEventPendingCount=".intval($c['c'])." WHERE id_socialevent=".$id_socialevent);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialEventMemberFix($opt){

	$users	= $opt['users'];
	$users	= is_array($users) ? $users : array($users);

	if(sizeof($users) == 0) return false;

	foreach($users as $u){

		// Member
		$mem		= array();
		$events	= $this->dbMulti("SELECT id_socialevent FROM  k_socialeventuser WHERE id_user=".$u);
		foreach($events as $c){ $mem[] = intval($c['id_socialevent']); }
		$jsonM		= json_encode($mem);

		// Owner
		$own		= array();
		$owner		= $this->dbMulti("SELECT id_socialevent FROM  k_socialevent WHERE id_user=".$u);
		foreach($owner as $o){ $own[] = intval($o['id_socialevent']); }
		$jsonO		= json_encode($own);

		// Pending
		$pend		= array();
		$pending	= $this->dbMulti("SELECT id_socialevent FROM  k_socialeventpending WHERE id_user=".$u);
		foreach($pending as $p){ $pend[] = intval($p['id_socialevent']); }
		$jsonP		= json_encode($pend);

		// Save
		$query	= $this->dbInsert(array('k_usersocial' => array(
			'id_user'					=> array('value' => $u),
			'userSocialEventMember'		=> array('value' => $jsonM),
			'userSocialEventOwner'		=> array('value' => $jsonO),
			'userSocialEventPending'	=> array('value' => $jsonP)
		)));

		$query .= "\nON DUPLICATE KEY UPDATE ".
			"userSocialEventMember='".$jsonM."', ".
			"userSocialEventOwner='".$jsonO."', ".
			"userSocialEventPending='".$jsonP."'; ";

		$this->dbQuery($query);
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);

		// Cache cleaning
		$this->socialUserCacheClean($u);
	}

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialEventUserDataGet($opt=array()){

	if($opt['debug']) $this->pre("[OPT]", $opt);

	# Gerer les OPTIONS
	#
	$dbMode		= 'dbMulti';
	$searchLink	= ($opt['searchLink'] != '') ? $opt['searchLink'] : 'OR';

	// GET id_user
	if(array_key_exists('id_user', $opt)){

		if(is_array($opt['id_user']) && sizeof($opt['id_user']) > 0){
			$cond[] = "k_socialeventuserdata.id_user IN(".implode(',', $opt['id_user']).')';
		}else
		if(intval($opt['id_user']) > 0){
			$cond[] = "k_socialeventuserdata.id_user=".$opt['id_user'];
		}else{
			if($opt['debug']) $this->pre("ERROR: ID_USER (NUMERIC)", "GIVEN", var_export($opt['id_user'], true));
			return array();
		}
	}

	// GET id_socialevent
	if(array_key_exists('id_socialevent', $opt)){

		if(is_array($opt['id_socialevent']) && sizeof($opt['id_socialevent']) > 0){
			$cond[] = "k_socialeventuserdata.id_socialevent IN(".implode(',', $opt['id_socialevent']).')';
		}else
		if(intval($opt['id_socialevent']) > 0){
			$dbMode = 'dbOne';
			$cond[] = "k_socialeventuserdata.id_socialevent=".$opt['id_socialevent'];
		}else{
			if($opt['debug']) $this->pre("ERROR: ID_SOCIALEVENT (NUMERIC)", "GIVEN", var_export($opt['id_socialevent'], true));
			return array();
		}
	}

	# FIELD
	#
	$fields = $this->apiLoad('field')->fieldGet(array('socialEventUserData' => true));
	foreach($fields as $f){
		$fieldKey[$f['fieldKey']] = $f;
		if($f['is_search'])												$fieldSearch[]		= $f;
		if($f['fieldType'] == 'content' && $f['fieldContentType'] > 0)	$fieldAssoContent[] = $f;
		if($f['fieldType'] == 'user')									$fieldAssoUser[]	= $f;
	}


	# Former les CONDITIONS
	#		
	if(sizeof($cond) > 0) $where = "WHERE ".implode(" AND ", $cond);
	if(sizeof($join) > 0) $join	 = "\n".implode("\n", $join)."\n";


	# Former les LIMITATIONS et ORDRE
	#
	if($dbMode == 'dbMulti'){
		$order = "\nORDER BY ".(($opt['order'] != '' && $opt['direction'] != '')
			? $opt['order']." ".$opt['direction']
			: "socialEventUserDataDateUpdate DESC");

		if($opt['offset'] >= 0 && $opt['limit'] > 0) $limit = "\nLIMIT ".$opt['offset'].",".$opt['limit'];
		if($opt['noLimit']) unset($limit);
	}else{
		$flip = true;
	}


	# QUERY
	#
	$events 		= $this->$dbMode("SELECT SQL_CALC_FOUND_ROWS * FROM k_socialeventuserdata\n" . $join . $where . $order . $limit);
	$this->total	= $this->db_num_total;
	if($opt['debug']) $this->pre("[QUERY]", $this->db_query, "[ERROR]", $this->db_error, "[DATA]", $events);


	# FORMAT
	#
	if(sizeof($events) > 0){
		if($flip) $events = array($events);

		$events = $this->socialEventMapping(array(
			'data'		=> $events,
			'fields'	=> $fields,
		));

		if($flip) $events = $events[0];
	}

	if($opt['debug']) $this->pre("[FORMAT]", $events);

	return $events;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialEventUserDataSet($opt){

	# SECURITY
	#	
	if(intval($opt['id_user']) == 0 OR intval($opt['id_socialevent']) == 0){
		return false;
	}


	# NEW/UPD.CORE
	#
	$core = array(
		'id_socialevent'					=> array('value'	=> $opt['id_socialevent']),
		'id_user'							=> array('value'	=> $opt['id_user']),
		'socialEventUserDataDateCreation'	=> array('function' => 'NOW()'),
		'socialEventUserDataDateUpdate'		=> array('function' => 'NOW()')
	);
	
	$query	= $this->dbInsert(array('k_socialeventuserdata' => $core)).
			  "\nON DUPLICATE KEY UPDATE socialEventUserDataDateUpdate = NOW();";
	
	$this->dbQuery($query);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	

	# CORE
	#
	$where	= " WHERE id_socialevent=".$opt['id_socialevent']." AND id_user=".$opt['id_user'];
	if(is_array($opt['core'])){
		$this->dbQuery($this->dbUpdate(array('k_socialeventuserdata' => $opt['core'])).$where);
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	}


	# FIELD
	#
	if(sizeof($opt['field']) > 0){

		# Si on utilise le KEY au lieu des ID
		$fields = $this->apiLoad('field')->fieldGet(array('socialEventUserData' => true));
		foreach($fields as $e){
			$fieldsKey[$e['fieldKey']] = $e;
		} $fields = $fieldsKey;

		unset($def);
		$apiField = $this->apiLoad('field');

		foreach($opt['field'] as $id_field => $value){
			if(!is_integer($id_field)) $id_field = $fields[$id_field]['id_field'];

			if(intval($id_field) > 0){
				$value = $apiField->fieldSaveValue($id_field, $value);
				$def['k_socialeventuserdata']['field'.$id_field] = array('value' => $value); 
			}
		}

		$this->dbQuery($this->dbUpdate($def).$where);
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	}

	return true;
}

}