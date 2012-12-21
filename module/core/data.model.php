<?php
// http://forums.phpfreaks.com/topic/179477-need-help-how-to-catch-acess-of-undefined-class-properties/

class dataModel extends dataSql{

private $data       = array();
private $table      = NULL;
private $primary    = array();
private $structure  = array();
private $query      = NULL;
private $debug      = false;
private $keep       = false;

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function dataModel(){
	$this->query = new dataSQL();
	return $this;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function table($v=NULL){
	if($v == NULL) return $this->table;
	$this->table = $v;
	return $this;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
primary = {key: NULL}
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function primaryKey(){

	$args = func_get_args();
	if(count($args) == 0) return $this->primary;

	foreach($args as $k){
		$this->primary[$k] = NULL;
	}

	return $this;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
primary = {key: value}
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function primary(){

	$args = func_get_args();
	if(count($args) == 0) return $this->primary;

	$primKey = array_keys($this->primary);
	foreach($args as $n => $v){
		$this->primary[$primKey[$n]] = $v;
	}

	return $this;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function structure($v=NULL){
	if($v === NULL) return $this->structure;
	$this->structure = $v;
	return $this;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function set($key, $value=NULL){

	if(is_array($key) && $value == NULL){

		$tmp = array();
		foreach($key as $k => $v){
			if(!array_key_exists($k, $this->structure)) throw new Exception('Key '.$k.' is not defined in STRUCTURE');
			$tmp[$k] = $v;
		}
		$this->data = $tmp;

	}else
	if(is_string($key) && $value != ''){
		if(!array_key_exists($key, $this->structure)) throw new Exception('Key: '.$key.' is not defined in STRUCTURE');
		$this->data[$key] = $value;
	}

	$this->query->set($this->data);

	return $this;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
Return the DB result
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function get(){

	$args  = func_get_args();
	$query = $this->query->from($this->table);

	# {field:value}
	if(count($args) == 1 && is_array($args[0])){
		$query->select('*');
		call_user_func_array(array($query, 'where'), $args);
	}else
	# field,value
	if(count($args) > 0){
		call_user_func_array(array($this, 'primary'), $args);
		$query->selectOne('*')->where($this->primary());
	}

	if($this->debug()) echo $query."\n";

	$this->data = $query->toArray();
	return $this->keep ? $this : $this->data;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
Return the current Object and Keep the DB Result in this->data array
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function get_(){

	$this->keep = true;
	$data = call_user_func_array(array($this, 'get'), func_get_args());
	$this->keep = false;

	return $data;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function exists(){

	$args  = func_get_args();
	$query = $this->query->selectOne('1')->from($this->table);

	# {field:value}
	if(count($args) == 1 && is_array($args[0])){
		call_user_func_array(array($query, 'where'), $args);
	}else
	# field,value
	if(count($args) > 0){
		call_user_func_array(array($this, 'primary'), $args);
		$query->where($this->primary());
	}

	if($this->debug()) echo $query."\n";

	$data = $query->toArray();
	$data = ($data['1'] == '1') ? true : false;

	return $data;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function count(){

	$args  = func_get_args();
	$query = $this->query->selectOne('COUNT(*)')->from($this->table);

	# {field:value}
	if(count($args) == 1 && is_array($args[0])){
		call_user_func_array(array($query, 'where'), $args);
	}else
	# field,value
	if(count($args) > 0){
		call_user_func_array(array($this, 'primary'), $args);
		$query->where($this->primary());
	}

	if($this->debug()) echo $query."\n";

	$data = $query->toArray();
	$data = intval($data['COUNT(*)']);

	return $data;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function debug($d=NULL){
	if(!is_bool($d)) return $this->debug;
	$this->debug = $d;
	return $this;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function insert(){
	$query = (string) $this->query->insert($this->table);
	$this->dbQuery($query);

	return (($this->db_insert_id > 0) ? $this->db_insert_id : false);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function update(){

	$args  = func_get_args();
	$query = $this->query->update($this->table);

	# {field:value}
	if(count($args) == 1 && is_array($args[0])){
		call_user_func_array(array($query, 'where'), $args);
	}else
	# field,value
	if(count($args) > 0){
		call_user_func_array(array($this, 'primary'), $args);
		$query->where($this->primary());
	}

	if($this->debug()) echo $query."\n";
	$success = $query->execute();

	if($this->keep) return $this;
	return $success;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function update_(){

	$this->keep = true;
	$data = call_user_func_array(array($this, 'update'), func_get_args());
	$this->keep = false;

	return $data;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function remove(){
	call_user_func_array(array($this, 'primary'), func_get_args());
	$this->query->delete($this->table)->where($this->primary());
	$query = (string) $this->query;

	echo $query;
	$this->dbQuery($query);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function toArray(){
	$out = (array) $this->data;
	return $out;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function toJson(){
	$out = json_encode($this->data);
	return $out;
}


}

?>