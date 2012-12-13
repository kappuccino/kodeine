<?php
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Last release		2011.06.09
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

class shop extends coreApp {

function __clone(){}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function shop(){
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function shopGet($opt=array()){
if($opt['debug']) $this->pre("[OPT]", $opt);

	# Gérer les options
	#
	$limit		= ($opt['limit'] != '') 		? $opt['limit']			: 30;
	$offset		= ($opt['offset'] != '') 		? $opt['offset']		: 0;

	if($opt['id_shop'] > 0){
		$dbMode = 'dbOne';
		$cond[] = "k_shop.id_shop=".$opt['id_shop'];
	}else{
		$dbMode = 'dbMulti';
	}

	# Former les LIMITATIONS et ORDRE
	#
	if($dbMode == 'dbMulti'){
		$sqlOrder = (isset($opt['order']) && isset($opt['direction']))
			? "\nORDER BY ".$opt['order']." ".$opt['direction']
			: "\nORDER BY id_shop ASC";

		$sqlLimit = "\nLIMIT ".$offset.",".$limit;
	}

	if(sizeof($cond) > 0) $where = "WHERE ".implode(" AND ", $cond);

	# SHOP
	#
	$shops = $this->$dbMode("SELECT SQL_CALC_FOUND_ROWS * FROM k_shop ". $where . $sqlOrder . $sqlLimit);

	$this->total	= $this->db_num_total;
	$this->limit	= $limit;

	if($opt['debug']) $this->pre($this->db_query, $this->db_error, $shops);
	
	return $shops;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function shopSet($id_shop, $def){
if($opt['debug']) $this->pre("[opt]", $opt);

	$q = ($id_shop > 0)
		? $this->dbUpdate($def)." WHERE id_shop=".$id_shop
		: $this->dbInsert($def);

	@$this->dbQuery($q);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	if($this->db_error != NULL) return false;

	$this->id_shop = ($id_shop > 0) ? $id_shop : $this->db_insert_id;

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function shopRemove($id_shop){

	if($id_shop == NULL) return false;

	$this->dbQuery("DELETE FROM k_contentshop	WHERE id_shop=".$id_shop);
	$this->dbQuery("DELETE FROM k_shop 			WHERE id_shop=".$id_shop);

	return true;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function shopSelector($opt){

	$shop = $this->shopGet();

	if($opt['multi']){
		$value = is_array($opt['value']) ? $opt['value'] : array();

		$form .= "<select name=\"".$opt['name']."\" id=\"".$opt['id']."\" size=\"".$opt['size']."\" multiple style=\"".$opt['style']."\" ".$opt['events']." class=\"".$opt['class']."\">";
		foreach($shop as $e){
			$selected = in_array($e['id_shop'], $value) ? ' selected' : NULL;
			$form .= "<option value=\"".$e['id_shop']."\"".$selected.">".str_repeat(' &nbsp; &nbsp;', $e['level']).'&nbsp;'.$e['shopName']."</option>";
		}
		$form .= "</select>";
	}else
	if($opt['one']){
		$value = is_array($opt['value']) ? $opt['value'][0] : $opt['value'];

		$form .= "<select name=\"".$opt['name']."\" id=\"".$opt['id']."\" style=\"".$opt['style']."\" ".$opt['events']." class=\"".$opt['class']."\">";
		if($opt['empty']) $form .= "<option value=\"\"></option>";
		foreach($shop as $e){
			$selected = ($e['id_shop'] == $value) ? ' selected' : NULL;
			$form .= "<option value=\"".$e['id_shop']."\"".$selected.">".str_repeat(' &nbsp; &nbsp;', $e['level']).'&nbsp;'.$e['shopName']."</option>";
		}
		$form .= "</select>";
	}
	
	return $form;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function shopMailExtraction($raw){

	$raw	= trim($raw);
	$lines	= explode("\n", $raw);

	foreach($lines as $e){
		$e = trim($e);

		if(!filter_var($e, FILTER_VALIDATE_EMAIL) === FALSE){
			$end[] = $e;
		}
	}

	return is_array($end) ? $end : array();
}











} ?>