<?php

namespace Kodeine;

class appMysql{

	public  static $_instance;
	private $mysql;
	private $db_query;
	private $db_error;
	private $db_time;
	private $db_num_total;

// SINGLETON ///////////////////////////////////////////////////////////////////////////////////////////////////////////

	private function __construct($host=NULL, $login=NULL, $passwd=NULL, $database=NULL){

		register_shutdown_function(array($this, '__destruct__'));

		if(BENCHME) $GLOBALS['q'] = array();
		if(file_exists(CONFIG.'/config.php')) include(CONFIG.'/config.php');

		if(isset($config)){
			$conf   = $config['mysql'];

			$port   = $conf['port'] ?: 3306;
			$sdn    = 'mysql:host='.$conf['host'].';port='.$port.';dbname='.$conf['database'].';charset=UTF-8';

			try{
				$mysql = new \PDO($sdn, $conf['login'], $conf['password']);
			} catch (PDOException $e) {
				throw new Exception('Failed to connect to MySQL '.mysqli_connect_error());
			}
		}

		$this->mysql = $mysql;

		return $this;
	}

	public function __destruct__(){
		if(self::$_instance->thread_id > 0) self::$_instance->close();
	}

	public static function getInstance(){
		if(!isset(self::$_instance) OR self::$_instance === null) self::$_instance = new appMysql(); //self();
		return self::$_instance;
	}

	public function test(){
		echo "test depuis ".__CLASS__;
	}


// PDO CLASS ///////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function query($query, $link=NULL){

		if($query == NULL) return false;

		$before	= microtime(true);
		$flux   = $link ?: self::getInstance()->mysql;
		$query  = trim($query);
		$result = $flux->query($query);
		$error	= $flux->error;
		$time	= microtime(true) - $before;

		$this->db_query($query);
		$this->db_error($error);
		$this->db_time($time);


		/*
		# Benchmark +1
		if(BENCHME) $GLOBALS['q'][] = array($time, $query, $GLOBALS['bench']->benchmark['current']);

		# Log Error
		if($this->db_error) $this->pre($this->db_error);

		if($GLOBALS['dblog'] && $this->db_error != NULL){
			$file = DBLOG.'/E.'.date("Y-m-d-H").'h.log';
			$fo   = fopen($file, 'a+');
			$raw  = date("Y-m-d H:i:s")."\n".$query."\n".$this->db_error."\n";
			$raw .= "Url http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."\n";
			$raw .= "Referer : ".$_SERVER['HTTP_REFERER']."\n";
			foreach(debug_backtrace() as $e){
				$raw .= "\t".$e['file'].":".$e['line']."\n\t".$e['function']."()\n\t".$_SERVER['REQUEST_URI']."\n\n";
			}
			$raw .= "----\n";
			$fw   = fwrite($fo, $raw, strlen($raw));
			$fc   = fclose($fo);
		}else
		if($GLOBALS['dblograw'] && preg_match("#^(INSERT|UPDATE|DELETE)#", trim($query))){
			$file = DBLOG.'/R.'.date("Y-m-d-H").'h.log';
			$fo   = fopen($file, 'a+');
			$raw  = date("Y-m-d H:i:s")." ".str_replace("\n", ' ', $query)."\n";
			$fw   = fwrite($fo, $raw, strlen($raw));
			$fc   = fclose($fo);
		}

		$this->db_num_rows		= NULL;
		$this->db_num_fields	= NULL;
		$this->db_insert_id		= NULL;
		$this->db_affected_rows	= 0;

		if(strtoupper(substr($query, 0, 6)) == 'SELECT'){
			$this->db_num_rows		= $flux->num_rows;
			$this->db_num_fields	= $flux->field_count;
		}else
		if(strtoupper(substr($query, 0, 6)) == 'INSERT'){
			$this->db_insert_id		= $flux->insert_id;
			$this->db_affected_rows	= $flux->affected_rows;
		}else
		if(strtoupper(substr($query, 0, 6)) == 'UPDATE'){
			$this->db_affected_rows	= $flux->affected_rows;
		}else
		if(strtoupper(substr($query, 0, 6)) == 'DELETE'){
			$this->db_affected_rows	= $flux->affected_rows;
		}

		*/

