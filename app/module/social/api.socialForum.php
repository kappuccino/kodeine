<?php

class socialForum extends social{

function __clone(){}
function socialForum(){}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialForumGet($opt=array()){

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep='socialForumGet() @='.json_encode($opt));

	if($opt['debug']) $this->pre("[OPT]", $opt);
	$dbMode = 'dbMulti';

	// GET id_socialforum
	if(array_key_exists('id_socialforum', $opt)){
		if(is_array($opt['id_socialforum'])){
			$dbMode = 'dbMulti';
			$cond[] = "k_socialforum.id_socialforum IN(".implode(', ', $opt['id_socialforum']).")";
		}else
		if(intval($opt['id_socialforum']) > 0){
			$dbMode = 'dbOne';
			$cond[] = "k_socialforum.id_socialforum=".$opt['id_socialforum'];
		}else{
			if($opt['debug']) $this->pre("ERROR: ID_SOCIALFORUM (ARRAY, NUMERIC)", "GIVEN", var_export($opt['id_socialforum'], true));
			return array();
		}
	}

	// GET mid_socialforum
	if(array_key_exists('mid_socialforum', $opt)){
		if(is_numeric($opt['mid_socialforum'])){
			$cond[] = "k_socialforum.mid_socialforum=".$opt['mid_socialforum'];
		}else{
			if($opt['debug']) $this->pre("ERROR: MID_SOCIALFORUM (NUMERIC)", "GIVEN", var_export($opt['mid_socialforum'], true));
			return array();
		}
	}

	// GET noid_socialforum
	if(array_key_exists('noid_socialforum', $opt) && intval($opt['noid_socialforum']) > 0){
		if(is_numeric($opt['noid_socialforum'])){
			$cond[] = "k_socialforum.id_socialforum != ".$opt['noid_socialforum'];
		}else{
			if($opt['debug']) $this->pre("ERROR: NOID_SOCIALFORUM (NUMERIC > 0)", "GIVEN", var_export($opt['noid_socialforum'], true));
			return array();
		}
	}
	
	// GET thread (multi-mode)
	if($opt['threadFlat'] === true){

		$forum = $this->socialForumGet(array(
			'debug'				=> $opt['debug'],
			'thread'			=> true,
			'mid_socialforum'	=> $opt['mid_socialforum'],
			'noid_socialforum'	=> $opt['noid_socialforum']
		));

		$this->threadFlatWork = array();

		$forum = $this->socialForumGet(array(
			'debug'				=> $opt['debug'],
			'threadFlatWork'	=> true,
			'mid_socialforum'	=> $opt['mid_socialforum'],
			'noid_socialforum'	=> $opt['noid_socialforum'],
			'forum'				=> $forum,
			'level'				=> 0
		));

		return $this->threadFlatWork;
	}else
	if($opt['threadFlatWork'] === true){

		foreach($opt['forum'] as $e){

			$e['level'] = $opt['level'];
			$tmp = $e; unset($tmp['sub']);

			$this->threadFlatWork[] = $tmp;

			if(is_array($e['sub'])){
				$this->socialForumGet(array(
					'debug'				=> $opt['debug'],
					'threadFlatWork'	=> true,
					'mid_socialforum'	=> $opt['mid_socialforum'],
					'noid_socialforum'	=> $opt['noid_socialforum'],
					'forum'				=> $e['sub'],
					'level'				=> ($opt['level'] + 1)
				));
			}
		}

		return $es;

	}else
	if($opt['thread'] === true){

		$where 	= (sizeof($cond)  > 0) ? "WHERE ".implode(' AND ', $cond) : NULL;
		$query	= "SELECT * FROM k_socialforum".$inner."\n".$where."\nORDER by pos_forum";
		$forum	= $this->dbMulti($query);
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);

		foreach($forum as $idx => $e){

			if($e['id_socialforum'] != $opt['noid_socialforum']){
				$forum[$idx]['sub'] = $this->socialForumGet(array(
					'debug'				=> $opt['debug'],
					'thread'			=> true,
					'mid_socialforum'	=> $e['id_socialforum'],
					'noid_socialforum'	=> $opt['noid_socialforum']
				));
			}

		}

