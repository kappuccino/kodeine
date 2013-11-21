<?php

class socialCircle extends social{

function __clone(){}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function socialCircleGet($opt=array()){

		if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep='socialCircleGet() @='.json_encode($opt));

		if($opt['debug']) $this->pre("[OPT]", $opt);

		# Gerer les OPTIONS
		#
		$dbMode		= 'dbMulti';
		$cond[]     = 'k_socialcircle.socialCircleHide=0';
		$searchLink	= ($opt['searchLink'] != '') ? $opt['searchLink'] : 'OR';

		// GET id_socialcircle
		if(array_key_exists('id_socialcircle', $opt)){

			if(is_array($opt['id_socialcircle']) && sizeof($opt['id_socialcircle']) > 0){
				$cond[] = "k_socialcircle.id_socialcircle IN(".implode(',', $opt['id_socialcircle']).")";
			}else
			if(intval($opt['id_socialcircle']) > 0){
				$cond[] = "k_socialcircle.id_socialcircle=".$opt['id_socialcircle'];
				$dbMode = 'dbOne';
			}else{
				if($opt['debug']) $this->pre("ERROR: ID_SOCIALCIRCLE (NUMERIC,ARRAY)", "GIVEN", var_export($opt['id_socialcircle'], true));
				return array();
			}
		}

		// GET id_user
		if(array_key_exists('id_user', $opt)){

			if(intval($opt['id_user']) > 0){
				if($opt['is_into']){
					$cond[] = "(k_socialcircleuser.id_user=".$opt['id_user']." OR k_socialcircle.id_user=".$opt['id_user'].")";

					$join[] = "INNER JOIN k_socialcircleuser ON k_socialcircle.id_socialcircle = k_socialcircleuser.id_socialcircle";
				}else
				if($opt['public']){
					$cond[] = "(k_socialcircle.id_user IS NULL OR k_socialcircle.id_user=".$opt['id_user'].")";
					$public = true;
				}else{
					$cond[] = "k_socialcircle.id_user=".$opt['id_user'];
				}
			}else{
				if($opt['debug']) $this->pre("ERROR: ID_USER (NUMERIC)", "GIVEN", var_export($opt['id_user'], true));
				return array();
			}
		}

		// GET withAutor
		if($opt['withOwner'] == true){
			$join[] = "INNER JOIN k_user ON k_socialcircle.id_user = k_user.id_user";
			$join[] = "INNER JOIN k_userdata ON k_user.id_user = k_userdata.id_user";
			$mapp	= true;
		}


