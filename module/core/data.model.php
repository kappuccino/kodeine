<?php
// http://forums.phpfreaks.com/topic/179477-need-help-how-to-catch-acess-of-undefined-class-properties/

class dataModel extends coreDbQuery{

	private $id         = NULL;
	private $data       = array();
	private $table      = NULL;
	private $primary    = array();
	private $structure  = array();
	private $query      = NULL;
	private $debug      = false;
	private $keep       = false;
	private $hasMany    = array();
	private $hasOne     = array();
	private $success    = NULL;
	private $valid      = true;


	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
	public function __toString(){
		return 'dataModel Object';
	}

	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
	public function dataModel(){
		$this->query = new coreDbQuery();
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
				if(!$this->valid($k, $v)) $this->valid(false);
				$tmp[$k] = $v;
			}
			$this->data = $tmp;

		}else
		if(is_string($key) && $value != ''){
			if(!array_key_exists($key, $this->structure)) throw new Exception('Key: '.$key.' is not defined in STRUCTURE');
			if(!$this->valid($key, $value)) $this->valid(false);
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

		/*if(count($this->hasMany) > 0){
			foreach($this->hasMany as $m){
				$model  = new $m['model'];

				echo "****\n";
				$tmp = $model->debug(true)->get(); //1, 'fr');
				print_r($tmp);
				echo "****\n";

			}
		}

		$this->pre($this->hasMany);*/








		echo "\n--\n";

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
		if($this->debug()) echo $query."\n";

		$success = $this->query->execute();

		$this->success($success);
		$this->insertId($this->query->db_insert_id);

		return $this;
	}

	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
	function success($v=NULL){
		if($v === NULL) return $this->success;
		$this->success = $v;

		return $this;
	}

	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
	function insertId($v=NULL){
		if($v === NULL) return $this->id;
		$this->id = $this->query->db_insert_id;
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
	function valid($key=NULL, $value=NULL){

		if($key == NULL && $value == NULL) return $this->valid;

		if(is_bool($key) && $value == NULL){
			$this->valid = $key;
			return $this;
		}

		$struct  = $this->structure[$key];
		$pattern = '#'.$struct['valid'].'#';

		if(!array_key_exists('valid', $struct)) return true;
		return preg_match($pattern, $value) == true;
	}

	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
	function getQuery(){
		return $this->query;
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

	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
	function hasOne($model){
		$this->hasOne[] = $model;
		return $this;
	}

	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
	function hasMany($model){
		$this->hasMany[] = $model;
		return $this;
	}



}