		return $result;
	}

	public function db_query($q=NULL){
		if($q === NULL) return $this->db_query;
		$this->db_query = $q;
		return $this;
	}

	public function db_time($t=NULL){
		if($t === NULL) return $this->db_time;
		$this->db_time = $t;
		return $this;
	}

	public function db_error($e=NULL){
		if($e === NULL) return $this->db_error;
		$this->db_error = $e;
		return $this;
	}

	public function db_num_total($t=NULL){
		if($t === NULL) return $this->db_num_total;
		$this->db_num_total = $t;
		return $this;
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function one($query, $link=NULL){

		$result = $this->query($query, $link);
		if($result === false) return array();

		$data = $result->fetch(\PDO::FETCH_ASSOC);

		return is_array($data) ? $data : array();
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function multi($query, $link=NULL){

		$result = $this->query($query, $link);
		if($result === false) return array();

		$data = array();
		while($e = $result->fetch(\PDO::FETCH_ASSOC)){
			array_push($data, $e);
		}

		if(strpos($query, "SQL_CALC_FOUND_ROWS") !== false){
			$flux = $link ?: self::getInstance()->mysql;
			$tmp  = $flux->query("SELECT FOUND_ROWS() AS H")->fetch(\PDO::FETCH_ASSOC);
			$this->db_num_total($tmp['H']);
		}

		return $data;
	}


	public function insert(){

	}

	public function update(){

	}

	public function delete(){

	}






// MYSQLI CLASS ////////////////////////////////////////////////////////////////////////////////////////////////////////

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function dbEscape($v, $link=NULL){
		$flux	= ($link == NULL) ? self::__getInstance() : $link;
		return (substr_count($v, '\\') > 0) ? $v : $flux->real_escape_string($v);
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function dbMatch($field, $value, $mode){

		/*
			$fields     = array_map('trim', explode(',', $field));
			$values     = array_map('trim', explode(',', $value));

			foreach($values as $val){
				$values_quoted[] = (preg_match("#^[0-9]{1,}$#",$val)) ? $val : "'".$val."'";
			}
			if(!is_array($values_quoted)) $values_quoted = array();
		*/

		switch($mode){
			case 'EG' : $return[] = " ($field = '$value') ";		break; // egal
			case 'NE' : $return[] = " ($field != '$value') ";		break; // not egal
			case 'BW' : $return[] = " ($field LIKE '$value%') ";	break; // begin with
			case 'EW' : $return[] = " ($field LIKE '%$value') ";	break; // end with
			case 'CT' : $return[] = " ($field LIKE '%$value%') ";	break; // contains

			case 'MT' : $return[] = " ($field > '$value') ";		break; // more than
			case 'LT' : $return[] = " ($field < '$value') ";		break; // less than
			case 'ME' : $return[] = " ($field >= '$value') ";		break; // more or egal
			case 'LE' : $return[] = " ($field <= '$value') ";		break; // less or egal
			/*
					case 'BT' : $q = &$values_quoted;   list($values_quoted[0], $values_quoted[1]) = ($q[0] < $q[1]) ? array($q[0], $q[1]) : array($q[1], $q[0]);
								$return[] = " ($field    BETWEEN $values_quoted[0] AND $values_quoted[1]) ";	break; // between
					case 'NB':  $q = &$values_quoted;  list($values_quoted[0], $values_quoted[1]) = ($q[0] < $q[1]) ? array($q[0], $q[1]) : array($q[1], $q[0]);
								$return[] = " ($field NOT BETWEEN $values_quoted[0] AND $values_quoted[1]) ";	break; // not between
			*/
			case 'IN' : $return[] = " ($field    IN (".implode(', ', $values_quoted).")) ";	break; // in
			case 'NI' : $return[] = " ($field NOT IN (".implode(', ', $values_quoted).")) ";	break; // not in

			case 'RE' : $return[] = " ($field REGEXP BINARY '$value') ";	break; // regular expression

			default   : $return[] = " ($field LIKE '%$value%') "; 			break; // like
		}

		return implode($return, $insert);
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
// Retourne la nieme table de l'array sql_field pour simplifier les requetes
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function dbTables($n=NULL, $newDB=NULL){
		$tables = ($newDB === NULL) ? array_keys($this->sql_field) : array_keys($newDB);
		return (is_numeric($n)) ? $tables[$n] : implode(', ', $tables);
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function dbInsert($def, $o=array()){

		$tables	= array_keys($def);
		$table 	= $tables[0];
		$fields	= $def[$table];

		if($o['ignore']) $ignore = 'IGNORE';

		if(sizeof($fields) > 0){
			foreach($fields as $field => $data){
				if($data['use'] !== false){
					if($data['null'] && $data['value'] == NULL) $data['function'] 	= 'NULL';
					if($data['zero'] && $data['value'] == NULL) $data['value'] 		= '0';

					$f[] = '`'.$field.'`';
					$v[] = ($data['function'] == NULL) ? "'".$this->dbEscape($data['value'])."'" : $data['function'];
				}
			}
			return "INSERT ".$ignore." INTO `".$table."` (".implode(', ', $f).") VALUES (".implode(', ', $v).")";
		}

		return false;
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function dbUpdate($def){

		$tables	= array_keys($def);
		$table 	= $tables[0];
		$fields	= $def[$table];

		if(sizeof($fields) > 0){
			foreach($fields as $field => $data){
				if($data['use'] !== false){
					if($data['null'] && $data['value'] == NULL) $data['function'] 	= 'NULL';
					if($data['zero'] && $data['value'] == NULL) $data['value'] 		= '0';

					$v	 = ($data['function'] == NULL) ? "'".$this->dbEscape($data['value'])."'" : $data['function'];
					$f[] = '`'.$field.'`='.$v;
				}
			}

			return "UPDATE `".$table."` SET ".implode(', ', $f);
		}

		return false;
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function dbAssoSet($table, $a, $b, $id, $values, $type='', $id_profile='', $profileKey='', $child=''){

		$debug = false;
		#	if($table == 'k_contentsocialforum') $debug = true; // Human action

		if($debug) $this->pre(func_get_args());

		if($id_profile != NULL) $profile = $this->userProfile($id_profile);

		if($type == 'PROFILE' && $profileKey != '' && $profile[$profileKey] != ''){
			$this->mysql->query("DELETE FROM ".$table." WHERE ".$a."='".$id."' AND ".$b." IN(".$profile[$profileKey].")");
		}else
			if($type == 'UNIQUE'){
				$this->mysql->query("DELETE FROM ".$table." WHERE ".$a."='".$id."' AND ".$b."='".$values."'");
			}else
				if($type == 'ALL'){
					$this->mysql->query("DELETE FROM ".$table." WHERE ".$a."='".$id."'");
				}

		#	if($debug) die($this->pre($profile));
		if($debug) $this->pre($this->db_query, $this->db_error);

		if(is_array($values) && sizeof($values) > 0){

			foreach($values as $value){
				if($child == NULL){
					$added[] = '('.$id.','.$value.')';
				}else{
					$added[] = '('.$id.','.$value.',1)';
				}
			}

			if($child == ''){
				$this->mysql->query("INSERT IGNORE INTO ".$table." (".$a.", ".$b.") VALUES ".implode(',', $added));
			}else{
				$this->mysql->query("INSERT IGNORE INTO ".$table." (".$a.", ".$b.", is_selected) VALUES ".implode(',', $added));
			}

			if($debug) $this->pre('A', $this->db_query, $this->db_error);

			if($child != ''){
				unset($added);
				foreach($child as $e){
					if($e != NULL) $added[] = '('.$id.','.$e.',0)';
				}
				if(sizeof($added) > 0){
					$this->mysql->query("INSERT IGNORE INTO ".$table." (".$a.", ".$b.", is_selected) VALUES ".implode(',', $added));
					if($debug) $this->pre('B', $this->db_query, $this->db_error);
				}
			}
		}

		if($debug) die('----');
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function dbDump($opt=array()){

		$file = USER.'/config/config.php';
		if(!file_exists($file)) return false;

		include($file);
		if(!isset($config)) return false;

		$conf = $config['mysql'] ?: $config['db'];

		if(!file_exists($conf['dump'])) mkdir($conf['dump'], 0755, true);

		$dst  = $conf['dump'].'/'. $opt['file'] ?: 'export-'.time().'.sql';
		$bin  = $config['mysqldump'] ?: 'mysqldump';

		$cmd  = sprintf($bin.' --host=%s --user=%s --password=%s --comments=0 %s > %s',
			$conf['host'], $conf['login'], $conf['password'], $conf['database'], $dst);

		system($cmd, $r);

		return ($r == 0) ? true : false;
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function dbKey($array, $key, $isInt=false){

		if(sizeof($array) == 0) return array();

		foreach($array as $a){
			$tmp[] = ($isInt) ? intval($a[$key]) : $a[$key];
		}

		return is_array($tmp) ? $tmp : array();

	}

}