		# FIELD
		#
		$fields = $this->apiLoad('field')->fieldGet(array('socialCircle' => true));
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
					$tmp[] = $this->dbMatch("k_socialcircle.field".$e['searchField'], $e['searchValue'], $e['searchMode']);
				}else
				if($fieldKey[$e['searchField']]['id_field'] != NULL){
					$tmp[] = $this->dbMatch("k_socialcircle.field".$fieldKey[$e['searchField']]['id_field'], $e['searchValue'], $e['searchMode']);
				}else
				if($field[$e['searchField']]['id_field'] != NULL){
					$tmp[] = $this->dbMatch("k_socialcircle.field".$field[$e['searchField']]['id_field'], $e['searchValue'], $e['searchMode']);
				}else{
					$tmp[] = $this->dbMatch("k_socialcircle.".$e['searchField'], $e['searchValue'], $e['searchMode']);
				}
			}

			if(sizeof($tmp) > 0) $cond[] = "(".implode(' '.$searchLink.' ', $tmp).")";
		}else
		if($opt['search'] != ''){
			$cond[] = $this->dbMatch("socialCircleName", $opt['search'], 'CT');
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
				: "k_socialcircle.id_socialcircle ASC");

			if($opt['offset'] >= 0 && $opt['limit'] > 0) $limit = "\nLIMIT ".$opt['offset'].",".$opt['limit'];
		}else{
			$flip = true;
		}


		# CIRCLES
		#
		$circles 		= $this->$dbMode("SELECT SQL_CALC_FOUND_ROWS * FROM k_socialcircle\n" . $join . $where . $order . $limit);
		$this->total	= $this->db_num_total;
		if($opt['debug']) $this->pre("[QUERY]", $this->db_query, "[ERROR]", $this->db_error, "[DATA]", $circles);


		# FORMAT
		#
		if(sizeof($circles) > 0){

			if($flip) $circles = array($circles);

			$circles = $this->socialCircleMapping(array(
				'circles'	=> $circles,
				'fields'	=> $this->apiLoad('field')->fieldGet(array('socialCircle' => true)),
				'withOwner'	=> $opt['withOwner'],
				'withMedia'	=> $opt['withMedia']
			));

			if($flip) $circles = $circles[0];
		}

		if($opt['debug']) $this->pre("[FORMAT]", $circles);

		if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep);

		return $circles;
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function socialCircleSet($opt){

		# NEW !
		#
		if($opt['id_socialcircle'] == NULL){
			$this->dbQuery("INSERT INTO k_socialcircle (socialCircleDateCreation, socialCircleDateUpdate) VALUES (NOW(), NOW())");
			if($opt['debug']) $this->pre($this->db_query, $this->db_error);
			$id_socialcircle = $this->db_insert_id;

			$opt['core']['id_socialcircle'] = array('value' => $id_socialcircle);

			unset($opt['core']['id_socialcircle']);
		}else{
			$opt['core']['socialCircleDateUpdate'] = array('function' => 'NOW()');
			$id_socialcircle = $opt['id_socialcircle'];
		}

		$this->id_socialcircle = $id_socialcircle;

		# CORE
		#
		$query = $this->dbUpdate(array('k_socialcircle' => $opt['core']))." WHERE id_socialcircle=".$id_socialcircle;
		$this->dbQuery($query);
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);

		# FIELD
		#
		if(sizeof($opt['field']) > 0){

			# Si on utilise le KEY au lieu des ID
			$fields = $this->apiLoad('field')->fieldGet(array('socialCircle' => true));
			foreach($fields as $e){
				$fieldsKey[$e['fieldKey']] = $e;
			} $fields = $fieldsKey;

			unset($def);
			$apiField = $this->apiLoad('field');

			foreach($opt['field'] as $id_field => $value){
				if(!is_integer($id_field)) $id_field = $fields[$id_field]['id_field'];
				$value = $apiField->fieldSaveValue($id_field, $value);
				$def['k_socialcircle']['field'.$id_field] = array('value' => $value);
			}

			$this->dbQuery($this->dbUpdate($def)." WHERE id_socialcircle=".$id_socialcircle);
			if($opt['debug']) $this->pre($this->db_query, $this->db_error);
		}

		# ... NEW => Member
		#
		if($opt['id_socialcircle'] == NULL && intval($opt['core']['id_user']['value']) > 0){
			$this->socialCircleMemberAdd(array(
				'debug'				=> $opt['debug'],
				'id_socialcircle'	=> $id_socialcircle,
				'user'				=> $opt['core']['id_user']['value'],
				'force'				=> true
			));
		}

		# SANDBOXING
		#
		/*$this->apiLoad('socialSandbox')->socialSandboxPush(array(
			'debug'				=> false,
			'socialSandboxType'	=> 'id_socialcircle',
			'socialSandboxId'	=> $id_socialcircle
		));*/

		# NOTIFICATION
		#
		if($opt['notification']){

			$this->apiLoad('socialActivity')->socialActivitySet(array(
				'debug'					=> $opt['debug'],
				'id_user'				=> $opt['core']['id_user']['value'],
				'notification'			=> true,

				'socialActivityKey'		=> 'id_socialcircle',
				'socialActivityId'		=> $id_socialcircle,
				'socialActivityFlag'	=> 'UPDATE'
			));
		}

		return true;
	}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialCircleMapping($opt){

	$circles = $opt['circles'];

	foreach($circles as $n => $e){

		# OWNER
		#
		if($opt['withOwner'] == true){
			$circles[$n]['owner'] = $this->apiLoad('user')->userGet(array(
				'id_user' => $e['id_user']
			));
		}
		
		# MEDIA
		#
		if($opt['withMedia'] == true){
			$media = json_decode(($e['socialCircleMedia']), true);
			if(sizeof($media) > 0){
				unset($media_);
				foreach($media as $m){
					$v = $this->mediaInfos($m['url']);
					$media_[$m['type']][] = $v;
				}
				$circles[$n]['socialCircleMedia'] = $media_;
			}else{
				$circles[$n]['socialCircleMedia'] = array();
			}
		}

		# FIELD
		#
		if(sizeof($opt['fields']) > 0){
			foreach($opt['fields'] as $f){

				$v = $e['field'.$f['id_field']];

				if($f['fieldType'] == 'media'){
					$v = json_decode($v, true); unset($media);
					if(sizeof($v) > 0 && is_array($v)){
						foreach($v as $e){
							$e_ = $this->mediaInfos($e['url']);
							$e_['caption'] = $e['caption'];
							$media[$e['type']][] = $e_;
						}
						$circles[$n]['field'][$f['fieldKey']] = $media;
					}
				}else

				if(is_array($v) && $f['fieldType'] == 'user'){
					unset($tmp);
					foreach($v as $id_user){
						$tmp[] = $this->dbOne("SELECT * FROM k_user WHERE id_user=".$id_user);
					}
					$circles[$n]['field'][$f['fieldKey']] = $tmp;
				}else

				if(is_array($v) && $f['fieldType'] == 'content'){
					unset($tmp);
					foreach($v as $id_content){
						$tmp[] = $this->dbOne("SELECT * FROM k_contentdata WHERE id_content=".$id_content);
					}
					$circles[$n]['field'][$f['fieldKey']] = $tmp;
				}else
				if(in_array($f['fieldType'], array('onechoice', 'multichoice')) && substr($v, 0, 2) == $this->splitter && substr($v, -2) == $this->splitter && $v != $this->splitter){
					$part = explode($this->splitter, substr($v, 2, -2));
					$circles[$n]['field'][$f['fieldKey']] = implode("<br />", $part);

				}else
				if(in_array($f['fieldType'], array('social-forum')) && substr($v, 0, 2) == $this->splitter && substr($v, -2) == $this->splitter && $v != $this->splitter){
					$part = explode($this->splitter, substr($v, 2, -2));
					$circles[$n]['field'][$f['fieldKey']] = $part;
				}else{
					$circles[$n]['field'][$f['fieldKey']] = $v;
				}

				unset($circles[$n]['field'.$f['id_field']]);
			}
		}		
	}

	return $circles;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialCircleRemove($id_socialcircle){

	if(intval($id_socialcircle) == 0) return false;

	// Cache cleaning
	$users = $this->dbMulti("SELECT id_user FROM k_socialcircleuser WHERE id_socialcircle=".$id_socialcircle);
	$users = $this->dbKey($users, 'id_user');

	foreach($users as $e){
		$this->socialUserCacheClean($e);
	}

	// Suppression du CIRCLE et de ses USER
	$this->dbQuery("DELETE FROM k_socialcirclepending 	WHERE id_socialcircle=".$id_socialcircle);
	$this->dbQuery("DELETE FROM k_socialcircleuser 		WHERE id_socialcircle=".$id_socialcircle);
	$this->dbQuery("DELETE FROM k_socialcircle 			WHERE id_socialcircle=".$id_socialcircle);

	// Supprime les POST relier a ce CIRCLE
	$posts = $this->apiLoad('socialPost')->socialPostGet(array(
		'id_socialcircle'	=> $id_socialcircle,
		'noLimit'			=> true
	));

	foreach($posts as $p){
		$this->apiLoad('socialPost')->socialPostHide(array(
			'id_socialpost' => $p['id_socialpost']
		));
	}
}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function socialCircleHide($opt){

		$id_socialcircle = $opt['id_socialcircle'];
		if(intval($id_socialcircle) == 0) return false;

		// Cache cleaning
		$users = $this->dbMulti("SELECT id_user FROM k_socialcircleuser WHERE id_socialcircle=".$id_socialcircle);
		foreach($users as $e){
			$this->socialUserCacheClean($e['id_user']);
		}

		// Suppression des data CIRCLE/USER
		$this->dbQuery("DELETE FROM k_socialcirclepending 	WHERE id_socialcircle=".$id_socialcircle);
		$this->dbQuery("DELETE FROM k_socialcircleuser 		WHERE id_socialcircle=".$id_socialcircle);

		// Masquer le CIRCLE
		$this->dbQuery("UPDATE k_socialcircle SET socialCircleHide=1 WHERE id_socialcircle=".$id_socialcircle);

		// Supprime les POST relier a ce CIRCLE
		$posts = $this->apiLoad('socialPost')->socialPostGet(array(
			'id_socialcircle'	=> $id_socialcircle,
			'noLimit'			=> true
		));

		foreach($posts as $p){
			$this->apiLoad('socialPost')->socialPostHide(array(
				'id_socialpost' => $p['id_socialpost']
			));
		}
	}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialCircleMediaLink($opt){

	if(!is_array($opt['url'])) $opt['url'] = array($opt['url']);

	# Get content
	#
	$circle = $this->socialCircleGet(array(
		'id_socialcircle'	=> $opt['id_socialcircle'],
		'raw'				=> true
	));
	if($circle['id_socialcircle'] == NULL){
		if($opt['debug']) $this->pre("Circle not found with id_socialcircle", $opt['id_socialcircle']);
		return false;
	}
	
	# CLEAR and Exit 
	#
	if($opt['clear']){
		$this->dbQuery("UPDATE k_socialcircle SET socialCircleMedia='' WHERE id_socialcircle=".$opt['id_socialcircle']);
		if($opt['debug']) $this->pre("CLEAR", $this->db_query, $this->db_error);
		return true;
	}

	# Update ARRAY
	#
	$media = json_decode($circle['socialCircleMedia'], true);
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
	
		$def = array('k_socialcircle' => array(
			'socialCircleMedia' => array('value' => json_encode($media))
		));
	
		$this->dbQuery($this->dbUpdate($def)." WHERE id_socialcircle=".$opt['id_socialcircle']);
		if($opt['debug']) $this->pre("UPDATE", $this->db_query, $this->db_error);

		return true;
	}
	
	return false;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialCirclePendingAccept($opt){

	$user				= is_array($opt['user']) ? $opt['user'] : array($opt['user']);
	$id_socialcircle	= $opt['id_socialcircle'];
	$circle				= $this->socialCircleGet(array(
		'id_socialcircle' => $id_socialcircle
	));
	
	foreach($user as $id_user){
		if(intval($id_user) > 0){
			$del[] = $id_user;
			$add[] = "(".$id_socialcircle.", ".$id_user.", ".time().")";
		}
	}

	if(sizeof($add) > 0){
		$this->dbQuery("DELETE FROM k_socialcirclepending WHERE id_socialcircle=".$id_socialcircle." AND id_user IN(".implode(', ', $del).")");
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);

		$this->dbQuery("INSERT IGNORE INTO k_socialcircleuser (id_socialcircle, id_user, timeline) VALUES ".implode(', ', $add));
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);

		// ACTIVITY + NOTIFICATION
		$this->apiLoad('socialActivity')->socialActivitySet(array(
			'debug'					=> false,
			'id_user'				=> $circle['id_user'],	// ACTIVITY au nom du OWNER du CIRCLE
			'notification'			=> true,
			'notificationUser'		=> $id_user,			// NOTIFIER ces utilisateurs

			'socialActivityKey'		=> 'id_socialcircle',
			'socialActivityId'		=> $id_socialcircle,
			'socialActivityFlag'	=> 'ACCEPTED'
		));
	}


	$this->socialCircleMemberCount(array(
		'debug'				=> false,
		'id_socialcircle'	=> $id_socialcircle
	));

	$this->socialCircleMemberFix(array(
		'debug'				=> false,
		'users'				=> $user
	));
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialCirclePendingDismiss($opt){

	$id_socialcircle	= $opt['id_socialcircle'];
	$user				= $opt['user'];

	$user = is_array($user) ? $user : array($user);

	$this->dbQuery("DELETE FROM k_socialcircleuser 	  WHERE id_socialcircle=".$id_socialcircle." AND id_user IN(".implode(',', $user).")");
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);

	$this->dbQuery("DELETE FROM k_socialcirclepending WHERE id_socialcircle=".$id_socialcircle." AND id_user IN(".implode(',', $user).")");
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);

	$this->socialCircleMemberCount(array(
		'debug'				=> false,
		'id_socialcircle'	=> $id_socialcircle
	));
	
	$this->socialCircleMemberFix(array(
		'debug'				=> false,
		'users'				=> $user
	));

}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialCirclePendingGet($opt){

	if($opt['debug']) $this->pre("OPTION", $opt);

	$pushId = true;

	// GET id_socialcircle (les MEMBRE EN ATTENTE pour cercle)
	if(array_key_exists('id_socialcircle', $opt)){

		if(is_array($opt['id_socialcircle'])){
			$cond[] = "id_socialcircle IN(".implode(', ', $opt['id_socialcircle']).")";
		}else
		if(intval($opt['id_socialcircle']) > 0){
			$cond[] = "id_socialcircle=".$opt['id_socialcircle'];
		}else{
			if($opt['debug']) $this->pre("ERROR: ID_SOCIALCIRCLE (NUMERIC,ARRAY)", "GIVEN", var_export($opt['id_socialcircle'], true));
			return array();
		}

		$select = 'k_socialcirclepending.id_user';

		if(array_key_exists('withUser', $opt)){
			if($opt['withUser'] === true){
				$join[] = "INNER JOIN k_user 	 ON k_socialcirclepending.id_user = k_user.id_user";
				$join[] = "INNER JOIN k_userdata ON k_socialcirclepending.id_user = k_userdata.id_user";
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

		$select = 'k_socialcirclepending.id_socialcircle';
	}

	if(sizeof($cond) > 0) $where = "\n\nWHERE\n\t".implode("\n\tAND\n\t", $cond)."\n";
	if(sizeof($join) > 0) $join	 = "\n".implode("\n", $join)."\n";

	$ids = $this->dbMulti("SELECT ".$select." FROM k_socialcirclepending ". $join . $where);
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
function socialCircleMemberGet($opt){

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep='socialCircleMemberGet() @='.json_encode($opt));

	if($opt['debug']) $this->pre("OPTION", $opt);

	$pushId = true;

	// GET id_socialcircle (les membre d'un cercle)
	if(array_key_exists('id_socialcircle', $opt)){

		if(is_array($opt['id_socialcircle'])){
			$cond[] = "id_socialcircle IN(".implode(', ', $opt['id_socialcircle']).")";
		}else
		if(intval($opt['id_socialcircle']) > 0){
			$cond[] = "id_socialcircle=".$opt['id_socialcircle'];
		}else{
			if($opt['debug']) $this->pre("ERROR: ID_SOCIALCIRCLE (NUMERIC,ARRAY)", "GIVEN", var_export($opt['id_socialcircle'], true));
			return array();
		}

		$select = 'k_socialcircleuser.id_user';

		if(array_key_exists('withUser', $opt)){
			if($opt['withUser'] === true){
				$join[] = "INNER JOIN k_user 	 ON k_socialcircleuser.id_user = k_user.id_user";
				$join[] = "INNER JOIN k_userdata ON k_socialcircleuser.id_user = k_userdata.id_user";
				$select	= '*';
				$pushId	= false;
			}else{
				if($opt['debug']) $this->pre("ERROR: WITH_USER (BOOLEAN:TRUE)", "GIVEN", var_export($opt['withUser'], true));
				return array();
			}
		}

	}else

	// GET id_user (les cercle dont un user est membre)
	if(array_key_exists('id_user', $opt)){

		if(intval($opt['id_user']) > 0){
			$cond[] = "id_user=".$opt['id_user'];
		}else{
			if($opt['debug']) $this->pre("ERROR: ID_USER (NUMERIC)", "GIVEN", var_export($opt['id_user'], true));
			return array();
		}

		$select = 'k_socialcircleuser.id_socialcircle';
	}

	if(sizeof($cond) > 0) $where = "\n\nWHERE\n\t".implode("\n\tAND\n\t", $cond)."\n";
	if(sizeof($join) > 0) $join	 = "\n".implode("\n", $join)."\n";

	$ids = $this->dbMulti("SELECT ".$select." FROM k_socialcircleuser ". $join . $where);
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

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialCircleMemberAdd($opt){
if($opt['debug']) $this->pre($opt);

	$id_socialcircle	= $opt['id_socialcircle'];
	$user				= $opt['user'];

	$circle	= $this->socialCircleGet(array(
		'id_socialcircle' => $id_socialcircle
	));
	
	$table	= ($circle['is_private'] == '1') ? 'k_socialcirclepending' : 'k_socialcircleuser';
	$flag   = ($table == 'k_socialcirclepending') ? 'PENDING' : 'ENTER';
	$user	= is_array($user) ? $user : array($user);

	if($opt['force']) $table = 'k_socialcircleuser';

	foreach($user as $id_user){
		if(intval($id_user) > 0) $tmp[] = "(".$id_socialcircle.", ".$id_user.", ".time().")";
	}

	if(sizeof($tmp) > 0){
		$this->dbQuery("INSERT IGNORE INTO ".$table." (id_socialcircle, id_user, timeline) VALUES ".implode(', ', $tmp));
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	}

	$this->socialCircleMemberCount(array(
		'debug'				=> $opt['debug'],
		'id_socialcircle'	=> $id_socialcircle
	));

	$this->socialCircleMemberFix(array(
		'debug'				=> $opt['debug'],
		'users'				=> $user
	));

	# SANDBOXING
	#
	$this->apiLoad('socialSandbox')->socialSandboxPush(array(
		'debug'				=> false,
		'socialSandboxType'	=> 'id_socialcircle',
		'socialSandboxId'	=> $id_socialcircle
	));

	# ACTIVITY + NOTIFICATION
	#
	foreach($user as $id_user){
		$this->apiLoad('socialActivity')->socialActivitySet(array(
			'debug'					=> false,
			'id_user'				=> $id_user,
			'notification'			=> true,
		#	'notificationUser'		=> $circle['id_user'], 	// Notifier le OWNER du circle
	
			'socialActivityKey'		=> 'id_socialcircle',
			'socialActivityId'		=> $id_socialcircle,
			'socialActivityFlag'	=> $flag
		));
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialCircleMemberRemove($opt){

	$id_socialcircle	= $opt['id_socialcircle'];
	$user				= $opt['user'];

	$user = is_array($user) ? $user : array($user);

	$this->dbQuery("DELETE FROM k_socialcircleuser WHERE id_socialcircle=".$id_socialcircle." AND id_user IN(".implode(',', $user).")");
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);

	$this->socialCircleMemberCount(array(
		'debug'				=> $opt['debug'],
		'id_socialcircle'	=> $id_socialcircle
	));
	
	$this->socialCircleMemberFix(array(
		'debug'				=> $opt['debug'],
		'users'				=> $user
	));

	# ACTIVITY + NOTIFICATION
	#
	foreach($user as $id_user){
		$this->apiLoad('socialActivity')->socialActivitySet(array(
			'debug'					=> false,
			'remove'				=> true,
			'id_user'				=> $id_user,
			'socialActivityKey'		=> 'id_socialcircle',
			'socialActivityId'		=> $id_socialcircle,
		));
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialCircleMemberCount($opt){

	$id_socialcircle = $opt['id_socialcircle'];

	// The Circle
	$circle	= $this->socialCircleGet(array(
		'id_socialcircle' => $id_socialcircle
	));
	if(intval($circle['id_socialcircle']) == 0){
		if($opt['debug']) $this->pre("ERROR: ID_SOCIALCIRCLE (NUMERIC)", "GIVEN", var_export($opt['id_socialcircle'], true));
		return false;
	}

	// Member
	$c = $this->dbOne("SELECT COUNT(*) AS c FROM k_socialcircleuser WHERE id_socialcircle=".$id_socialcircle);
	$this->dbQuery("UPDATE k_socialcircle SET socialCircleMemberCount=".intval($c['c'])." WHERE id_socialcircle=".$id_socialcircle);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	

	// Pending
	$c = $this->dbOne("SELECT COUNT(*) AS c FROM k_socialcirclepending WHERE id_socialcircle=".$id_socialcircle);
	$this->dbQuery("UPDATE k_socialcircle SET socialCirclePendingCount=".intval($c['c'])." WHERE id_socialcircle=".$id_socialcircle);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	
	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialCircleMemberFix($opt){

	$users	= $opt['users'];
	$users	= is_array($users) ? $users : array($users);

	if(sizeof($users) == 0) return false;

	foreach($users as $u){

		// Member
		$mem		= array();
		$circles	= $this->dbMulti("SELECT id_socialcircle FROM  k_socialcircleuser WHERE id_user=".$u);
		foreach($circles as $c){ $mem[] = intval($c['id_socialcircle']); }
		$jsonM		= json_encode($mem);

		// Owner
		$own		= array();
		$owner		= $this->dbMulti("SELECT id_socialcircle FROM  k_socialcircle WHERE id_user=".$u);
		foreach($owner as $o){ $own[] = intval($o['id_socialcircle']); }
		$jsonO		= json_encode($own);

		// Pending
		$pend		= array();
		$pending	= $this->dbMulti("SELECT id_socialcircle FROM  k_socialcirclepending WHERE id_user=".$u);
		foreach($pending as $p){ $pend[] = intval($p['id_socialcircle']); }
		$jsonP		= json_encode($pend);

		// Save
		$query	= $this->dbInsert(array('k_usersocial' => array(
			'id_user'					=> array('value' => $u),
			'userSocialCircleMember'	=> array('value' => $jsonM),
			'userSocialCircleOwner'		=> array('value' => $jsonO),
			'userSocialCirclePending'	=> array('value' => $jsonP)
		)));

		$query .= "\nON DUPLICATE KEY UPDATE ".
			"userSocialCircleMember='".$jsonM."', ".
			"userSocialCircleOwner='".$jsonO."', ".
			"userSocialCirclePending='".$jsonP."'; ";

		$this->dbQuery($query);
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);

		// Cache cleaning
		$this->socialUserCacheClean($u);
	}

	return true;
}

}