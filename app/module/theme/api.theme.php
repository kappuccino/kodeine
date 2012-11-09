<?php
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Last release		2011.09.25
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

class theme extends coreApp {

function __clone(){}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function theme(){}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function themeGet($opt=array()){

	if($opt['id_theme'] > 0){
		$dbMode = 'dbOne';
		$cond[] = "k_theme.id_theme=".$opt['id_theme'];
	}else{
		$dbMode = 'dbMulti';
	}

	# Former les conditions
	#
	if(sizeof($cond) > 0) $where = "WHERE ".implode(" AND ", $cond);

	# Theme
	#
	$theme = $this->$dbMode("SELECT * FROM k_theme ".$where);

	if($opt['debug']) $this->pre($opt, $this->db_query, $this->db_error, $theme);

	return $theme;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function themeSet($id_theme, $def){

	if($id_theme > 0){
		$q = $this->dbUpdate($def)." WHERE id_theme=".$id_theme;
	}else{
		$q = $this->dbInsert($def);
	}

	@$this->dbQuery($q);
	if($this->db_error != NULL) return false;

	$this->id_theme = ($id_theme > 0) ? $id_theme : $this->db_insert_id;

	return true;
}



} ?>