<?php

namespace Kodeine;

class user extends appModule{

	public $userLoginSuccess    = false;
	public $userNotExists       = false;
	public $userIsExpired       = false;
	public $userIsLogged        = false;
	public $user                = array();
	public $profile             = array();
	public $userIsAdmin         = false;
	private $userCookieName     = '';
	private $userCookieTtl      = 31104000;

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public function __construct(){
		$this->userCookieName = str_replace('.', '_', $_SERVER['SERVER_NAME']);
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public function logout(){
		$this->app->setMe(NULL);
		unset($_SESSION['id_user']);
		return false;
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public function login($login=NULL, $passwd=NULL, $cookie=true){

		$this->userLoginSuccess = false;

		$login 	= addslashes($login);
		$passwd = addslashes($passwd);

		$get = "SELECT * FROM k_user INNER JOIN k_group ON k_user.id_group = k_group.id_group\n";

		# Log depuis le POST, SESSION ou le COOKIE
		#
		if(filter_var(trim($login, FILTER_VALIDATE_EMAIL) !== false && trim($passwd) != '')){
			$query = $get."WHERE is_active=1 AND is_deleted=0 AND userMail='".$login."' AND userPasswd=MD5('".$passwd."')";
			$hook  = true;
		}else
			if(intval($_SESSION['id_user']) > 0){
				$query	= $get."WHERE is_active=1 AND is_deleted=0 AND id_user=".$_SESSION['id_user'];
			}else
				if($_COOKIE[$this->userCookieName] != NULL && $cookie){
					$cookie = json_decode(stripslashes($_COOKIE[$this->userCookieName]), true);

					if($cookie[0] != NULL && $cookie[1] != NULL){
						$query = $get."WHERE is_active=1 AND is_deleted=0 AND userMail='".$cookie['u']."' AND userPasswd='".$cookie['p']."'";
					}else{
						return $this->logout();
					}
				}else{
					return $this->logout();
				}

		$user = $this->mysql->one($query);

		# Deloger de force si rien de trouver
		#
		if($user['id_user'] == ''){
			$this->userNotExists = true;
			return $this->logout();
		}

		# Si le USER a plus le droit d'etre logue (EXPIRED), le deloger
		#
		if($user['userDateExpire'] != '' && $this->helper->timestamp($user['userDateExpire']) < time()){
			$this->userIsExpired = true;
			$this->logout();
			return false;
		}

		# Sauver le USER
		#
		$this->user        = $user;
		#   $this->profile     = $this->profile($user['id_profile']);
		$this->userIsAdmin = ($user['is_admin'] === '1');

		$this->app->setMe($this);

		$_SESSION['id_user'] = $user['id_user'];

		if($cookie){
			@setcookie(
				$this->userCookieName,
				json_encode(array('u' => $user['userMail'], 'p' => $user['userPasswd'])),
				(time()+($this->userCookieTtl)),
				'/'
			);
		}

		$this->userIsLogged = true;
		$this->userLoginSuccess = true;

		if($hook) $this->hook->action('userLogin', $user['id_user']);

		return true;
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function get($opt=array()){

		if(BENCHME) $this->bench->marker($bmStep='userGet() @='.json_encode($opt));

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

		# GET not this id_user
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

		# Gerer fields
		#
		$field = $this->app->load('field')->fieldGet(array('user' => true));
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
			'data'     => $users,
			'useMedia' => $useMedia,
			'useField' => $useField,
			'fields'   => $field
		));

		if($flip) $users = $users[0];

		if(BENCHME) $this->bench->marker($bmStep);

		return $users;
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function set($opt){

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

			@$this->mysql->query($q);
			if($opt['debug']) $this->pre($this->db_query, $this->db_error);
			if($this->db_error != NULL) return false;
		}

		$this->id_user = ($id_user > 0) ? $id_user : $this->db_insert_id;

		if($id_user == NULL){
			$this->mysql->query("UPDATE k_user SET userDateCreate=NOW(), userDateUpdate=NOW() WHERE id_user=".$this->id_user);
			if($opt['debug']) $this->pre($this->db_query, $this->db_error);

			$this->mysql->query("INSERT INTO k_userdata (id_user) VALUES (".$this->id_user.")");
			if($opt['debug']) $this->pre($this->db_query, $this->db_error);

			$this->mysql->query(
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
			$this->mysql->query($this->dbUpdate($def)." WHERE id_user=".$this->id_user);
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

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function remove($id_user){

		if(empty($id_user)) return false;

		$this->mysql->query("UPDATE k_user SET is_deleted=1 WHERE id_user=".$id_user);
		$this->hookAction('userRemove', $id_user);

		return true;
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userFieldSet($id_field, $def){

		if(!$this->formValidation($def)) return false;

		if($id_field > 0){
			$q = $this->dbUpdate($def)." WHERE id_field=".$id_field;
		}else{
			$q = $this->dbInsert($def);
		}

		@$this->mysql->query($q);

		if($this->db_error != NULL) return false;
		$this->id_field = ($id_field > 0) ? $id_field : $this->db_insert_id;

		if($id_field == NULL){
			$this->mysql->query("ALTER TABLE `k_userdata` ADD `field".$this->id_field."` VARCHAR(255) NOT NULL");
		}

		return true;
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
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
			$this->mysql->query("UPDATE k_user SET userMedia='' WHERE id_user=".$opt['id_user']);
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

			$this->mysql->query($this->dbUpdate($def)." WHERE id_user=".$opt['id_user']);
			if($opt['debug']) $this->pre("UPDATE", $this->db_query, $this->db_error);

			return true;
		}

		return false;
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
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
							$tmp[] = $this->mysql->one("SELECT * FROM k_user WHERE id_user=".$id_user);
						}
						$users[$idx]['field'][$f['fieldKey']] = $tmp;
					}else

					if(is_array($v) && $f['fieldType'] == 'content'){
						unset($tmp);
						foreach($v as $id_content){
							$tmp[] = $this->mysql->one("SELECT * FROM k_contentdata WHERE id_content=".$id_content);
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

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userAssoGet($id_user, $id_field, $id_type){

		$asso = $this->mysql->multi("
			SELECT * FROM k_userasso
			WHERE id_user=".$id_user." AND id_field=".$id_field." AND id_type=".$id_type
		);

		foreach($asso as $e){
			$r[] = $e['id_content'];
		}

		return is_array($r) ? $r : array();
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userAssoSet($id_user, $id_field, $id_type, $value){

		if(!is_array($value)) $value = array();
		$this->mysql->query("DELETE FROM k_userasso WHERE id_user=".$id_user." AND id_field=".$id_field);
		#$this->pre($this->db_query, $this->db_error);

		if(sizeof($value) > 0){
			foreach($value as $id_content){
				if($id_content > 0) $added[] = "(".$id_user.", ".$id_field.", ".$id_type.", ".$id_content.")";

			}

			if(sizeof($added) > 0){
				$this->mysql->query("INSERT IGNORE INTO k_userasso (id_user, id_field, id_type, id_content) VALUES ".implode(',', $added));
				#$this->pre($this->db_query, $this->db_error);
				#dbQuery
			}
		}
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userAssoUserGet($id_user, $id_field){

		$asso = $this->mysql->multi("SELECT * FROM k_userasso WHERE id_user=".$id_user." AND id_field=".$id_field);

		foreach($asso as $e){
			$r[] = $e['id_userb'];
		}

		return is_array($r) ? $r : array();
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userAssoUserSet($id_user, $id_field, $ids_user){

		$this->mysql->query("DELETE FROM k_userasso WHERE id_user=".$id_user." AND id_field=".$id_field);
		#$this->pre($this->db_query, $this->db_error);

		if(sizeof($ids_user) > 0){
			foreach($ids_user as $e){
				if($id_user > 0) $added[] = "(".$id_user.", ".$id_field.", ".$e.")";
			}

			if(sizeof($added) > 0){
				$this->mysql->query("INSERT IGNORE INTO k_userasso (id_user, id_field, id_userb) VALUES ".implode(',', $added));
			#	$this->pre($this->db_query, $this->db_error);
			}
		}
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userSocialCircle($id_user){

		# Security
		#
		$found = false;
		foreach($this->mysql->multi("SHOW TABLES") as $t){
			$t = array_values($t);
			if($t[0] == 'k_usersocial'){ $found = true; break; }
		}
		if(!$found) return false;


		# Circles
		#
		$circles = $this->mysql->multi("SELECT * FROM k_socialcircle WHERE id_user IS NULL AND socialCircleMemberQuery != ''");
		if(sizeof($circles) == 0) return false;

		foreach($circles as $e){

			$q = json_decode($e['socialCircleMemberQuery'], true);
			if(is_array($q)){
				$sql = $this->userSearchSQL($q);


				$sql = "SELECT id_user FROM k_userdata WHERE id_user=".$id_user." AND ".$sql;
				$raw = $this->mysql->one($sql);
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
				$this->mysql->query("INSERT IGNORE INTO  k_socialcircleuser (id_user, id_socialcircle) VALUES \n".implode(",\n", $in));
				#$this->pre($this->db_query, $this->db_error);
			}

			if(sizeof($out) > 0){
				$this->mysql->query("DELETE FROM k_socialcircleuser WHERE id_user=".$id_user." AND id_socialcircle IN(".implode(',', $out).")");
				#$this->pre($this->db_query, $this->db_error);
			}

			foreach($upd as $e){
				$this->apiLoad('socialCircle')->socialCircleMemberCount($e);
			}
		}

	}

}