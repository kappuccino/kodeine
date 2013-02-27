<?php
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Last release		2011.05.11
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

class comment extends coreApp {

function __clone(){}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function comment(){
//	$this->coreApp();
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function commentGet($opt=array()){

	$limit		= ($opt['limit'] != '') 	? $opt['limit']		: 30;
	$offset		= ($opt['offset'] != '')	? $opt['offset']	: 0;

	if(isset($opt['is_moderate'])) $cond[] = " is_moderate=".$opt['is_moderate'];

	if($opt['id_comment'] != NULL){
		$cond[] = 'id_comment = '.$opt['id_comment'];
		$dbMode = 'dbOne';
	}else
	if($opt['id_content'] != NULL){
		$cond[] = 'id_content = '.$opt['id_content'];
		$dbMode = 'dbMulti';
	}else{
		$dbMode = 'dbMulti';
	}

	if(sizeof($cond) > 0) $where_ = "WHERE ".implode(' AND ', $cond);

	if($dbMode == 'dbMulti'){
		if($opt['order'] != NULL && $opt['direction'] != NULL){
			$order_ = " ORDER BY ".$opt['order']." ".$opt['direction'];
		}
		
		$limit_ = " LIMIT ".$offset.",".$limit;
	}

	$out =  $this->$dbMode("SELECT * FROM k_contentcomment ". $where_ . $order_ . $limit_);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error, $out);

	return $out;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function commentSet($id_comment, $def){

	## escape user inputs
	if (isset($def["k_contentcomment"]['commentData'])) {
		$def["k_contentcomment"]['commentData'] = addslashes($def["k_contentcomment"]['commentData']);
	}
	if (isset($def["k_contentcomment"]['commentUsername'])) {
		$def["k_contentcomment"]['commentUsername'] = addslashes($def["k_contentcomment"]['commentUsername']);
	}

	if($id_comment > 0){
		$q = $this->dbUpdate($def)." WHERE id_comment=".$id_comment;
	}else{
		$q = $this->dbInsert($def);
	}

	@$this->dbQuery($q);
	if($this->db_error != NULL) return false;

	$this->id_comment = ($id_comment > 0) ? $id_comment : $this->db_insert_id;

	$id_content = $def['k_contentcomment']['id_content']['value'];
	
	if($id_content > 0 && $def['k_contentcomment']['is_moderate']['value'] == 1){
		$this->commentUpdateCount($id_comment);
	}

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function commentRemove($id_comment){
	if(intval($id_comment) <= 0) return false;
	
	$tmp = $this->dbOne("SELECT * FROM k_contentcomment WHERE id_comment=".$id_comment);

	if(intval($tmp['id_comment']) <= 0) return false;

	$this->dbQuery("UPDATE k_contentcomment SET is_moderate=0 WHERE id_comment=".$id_comment);
	$this->commentUpdateCount($id_comment);

	$this->dbQuery("DELETE FROM k_contentcomment WHERE id_comment=".$id_comment);
	$this->dbQuery("DELETE FROM k_contentcommentrate WHERE id_comment=".$id_comment);
	
	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function commentModerate($id_comment, $mod){
	if(intval($id_comment) <= 0) return false;

	$tmp = $this->dbOne("SELECT * FROM k_contentcomment WHERE id_comment=".$id_comment);
	
	if(intval($tmp['id_content']) <= 0) return false;

	$this->dbQuery("UPDATE k_contentcomment SET is_moderate=".$mod." WHERE id_comment=".$id_comment);
	$this->commentUpdateCount($id_comment);
	
	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function commentUpdateCount($id_comment){

	$tmp = $this->dbOne("SELECT * FROM k_contentcomment WHERE id_comment=".$id_comment);
	if(intval($tmp['id_content']) <= 0) return false;

	$count = $this->dbOne("SELECT COUNT(id_comment) as H FROM k_contentcomment WHERE is_moderate=1 AND id_content=".$tmp['id_content']);
	$this->dbQuery("UPDATE k_content SET contentCommentCount=".$count['H']." WHERE id_content=".$tmp['id_content']);
#	echo $this->db_query."\n";	

	return true;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function commentGoodBad($opt){

	$id_content	= $opt['id_content'];	if(intval($id_content)	<= 0) return false;
	$id_comment	= $opt['id_comment'];	if(intval($id_comment)	<= 0) return false;
	$id_user	= $opt['id_user'];		if(intval($id_user) 	<= 0) return false;

	$field = "comment".ucfirst(strtolower($opt['gb']));
	$this->dbQuery("UPDATE k_contentcomment SET ".$field."=".$field."+1 WHERE id_comment=".$id_comment);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);

	$this->commentGoodBadCalculate($id_comment);

	$tmp['k_contentcommentrate'] = array(
		'id_content'		=> array('value' => $id_content),
		'id_comment' 		=> array('value' => $id_comment), 
		'id_user'			=> array('value' => $id_user),
		'commentRateDate'	=> array('value' => date("Y-m-d H:i:s")),
		'commentRateValue'	=> array('value' => (($opt['gb'] == 'good') ? '1' : '-1'))
	);

	$this->dbQuery($this->dbInsert($tmp));
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function commentGoodBadCalculate($id_comment){

	$avg = $this->dbOne("SELECT * FROM k_contentcomment WHERE id_comment=".$id_comment);
	$avg = $avg['commentGood'] + ($avg['commentBad'] * -1);

	$this->dbQuery("UPDATE k_contentcomment SET commentAvg=".$avg." WHERE id_comment=".$id_comment);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function commentGoodBadUserValue($opt){

	$id_content = $opt['id_content'];	if(intval($id_content) <= 0) return array();
	$id_user	= $opt['id_user'];		if(intval($id_user) <= 0) 	 return array();

	$done = $this->dbMulti(
		"SELECT k_contentcommentrate.* FROM k_contentcomment\n".

		"INNER JOIN k_contentcommentrate ON k_contentcomment.id_comment = k_contentcommentrate.id_comment\n".

		"WHERE k_contentcommentrate.id_content=".$id_content." AND k_contentcommentrate.id_user=".$id_user
	);

	if(sizeof($done) > 0){
		foreach($done as $e){
			$out[$e['id_comment']] = $e;
		}
	}else{
		$out = array();
	}

	return $out;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function commentGoodBadUsers($opt){

	$id_comment = $opt['id_comment'];	if(intval($id_comment) <= 0) return array();;

	$users = $this->dbMulti("SELECT * FROM k_contentcommentrate WHERE id_comment=".$id_comment);
	
	return $users;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function commentGoodBadUndo($opt){

	$id_user	= $opt['id_user'];		if(intval($id_user) <= 0) 	 return false;
	$id_comment	= $opt['id_comment'];	if(intval($id_comment) <= 0) return false;

	$rate		= $this->dbOne("SELECT * FROM k_contentcommentrate WHERE id_comment=".$id_comment." AND id_user=".$id_user);
	
	if($rate['id_comment'] > 0){
		
		// Decremente le COMPTEUR sur la table COMMENT
		$field = "comment".ucfirst(strtolower(	(($rate['commentRateValue'] == -1) ? 'bad' : 'good')			));
		$this->dbQuery("UPDATE k_contentcomment SET ".$field."=".$field."-1 WHERE id_comment=".$id_comment);

		// Supprime HISTORIQUE de vote dans la table COMMENTRATE
		$this->dbQuery("DELETE FROM k_contentcommentrate WHERE id_comment=".$id_comment." AND id_user=".$id_user);
	
		// Calculer AVERAGE et sauver le resultat
		$this->commentGoodBadCalculate($id_comment);
	}

	return true;
}



} ?>