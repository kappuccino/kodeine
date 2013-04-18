<?php
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Last release		2010.10.18
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

class chapter extends coreApp {

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function chapterGet($opt=array()){

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep='chapterGet() @='.json_encode($opt));

	# Gerer les OPTIONS
	$offset		= $opt['offset'];
	$limit		= $opt['limit'];
	$order		= $opt['order'];
	$direction	= $opt['direction'];
	$language	= isset($opt['language']) ? $opt['language'] : $this->kodeine['language'];

	# Gerer les JOINTURES
	$inner[] 	= "INNER JOIN k_chapterdata ON k_chapter.id_chapter = k_chapterdata.id_chapter";

	if($opt['distinctParent'] && is_array($opt['id_chapter'])){
		foreach($opt['id_chapter'] as $e){
			$list[] = $this->chapterGet(array('id_chapter' => $e, 'language' => $opt['language']));
		}
		foreach($list as $e){
			$str .= $e['chapterParent'].',';
		}
		$parent = explode(',', $str);
		foreach($parent as $idx => $e){
			if($e == '0') unset($parent[$idx]);
		}
		return $parent;
	}else
	if($opt['distinctChildren'] && is_array($opt['id_chapter'])){
		foreach($opt['id_chapter'] as $e){
			$list[] = $this->chapterGet(array('id_chapter' => $e, 'language' => 'fr'));
		}
		if(sizeof($list) > 0){
			foreach($list as $e){
				$str .= $e['chapterChildren'].',';
			}
			$children = explode(',', $str);
			return $children;
		}else{
			return array();
		}
	}else
	if($opt['threadFlat']){
		$chapters = $this->chapterGet(array(
			'language'			=> $opt['language'],
			'profile'			=> $opt['profile'],
			'thread'			=> true,
			'mid_chapter'		=> $opt['mid_chapter'],
			'noid_chapter'		=> $opt['noid_chapter'],
		));

		$this->threadFlatWork = array();

		$chapters = $this->chapterGet(array(
			'language'			=> $opt['language'],
			'threadFlatWork'	=> true,
			'mid_chapter'		=> $opt['mid_chapter'],
			'noid_chapter'		=> $opt['noid_chapter'],
			'chapters'			=> $chapters,
			'level'				=> 0
		));

		return $this->threadFlatWork;

	}else
	if($opt['threadFlatWork']){

		if(!is_array($this->threadFlatWork)) $this->threadFlatWork = array();

		foreach($opt['chapters'] as $e){
			$e['level'] = $opt['level'];
			$tmp = $e; unset($tmp['sub']);

			$this->threadFlatWork[] = $tmp;

			if(is_array($e['sub'])){
				$this->chapterGet(array(
					'language'			=> $opt['language'],
					'threadFlatWork'	=> true,
					'mid_chapter'		=> $opt['mid_chapter'],
					'noid_chapter'		=> $opt['noid_chapter'],
					'chapters'			=> $e['sub'],
					'level'				=> ($opt['level'] + 1)
				));
			}
		}
		
		return true; // No bottom function action

	}else
	if($opt['thread']){
		if($opt['noid_chapter'] > 0) $cond[] = " k_chapter.id_chapter != ".$opt['noid_chapter'];

		$mid 	= isset($opt['mid_chapter']) ? $opt['mid_chapter'] : 0;
		$cond[] = ($opt['profile']) ? "k_chapter.id_chapter IN(".$this->profile['chapter'].")" : "mid_chapter=".$mid;
		$cond[] = "language='".$opt['language']."'";

		if(sizeof($cond) > 0) 	$where = "WHERE ".implode(' AND ', $cond);
		if(sizeof($inner) > 0)	$inner = implode("\n", $inner)."\n";

		$chapter = $this->dbmulti("SELECT * FROM k_chapter ".$inner . $where." ORDER by pos_chapter");

		foreach($chapter as $idx => $e){
			if($e['id_chapter'] != $opt['noid_chapter']){
				$chapter[$idx]['sub'] = $this->chapterGet(array(
					'language'		=> $opt['language'],
					'profile'		=> false, // crazy loop if true
					'thread'		=> true,
					'mid_chapter'	=> $e['id_chapter'],
					'noid_chapter'	=> $opt['noid_chapter']
				));
			}
		}

		return $chapter;
	}else
	if($opt['mid_chapter'] > 0){
		$dbMode = 'dbMulti';
		$cond[] = "mid_chapter=".$opt['mid_chapter'];
	}else
	if($opt['chapterUrl'] != ''){
		$dbMode = 'dbOne';
		$cond[] = "chapterUrl='".$opt['chapterUrl']."'";
	}else
	if($opt['id_chapter'] > 0){
		$dbMode = 'dbOne';
		$cond[] = "k_chapter.id_chapter=".$opt['id_chapter'];
	}else{

		// Extraction des donnes depuis la cache
		/*if(is_array($this->apiConfig['jsonCacheChapter']) && $opt['language'] != NULL){
			$conf = $this->apiConfig['jsonCacheChapter'][$opt['language']];
		
			$this->pre($conf);
		#	if(is_array($conf[$opt['id_type']])) return $conf[$opt['id_type']];
		}

		die('--');*/
	
		$dbMode	= 'dbMulti';
		if(is_array($this->profile['chapterChildren'])){
			$cond[] = "mid_chapter IN(".$this->profile['chapterChildren'].")";
		}
	}


