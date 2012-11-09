<?php
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Last release		2011.05.11
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

class coreLog extends coreApp {


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function logAdd($data){

	foreach($data as $k => $v){
		$def['k_log'][$k] = array('value' => $v);
	}

	$this->dbQuery($this->dbInsert($def));
	#$this->pre($this->db_query, $this->db_error);
}


} ?>