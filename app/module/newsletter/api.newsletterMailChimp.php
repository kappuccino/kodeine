<?php

class newsletterMailChimp extends newsletter {

function __clone(){}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function newsletterMailChimp(){
	
	$pref	= $this->configGet('newsletter');
	
	
	// Test
	/*$this->apikey 	= '8f34650efa3b349a402860743e28c5e9-us5';
	$this->apiUrl 	= 'us5';
	$this->listId	= '4d53045cc0';*/
	
	// Prod
	/*$this->apikey 	= 'f70bac8652b9b3ae80bba06d69e9df5a-us1';
	$this->apiUrl 	= 'us1';
	$this->listId	= '3e8522fbd3'; // Test : 4d53045cc0*/
	
	// Prod
	$this->apikey 	= $pref['mailchimpPass'];
	$this->apiUrl 	= $pref['mailchimpAuth'];
	//$this->listId	= '3e8522fbd3'; // Test : 4d53045cc0
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

function help(){
	echo 'je suis le connecteur de Mail Chimp';
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
private function send($data = array(), $method) {

	$data['apikey'] = $this->apikey;
	
	$payload = json_encode($data);
	
	$submit_url = "http://".$this->apiUrl.".api.mailchimp.com/1.3/?method=".$method;
	 
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $submit_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, urlencode($payload));
	 
	$result = curl_exec($ch);
	curl_close ($ch);
	$data = json_decode($result, true);
	
	return $data;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

public function listGet($opt = array()) {
	return $this->send($opt, 'lists');
}
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

public function listInterestGroupings($opt = array()) {
	return $this->send($opt, 'listInterestGroupings');
}
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

public function campaignCreate($opt = array()) {
	return $this->send($opt, 'campaignCreate');
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */


} ?>