		return $forum;
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
			: "k_socialforum.pos_forum ASC");

		if($opt['offset'] != '' && $opt['limit']) $limit = "\nLIMIT ".$opt['offset'].",".$opt['limit'];
	}else{
		$flip  = true;
	}


	# FORUMS
	#
	$forums 		= $this->$dbMode("SELECT SQL_CALC_FOUND_ROWS * FROM k_socialforum\n" . $join . $where . $order . $limit);
	$this->total	= $this->db_num_total;
	if($opt['debug']) $this->pre("[QUERY]", $this->db_query, "[ERROR]", $this->db_error, "[DATA]", $forums);

	# FORMAT
	#
	if(sizeof($forums) > 0){

		if($flip) $forums = array($forums);
		
		$forums = $this->socialForumMapping(array(
			'forums'	=> $forums,
			'fields'	=> $this->apiLoad('field')->fieldGet(array('socialForum' => true))
		));

		if($flip) $forums = $forums[0];
	}

	if($opt['debug']) $this->pre("[FORMAT]", $forums);

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep);

	return $forums;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialForumSet($opt){

	# NEW !
	#
	if($opt['id_socialforum'] == NULL){
		$this->dbQuery("INSERT INTO k_socialforum (socialForumName) VALUES ('TEMP_NAME')");
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
		$id_socialforum = $this->db_insert_id;

		// position		
		if(intval($opt['core']['mid_socialforum']['value']) > 0){
			$last = $this->dbOne("SELECT MAX(pos_forum) AS m FROM k_socialforum WHERE mid_socialforum=".$opt['core']['mid_socialforum']['value']);
			$opt['core']['pos_forum'] = array('value' => $last['m']+1);
		}

	}else{
		$id_socialforum = $opt['id_socialforum'];
	}

	$this->id_socialforum = $id_socialforum;

	# CORE
	#
	$query = $this->dbUpdate(array('k_socialforum' => $opt['core']))." WHERE id_socialforum=".$id_socialforum;
	$this->dbQuery($query);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);

	# FIELD
	#
	if(sizeof($opt['field']) > 0){

		# Si on utilise le KEY au lieu des ID
		$fields = $this->apiLoad('field')->fieldGet(array('socialForum' => true));
		foreach($fields as $e){
			$fieldsKey[$e['fieldKey']] = $e;
		} $fields = $fieldsKey;

		unset($def);
		$apiField = $this->apiLoad('field');

		foreach($opt['field'] as $id_field => $value){
			if(!is_integer($id_field)) $id_field = $fields[$id_field]['id_field'];
			$value = $apiField->fieldSaveValue($id_field, $value);
			$def['k_socialforum']['field'.$id_field] = array('value' => $value); 
		}

		$this->dbQuery($this->dbUpdate($def)." WHERE id_socialforum=".$id_socialforum);
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	}

	# Mettre a jour la famille de FORUM
	#
	$this->socialForumFamily();
	
	return true;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialForumMapping($opt){

	$forums = $opt['forums'];

	foreach($forums as $n => $e){

		# JSON
		#
		$forums[$n]['socialForumFlat']		= ($e['socialForumFlat'] != '') 	? json_decode($e['socialForumFlat']) 	: array();
		$forums[$n]['socialForumThread']	= ($e['socialForumThread'] != '')	? json_decode($e['socialForumThread'])	: array();
		$forums[$n]['socialForumParent']	= ($e['socialForumParent'] != '')	? json_decode($e['socialForumParent'])	: array();

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
						$forums[$n]['field'][$f['fieldKey']] = $media;
					}
				}else

				if(is_array($v) && $f['fieldType'] == 'user'){
					unset($tmp);
					foreach($v as $id_user){
						$tmp[] = $this->dbOne("SELECT * FROM k_user WHERE id_user=".$id_user);
					}
					$forums[$n]['field'][$f['fieldKey']] = $tmp;
				}else

				if(is_array($v) && $f['fieldType'] == 'content'){
					unset($tmp);
					foreach($v as $id_content){
						$tmp[] = $this->dbOne("SELECT * FROM k_contentdata WHERE id_content=".$id_content);
					}
					$forums[$n]['field'][$f['fieldKey']] = $tmp;
				}else

				if(in_array($f['fieldType'], array('onechoice', 'multichoice')) && substr($v, 0, 2) == $this->splitter && substr($v, -2) == $this->splitter && $v != $this->splitter){
					$part = explode($this->splitter, substr($v, 2, -2));
					$forums[$n]['field'][$f['fieldKey']] = implode("<br />", $part);

				}else{
					$forums[$n]['field'][$f['fieldKey']] = $v;
				}

				unset($forums[$n]['field'.$f['id_field']]);
			}
		}		
	}

	return $forums;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialForumRemove($opt){

	$forum = $this->socialForumGet(array(
		'id_socialforum'	=> $opt['id_socialforum']
	));

	if(intval($forum['id_socialforum']) == 0) return false;
	
	$ids[] = $forum['id_socialforum'];
	$ids   = array_merge($ids, $forum['socialForumFlat']);

	$this->dbQuery("DELETE FROM k_socialforum WHERE id_socialforum IN(".implode(',', $ids).")");
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);

	$this->socialForumFamily();
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	Mettre a jour les PARENT, CHILDREN et THREAD de tous les FORUM
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function socialForumFamily(){

	$forum = $this->socialForumGet(array(
		'thread'	=> true,
		'mid_socialforum' => 0,
		'debug'		=> false
	));

	# PARENTS
	#
	$this->tempFor	= array();
	$this->socialForumFamilyParent($forum);

	foreach($this->tempFor as $id_socialforum => $tree){
		$this->dbQuery("UPDATE k_socialforum SET socialForumParent='".json_encode($tree)."' WHERE id_socialforum=".$id_socialforum);
		#$this->pre($this->db_query, $this->db_error);
	}

	# CHILDREN + THREAD
	#
	foreach($forum as $e){
		$child	= $this->socialForumFamilyChildren($e);
		$thread	= $this->socialForumFamilyThread($e['id_socialforum']);

		$this->dbQuery(
			"UPDATE k_socialforum SET ".
			"socialForumFlat='".json_encode($child)."', socialForumThread='".json_encode($thread)."'".
			"WHERE id_socialforum=".$e['id_socialforum']
		);
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	Trouver tous les PARENTS pour un FORUM
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
private function socialForumFamilyParent($forum, $path=array(), $add=NULL){

	if($add != NULL) $path[] = $add;

	foreach($forum as $c){
		$this->tempFor[$c['id_socialforum']] = $path;
		if(sizeof($c['sub']) > 0){
			$this->socialForumFamilyParent($c['sub'], $path, intval($c['id_socialforum']));
		}
	}

}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	Trouver tous les CHILDREN pour un FORUM
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function socialForumFamilyChildren($e, &$line=array()){

	$children = $this->socialForumGet(array(
		'debug'				=> false,
		'mid_socialforum' 	=> $e['id_socialforum']
	));

	foreach($children as $child){
		$line[] = intval($child['id_socialforum']);
		$this->socialForumFamilyChildren($child, $line);
	}

	return $line;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Trouver le THREAD DESCENDANT pour un FORUM
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialForumFamilyThread($mid_socialforum){

	$children = $this->dbMulti("SELECT id_socialforum FROM k_socialforum WHERE mid_socialforum=".$mid_socialforum." ORDER BY pos_forum");

	if(sizeof($children) > 0){
		foreach($children as $c){
			$tmp[] = array(
				'i' => intval($c['id_socialforum']),
				's' => $this->socialForumFamilyThread($c['id_socialforum'])
			);
		}
		return $tmp;
	}else{
		return array();
	}
}	

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function socialForumSelector($opt){

	$forum = $this->socialForumGet(array(
		'debug'				=> false,
		'mid_socialforum'	=> 0,
		'noid_socialforum'	=> $opt['noid'],
		'threadFlat'		=> true
	));

	if($opt['multi']){
		$value = is_array($opt['value']) ? $opt['value'] : array();

		$form .= "<select name=\"".$opt['name']."\" id=\"".$opt['id']."\" size=\"".$opt['size']."\" multiple style=\"".$opt['style']."\">";
		foreach($forum as $e){
			$selected = in_array($e['id_socialforum'], $value) ? ' selected' : NULL;
			$form .= "<option value=\"".$e['id_socialforum']."\"".$selected.">".str_repeat(' &nbsp; &nbsp;', $e['level']).'&nbsp;'.$e['socialForumName']."</option>";
		}
		$form .= "</select>";
	}else
	if($opt['one']){
		$value = is_array($opt['value']) ? $opt['value'][0] : $opt['value'];

		$form .= "<select name=\"".$opt['name']."\" id=\"".$opt['id']."\" style=\"".$opt['style']."\">";
		if($opt['empty']) $form .= "<option value=\"\"></option>";
		foreach($forum as $e){
			$selected = ($e['id_socialforum'] == $value) ? ' selected' : NULL;
			$form .= "<option value=\"".$e['id_socialforum']."\"".$selected.">".str_repeat(' &nbsp; &nbsp;', $e['level']).'&nbsp;'.$e['socialForumName']."</option>";
		}
		$form .= "</select>";
	}
	
	return $form;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function socialForumCheck($forum){

	if(!is_array($forum)) return array();
	
	foreach($forum as $e){
		unset($autre);

		foreach($forum as $a){
			if($a != $e) $autre[] = $a;
		}
		
		$me = $this->socialForumGet(array(
			'language' 			=> 'fr',
			'id_socialforum' 	=> $e
		));

		foreach($me['socialForumFlat'] as $c){
			if(@in_array($c, $autre)) $louche[] = $c;
		}
	}

	foreach($forum as $e){
		if(!@in_array($e, $louche)) $rest[] = $e;
	}

	return is_array($rest) ? $rest : array();
}






































} ?>