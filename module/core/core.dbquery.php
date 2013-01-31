<?php
// http://guides.rubyonrails.org/active_record_querying.html

class coreDbQuery extends coreApp{

private $dbQuery    = 'SELECT';
private $dbMode     = 'dbMulti';
private $select     = '*';
private $from       = '';
private $join      = array();
private $values     = array();
private $where      = array();
private $group      = '';
private $order      = '';
private $limit      = 30;
private $noLimit    = false;
private $offset     = 0;

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function delete($v=NULL){
	if($v === NULL) return $this->from;
	$this->dbQuery = 'DELETE';
	$this->from = $v;
	return $this;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function update($v=NULL){
	if($v === NULL) return $this->from;
	$this->dbQuery = 'UPDATE';
	$this->from = $v;
	return $this;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function insert($v=NULL){
	if($v === NULL) return $this->from;
	$this->dbQuery = 'INSERT';
	$this->from = $v;
	return $this;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function select($v=NULL){
	if($v === NULL) return $this->select;
	$this->where  = array();
	$this->select = $v;
	return $this;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function selectOne($v=NULL){
	if($v === NULL) return $this->select;
	$this->limit(1);
	$this->where  = array();
	$this->dbMode = 'dbOne';
	$this->select = $v;
	return $this;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function from($v=NULL){
	if($v === NULL) return $this->from;
	$this->from = $v;
	return $this;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function join($table=NULL, $on=NULL, $field=NULL){

	if($table === NULL && $on === NULL && $field === NULL){
		return implode("\n", $this->join);
	}else{
		$this->join[] = 'INNER JOIN '.$table.' ON '.$on.'.'.$field.' = '.$table.'.'.$field."\n";
		return $this;
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function where($key=NULL, $value=NULL, $mode='EG'){

	if(is_array($key)){
		$tmp = array();
		$ak  = array_keys($key);

		// Key => Value (mode = EG)
		if($ak[0] != $key[0]){
			foreach($key as $k => $v){
				$tmp[] = $this->dbMatch($k, $v, 'EG');
			}
		}
		// Key, Value, Mode
		else{
			foreach($key as $p){
				$tmp[] = $this->dbMatch($p[0], $p[1], (empty($p[2]) ? 'EG' : $p[2]));
			}
		}

		$this->where[] = implode(' AND ', $tmp);
	}else
	if(is_string($value) && $value != ''){
		$this->where[] = $this->dbMatch($key, $value, $mode);
	}else
	if($key === NULL){
	#	echo '@';
	#   print_r($this->where);
	#	echo '@'.PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL;

		return '('.implode("\t", $this->where).')';
	}

	return $this;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function set($key=NULL, $value=NULL){

	if(is_array($key)){
		foreach($key as $k => $v){
		#	$this->values[]   = $k."='".$v."'";
			$this->values[$k] = $k."='".$v."'";
		}
	}else
	if(is_string($key) && $value != ''){
	#	$this->values[]     = $key."='".$value."'";
		$this->values[$key] = $key."='".$value."'";
	}else
	if($key === NULL){
		return implode(', ', $this->values);
	}

	return $this;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function group($g=NULL){

	if($g === NULL) return $this->group;
	$this->group = $g;
	return $this;

}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function order($o=NULL){

	if($o === NULL) return $this->order;
	$this->order = $o;
	return $this;

}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function limit($v=NULL){
	if($v === false){
		$this->noLimit = true;
		return $this;
	}else
	if($v === NULL) return $this->limit;
	$this->limit = $v;
	return $this;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function offset($v=NULL){
	if($v === NULL) return $this->offset;
	$this->offset = $v;
	return $this;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function toArray(){
	$query = (string) $this;
	$mode  = $this->dbMode;
	$data  = $this->$mode($query);

	return $data;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function execute(){
	$query = (string) $this;
	$this->dbQuery($query);

	return (($this->db_error == NULL) ? true : false);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function __toString(){

	$query = array();

	if(count($this->values) > 0) $set   = "SET ".$this->set();
	if(count($this->where)  > 0) $where = "WHERE ".$this->where();

	if($this->dbQuery == 'SELECT'){
		$query[] = 'SELECT '.$this->select();
		$query[] = 'FROM `'.$this->from().'`';

		if(count($this->join) > 0) $query[] = $this->join();

		if(isset($where)) $query[] = $where;

		if($this->group != '') $query[] = "GROUP BY ".$this->group();
		if($this->order != '') $query[] = "ORDER ".$this->order();

		if(!$this->noLimit) $query[] = 'LIMIT '.$this->offset().','.$this->limit();

	}else
	if($this->dbQuery == 'INSERT'){
		$query[] = 'INSERT INTO `'.$this->from().'`';
		if(isset($set)) $query[] = $set;

	}else
	if($this->dbQuery == 'UPDATE'){
		$query[] = 'UPDATE `'.$this->from().'`';
		if(isset($set))     $query[] = $set;
		if(isset($where))   $query[] = $where;

	}else
	if($this->dbQuery == 'DELETE'){
		$query[] = 'DELETE FROM `'.$this->from().'`';
		if(isset($where)) $query[] = $where;
	}

	return implode("\n", $query);
}

} ?>