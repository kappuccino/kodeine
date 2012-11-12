<?php 

require_once(dirname(dirname(dirname(__FILE__))).'/module/core/helper/app.php');

class coreAdmin extends coreApp {

public function coreAdmin(){

	setlocale(LC_ALL, 'fr_FR');

	$this->adminZone	= true; 
	$this->total		= 0;
	$this->limit		= 0;
	$this->apiContext	= 'admin';

	$this->coreApp();
	$this->userIsLoged();
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function go($url){
	header("Location: ".$url);
	exit();
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function filterSet($mod, $value, $key=NULL){

	$old = $this->filterGet();

	if(is_array($value)) unset($value['id_type'], $value['cf']);

	if($key != NULL){
		if(!is_array($old[$mod])) $old[$mod] = array();

		if($value == NULL && isset($old[$mod][$key])){
			unset($old[$mod][$key]);
		}else{
			$old[$mod][$key] = $value;
		}
	}else{
		foreach($value as $k => $v){
			if($v == NULL){
				if(isset($old[$mod][$k])) unset($old[$mod][$k]);
			}else{
				$old[$mod][$k] = $v;
			}
		}
	}
	
	$value = serialize($old);

	return setcookie('filter', $value, (time()+(60*60*24*30)), '/');
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function filterGet($mod=NULL){

	if($_COOKIE['filter'] != NULL){

		$cookie = unserialize(stripslashes($_COOKIE['filter']));

		return ($mod == NULL)
			? (is_array($cookie) 		? $cookie 		: array()) 
			: (is_array($cookie[$mod])	? $cookie[$mod] : array());

	}else{
		return array(); #($mod == NULL) ? array() : array($mod => array());
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function filterReset(){
	return setcookie('filter', '', (time()-(60*60*24*30)), '/');
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function searchGet($opt=array()){

	# Gérer les options
	#
	$limit		= isset($opt['limit']) 		? $opt['limit']			: 30;
	$offset		= isset($opt['offet']) 		? $opt['offset']		: 0;

	if($opt['id_search'] > 0){
		$dbMode = 'dbOne';
		$cond[] = "k_search.id_search=".$opt['id_search'];
	}else{
		$dbMode = 'dbMulti';
	}

	if($opt['searchType'] != '') $cond[] = "searchType='".$opt['searchType']."'";

	# Former les conditions
	#
	if($opt['type'] == 'user') 		$cond[] = "searchType ='user'";
	if($opt['type'] == 'content')	$cond[] = "searchType!='user'";
	if(sizeof($cond) > 0) $where = "WHERE ".implode(" AND ", $cond);

	# SEARCH
	#
	$search = $this->$dbMode("SELECT * FROM k_search\n". $where);

	#  PARAM
	#
	if($dbMode == 'dbMulti'){
		foreach($search as $idx => $c){
			$search[$idx]['searchParam'] = unserialize($search[$idx]['searchParam']);
			if(!is_array($search[$idx]['searchParam'])) $search[$idx]['searchParam'] = array();
		}
	}else
	if($dbMode == 'dbOne'){
		$search['searchParam'] = unserialize($search['searchParam']);
		if(!is_array($search['searchParam'])) $search['searchParam'] = array();
	}

	if($opt['debug']) $this->pre($this->db_query, $this->db_error, $search);

	return $search;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function searchSet($id_search, $def){

	if($id_search > 0){
		$q = $this->dbUpdate($def)." WHERE id_search=".$id_search;
	}else{
		$q = $this->dbInsert($def);
	}

	@$this->dbQuery($q);
	if($this->db_error != NULL) return false;

	$this->id_search = ($id_search > 0) ? $id_search : $this->db_insert_id;

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function searchSelector($opt){

	$search = $this->searchGet(array(
		'searchType'	=> $opt['searchType']
	));

	if($opt['multi']){
		$value = is_array($opt['value']) ? $opt['value'] : array();

		$form .= "<select name=\"".$opt['name']."\" id=\"".$opt['id']."\" size=\"".$opt['size']."\" multiple style=\"".$opt['style']."\">";
		foreach($search as $e){
			$selected = in_array($e['id_search'], $value) ? ' selected' : NULL;
			$form .= "<option value=\"".$e['id_search']."\"".$selected.">".$e['searchName']."</option>";
		}
		$form .= "</select>";
	}else
	if($opt['one']){
		$value = is_array($opt['value']) ? $opt['value'][0] : $opt['value'];

		$form .= "<select name=\"".$opt['name']."\" id=\"".$opt['id']."\" style=\"".$opt['style']."\">";
		foreach($search as $e){
			$selected = ($e['id_search'] == $value) ? ' selected' : NULL;
			$form .= "<option value=\"".$e['id_search']."\"".$selected.">".$e['searchName']."</option>";
		}
		$form .= "</select>";
	}
	
	return $form;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function countrySet($def){

	$x = $this->dbOne("SELECT 1 FROM k_country WHERE iso='".$def['k_country']['iso']['value']."'");
	$q = ($x[1])
		? $this->dbUpdate($def)." WHERE iso='".$def['k_country']['iso']['value']."'"
		: $this->dbInsert($def);

	@$this->dbQuery($q);
	if($this->db_error != NULL) return false;

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function pagination($total, $view, $offset, $pattern){

	if($total > $view && $view > 0){
		$p = ceil($total / $view);

		if($p < 10){
			echo "Page : ";
			for($i=0; $i<$p; $i++){
				echo "<a href=\"".sprintf($pattern, ($i * $view))."\"".(($i * $view == $offset) ? "class=\"me\"" : "").">".($i+1)."</a> ";
			}
		}else{
			echo "<select onChange=\"jumpMenu('self',this,0)\">";
			for($i=0; $i<$p; $i++){
				echo "<option value=\"".sprintf($pattern, ($i*$view))."\"".(($i * $view == $offset) ? ' selected' : '').">".($i+1)."</option>";
			}
			echo "</select>";
		}
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function moduleList($opt=array()){

	$core = @$this->fsFolder(APP.'/module', '', 'NOROOT_FLAT');
	$user = @$this->fsFolder(USER.'/module', '', 'NOROOT_FLAT');

	$core = is_array($core) ? $core : array();
	$user = is_array($user) ? $user : array();
	$mods = array_merge($core, $user);
	ksort($mods);

	foreach($mods as $mod){

		$core	= (strpos($mod, "/user/module") === false) ? true : false;
		$mod	= basename($mod);
		$module = $this->moduleData($mod, $core);

		if(is_array($module)){
			if($module['hidden'] != 'YES' OR $opt['all']){
				if($opt['profile']){
					if($this->userCan($module['key'].'.index')) $out[$module['name']] = $module;
				}else{
					$out[$module['name']]	= $module;
					$pkg[$module['key']]	= $module;
				}
			}
		}
	}

	ksort($out); ksort($pkg);

	if($opt['dependencies']){
		foreach($pkg as $key => $mod){
			if(is_array($mod['dependencies']) && sizeof($mod['dependencies']) > 0){
				foreach($mod['dependencies'] as $i => $dep){
					if(array_key_exists($dep, $pkg)){
						$pkg[$key]['dependencies'][$dep] = $pkg[$dep];
						$un[] = $dep;
						
					#	$this->pre('@'.$dep, $un);

						unset($pkg[$key]['dependencies'][$i]);

					}
				}
				ksort($mod['dependencies']);
			}
		}

		if(sizeof($un) > 0){
		#	$this->pre($un, $pkg);
			foreach($un as $unk){
				unset($pkg[$unk]);
			}
		}

		return $pkg;
	}

	#$this->pre($un);


	return $out;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function moduleData($mod, $core=true){

	$folder = ($core) ? '/app/module' : '/user/module';
	$config = KROOT.$folder.'/'.$mod.'/config/config.xml';

	if(!file_exists($config)) return false;

	$doc = new DOMDocument();
	$doc->preserveWhiteSpace = false;
	$doc->load($config);

	$module	= array('folder' => $mod);
	$xpath 	= new DOMXPath($doc);

	$data = $xpath->query('/element/data')->item(0)->childNodes;
	if($data->length > 0){
		foreach($data as $e){
			$module[$e->nodeName] = utf8_decode($e->nodeValue);
		#	$module[$e->nodeName] = $e->nodeValue;
		}
	}

	$less = $xpath->query('/element/less')->item(0)->childNodes;
	if($less->length > 0){
		foreach($less as $e){
			$module['less'][] = $folder.'/'.$mod.utf8_decode($e->nodeValue);
		#	$module['less'][] = $folder.'/'.$mod.$e->nodeValue;
		}
	}
	
	$module['menu'] = ($module['menu'] != '') ? $module['menu'] : 'YES';

	$settings = $xpath->query('/element/profile/item');
	if($settings->length > 0){
		foreach($settings as $set){
			$module['profile'][] = array(
				'code' => $set->getAttributeNode('code')->nodeValue,
				'type' => $set->getAttributeNode('type')->nodeValue,
				'name' => $set->getAttributeNode('name')->nodeValue
			);
		}
	}

	$dependencies = $xpath->query('//element/dependencies/module');
	if($dependencies->length > 0){
		foreach($dependencies as $dep){
			$module['dependencies'][] = $dep->nodeValue;
		}
	}

	$module['needPatch']	= file_exists(KROOT.$folder.'/'.$mod.'/config/patch-todo.xml');
	$module['rePatch']		= file_exists(KROOT.$folder.'/'.$mod.'/config/patch-done.xml');
	$module['isCore']		= $core;

	if($module['panelIcon'] == ''){
		$module['panelIcon'] = NULL;
	}else{
		$module['panelIcon'] = $this->helperReplace($module['panelIcon'], array(
			'moduleFolder' => $mod,
		));
	}
	
	$config = $this->dbMulti("
		SELECT configName, configValue
		FROM k_config
		WHERE configName NOT LIKE 'jsonCache%' AND configModule='".basename($mod)."'"
	);

	$module['config'] = array();
	if(sizeof($config) > 0){
		foreach($config as $e){
			$v = $e['configValue'];
			$module['config'][$e['configName']] = $v;
		}
	}
	

	return  $module;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function less($less){

	$css = substr($less, 0, -4).'css';
	
	$css_ = str_replace('/admin/', '/app/module/', $css);
	$les_ = str_replace('/admin/', '/app/module/', $less);

	$statCss = @stat(KROOT.$css_);
	$statLes = @stat(KROOT.$les_);
	
	if($statCss['mtime'] > $statLes['mtime']){
		$head = '<link rel="stylesheet" type="text/css" href="'.$css.'" />'.PHP_EOL;
	#	define('USELESS', false);
	}else{
		$head = '<link rel="stylesheet/less" type="text/css" href="'.$less.'" />'.PHP_EOL;
	#	define('USELESS', true);
	}

	return $head;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function loc($mod){

	$loc = 'fr';

	$doc = new DOMDocument();
	$doc->preserveWhiteSpace = false;
	$doc->load(APP.'/module/core/config/language-'.$loc.'.xml');

	$items	= array();
	$xpath 	= new DOMXPath($doc);
	$data	= $xpath->query('/language/item');

	foreach($data as $e){
		$items[$e->getAttributeNode('key')->nodeValue] = utf8_decode($e->nodeValue);
	#	$items[$e->getAttributeNode('key')->nodeValue] = $e->nodeValue;
	}

	# - - - - - - - - - - - - - - - - - - - - - - - -	

	$xml = APP.'/module/'.$mod.'/config/language-'.$loc.'.xml';

	if(file_exists($xml)){
		$doc = new DOMDocument();
		$doc->preserveWhiteSpace = false;
		$doc->load($xml);
	
		$xpath 	= new DOMXPath($doc);
		$data	= $xpath->query('/language/item');
	
		if($data->length > 0){
			foreach($data as $e){
				$items[$e->getAttributeNode('key')->nodeValue] = utf8_decode($e->nodeValue);
			#	$items[$e->getAttributeNode('key')->nodeValue] = $e->nodeValue;
			}
		}
	}
	
	return $items;
}


} ?>