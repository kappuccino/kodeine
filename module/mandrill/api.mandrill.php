<?php

class mandrill extends coreApp {

private $rest;
private $key  = 'b0dfc678-266f-4707-a2db-8f66f9c80ece';

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function mandrill(){

	$this->rest = new coreRest('', '', 'mandrillapp.com', true);
	$this->rest->setMode('curl');

}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
private function send($url, $data){

	$a = $this->rest->request(array(
		'debug' => false,
		'verb'  => 'POST',
		'uri'   => '/api/1.0'.$url,
		'data'  => $data
	));

	$body = json_decode($a['body'], true);

	echo date("Y-m-d H:i:s")."\n";
	print_r($body);

}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function test(){

	$data = array(
	    "key"       => $this->key,
	    "async"     => true,

		"template_name"=>  "mytemplate",
	    "template_content"=> array(
	        array("name"=> "test", "content" => "youpi")
	    ),

	    "message"   => array(
	        "subject"    => "example subject ".time(),
	    #   "html"       => "Bonjour ",
	        "from_email" => "bm@kappuccino.org",
	        "from_name"  => "Benjamin Mosnier",
	        "to"         => array(
				array("email" => "bm@kappuccino.org", "name" => "Moi Meme")
	        ),
	        "track_opens"         => true,
	        "track_clicks"        => true,
	        "auto_text"           => true,
	        "url_strip_qs"        => true,
	        "preserve_recipients" => true,
	#       "bcc_address"         => "bm@kappuccino.org",
	        "merge"               => true,
	        "global_merge_vars"   => array(
	            array("name" => "fname", "content" => "Benjamin")
	        )
	        /*,
	        "merge_vars" => array(
	            array(
	                "rcpt" => "example rcpt",
	                "vars" => array(
	                    array(
	                        "name" => "example name",
	                        "content" => "example content"
	                    )
	                )
	            )
	        )*/
	    ),
	);

	$this->send('/messages/send-template.json', $data);
	
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function render(){

	$data = array(
		"key"               => $this->key,
		"template_name"     => "mytemplate",
		"template_content"  => array(
			array(
				"name"=> "test", "content" => "youpi"
			)
		),
		"merge_vars" => array(
			array(
				"name"=> "fname", "content" => "Benjamin"
			)
		)
	);

	$this->send('/templates/render.json', $data);

}

} ?>