	# Gerer les ORDRES
	$order = "\nORDER BY ".(($opt['order'] != NULL && $opt['direction'] != NULL)
		? $opt['order']." ".$opt['direction']
		: "pos_chapter ASC");


	# Gerer les LIMITATIONS
	if($limit != '' && $offset != '') $limit = "\nLIMIT ".$offset.",".$limit;


	if(sizeof($cond) > 0) 	$where = " AND ".implode(" AND ", $cond);
	if(sizeof($inner) > 0)	$inner = implode("\n", $inner)."\n";
	if(sizeof($field) > 0)	$field = ", ".implode(', ', $field);

	if($dbMode == 'dbOne') unset($limit, $order);

	$chapter = $this->$dbMode(
		"SELECT k_chapter.*, k_chapterdata.*".$field." FROM k_chapter\n". $inner .
		"WHERE language='".$language."'".$where . $group . $order . $limit.' /* GGG */'
	);

	if($opt['debug']) $this->pre($this->db_query, $this->db_error, $chapter);

	if(sizeof($chapter) > 0){
		if($dbMode == 'dbOne') $chapter = array($chapter);
	
		// Associer les CHAMP
		$fields = is_array($this->apiConfig['jsonCacheField'])
			? $this->apiConfig['jsonCacheField']
			: $this->apiLoad('field')->fieldGet(array('chapter' => true));

		if(is_array($fields) && sizeof($fields) > 0){
			foreach($chapter as $idx => $c){
				foreach($fields as $f){
					$chapter[$idx]['field'][$f['fieldKey']] = $c['field'.$f['id_field']];
					unset($chapter[$idx]['field'.$f['id_field']]);
				}	
			}
		}

		if($dbMode == 'dbOne') $chapter = $chapter[0];
	}

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep);

	return $chapter;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function chapterSet($id_chapter, $def, $lan){

	if($id_chapter == ''){
		$nxt = $this->dbOne("SELECT MAX(pos_chapter)+1 as next FROM k_chapter");
		$def['k_chapter']['pos_chapter'] = array('value' => $nxt['next']);

		$this->dbQuery($this->dbInsert($def));
		$id_chapter = $this->db_insert_id;
		$needNewAffect = true;
	}

	$query = $this->dbQuery($this->dbUpdate($def)." WHERE id_chapter=".$id_chapter);

	foreach($lan as $iso => $e){
		$exists = $this->dbOne("SELECT 1 FROM k_chapterdata WHERE language='".$iso."' AND id_chapter=".$id_chapter);
		if(!$exists[1]) $this->dbQuery("INSERT INTO k_chapterdata (id_chapter, language) VALUES (".$id_chapter.", '".$iso."')");

		if($e['copy'] != NULL){ // je dois recopier la source
			$me = $lan[$e['copy']]['sql'];
			$me['k_chapterdata']['is_copy'] = array('value' => 1);
			$fd = $lan[$e['copy']]['field'];
		}else{
			$me = $e['sql'];
			$me['k_chapterdata']['is_copy'] = array('value' => 0);
			$fd = $e['field'];
		}

		if(is_array($fd) && sizeof($fd) > 0){
			foreach($fd as $id_field => $value){
				$me['k_chapterdata']['field'.$id_field] = array(
					'value' => $this->apiLoad('field')->fieldSaveValue($id_field, $value)
				);
			}
		}

	 	@$this->dbQuery($this->dbUpdate($me)." WHERE id_chapter=".$id_chapter." AND language='".$iso."'");
		#$this->pre($this->db_query, $this->db_error);
	}

	$this->id_chapter = $id_chapter;

	# Remettre de l'odre dans la famille : PARENT/CHILDREN
	$this->chapterFamily();

	# Remettre les associations a jour si on rajoute un chapitre
	if($needNewAffect) $this->chapterNewAffect($id_chapter);

	# Remettre à jour le profile
	$this->chapterProfile($id_chapter);

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	Supprime un CHAPITRE, modifit les PROFILE, et CONTENT 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function chapterRemove($id_chapter){

	# Mettre a jour les points d'entré des profiles
	foreach($this->dbMulti("SELECT * FROM k_userprofile") as $p){
		$rule = unserialize($p['profileRule']);

		foreach($rule['id_chapter'] as $idx => $id){
			if(in_array($id, $_POST['del'])){
				unset($rule['id_chapter'][$idx]);
			}
		}

		$this->dbQuery("UPDATE k_userprofile SET profileRule='".addslashes(serialize($rule))."' WHERE id_profile=".$p['id_profile']);
	}

	# Supprimer les entres
	foreach($_POST['del'] as $e){
		$me = $this->chapterGet(array('language' => 'fr', 'id_chapter' => $e));
		if($me['chapterChildren'] != NULL){
			$this->dbQuery("DELETE FROM k_contentchapter WHERE id_chapter IN(".$me['chapterChildren'].")");
			$this->dbQuery("DELETE FROM k_chapter WHERE id_chapter IN(".$me['chapterChildren'].")");
			$this->dbQuery("DELETE FROM k_chapterdata WHERE id_chapter IN(".$me['chapterChildren'].")");
		}
	}

	# Recalcul la FAMILY qui a bouger suite a la SUPPRESSION
	$this->chapterFamily();
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	Mettre a jour les assocition CONTENT/CHAPTER si on ajoute un CHAPTER
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
private function chapterNewAffect($id_chapter){

	$me = $this->chapterGet(array('id_chapter' => $id_chapter, 'language' => 'fr'));

	$asso = $this->dbMulti("SELECT id_content FROM k_contentchapter WHERE id_chapter=".$me['mid_chapter']);
	foreach($asso as $e){
		$add[] = "(".$e['id_content'].",".$id_chapter.",0)";
	}
	
	if(sizeof($add) > 0){
		$this->dbQuery("INSERT IGNORE INTO k_contentchapter (id_content, id_chapter, is_selected) VALUES ".implode(',', $add));
	}

}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	Mettre a jour les PARENT et CHILDREN et les sauver en base
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function chapterFamily(){

	$chapter = $this->chapterGet(array(
		'language'		=> 'fr',
		'threadFlat'	=> true
	));

	foreach($chapter as $e){
		$tree = $this->chapterFamilyParent($e);
		$tree = sizeof($tree) > 0 ? '0,'.implode(',', array_reverse($tree)) : 0;
		$this->dbQuery("UPDATE k_chapter SET chapterParent='".$tree."' WHERE id_chapter=".$e['id_chapter']);
	}

	foreach($chapter as $e){
		$tree = $this->chapterFamilyChildren($e);
		$tree = sizeof($tree) > 0 ? $e['id_chapter'].','.implode(',', $tree) : $e['id_chapter'];
		$this->dbQuery("UPDATE k_chapter SET chapterChildren='".$tree."' WHERE id_chapter=".$e['id_chapter']);
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	Trouver tous les PARENTS pour un CHAPITRE
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function chapterFamilyParent($e, &$line=array()){
	if($e['mid_chapter'] > 0){
		$next 	= $this->chapterGet(array('language' => 'fr', 'id_chapter' => $e['mid_chapter']));
		$line[] = $e['mid_chapter'];
		return $this->chapterFamilyParent($next, $line);
	}else{
		return $line;
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	Trouver tous les CHILDREN pour un CHAPITRE
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function chapterFamilyChildren($e, $line=array()){

	$children = $this->chapterGet(array(
		'language'		=> 'fr',
		'mid_chapter'	=> $e['id_chapter']
	));
	
	foreach($children as $child){
		$line[] = $child['id_chapter'];
		$this->chapterFamilyChildren($child, $line);
	}

	return $line;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function chapterUpdatePos($chapter){
	foreach($chapter as $pos => $e){
		$this->dbQuery("UPDATE k_chapter SET pos_chapter=".$pos." WHERE id_chapter=".$e['id_chapter']);
		if(is_array($e['sub'])) $this->chapterUpdatePos($e['sub']);
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function chapterSelector($opt){

	$chapter = $this->chapterGet(array(
		'language'		=> 'fr',
		'profile'		=> $opt['profile'],
		'mid_chapter'	=> 0,
		'threadFlat'	=> true
	));
	
	if($opt['multi']){
		$value = is_array($opt['value']) ? $opt['value'] : array();

		$form .= "<select name=\"".$opt['name']."\" id=\"".$opt['id']."\" size=\"".$opt['size']."\" multiple style=\"".$opt['style']."\">";
		foreach($chapter as $e){
			$selected = in_array($e['id_chapter'], $value) ? ' selected' : NULL;
			$form .= "<option value=\"".$e['id_chapter']."\"".$selected.">".str_repeat(' &nbsp; &nbsp;', $e['level']).'&nbsp;'.$e['chapterName']."</option>";
		}
		$form .= "</select>";
	}else
	if($opt['one']){
		$value = is_array($opt['value']) ? $opt['value'][0] : $opt['value'];

		$form .= "<select name=\"".$opt['name']."\" id=\"".$opt['id']."\" style=\"".$opt['style']."\">";
		if($opt['empty']) $form .= "<option value=\"\"></option>";
		foreach($chapter as $e){
			$selected = ($e['id_chapter'] == $value) ? ' selected' : NULL;
			$form .= "<option value=\"".$e['id_chapter']."\"".$selected.">".str_repeat(' &nbsp; &nbsp;', $e['level']).'&nbsp;'.$e['chapterName']."</option>";
		}
		$form .= "</select>";
	}
	
	return $form;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function chapterProfile($id_chapter){

	$id_profile 	= $this->user['id_profile'];
	$profile		= $this->dbOne("SELECT * FROM k_userprofile WHERE id_profile=".$id_profile);
	$profileRule	= unserialize($profile['profileRule']);	
	
	$chapter	= array_merge($profileRule['id_chapter'], array($id_chapter));
	$chapter	= $this->apiLoad('user')->userProfileCheckChapter($chapter);

	$profileRule['id_chapter'] = $chapter;

	$this->dbQuery("UPDATE k_userprofile SET profileRule='".serialize($profileRule)."' WHERE id_profile=".$id_profile);

	$this->profile = $this->apiLoad('user')->userProfile($id_profile);
}

}