<?php
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Last release		2011.05.11
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

class rate extends coreApp {

function __clone(){}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function rate(){
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function rateSet($id_rate, $def){

	if($id_rate > 0){
		$q = $this->dbUpdate($def)." WHERE id_rate=".$id_rate;
	}else{
		$q = $this->dbInsert($def);
	}

	@$this->dbQuery($q);
	if($this->db_error != NULL) return false;

	$this->id_rate = ($id_rate > 0) ? $id_rate : $this->db_insert_id;

	if($def['k_contentrate']['id_content']['value'] > 0){
		$rate = $this->rateCalcultate($def['k_contentrate']['id_content']['value']);

		$this->dbQuery(
			"UPDATE k_content ".
			"SET contentRateAvg=".$rate.", contentRateCount=contentRateCount+1 ".
			"WHERE id_content=".$def['k_contentrate']['id_content']['value']
		);
	}

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function rateCalcultate($id_content){

	$raw = $this->dbMulti("SELECT * FROM k_contentrate WHERE id_content=".$id_content);
	
	$total = 0;
	foreach($raw as $e){
		$total += $e['rateValue'];
	}
	
	$rate = @round($total / sizeof($raw));
	
	return $rate;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function rateUsers($opt){

	$id_content = $opt['id_content'];	if(intval($id_content) <= 0) return array();

	$users = $this->dbMulti("SELECT * FROM k_contentrate WHERE id_content=".$id_content);
	
	return $users;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function rateUserValue($opt){

	$id_content = $opt['id_content'];	if(intval($id_content) <= 0) return -1;
	$id_user	= $opt['id_user'];		if(intval($id_user) <= 0) 	 return -1;

	$done = $this->dbOne("SELECT rateValue FROM k_contentrate WHERE id_content=".$id_content." AND id_user=".$id_user);
	
	return ($done['rateValue'] > 0) ? $done['rateValue'] : -1;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function rateUndo($opt){

	$id_content = $opt['id_content'];	if(intval($id_content) <= 0) return false;
	$id_user	= $opt['id_user'];		if(intval($id_user) <= 0) 	 return false;
	$rate		= $this->dbOne("SELECT * FROM k_contentrate WHERE id_content=".$id_content." AND id_user=".$id_user);
	
	if($rate['id_rate'] > 0){
		$this->dbQuery("DELETE FROM k_contentrate WHERE id_content=".$id_content." AND id_user=".$id_user);
		$avg = $this->rateCalcultate($rate['id_rate']);
		$this->dbQuery("UPDATE k_content SET contentRateAvg=".$avg.", contentRateCount=contentRateCount-1 WHERE id_content=".$id_content);
	}	

	return true;
}

} ?>