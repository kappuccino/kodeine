<?php

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Last release		2011.10.21
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

class category extends coreApp {

function __clone(){}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function category(){
//	$this->coreApp();
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function categoryGet($opt=array()){

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep='categoryGet() @='.json_encode($opt));

	# Gerer les OPTIONS
	#
	$id_group		 = isset($opt['id_group']) 	 ? $opt['id_group'] 	: $this->kodeine['id_group'];
	$id_chapter		 = isset($opt['id_chapter']) ? $opt['id_chapter'] 	: $this->kodeine['id_chapter'];
	$opt['language'] = isset($opt['language']) 	 ? $opt['language'] 	: $this->kodeine['language'];

	// Permet de trouver la premiere langue disponible
	if($opt['language'] == '??' && $opt['id_category'] != ''){
		$tmp = $this->dbMulti("SELECT language FROM k_categorydata WHERE id_category=".$opt['id_category']);
		if(sizeof($tmp) == 0) return array();
		$opt['language'] = $tmp[0]['language']; 
		unset($tmp);
	}

	# Jointure (seulement si necessaire)
	#
	if($opt['language'] != ''){
		$inner[] = "INNER JOIN k_categorydata ON k_category.id_category = k_categorydata.id_category";
	}

	if($opt['distinctParent'] && is_array($opt['id_category'])){
		foreach($opt['id_category'] as $e){
			$list[] = $this->categoryGet(array('id_category' => $e, 'language' => $opt['language']));
		}

		if(sizeof($list) == 0) return array();

		$str = NULL;
		foreach($list as $e){
			$str .= $e['categoryParent'].',';
		}

		$parent = explode(',', $str);

		foreach($parent as $idx => $e){
			if($e == '0' OR $e == NULL ) unset($parent[$idx]);
		}
		return $parent;
	}else
	if($opt['distinctChildren'] && is_array($opt['id_category'])){
		foreach($opt['id_category'] as $e){
			$list[] = $this->categoryGet(array('id_category' => $e, 'language' => $opt['language']));
		}
		foreach($list as $e){
			$str .= $e['categoryChildren'].',';
		}
		$children = explode(',', $str);
		return $children;
	}else
	if($opt['threadFlat']){

		$category = $this->categoryGet(array(
			'debug'				=> $opt['debug'],
			'cache'				=> $opt['cache'],
			'profile'			=> $opt['profile'],
			'thread'			=> true,
			'mid_category'		=> $opt['mid_category'],
			'noid_category'		=> $opt['noid_category'],
			'language'			=> $opt['language']
		));

		$this->threadFlatWork = array();

		$category = $this->categoryGet(array(
			'debug'				=> $opt['debug'],
			'cache'				=> $opt['cache'],
			'threadFlatWork'	=> true,
			'mid_category'		=> $opt['mid_category'],
			'noid_category'		=> $opt['noid_category'],
			'category'			=> $category,
			'level'				=> 0,
			'language'			=> $opt['language']
		));

		return $this->threadFlatWork;

	}else
	if($opt['threadFlatWork']){

		foreach($opt['category'] as $e){
			$e['level'] = $opt['level'];
			$tmp = $e; unset($tmp['sub']);

			$this->threadFlatWork[] = $tmp;

			if(is_array($e['sub'])){
				$this->categoryGet(array(
					'debug'				=> $opt['debug'],
					'cache'				=> $opt['cache'],
					'profile'			=> $opt['profile'],
					'threadFlatWork'	=> true,
					'mid_category'		=> $opt['mid_category'],
					'noid_category'		=> $opt['noid_category'],
					'category'			=> $e['sub'],
					'level'				=> ($opt['level'] + 1)
				));
			}
		}

		return $es;

	}else
	if($opt['thread']){

		if($opt['noid_category'] > 0) 	$cond[] = " k_category.id_category != ".$opt['noid_category'];
		if($opt['language'] != '')		$cond[] = " language='".$opt['language']."'";

		$mid 	= isset($opt['mid_category']) ? $opt['mid_category'] : 0;
		$cond[] = ($opt['profile']) ? "k_category.id_category IN(".$this->profile['category'].")" : "mid_category=".$mid;

		$inner 		= (sizeof($inner) > 0) ? "\n".implode("\n", $inner) : NULL;
		$where 		= (sizeof($cond)  > 0) ? "WHERE ".implode(' AND ', $cond) : NULL;
		$category	= $this->dbMulti("SELECT * FROM k_category".$inner."\n".$where." ORDER by pos_category");

		if($opt['debug']) $this->pre($this->db_query, $this->db_error);

		foreach($category as $idx => $e){
			if($e['id_category'] != $opt['noid_category']){

				$category[$idx]['sub'] = $this->categoryGet(array(
					'cache'			=> $opt['cache'],
					'thread'		=> true,
					'debug'			=> $opt['debug'],
					'mid_category'	=> $e['id_category'],
					'noid_category'	=> $opt['noid_category'],
					'language'		=> $opt['language']
				));
			}
		}

		return $category;
	}else


	# Par URL
	if($opt['categoryUrl'] != NULL){
		$dbMode = 'dbOne';
		if(is_array($opt['categoryUrl'])){
			if(sizeof($opt['categoryUrl']) == 0) return array();
			unset($tmp);
			foreach($opt['categoryUrl'] as $e){
				$tmp[] = "'".stripslashes($e)."'";
			}
			$cond[] = "k_categorydata.categoryUrl IN(".implode(',', $tmp).")";
			$dbMode = 'dbMulti';
		}else{
			$cond[] = "k_categorydata.categoryUrl='".$opt['categoryUrl']."'";
		}
	}else
	
	
	# Par ID
	if($opt['id_category'] != NULL){
		$dbMode = 'dbOne';
		if(is_array($opt['id_category'])){
			if(sizeof($opt['id_category']) == 0) return array();
			$cond[] = "k_categorydata.id_category IN(".@implode(',', $opt['id_category']).")";
			$dbMode = 'dbMulti';
		}else{
			$cond[] = "k_category.id_category=".$opt['id_category'];
			
			/*if($opt['cache'] == true){
				$key 	= "category:".$opt['language'].":".$opt['id_category'];
				$cache 	= $this->cache->sqlcacheGet($key);
				if($cache !== false){
				#	$this->pre("USE CACHE SUR ID_CATEGORY");
					return $cache;
				}
			}*/
		}
	}else


	# Par ID Parent
	if(isset($opt['mid_category'])){
		$dbMode = 'dbMulti';
		$cond[] = "mid_category=".$opt['mid_category'];
	}


	# Recherche plus classic
	else{

		$dbMode = 'dbMulti';
		if(isset($opt['mid_category'])) $cond[] = "mid_category=".$opt['mid_category'];

		if($opt['contentlinked']){
			$inner[] = "INNER JOIN k_contentcategory ON k_category.id_category = k_contentcategory.id_category";
			$inner[] = "INNER JOIN k_contentchapter ON k_contentcategory.id_content = k_contentchapter.id_content";
			$inner[] = "INNER JOIN k_contentgroup ON k_contentcategory.id_content = k_contentgroup.id_content";

			$cond[]  = "id_chapter=".$id_chapter;
			$cond[]  = "id_group=".$id_group;

			$field[] = 'COUNT(k_contentchapter.id_content) AS contentCount';
			$group	 = "\nGROUP BY k_contentchapter.id_content";
		}
	}


	# Gerer les LIMITATION et ORDRE
	if($dbMode == 'dbMulti'){
		$order = "\nORDER BY ".(($opt['order'] != NULL && $opt['direction'] != NULL)
			? $opt['order']." ".$opt['direction']
			: "pos_category ASC");
	
		if($opt['limit'] != '' && $opt['offset'] != '') $limit = "\nLIMIT ".$opt['offset'].",".$opt['limit'];
	}

	if($opt['language'] != ''){
		$cond[]		= 'language=\''.$opt['language'].'\'';
		$field[]	= 'k_categorydata.*';
	}

	if(sizeof($cond) > 0) 	$where = "WHERE ".implode(" AND ", $cond);
	if(sizeof($inner) > 0)	$inner = implode("\n", $inner)."\n";
	if(sizeof($field) > 0)	$field = ", ".implode(', ', $field);

	if(isset($cache)){
	#	$this->pre("USE CACHE = MIAM");# $cache);
		$categories = $cache;
	}else{
		$categories = $this->$dbMode(
			"SELECT k_category.*".$field." FROM k_category\n". $inner.
			$where . $group . $order . $limit
		);
		
		if($opt['debug']) $this->pre("[OPT]", $opt, "[QUERY]", $this->db_query, "[ERROR]", $this->db_error, "[CATEGORIES]", $categories);
	}

	if(sizeof($categories) > 0){
		if($dbMode == 'dbOne') $categories = array($categories);
	
		# Associer les CHAMPS
		$apiField	= $this->apiLoad('field');
		$jsCache	= $apiField->apiConfig['jsonCacheFieldCategory'];

		$fields  	= is_array($jsCache)
			? $jsCache
			: $this->apiLoad('field')->fieldGet(array('category' => true));

		if(is_array($fields) && sizeof($fields) > 0){
			foreach($categories as $idx => $c){
				foreach($fields as $f){
					$categories[$idx]['field'][$f['fieldKey']] = $c['field'.$f['id_field']];
				#	unset($categories[$idx]['field'.$f['id_field']]);
				}	
			}
		}

		if($dbMode == 'dbOne') $categories = $categories[0];
	}

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep);

