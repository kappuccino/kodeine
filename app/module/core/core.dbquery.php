<?php

class coreDbQuery{

private static $query;

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
 - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */
function __construct(){
	return $this;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
 - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */
function select($field){
	$this->query['select'] = $field;
	return $this;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
 - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */
function from($table){
	$this->query['from'] = $table;
	return $this;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
- + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */
function get(){



	print_r($this->query);

}



} ?>