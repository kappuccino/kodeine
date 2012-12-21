<?php
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Last release		2010.10.18
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

class sms extends coreApp {

function __clone(){}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function sms(){
//	$this->coreApp();
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function smsSend($opt=array()){

	if($opt['receiver'] == '') return false;

	$context = stream_context_create(
		array( 
			'http' => array( 
			'method'  => 'POST', 
			'content' => http_build_query($opt), 
		)
	));

	$back = file_get_contents('http://www.sms-web20.com/gateway/send.php', false, $context); 
	
#	$this->pre($back);
	
#	die();
	
	return true;
}

} ?>