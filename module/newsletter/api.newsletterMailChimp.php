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
	//echo $submit_url;
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

public function listInterestGroupingAdd($opt = array()) {
    return $this->send($opt, 'listInterestGroupingAdd');
}

    /* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
    + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

    public function listStaticSegments($opt = array()) {
        return $this->send($opt, 'listStaticSegments');
    }
    /* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
    + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

    public function listStaticSegmentAdd($opt = array()) {
        return $this->send($opt, 'listStaticSegmentAdd');
    }

    /* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
    + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

    public function listBatchSubscribe($opt = array()) {
        return $this->send($opt, 'listBatchSubscribe');
    }
    /* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
    + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

    public function listStaticSegmentMembersAdd ($opt = array()) {
        return $this->send($opt, 'listStaticSegmentMembersAdd ');
    }
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

public function listInterestGroupAdd($opt = array()) {
    return $this->send($opt, 'listInterestGroupAdd');
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

public function campaignCreate($opt = array()) {
	return $this->send($opt, 'campaignCreate');
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

public function campaignSendTest($opt = array()) {
	return $this->send($opt, 'campaignSendTest');
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

public function campaignDelete($opt = array()) {
	return $this->send($opt, 'campaignDelete');
}



/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

public function campaignStats($opt = array()) {
    return $this->send($opt, 'campaignStats');
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

public function listSubscribe($opt = array()) {

    $double_optin		= isset($opt['double_optin']) ? $opt['double_optin'] : true;
    $update_existing	= false;
    $replace_interests	= true;
    $send_welcome		= false;
    $email_type			= 'html';

    /* --- Example ---
    $opt = array(
        'email'		=> $email,
        'merges'	=> array(
                            'GROUPINGS'   => array(
                                array('name'=>'Type contact', 'groups' => 'contacts NL')
                            )
                        )
    );
     --- */

    $data = array(
        'id' 				=> $opt['id_list'],
        'email_address'		=> $opt['email'],
        'merge_vars' 		=> $opt['merges'],
        'double_optin' 		=> $double_optin,
        'update_existing' 	=> $update_existing,
        'replace_interests' => $replace_interests,
        'send_welcome' 		=> $send_welcome,
        'email_type' 		=> $email_type
    );

    return $this->send($data, 'listSubscribe');
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
EXEMPLE
	$opt = array(
		'email'	=> $email,
		'list'  => 'abcdef'
	);
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function listUnsubscribe($opt = array()) {

	$data = array(
		'id'            => $opt['id_list'],
		'email_address' => $opt['email']
	);

	return $this->send($data, 'listUnsubscribe');
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
EXEMPLE
	$opt = array(
		'email'	=> $email,
		'list'  => 'abcdef'
	);
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function listMemberInfo($opt = array()){

	$data = array(
		'id'            => $opt['id_list'],
		'email_address' => $opt['email']
	);

	return $this->send($data, 'listMemberInfo');
}

}