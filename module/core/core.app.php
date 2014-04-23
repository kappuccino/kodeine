<?php

class coreApp extends coreMedia {

	public	$splitter = '@@';
	public  $hook     = array('action' => array(), 'filter' => array());

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function __construct($autolog=true){

		$this->benchmarkInit();

		$this->debugMode      = isset($_GET['debug']);
		$this->formError      = array();
		$this->userCookieName = str_replace('.', NULL, $_SERVER['SERVER_NAME']);
		$this->userCookieTtl  = 31104000;
		$this->cache          = coreCache::getInstance($this);

		if(!isset($this->kodeine)) 		$this->kodeine		= array();
		if(!isset($this->apisConfig))	$this->apisConfig	= array();
		if(!isset($this->apiContext))	$this->apiContext	= 'site';
		if(!isset($this->apiConfig))	$this->apiConfig	= array();
		if(!isset($this->user))			$this->user			= array();
		if(!isset($this->profile))		$this->profile		= array();
		if(!isset($this->userIsLogged))	$this->userIsLogged	= false;

		if($autolog) $this->userIsLoged();
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function go($url){
		header("Location: ".$url);
		exit();
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function pre(){

		echo '<pre style="text-align:left; background-color:#FFFFFF; color:#515151; padding:5px; border:1px solid #515151;">';

		for($i=0; $i<func_num_args(); $i++){
			(!is_array(func_get_arg($i)) && !is_object(func_get_arg($i)))
					? print(func_get_arg($i)."\n")
					: print_r(func_get_arg($i));
		}

		echo '</pre>';
	}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function userIsLoged($login=NULL, $passwd=NULL){

#	$this->pre('-@--@-', var_export($this->userIsLogged, true));
	return ($this->userIsLogged) ? true : $this->userLogIn($login, $passwd);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function userLogin($login=NULL, $passwd=NULL){

#	$this->pre('userLogin()');#, var_export($this, true));

	$login 		= addslashes($login);
	$passwd		= addslashes($passwd);

	$get = "SELECT * FROM k_user INNER JOIN k_group ON k_user.id_group = k_group.id_group\n";

	$this->userLoginSuccess = false;

	# Log depuis le POST, SESSION ou le COOKIE
	#
	if(filter_var(trim($login, FILTER_VALIDATE_EMAIL) !== FALSE && trim($passwd) != '')){
		$user = $this->dbOne($get."WHERE is_active=1 AND is_deleted=0 AND userMail='".$login."' AND userPasswd=MD5('".$passwd."')");
		$hook = true;
	}else
	if(intval($_SESSION['id_user']) > 0){
		$user	= $this->dbOne($get."WHERE is_active=1 AND is_deleted=0 AND id_user=".$_SESSION['id_user']);
	}else
	if($_COOKIE[$this->userCookieName] != NULL){
		$cookie = unserialize(stripslashes($_COOKIE[$this->userCookieName]));

		if($cookie[0] != NULL && $cookie[1] != NULL){
			$user 	= $this->dbOne($get."WHERE is_active=1 AND is_deleted=0 AND userMail='".$cookie[0]."' AND userPasswd='".$cookie[1]."'");
		}else{
			$this->userLogout();
			return false;
		}
	}else{
		$this->userLogout();
		return false;
	}

	# Deloger de force si rien de trouver
	#
	if($user['id_user'] == ''){
		$this->userNotExists	= true;
		$this->userLogout();
		return false;
	}

	# Si le USER a plus le droit d'etre logue (EXPIRED), le deloger
	#
	if($user['userDateExpire'] != '' && $this->helperDate($user['userDateExpire'], TIMESTAMP) < time()){
		$this->userIsExpired 	= true;
		$this->userLogout();
		return false;
	}


	# Recupere le PROFILE
	#
	$profile = ($this->adminZone)
		? $this->userProfile($user['id_profile'])
		: array();
	
	# Sauver le USER
	#
	$this->user 			= $user;
	$this->profile			= $profile;
	$this->userIsAdmin		= ($user['is_admin'] === '1') ? true : false;
	$_SESSION['id_user']	= $user['id_user'];


	@setcookie(
		$this->userCookieName,
		serialize(array(
			$user['userMail'],
			$user['userPasswd'])
		),
		(time()+($this->userCookieTtl)),
		'/'
	);

	$this->userIsLogged = true;

	if($hook){
		$this->hookAction('userLogin', $user['id_user']);
		$this->userLoginSuccess = true;
	}

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function userProfile($id_profile){

	$p = ($id_profile > 0)
		? $this->dbOne("SELECT id_profile, profileRule FROM k_userprofile WHERE id_profile=".$id_profile)
		: '';
	
	if(sizeof($p) > 0){

		$p['profileRule'] = @unserialize($p['profileRule']);

		## Chapter
		#
		if(sizeof($p['profileRule']['id_chapter']) > 0){
			$id_chapter 	= array();
			$id_chapter_p	= array();
			$chapter 		= $this->dbMulti("SELECT * FROM k_chapter WHERE id_chapter IN(".implode(',', $p['profileRule']['id_chapter']).")");

			foreach($chapter as $e){
				$id_chapter		= array_merge($id_chapter, 	 explode(',', $e['chapterChildren']));
				$id_chapter_p	= array_merge($id_chapter_p, explode(',', $e['chapterParent']));
			}

			foreach($id_chapter as $idx => $e){
				if($e == NULL) unset($id_chapter[$idx]);
			}
			foreach($id_chapter_p as $idx => $e){
				if($e == NULL) unset($id_chapter_p[$idx]);
			}

			$chapter			= implode(',', $p['profileRule']['id_chapter']);
			$chapterChildren 	= implode(',', array_unique($id_chapter));
			$chapterParent		= implode(',', array_unique($id_chapter_p));
		}else{
			$chapter			= '';
			$chapterChildren 	= '';
			$chapterParent		= '';
		}

		## Category
		#
		if(sizeof($p['profileRule']['id_category']) > 0){
			$id_category	= array();
			$id_category_p	= array();
			$category 		= $this->dbMulti("SELECT * FROM k_category WHERE id_category IN(".implode(',', $p['profileRule']['id_category']).")");

			foreach($category as $e){
				$child  = $e['categoryChildren'];  if(empty($child))  $child  = $e['id_category'];
				$parent = $e['categoryParent'];    if(empty($parent)) $parent = $e['id_category'];

				$id_category 	= array_merge($id_category,   explode(',', $child));
				$id_category_p 	= array_merge($id_category_p, explode(',', $parent));
			}

			foreach($id_category as $idx => $e){
				if($e == NULL) unset($id_category[$idx]);
			}
			foreach($id_category_p as $idx => $e){
				if($e == NULL) unset($id_category_p[$idx]);
			}

			$category			= implode(',', $p['profileRule']['id_category']);
			$categoryChildren 	= implode(',', array_unique($id_category));
			$categoryParent 	= implode(',', array_unique($id_category_p));
		}else{
			$chapter			= '';
			$chapterChildren 	= '';
			$categoryParent 	= '';
		}

		## Group
		#
		if(sizeof($p['profileRule']['id_group']) > 0){
			$id_group		= array();
			$id_group_p		= array();
			$group 			= $this->dbMulti("SELECT * FROM k_group WHERE id_group IN(".implode(',', $p['profileRule']['id_group']).")");

			foreach($group as $e){
				$id_group 		= array_merge($id_group,   explode(',', $e['groupChildren']));
				$id_group_p		= array_merge($id_group_p, explode(',', $e['groupParent']));
			}

			foreach($id_group as $idx => $e){
				if($e == NULL) unset($id_group[$idx]);
			}
			foreach($id_group_p as $idx => $e){
				if($e == NULL) unset($id_group_p[$idx]);
			}

			$group				= implode(',', $p['profileRule']['id_group']);
			$groupChildren	 	= implode(',', array_unique($id_group));
			$groupParent	 	= implode(',', array_unique($id_group_p));
		}else{
			$group				= '';
			$groupeChildren 	= '';
			$groupParent		= '';
		}

		# Type
		#
		if(sizeof($p['profileRule']['id_type']) > 0){
			$type	= implode(',', $p['profileRule']['id_type']);
		}else{
			$type	= '';
		}

	}else{
		return array();
	}

	$r = array(
		'id_profile' 		=> $p['id_profile'],

		'chapter'			=> $chapter,
		'chapterChildren'	=> $chapterChildren,
		'chapterParent'		=> $chapterParent,

		'category'			=> $category,
		'categoryChildren'	=> $categoryChildren,
		'categoryParent'	=> $categoryParent,

		'group'				=> $group,
		'groupChildren'		=> $groupChildren,
		'groupParent'		=> $groupParent,

		'type'				=> $type
	);
	
	unset($p['profileRule']['id_chapter'], $p['profileRule']['id_category'],
	$p['profileRule']['id_group'], $p['profileRule']['id_type']);

	$r = is_array($p['profileRule']) ? array_merge($r, $p['profileRule']) : array();

#	$this->pre($r);

	return $r;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function userLogout(){

	$this->user			= array();
	$this->userIsAdmin	= false;
	$this->userIsLogged	= false;

	@setcookie($this->userCookieName, NULL, (time()-($this->userCookieName)), '/');
	unset($_SESSION['id_user'], $_COOKIE[$this->userCookieName]);
}

	//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function userCan($job){
		list($key, $code) = explode('.', $job);
		$profile = $this->userProfile($this->user['id_profile']);

		$v = @$profile[$key][$code];

		if($v == '1'){
			return true;
		}else
		if(strlen($v) > 1){
			return $v;
		}

		return false;
	}

	//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function apiLoad($api, $name=NULL, $new=false){

		$cst   = ($name == NULL) ? $api : $name;

		try{
			# REFERENCE	:: Utiliser l'objet deja existant
			if(get_class($this->api[$cst]) == $cst && !$new){
				$new = &$this->api[$cst];
			}else

			# CLONE 	:: Nouvel object a partir du precedent
			if(get_class($this->api[$cst]) == $cst && $new){
				$new = clone $this->api[$cst];
			}

			# CREATION 	:: Creer un nouvel objet tout neuf
			else{

				$this->api[$cst]    = $new = new $cst(false); // disable autoload

				$new->api           = &$this->api;
				$new->hook          = &$this->hook;
				$new->config		= &$this->config;
				$new->apiContext	= &$this->apiContext;
				$new->kodeine 		= &$this->kodeine;
				$new->user			= &$this->user;
				$new->profile		= &$this->profile;
				$new->cache         = &$this->cache;
				$new->apisConfig	= &$this->apisConfig;

				if(@array_key_exists($cst, $new->apisConfig)) $new->apiConfig = &$this->apisConfig[$cst];

                if(method_exists($new, '__loaded')) {
                    $new->__loaded();
                }
			}

			return $new;

		} catch (Exception $e) {
			throw new Exception('API could not be loaded : '.$api.'('.$cst.')');
		}
	}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
 + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function formValue($db, $fd){
	if(is_string($db)) $db 	= stripslashes($db);
	if(is_string($fd)) $fd 	= stripslashes($fd);

	return (sizeof($this->formError) > 0) ? $fd : $db;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function formValidation($def, $opt=array()){

	$suffix = isset($opt['suffix']) ? $opt['suffix'] : '';

	if(sizeof($def) == 0){
		$this->formErrorSet('-', true);
		return false;
	};

	foreach($def as $table => $fields){
		foreach($fields as $field => $data){
			if($data['integer']){
				if(!@is_int($data['value']+0)){ // Hack pour typage string
					$this->formErrorSet($field.$suffix, true);
				}
			}else
			if($data['email']){
				if(filter_var($data['value'], FILTER_VALIDATE_EMAIL) === FALSE){
					$this->formErrorSet($field.$suffix, true);
				}
			}else
			if($data['check'] != NULL){
				if(!preg_match('#'.$data['check'].'#', $data['value'], $regs)){
					$this->formErrorSet($field.$suffix, true);
				}
			}
		}		
	}

	return (sizeof($this->formError) > 0) ? false : true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function formErrorSet($nom, $message){
	$this->formError[$nom] = $message;
}

 /* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function formError($nom, $message){
	if($this->formError[$nom]) return $message;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function helperMessage($message){

	$message	= trim($message);
	$prompt		= substr($message, 0, 3);
	$text		= trim(substr($message, 3));

	if($prompt == 'OK:'){
		return array('valid', $text);
	}else
	if($prompt == 'KO:'){
		return array('error', $text);
	}else
	if($prompt == 'WA:'){
		return array('warning', $text);
	}
	
	return array('message', $message);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function helperDate($date, $format=''){

	if(substr_count($date, ' ') > 0){
		list($date, $time) 	= explode(' ', $date);
		list($a, $m,  $j) 	= explode('-', $date);
		list($h, $mn, $s)	= explode(':', $time);
	}else{
		list($a, $m, $j) 	= explode('-', $date);
	}

	$timestamp = mktime($h, $mn, $s, $m, $j, $a);
#				 mktime($h, $mn, $s, $m, $j, $a)

	$v = ($format == TIMESTAMP)
		? $timestamp
		: strftime($format, $timestamp);

#	$this->pre($date, 'a='.$a, 'm='.$m, 'j='.$j, 'v='.$v, 'ts='.$timestamp);

	return $v;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
 + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function helperReplace($source, $values, $del="{}"){

	if(sizeof($values) == 0) return $source;

	foreach($values as $k => $v){
		$source = str_replace($del{0}.$k.$del{1}, $v, $source);
	}

	return $source;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function helperUrlEncode($str, $language=NULL, $id_content=NULL){

	$NewText 	 = strtolower($str);
	$sep 		 = "-";
	$NewTextTemp = '';
	
	for($pos=0; $pos<strlen($NewText); $pos++){

		$l = $NewText{$pos};
		$c = ord($l);
		
		if($c >= 32 && $c < 128){
			$NewTextTemp .= $l;
		}else{
			if($c == '223'){ $NewTextTemp .= 'ss'; }
			if($c == '224'){ $NewTextTemp .= 'a';  }
			if($c == '225'){ $NewTextTemp .= 'a';  }
			if($c == '226'){ $NewTextTemp .= 'a';  }
			if($c == '229'){ $NewTextTemp .= 'a';  }
			if($c == '227'){ $NewTextTemp .= 'ae'; }
			if($c == '230'){ $NewTextTemp .= 'ae'; }
			if($c == '228'){ $NewTextTemp .= 'ae'; }
			if($c == '231'){ $NewTextTemp .= 'c';  }
			if($c == '232'){ $NewTextTemp .= 'e';  }
			if($c == '233'){ $NewTextTemp .= 'e';  }
			if($c == '234'){ $NewTextTemp .= 'e';  }
			if($c == '235'){ $NewTextTemp .= 'e';  }
			if($c == '236'){ $NewTextTemp .= 'i';  }
			if($c == '237'){ $NewTextTemp .= 'i';  }
			if($c == '238'){ $NewTextTemp .= 'i';  }
			if($c == '239'){ $NewTextTemp .= 'i';  }
			if($c == '241'){ $NewTextTemp .= 'n';  }
			if($c == '242'){ $NewTextTemp .= 'o';  }
			if($c == '243'){ $NewTextTemp .= 'o';  }
			if($c == '244'){ $NewTextTemp .= 'o';  }
			if($c == '245'){ $NewTextTemp .= 'o';  }
			if($c == '246'){ $NewTextTemp .= 'oe'; }
			if($c == '249'){ $NewTextTemp .= 'u';  }
			if($c == '250'){ $NewTextTemp .= 'u';  }
			if($c == '251'){ $NewTextTemp .= 'u';  }
			if($c == '252'){ $NewTextTemp .= 'ue'; }
			if($c == '255'){ $NewTextTemp .= 'y';  }
			if($c == '257'){ $NewTextTemp .= 'aa'; } 
			if($c == '269'){ $NewTextTemp .= 'ch'; }
			if($c == '275'){ $NewTextTemp .= 'ee'; }
			if($c == '291'){ $NewTextTemp .= 'gj'; }
			if($c == '299'){ $NewTextTemp .= 'ii'; }
			if($c == '311'){ $NewTextTemp .= 'kj'; }
			if($c == '316'){ $NewTextTemp .= 'lj'; }
			if($c == '326'){ $NewTextTemp .= 'nj'; }
			if($c == '353'){ $NewTextTemp .= 'sh'; }
			if($c == '363'){ $NewTextTemp .= 'uu'; }
			if($c == '382'){ $NewTextTemp .= 'zh'; }
			if($c == '256'){ $NewTextTemp .= 'aa'; }
			if($c == '268'){ $NewTextTemp .= 'ch'; }
			if($c == '274'){ $NewTextTemp .= 'ee'; }
			if($c == '290'){ $NewTextTemp .= 'gj'; }
			if($c == '298'){ $NewTextTemp .= 'ii'; }
			if($c == '310'){ $NewTextTemp .= 'kj'; }
			if($c == '315'){ $NewTextTemp .= 'lj'; }
			if($c == '325'){ $NewTextTemp .= 'nj'; }
			if($c == '352'){ $NewTextTemp .= 'sh'; }
			if($c == '362'){ $NewTextTemp .= 'uu'; }
			if($c == '381'){ $NewTextTemp .= 'zh'; }
		}
	}

	$NewText = $NewTextTemp;
	
	$NewText = preg_replace("/<(.*?)>/", 						'', 	$NewText);
	$NewText = preg_replace("/\&#\d+\;/", 						'', 	$NewText);
	$NewText = preg_replace("/\&\#\d+?\;/",						'',		$NewText);
	$NewText = preg_replace("/\&\S+?\;/",						'',		$NewText);
	$NewText = preg_replace("/['\"\?\.\!*$\#@%;:,=\(\)\[\]]/",	'',		$NewText);
	$NewText = preg_replace("/\s+/",							$sep,	$NewText);
	$NewText = preg_replace("/\//", 							$sep,	$NewText);
	$NewText = preg_replace("/[^a-z0-9-_]/",					'',		$NewText);
	$NewText = preg_replace("/\+/", 							$sep,	$NewText);
	$NewText = preg_replace("/[-_]+/",							$sep,	$NewText);
	$NewText = preg_replace("/\&/",								'',		$NewText);
	$NewText = preg_replace("/-$/",								'',		$NewText);
	$NewText = preg_replace("/_$/",								'',		$NewText);
	$NewText = preg_replace("/^_/",								'',		$NewText);
	$NewText = preg_replace("/^-/",								'',		$NewText);
	
	$url = $NewText;
	$found = false;
	if($language != NULL){
	
		if($id_content != NULL) $idc = " AND id_content != ".$id_content;

		$content = $this->dbMulti("SELECT contentUrl FROM k_contentdata WHERE contentUrl='".$url."' AND language='".$language."'".$idc);
		
		if(sizeof($content) > 0){
			$i = 1;

			while(!$found && $i <= 100){
				$check = $this->dbOne("SELECT 1 FROM k_contentdata WHERE contentUrl='".$url."-".$i."' AND language='".$language."'");
	
				if(!$check[1]){
					$url	= $url.'-'.$i;
					$found	= true;
				}
	
				$i++;
			}

		}
	}

	return $url;
}

/*+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-*/
public function helperJsonEncode($arr){

	//convmap since 0x80 char codes so it takes all multibyte codes (above ASCII 127).
	// So such characters are being "hidden" from normal json_encoding
	array_walk_recursive($arr, function (&$item, $key) {
		if (is_string($item)) $item = mb_encode_numericentity($item, array (0x80, 0xffff, 0, 0xffff), 'UTF-8');
	});

	return mb_decode_numericentity(json_encode($arr), array (0x80, 0xffff, 0, 0xffff), 'UTF-8');

}

/*+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-*/
public function helperJsonBeautifier($json){

	$tab          = "\t";
	$new_json     = '';
	$indent_level = 0;
	$in_string    = false;
	$json_obj     = json_decode($json);
	$len          = strlen($json);

	if($json_obj === false) return false;

#	$json = $this->helperJsonEncode($json_obj);

	for($c = 0; $c < $len; $c++){
		$char = $json[$c];
		switch($char){
			case '{':
			case '[':
				if(!$in_string){
					$new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
					$indent_level++;
				}else{
					$new_json .= $char;
				}
				break;

			case '}':
			case ']':
				if(!$in_string){
					$indent_level--;
					$new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
				}else{
					$new_json .= $char;
				}
				break;

			case ',':
				if(!$in_string){
					$new_json .= ",\n" . str_repeat($tab, $indent_level);
				}else{
					$new_json .= $char;
				}
				break;

			case ':':
				if(!$in_string){
					$new_json .= ": ";
				}else{
					$new_json .= $char;
				}
				break;

			case '"':
				if($c > 0 && $json[$c-1] != '\\'){
					$in_string = !$in_string;
				}

			default:
				$new_json .= $char;
				break;
		}
	}

	return $new_json;
}

/*+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-*/
public function helperNoAccent($string){

	return str_replace(
		array('à','á','â','ã','ä','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó',
			  'ô','õ','ö','ù','ú','û','ü','ý','ÿ','À','Á','Â','Ã','Ä','Ç','È','É',
			  'Ê','Ë','Ì','Í','Î','Ï','Ñ','Ò','Ó','Ô','Õ','Ö','Ù','Ú','Û','Ü','Ý'
		),
		array('a','a','a','a','a','c','e','e','e','e','i','i','i','i','n','o','o',
			  'o','o','o','u','u','u','u','y','y','A','A','A','A','A','C','E','E',
			  'E','E','I','I','I','I','N','O','O','O','O','O','U','U','U','U','Y'
		),
		$string
	);

}

/*+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-*/
public function helperPipeExec($cmd, $input=''){

    $proc = proc_open($cmd, array(
    	0 => array('pipe', 'r'),
    	1 => array('pipe', 'w'),
    	2 => array('pipe', 'w')),
    	$pipes
    );

    fwrite($pipes[0], $input);
    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);

    fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);

    fclose($pipes[2]);
    $rtn = proc_close($proc);

    return array(
        'stdout' => $stdout,
        'stderr' => $stderr,
        'return' => $rtn
    );
}

/*+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-*/
public function helperArrayWrapp($array, $glue){

	foreach($array as $n => $v){
		$array[$n] = $glue.$v.$glue;
	}
	
	return $array;
}


	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --
	// Retourne un float avec la precision identique a celle de la STRING (HACK ++)
	// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --
	public function helperFloat($string, $p=10){

		list($a, $b) = explode('.', $string);
		$f = $a.'.'.substr($b, 0, $p);

		return floatval($f);
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function benchmarkInit(){
		if(BENCHME && !isset($GLOBALS['bench'])){
			$GLOBALS['bench'] = &$this;
			$GLOBALS['bench']->benchmark = array(
				'time'		=> microtime(true),
				'step'		=> array(),
				'current' 	=> NULL,
				'previous' 	=> NULL,
			);
		}
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function benchmarkMarker($label){
		if(array_key_exists($label, $this->benchmark['step'])){
			$mem = max(memory_get_peak_usage(true), memory_get_usage(true));
			$this->benchmark['step'][$label]['duration']	= microtime(true) - $this->benchmark['step'][$label]['time'];
			$this->benchmark['step'][$label]['memory']		= number_format($mem, 0, '.', ',');
			$this->benchmark['current']						= NULL;
		}else{
			$this->benchmark['current']      = $label;
			$this->benchmark['step'][$label] = array(
				'time'     => microtime(true),
				'duration' => 0,
				'memory'   => 0
			);
		}
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function benchmarkProfiling(){

		$total		= microtime(true) - $this->benchmark['time'];
		$duration	= 0;
		$report		= array(array("%", "Time (s)", "Memory", "Label"));

		foreach($this->benchmark['step'] as $label => $e){
			$line = array(
				number_format(@(($e['duration'] / $total) * 100), 	6, '.', ''),
				number_format($e['duration'], 						8, '.', ''),
				$e['memory'],
				$label
			);

			list($a,$b) = explode('.', $line[0]);
			if(strlen($a) == 1) $line[0] = '0'.$line[0];

			$duration += $e['duration'];

			foreach($line as $j => $row){
				if($j < sizeof($line)-1){
					if(strlen($row) > $length[$j]) $length[$j] = strlen($row);
				}
			}

			$report[] = $line;
		}

		// Ajouter le *inconnu*
		$report[] = array(
			number_format(100 - @(($duration / $total) * 100), 	6, '.', ''),
			number_format($duration,							8, '.', ''),
			number_format(max(memory_get_peak_usage(true), memory_get_usage(true)), 0, '.', ','),
			'Not monitored code'
		);

		// Ajouter le total
		$report[] = array(
			'100',
			number_format($total, 8, '.', '')
		);

		foreach($report as $i => $line){
			foreach($line as $j => $row){
				$end[$i][] = str_pad($row, $length[$j]+5, ' ', STR_PAD_RIGHT);
			}
		}

		// Sortie visuel
		echo "<pre style=\"background-color:#333333; color:#FFFFFF; padding:5px; margin:5px; font-family:courier; font-size:10px;\">\n";

			for($i=0; $i<sizeof($end)-1; $i++){
				echo implode('', $end[$i])."\n";
			}

			echo "-------\n".implode('', $end[sizeof($end)-1])."\n";

			$total = 0; $last = '';
			echo "-------\n";
			foreach($GLOBALS['q'] as $n => $q_){
				list($t, $q, $m) = $q_;
				$total += $t;

				if($m != $last){
					echo "\n".$m."\n";
					$last = $m;
				}

				echo str_pad($n, 5);
				echo str_pad($t, 24);
				echo trim(str_replace(array("\n", "\t"), ' ', $q))."\n";
			}

			echo "-------\nTotal SQL: ".$total."\n";

			if(function_exists('__daevel_profiling')) __daevel_profiling();

		echo "</pre>";
	}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
 + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function fsFolder($folder, $mask=NULL, $options=NULL, $recursive=false){

	static $myFolders;
	static $myPWD;
	
	# Recursive
	if(!$recursive){
		$myFolders 	= array();
		$myPWD 		= getcwd();
	}
	
	# Options
	$segs = explode('_', $options);
	if(in_array('FLAT', 	$segs)) $flat 		= true;
	if(in_array('NOROOT',	$segs)) $noRoot 	= true;
	if(in_array('NOHIDDEN', $segs)) $noHidden	= true;
	if(in_array('PREG', 	$segs)) $usePreg	= true;

	if(!file_exists($folder)) return false;

	$dh  	= opendir($folder);
	$files	= array();
	while(false !== ($filename = readdir($dh))){
		if($filename != '.' && $filename != '..'){
			if($noHidden){
				if(substr($filename, 0, 1) != '.') $files[] = $filename;
			}else{
				$files[] = $filename;
			}
		}

	}
	
	if($mask == NULL){
		foreach($files as $file){
			if(is_dir($folder.'/'.$file)){
				$myFolders[] = (($noRoot) ? str_replace(KROOT, NULL, $folder) : $folder).'/'.$file;
				if(!$flat) $this->fsFolder($folder.'/'.$file, $mask, $options, true);
			}
		}
	}else{
		chdir($folder);
		$globFiles = glob($mask);
		if(!is_array($globFiles)) $globFiles = array();
	
		foreach($globFiles as $file){;
			if(is_dir($folder.'/'.$file)) $myFolders[] = (($noRoot) ? str_replace(KROOT, NULL, $folder) : $folder).'/'.$file;
			if(!$flat) $this->fsFolder($folder.'/'.$file, $mask, $options, true);
		}
	}

	chdir($myPWD);

	return $myFolders;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
 + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function fsFile($folder, $mask=NULL, $options=NULL, $recursive=false){

	static $myFiles;
	static $myPWD;
	
	# Folder Watch
	#if(substr($folder, 0, strlen(KROOT)) != KROOT) $folder = KROOT.$folder;
	#echo $folder;
	if(!file_exists($folder)) return false;
		
	# Recursive
	if(!$recursive){
		$myFiles 	= array();
		$myPWD 		= getcwd();
	}

	# Options
	$segs	= explode('_', $options);
	if(in_array('FLAT', 	$segs)) $flat 		= true;
	if(in_array('NOROOT',	$segs)) $noRoot 	= true;
	if(in_array('NOHIDDEN', $segs)) $noHidden	= true;
	if(in_array('PREG', 	$segs)) $usePreg	= true;

	if(!file_exists($folder)) return false;

	$raw	= array();
	$files	= array();
	$dh		= opendir($folder);

	while(false !== ($filename = readdir($dh))){
		if($filename != '.' && $filename != '..') $raw[] = $filename;
	}

	foreach($raw as $file){
		if($noHidden){
			if(substr($file, 0, 1) != '.') $files[] = $file;
		}else{
			$files[] = $file;
		}
	}

	if($mask == NULL){
		foreach($files as $file){
			if(is_file($folder.'/'.$file)) $myFiles[] = (($noRoot) ? str_replace(KROOT, NULL, $folder) : $folder).'/'.$file;
			if(!$flat) if(is_dir($folder.'/'.$file))	$this->fsFile($folder.'/'.$file, NULL, $options, true);
		}
	}else{
		chdir($folder);

		foreach($files as $file){
			if($usePreg){
				if(preg_match($mask, $file)){
					if(is_file($folder.'/'.$file))	$myFiles[] = (($noRoot) ? str_replace(KROOT, NULL, $folder) : $folder).'/'.$file;
				}
			}else{
				if(fnmatch($mask, $file, FNM_CASEFOLD)) {
					if(is_file($folder.'/'.$file))	$myFiles[] = (($noRoot) ? str_replace(KROOT, NULL, $folder) : $folder).'/'.$file;
				}
			}
		}

		foreach($files as $file){
			if(!$flat) if(is_dir($folder.'/'.$file) && !$flat) $this->fsFile($folder.'/'.$file, $mask, $options, true);
		}
	}

	chdir($myPWD);

	return $myFiles;
}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
// A utiliser au tout debut pour initialiser les valeurs (pour le site uniquement)
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function kodeineInit($get){

		// CONFIG //////////////////////////////////////////////////////////////////////////////////////////////////////
		// Charge les parametre de CONFIG BOOT + CUSTOM et memorise les autres APIsCONFIG
		if(BENCHME) $GLOBALS['bench']->benchmarkMarker($bmStep='kodeineInit(k_config)');
		$config = $this->dbMulti("SELECT * FROM k_config");
		foreach($config as $e){
			if($e['configModule'] == 'boot' OR $e['configModule'] == 'custom'){
				if(substr($e['configName'], 0, 7) == 'domain:' && empty($domainConfig)){
					$v = json_decode($e['configValue'], true);
					if(preg_match("#".$v['domain']."#", $_SERVER['HTTP_HOST'])) $domainConfig = $v;
				}else
				if(substr($e['configName'], 0, 9) == 'jsonCache'){
					$this->apisConfig[$e['configModule']][$e['configName']] = json_decode($e['configValue'], true);
				}else{
					$this->kodeine[$e['configName']] = $e['configValue'];
				}
			}else{
				if(substr($e['configName'], 0, 9) == 'jsonCache'){
					$e['configValue'] = json_decode($e['configValue'], true);
				}
				$this->apisConfig[$e['configModule']][$e['configName']] = $e['configValue'];
			}
		}
		unset($config, $e, $v);
		#die($this->pre("*", $this->apisConfig));
		if(BENCHME) $GLOBALS['bench']->benchmarkMarker($bmStep);


		// LANGUAGE ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Determine si on utilise la langue de l'URL GET ou celui par DEFAUT (FR)
		if(BENCHME) $GLOBALS['bench']->benchmarkMarker($bmStep='kodeineInit(languages)');
		$language = ($get['urlLanguage'] == NULL) ? $this->kodeine['defaultLanguage'] : $get['urlLanguage'];
		$country  = ($get['urlCountry']  == NULL) ? $this->kodeine['defaultLanguage'] : $get['urlCountry'];
		if(empty($this->apisConfig['boot']['jsonCacheCountry'])){
			$language = $this->countryGet(array('iso' => $language));
		}else{
			foreach($this->apisConfig['boot']['jsonCacheCountry'] as $tmp){
				if($tmp['iso'] == $language){ $language = $tmp; break; }
			}
		}
		$locale   = ($language['countryLocale'] == NULL) ? 'fr_FR' : $language['countryLocale'];
		$language = $language['iso_ref'];
		$this->kodeine['language']	= $language;
		$this->kodeine['country']	= $country;
		$this->kodeine['locale'] 	= $locale;
		setlocale(LC_ALL, $locale.'.UTF8');
		unset($locale, $language, $tmp);
		if(BENCHME) $GLOBALS['bench']->benchmarkMarker($bmStep);

		// CHAPTERS ////////////////////////////////////////////////////////////////////////////////////////////////////
		// La liste de tous les CHAPTER utilise
		if(BENCHME) $GLOBALS['bench']->benchmarkMarker($bmStep='kodeineInit(chapter)');
		$chapters = $this->apiLoad('chapter')->chapterGet(array('language' => $this->kodeine['language']));
		foreach($chapters as $e){
			$chaptersDbUi[$e['chapterUrl']] = $e['id_chapter'];
			$chaptersDbId[$e['id_chapter']] = $e;
		} unset($chapters, $e);
		if(BENCHME) $GLOBALS['bench']->benchmarkMarker($bmStep);


		// URL /////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Decortique l'URL pour determiner le CHAPTER, MODULE, FICHIER
		if(BENCHME) $GLOBALS['bench']->benchmarkMarker($bmStep='kodeineInit(url_parser)');
		$query = $get['urlRequest'];
		$parts = explode('/', $get['urlRequest']);
		if(sizeof($parts) > 0){
			$file = $parts[sizeof($parts)-1];
			if($file != NULL){
				$split = explode('.', $file);
				if(sizeof($split) > 1){
					$get['urlExtension']	= $split[sizeof($split)-1];
					$get['urlFile'] 		= substr($file, 0, strlen($file)-strlen($get['urlExtension'])-1);
				}else{
					$get['urlFile'] 		= $file;
				}
			}

			$get['urlChapter']	= $parts[sizeof($parts)-2];
			$get['urlModule'] 	= '';
			if(!@array_key_exists($get['urlChapter'], $chaptersDbUi)){
				$get['urlChapter'] = $parts[sizeof($parts)-3];
				$get['urlModule']  = $parts[sizeof($parts)-2];
			}
		}
		if(BENCHME) $GLOBALS['bench']->benchmarkMarker($bmStep);


		// CHECK DOMAIN ////////////////////////////////////////////////////////////////////////////////////////////////
		// Verifit les regles en fonction du domaine
		if(!empty($domainConfig)){
			if($domainConfig['id_chapter'] != NULL)	$this->kodeine['defaultIdChapter']	= $domainConfig['id_chapter'];
			if($domainConfig['id_theme']   != NULL)	$this->kodeine['defaultIdTheme']	= $domainConfig['id_theme'];
			if($domainConfig['language']   != NULL)	$this->kodeine['defaultLanguage']	= $domainConfig['language'];
		}

		// CHAPTER /////////////////////////////////////////////////////////////////////////////////////////////////////
		// Recupere le CHAPTER en fonction de l'URL GET ou celui par DEFAUT
		$chapter = @array_key_exists($get['urlChapter'], $chaptersDbUi)
			? $chaptersDbUi[$get['urlChapter']]
			: $chaptersDbId[$this->kodeine['defaultIdChapter']];

		$this->kodeine['id_chapter']		= $chapter['id_chapter'];
		$this->kodeine['chapterName']		= $chapter['chapterName'];
		$this->kodeine['chapterUrl']		= $chapter['chapterUrl'];
		$this->kodeine['chapterModule']		= $chapter['chapterModule'];
		$this->kodeine['chapterIdTheme']	= $chapter['id_theme'];
	#	$this->kodeine['chaptersUrl']		= $chaptersDbUi;
		$this->kodeine['chaptersIds']		= $chaptersDbId;
		unset($chapter, $chaptersDbUi, $chaptersDbId);


		# MODULE
		# Determine si on utilise le module/file de l'URL ou la valeur du CHAPTER/INDEX
		# Si le chapitre n'a pas de module de configure, utiliser content
		#
		$this->kodeine['chapterModule'] = ($this->kodeine['chapterModule'] != NULL) ? $this->kodeine['chapterModule'] : 'content';
		$this->kodeine['moduleFolder']	= ($get['urlModule'] != NULL) ? $get['urlModule'] : $this->kodeine['chapterModule'];
		$this->kodeine['moduleFile']	= ($get['urlFile']   != NULL) ? $get['urlFile']   : 'index';


		# THEME
		# Determine si on utilise le theme par DEFAUT ou le theme du CHAPTER
		#
		$id_theme	= ($this->kodeine['chapterIdTheme'] != NULL) ? $this->kodeine['chapterIdTheme'] : $this->kodeine['defaultIdTheme'];
		if(empty($this->apisConfig['boot']['jsonCacheTheme'])){
			$theme = $this->dbOne("SELECT * FROM k_theme WHERE id_theme=".$id_theme);
		}else{
			foreach($this->apisConfig['boot']['jsonCacheTheme'] as $e){
				if($e['id_theme'] == $id_theme){
					$theme = $e;
					break;
				}
			}
		}
		$this->kodeine['id_theme'] 		= $theme['id_theme'];
		$this->kodeine['themeName']		= $theme['themeName'];
		$this->kodeine['themeFolder']	= $theme['themeFolder'];
		unset($theme, $id_theme, $e);


		# GROUP
		# Memorise le groupe du USER ou bien celui par DEFAUT (-1)
		#
		$this->kodeine['id_group'] = $this->user['id_group'] ?: -1;


		# SAVE GET
		# Memorise les parametres GET modifies de l'URL
		#
		$this->kodeine['get'] = $get;


		# LOCALISATION
		# Definit en global les traductions
		#
		if(!isset($_GET['noLabel'])){
			if(BENCHME) $GLOBALS['bench']->benchmarkMarker($bmStep='kodeineInit(localisation)');

			foreach($this->dbMulti("SELECT * FROM k_localisation WHERE language = '".$this->kodeine['language']."'") as $e){
				define($e['label'], $e['translation']);
			}

			if(BENCHME) $GLOBALS['bench']->benchmarkMarker($bmStep);
		}

		# TYPE
		#
		#if(BENCHME) $GLOBALS['bench']->benchmarkMarker($bmStep='kodeineInit(type)');
		#foreach($this->dbMulti("SELECT * FROM k_type") as $e){
		#	unset($e['typeFormLayout']);
		#	$this->kodeine['typesIds'][$e['id_type']] = $e;
		#}
		#if(BENCHME) $GLOBALS['bench']->benchmarkMarker($bmStep);

		$this->kodeine = $this->hookFilter('kodeineInit', $this->kodeine);

		# DEBUG
		# Construit la zone de debugage par l'URL url?debug
		#
		if(constant('DEBUGME') === true){
			unset($this->user['groupFormLayout'], $this->user['userPasswd']);
			echo "<pre style=\"background-color:#333333; color:#FFFFFF; width:800px; padding:5px; margin:5px; font-family:courier; font-size:12px;\"> <h1>Debug Data</h1>";
				echo "[GET] ";		print_r($_GET);
				echo "[URL] ";		print_r($get);
				echo "[KODEINE] ";	print_r($this->kodeine);
				echo "[USER] ";		print_r($this->user);
				echo "[SERVER] "; 	print_r($_SERVER);
			echo "</pre>";
		}


		# FATAL Error
		# Verifier un certain nombre de point qui prend du temps a verifier a la main
		#
		$fatal = array();
		if($this->kodeine['language'] == ''){
			$fatal[] = "Language is not defined";
		}
		if(!file_exists(USER.'/theme/'.$this->kodeine['themeFolder'])){
			$fatal[] = "Theme folder \"".$this->kodeine['themeFolder']."\" is missing";
		}

		if(count($fatal) > 0){
			$this->pre(implode("\n", $fatal));
			exit();
		}

	}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function moduleInclude($module=NULL, $file=NULL){

	$file 	= ($file == NULL) 	? $this->kodeine['moduleFile']   : $file;
	$module = ($module == NULL)	? $this->kodeine['moduleFolder'] : $module;
	$inc  	= KROOT.'/module/'.$module.'/'.$file.'.php';

	if(file_exists($inc)){
		include($inc);
	}else
	if($this->debugMode){
		$this->pre('Enable to locate module file : '.$inc);
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function themeInclude($file, $options=NULL){

	# Options
	$segs = explode('_', $options);
	if(in_array('CACHE', $segs)) $useCache = true;

	$inc = $this->kTalkCheck($file)
		? $this->kTalk($file)
		: '/theme/'.$this->kodeine['themeFolder'].'/'.$file;

	if(file_exists(USER.$inc)){
		include(USER.$inc);
	}else
	if($this->debugMode){
		$this->pre('file', $file, $inc, 'Enable to locate theme file : '.$inc);
	}
}

	//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function countryGet($opt=array()){

		if(isset($opt['is_used'])) $cond[] = "is_used='".$opt['is_used']."'";

		$dbMode	= 'dbMulti';

		if($opt['ref'] != ''){
			$cond[] = "iso_ref='".$opt['ref']."'";
		}else
		if($opt['iso'] != ''){
			$dbMode = 'dbOne';
			$cond[] = "iso='".$opt['iso']."'";
		}

		if($opt['priced'] != '') $cond[] = "is_priced=".$opt['priced'];


		# Gerer les ORDRES
		if($opt['order'] != NULL && $opt['direction'] != NULL){
			$order = " ORDER BY ".$opt['order']." ".$opt['direction'];
		}

		if(sizeof($cond) > 0) $where = " WHERE ".implode(" AND ", $cond);

		if($dbMode == 'dbOne') unset($order);

		$country = $this->$dbMode("SELECT * FROM k_country ".$where.$order);
		if($opt['debug']) $this->pre($this->db_query, $this->db_error, $country);



		if($opt['debug']) $this->pre($this->db_query, $this->db_error, $country);

		return $country;
	}

	//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function countryGetByZone($iso=NULL, $l=0){

		if($l > 5) die('loop');

		$w = ($iso == NULL) ? 'iso' : "'".$iso."' AND iso!=iso_ref";
		$data = $this->dbMulti("SELECT * FROM k_country WHERE iso_ref=".$w." ORDER BY countryName ASC");

	#	$this->pre('('.$iso.')', $this->db_query, $data_);

		foreach($data as $n => $e){
			$data[$n]['sub'] = $this->countryGetByZone($e['iso'], $l+1);
		}

		return $data;
	}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function offlineMessage(){
	if(in_array($this->kodeine['id_group'], explode(',', $this->kodeine['offlineGroup']))){
		$off = $this->dbOne("SELECT * FROM k_config WHERE configModule='offline' AND configName='offlineMessage'");
		echo $off['configValue'];
		exit();
	}	
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function robotsTxtMessage(){
	$off = $this->dbOne("SELECT * FROM k_config WHERE configModule='robots.txt' AND configName='contentFile'");

	header("Content-Type: text/plain");
	echo $off['configValue'];
	exit();
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function configSet($module, $name, $value){

	// Protection
	$value	= addslashes(trim($value));
	$module	= addslashes(trim($module));
	$name	= addslashes(trim($name));

	$exists = $this->dbOne("SELECT 1 FROM k_config WHERE configModule='".$module."' AND configName='".$name."'");
	$query	= ($exists[1])
		? "UPDATE k_config SET configValue='".$value."' WHERE configModule='".$module."' AND configName='".$name."'"
		: "INSERT INTO k_config (configModule, configName, configValue) VALUES ('".$module."', '".$name."', '".$value."')";

	$this->dbQuery($query);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function configGet($module, $key=NULL){

	if(@array_key_exists($module, $this->apiConfig)){
		return is_array($this->apiConfig[$module]) ? $this->apiConfig[$module] : array();
	}else{
		$config = $this->dbMulti("SELECT * FROM k_config WHERE configModule='".$module."'");
		foreach($config as $v){
			if($module == 'bootExt'){
				$part	= explode(':', $v['configName']);
				$field	= $this->apiLoad('field')->fieldGet(array('id_field' => $part[2]));
				$cv		= $v['configValue'];
			 
				if(substr($cv, 0, 2) == $this->splitter && substr($cv, -2) == $this->splitter && $cv != $this->splitter){
					$out[$field['fieldKey']] = explode($this->splitter, substr($cv, 2, -2));
				}else{
					$out[$field['fieldKey']] = $v['configValue'];
				}
			}else{
				if($v['configName'] == $key) return $v['configValue'];
				$out[$v['configName']] = $v['configValue'];
			}
			
		}
		unset($cv, $field, $id, $config);
		return $out;
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function kTalk($str){

	$binding = array(
		'r'	=> $_SERVER['DOCUMENT_ROOT'],
		'R'	=> KROOT,
		'p'	=> KPROMPT,
		'l'	=> strtolower($this->kodeine['language']),
		'L'	=> strtoupper($this->kodeine['language']),
		'C' => $this->kodeine['chapterUrl'],
		'm'	=> $this->kodeine['moduleFolder'],
		'f' => $this->kodeine['moduleFile'],
		'F' => $this->kodeine['moduleFile'].'.php',
		't' => $this->kodeine['themeFolder'],
		'T' => 'user/theme/'.$this->kodeine['themeFolder'],
	);
	
	$def = get_defined_constants(true);
	$def = $def['user'];

	foreach($def as $data => $value){
		if(constant($data) != NULL) $binding[$data] = $value;
	}

	$str = $this->helperReplace($str, $binding);
	
	return $str;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function kTalkCheck($str){
	return substr_count($str, '{') > 0 ? true : false;
}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function hookAction($name){

		if(count($this->hook['action']) == 0) return false;
		if(!is_a($this->hook['action'][$name], 'ArrayIterator')) return false;
		$hooks = $this->hook['action'][$name];
		$hooks->ksort();
		$hooks = iterator_to_array($this->hook['action'][$name]);


		foreach($hooks as $priorities){
			foreach($priorities as $hook){

				if(is_array($hook['hook'])){
					$hook['hook'] = array($this->apiLoad($hook['hook'][0]), $hook['hook'][1]);
				}

				if(is_callable($hook['hook'])){
					$args = func_get_args(); array_shift($args);

					if(count($args) < $hook['args']){
						for($i=count($args); $i<$hook['args']; $i++){
							$args[] = NULL;
						}
					}

					$args = (count($args) > 0) ? $args : array();
					call_user_func_array($hook['hook'], $args);
				}else
				if(is_file($hook['hook'])){
					include $hook['hook'];
				}

			}
		}
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function hookFilter($name, $data){

		if(count($this->hook['filter']) == 0) return $data;
		if(!is_a($this->hook['filter'][$name], 'ArrayIterator')) return $data;

		$hooks = $this->hook['filter'][$name];
		$hooks->ksort();
		$hooks = iterator_to_array($this->hook['filter'][$name]);

		foreach($hooks as $priorities){
			foreach($priorities as $hook){

				if(is_array($hook['hook'])){
					$hook['hook'] = array($this->apiLoad($hook['hook'][0]), $hook['hook'][1]);
				}

				if(is_callable($hook['hook'])){
					$args = func_get_args(); array_shift($args);

					if(count($args) < $hook['args']){
						for($i=count($args); $i<$hook['args']; $i++){
							$args[] = NULL;
						}
					}

					$args = (count($args) > 0) ? $args : array();
					$temp = call_user_func_array($hook['hook'], $args);
					if(!empty($temp)) $data = $temp;
				}else
				if(is_file($hook['hook'])){
					include $hook['hook'];
				}

			}
		}

		return $data;
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function hookRegister($name, $hook, $type='action', $priority=10, $args=1){
		if(!isset($this->hook[$type][$name])) $this->hook[$type][$name] = new ArrayIterator();
		$this->hook[$type][$name][$priority][] = array('hook' => $hook, 'args' => $args);

	#	$this->pre($this->hook);
	}

}