	return $categories;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function categorySet($id_category, $def, $lan){

	# Inserer le CORE pour la category et gerer la POSITION
	if($id_category == ''){
		$mid = $def['k_category']['mid_category']['value'];
		$max = $this->dbOne("SELECT MAX(pos_category) as last FROM k_category WHERE mid_category=".(($mid >= 0) ? $mid : 0));
		$def['k_category']['pos_category'] = array('value' => $max['last'] + 1);
		$this->dbQuery($this->dbInsert($def));
		$id_category = $this->db_insert_id;
	}

	
	# Gerer le CORE (TEMPLATE et PARENT)
	$this->dbQuery($this->dbUpdate($def)." WHERE id_category=".$id_category);
	#$this->pre($this->db_query, $this->db_error);


	# Gerer les differentes LANGUES
	foreach($lan as $iso => $e){
		$exists = $this->dbOne("SELECT 1 FROM k_categorydata WHERE language='".$iso."' AND id_category=".$id_category);
		if(!$exists[1]) $this->dbQuery("INSERT INTO k_categorydata (id_category, language) VALUES (".$id_category.", '".$iso."')");

		if($e['copy'] != NULL){ // je dois recopier la source
			$me = $lan[$e['copy']]['sql'];
			$me['k_categorydata']['is_copy'] = array('value' => 1);
			$fd = $lan[$e['copy']]['field'];
		}else{
			$me = $e['sql'];
			$me['k_categorydata']['is_copy'] = array('value' => 0);
			$fd = $e['field'];
		}

		if(is_array($fd) && sizeof($fd) > 0){
			foreach($fd as $id_field => $value){
				$me['k_categorydata']['field'.$id_field] = array(
					'value' => $this->apiLoad('field')->fieldSaveValue($id_field, $value)
				);
			}
		}

	 	@$this->dbQuery($this->dbUpdate($me)." WHERE id_category=".$id_category." AND language='".$iso."'");
		#$this->pre($this->db_query, $this->db_error);
	}

	$this->id_category = $id_category;

	# Mettre a jour la hierarchie pour TOUTE les CATEGORY
	$this->categoryFamily();
	$this->categoryCache();

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function categorySelector($opt){

	$category = $this->categoryGet(array(
		'profile'		=> $opt['profile'],
		'language'		=> $opt['language'],
		'mid_category'	=> 0,
		'threadFlat'	=> true
	));
	
	if($opt['explorer']){
		$value = is_array($opt['value']) ? implode('-', $opt['value']) : NULL;
		
		echo "<ul class=\"explorer-keyword clearfix\">";
		foreach(explode('-', $value) as $idc){

			if(trim($idc) != ''){ 
				$tmp = $this->categoryGet(array(
					'id_category'	=> $idc,
					'language'		=> $opt['language']
				));
				
				if($tmp['id_category'] > 0){
					echo "<li class=\"key clearfix\">";
						echo "<input type=\"hidden\" name=\"".$opt['name']."\" value=\"".$tmp['id_category']."\" /> ";
						echo "<span onClick=\"explorerShow('multi', null, this, '".$tmp['categoryParent']."');\">".$tmp['categoryName']."</span>";
						echo "<a class=\"kill\" onClick=\"explorerKeyRemove(this)\"></a>";
					echo "</li>";
				}
			}
		}
		echo "</ul>";			

	#	echo "<input type=\"hidden\" class=\"cat-explorer-reload\" value=\"\" />";
	#	echo "<input type=\"hidden\" name=\"".$opt['name']."\" value=\"".$value."\" />";
		echo "<div class=\"cat-explorer multi\"></div>";

	}else
	if($opt['multi']){
		$value = is_array($opt['value']) ? $opt['value'] : array();

		$form .= "<select name=\"".$opt['name']."\" id=\"".$opt['id']."\" size=\"".$opt['size']."\" multiple style=\"".$opt['style']."\">";
		foreach($category as $e){
			$selected = in_array($e['id_category'], $value) ? ' selected' : NULL;
			$form .= "<option value=\"".$e['id_category']."\"".$selected.">".str_repeat(' &nbsp; &nbsp;', $e['level']).'&nbsp;'.$e['categoryName']."</option>";
		}
		$form .= "</select>";
	}else
	if($opt['one']){
		$value = is_array($opt['value']) ? $opt['value'][0] : $opt['value'];

		$form .= "<select name=\"".$opt['name']."\" id=\"".$opt['id']."\" style=\"".$opt['style']."\">";
		if($opt['empty']) $form .= "<option value=\"\"></option>";
		foreach($category as $e){
			$selected = ($e['id_category'] == $value) ? ' selected' : NULL;
			$form .= "<option value=\"".$e['id_category']."\"".$selected.">".str_repeat(' &nbsp; &nbsp;', $e['level']).'&nbsp;'.$e['categoryName']."</option>";
		}
		$form .= "</select>";
	}
	
	return $form;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	Supprime une CATEGORY + CONTENT, et modifit les PROFILE  
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function categoryRemove($id_category){

	# Mettre a jour les points d'entré des profiles
	#
	foreach($this->dbMulti("SELECT * FROM k_userprofile") as $p){
		$rule = unserialize($p['profileRule']);
		foreach($rule['id_category'] as $idx => $id){
			if(in_array($id, $_POST['del'])){
				unset($rule['id_category'][$idx]);
			}
		}
		$this->dbQuery("UPDATE k_userprofile SET profileRule='".addslashes(serialize($rule))."' WHERE id_profile=".$p['id_profile']);
	}


	# Supprimer les CATEGORY + CONTENT
	#
	$me = $this->categoryGet(array(
		'language'		=> '??',
		'id_category'	=> $id_category
	));

	if($me['categoryChildren'] != NULL){
		$this->dbQuery("DELETE FROM k_category 			WHERE id_category IN(".$me['categoryChildren'].")");
		$this->dbQuery("DELETE FROM k_categorydata 		WHERE id_category IN(".$me['categoryChildren'].")");
		$this->dbQuery("DELETE FROM k_contentcategory 	WHERE id_category IN(".$me['categoryChildren'].")");
	}

	$this->dbQuery("DELETE FROM k_category			WHERE id_category = ".$me['id_category']);
	$this->dbQuery("DELETE FROM k_categorydata		WHERE id_category = ".$me['id_category']);
	$this->dbQuery("DELETE FROM k_contentcategory	WHERE id_category = ".$me['id_category']);


	# Recalcul la FAMILY qui a bouger suite a la SUPPRESSION
	#
	$this->categoryFamily();
	$this->categoryCache();
}




/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	Mettre a jour les PARENT et CHILDREN et les sauver en base
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function categoryFamily(){

	# PARENTS
	#
	$this->tempCat	= array();
	$category		= $this->categoryGet(array(
		'thread'	=> true,
		'debug'		=> false
	));

	$this->categoryFamilyParent($category);
	foreach($this->tempCat as $id_category => $tree){
		$this->dbQuery("UPDATE k_category  SET categoryParent='".substr($tree, 0, -1)."' WHERE id_category=".$id_category);
	}

	# CHILDREN
	#
	$category = $this->categoryGet(array(
		'threadFlat'	=> true,
		'debug'			=> false
	));
	
	foreach($category as $e){
		$tree = $this->categoryFamilyChildren($e);
		$has  = (sizeof($tree) >= 1) ? '1' : '0';
		$tree = (sizeof($tree) > 0) ? implode(',', $tree) : ''; #$e['id_category'];

		$this->dbQuery(
			"UPDATE k_category ".
			"SET categoryChildren='".$tree."', categoryHasChildren=".$has." ".
			"WHERE id_category=".$e['id_category']
		);
	}
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	Trouver tous les PARENTS pour une CATEGORY
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
private function categoryFamilyParent($category, $path=''){

	foreach($category as $c){
		$this->tempCat[$c['id_category']] = $path;

		if(sizeof($c['sub']) > 0){
			$this->categoryFamilyParent($c['sub'], $path.$c['id_category'].',');	
		}
	}

}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	Trouver tous les CHILDREN pour une CATEGORY
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function categoryFamilyChildren($e, &$line=array()){

	$children = $this->categoryGet(array(
		'debug'			=> false,
		'mid_category' 	=> $e['id_category']
	));

	foreach($children as $child){
		$line[] = $child['id_category'];
		$this->categoryFamilyChildren($child, $line);
	}

	return $line;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function categoryCache(){

	$this->cache->sqlcacheClean();

	$items = $this->dbMulti("SELECT * FROM k_categorydata");

	foreach($items as $e){

		$e = $this->categoryGet(array(
			'id_category' 	=> $e['id_category'],
			'language'		=> $e['language']
		));

		$e['sub'] = $this->categoryGet(array(
			'mid_category'	=> $e['id_category'],
			'language'		=> $e['language']
		));

		$key = "category:".$e['language'].":".$e['id_category'];

		$r	 = $this->cache->sqlcacheSet($key, $e, 0);
	}

}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Remet a jour les POSITIONS de TOUTES LES CATEGORIES
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function categoryUpdatePos($category){

	foreach($category as $pos => $e){
		$this->dbQuery("UPDATE k_category SET pos_category=".$pos." WHERE id_category=".$e['id']);
		if(is_array($e['sub'])) $this->categoryUpdatePos($e['sub']);
	}

}

} ?>