<?php

class coreRest extends coreApp{

const RESTVERB_POST			= 'POST';
const RESTVERB_GET			= 'GET';
const RESTVERB_PUT			= 'PUT';
const RESTVERB_DELETE		= 'DELETE';
const ENCODING_MULTIPART	= 'multipart/form-data';
const ENCODING_FORM			= 'application/x-www-form-urlencoded';
const ENCODING_XML			= 'application/xml';
const ENCODING_JSON			= 'application/json';
const ENCODING_DEFAULT		= self::ENCODING_JSON;

protected $_credentials		= '';
protected $_host			= '';
protected $_port            = 80;
protected $_secure			= false;
protected $_mode			= 'classic';
protected $_persistent		= true;
protected $_handle;

public function __construct($userLogin, $userPassword, $host, $connectionSecure=false){
	$this->setCredentials($userLogin, $userPassword);
	$this->setHost($host);
	$this->setConnectionSecure($connectionSecure);
}

function __destruct(){
	if($this->getMode() == 'curl') $this->_curlReset();
}

public function _curlReset(){
	if($this->_handle != null && get_resource_type($this->_handle) == 'curl'){
		curl_close($this->_handle);
		$this->_handle = NULL;
    }
}

public function _curlInit(){
    if($this->_handle == null) $this->_handle = curl_init();
}

public function setPersistent($persistent){
    $this->_persistent = $persistent;
    return true;
}

public function isPersistent(){
    return $this->_persistent ? true : false;
}

public function setCredentials($userLogin, $userApiToken){
	$this->_credentials = $userLogin.':'.$userApiToken;
	if($this->getMode() == 'curl') $this->_curlReset();
	return true;
}

public function getCredentials(){
	return $this->_credentials;
}

public function setConnectionSecure($secure = true){
	$this->_secure = $secure;
	if($secure) $this->setPort(443);
	if($this->getMode() == 'curl') $this->_curlReset();

    return true;
}

public function setHost($host){
	$this->_host = $host;
	if($this->getMode() == 'curl') $this->_curlReset();
	return true;
}

public function getHost(){
	return $this->_host;
}

public function setPort($port){
	$this->_port = $port;
	return true;
}

public function getPort(){
	return $this->_port;
}

public function setMode($mode){
	$this->_mode = $mode;
	if($mode == 'curl') $this->_curlReset();
	return true;
}

public function getMode(){
	return $this->_mode;
}

public function isSecure(){
	return $this->_secure ? true : false;
}

// Sending HTTP query and receiving, with trivial keep-alive support
function sendREST($fp, $q, $debug = false){
    if ($debug) echo "\nQUERY<<{$q}>>\n";

    fwrite($fp, $q);
    $r = '';
    $check_header = true;
    while (!feof($fp)) {
        $tr = fgets($fp, 256);
        if ($debug) echo "\nRESPONSE<<{$tr}>>"; 
        $r .= $tr;

        if (($check_header)&&(strpos($r, "\r\n\r\n") !== false))
        {
            // if content-length == 0, return query result
            if (strpos($r, 'Content-Length: 0') !== false)
                return $r;
        }

        // Keep-alive responses does not return EOF
        // they end with \r\n0\r\n\r\n string
        if (substr($r, -7) == "\r\n0\r\n\r\n")
            return $r;
    }
    return $r;
}


public function headerToArray($h){

	$lines = is_array($h) ? $h : explode("\n", $h);

	foreach($lines as $line){
		if(strpos($line, " ") >= 1 && strpos($line, ":") === false){
			list($name, $value) = explode(" ", $line, 2);
		}else
		if(strpos($line, ":") >= 1){
			list($name, $value) = explode(": ", $line, 2);
		}

		if($name == 'Content-Type'){
			if(strpos($value, ';')){
				$hs[$name.'_'] = $value;
				$tmp	= explode(';', $value);
				$value	= $tmp[0];
			}
		}else
		if(substr($name, 0, 7) == 'HTTP/1.'){
			$tmp		= explode(' ', $value);
			$hs['HTTP'] = $tmp[0];
		}

		$hs[$name] = $value;
	}

	return is_array($hs) ? $hs : array();
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	REQUEST ...
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function request($opt=array()){

	$uri 	= $opt['uri'];
	$verb	= $opt['verb'];		if(!isset($opt['verb']))	$verb	= 'GET';
	$data	= $opt['data'];		if(!isset($opt['data']))	$data	= array();
	$debug	= $opt['debug'];	if(!isset($opt['debug']))	$debug	= false;
	
	if(isset($opt['mode'])) $this->setMode($opt['mode']);

	$mode	= $this->getMode();
	$scheme = ($this->isSecure() == true) ? 'https' : 'http';
	$url	= $scheme.'://'.$this->getHost().':'.$this->getPort().$uri;

	$opt_	= array_merge($opt, array(
		'url'	=> $url,
		'data'	=> $data,
		'verb'	=> $verb,
		'debug'	=> $debug
	));

	$a = microtime(true);

	if($mode == 'classic'){
		$out = @$this->requestClassic($opt_);
	}else
	if($mode == 'curl'){
		$out = @$this->requestCurl($opt_);
	}else
	if($mode == 'socket'){
		$out = @$this->requestSocket($opt_);
	}

	if($out['contentType'] == 'application/json'){

		$body = json_decode($out['body'], true);

		if(!is_array($body)){
			$body = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $out['body']);
			$body = json_decode($body, true);
		}

		$out['raw']  = $out['body'];
		$out['body'] = $body;
	}

	if(is_array($out)) $out['duration'] = microtime(true) - $a;
	
	return $out;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	... with SOCKET
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
private function requestSocket($opt){

	$url	= $opt['url'];
	$verb	= $opt['verb'];
	$data	= $opt['data'];
	$debug	= $opt['debug'];

	$query[] = "GET ".$opt['uri']." HTTP/1.1";
	$query[] = "Host: ".$this->getHost().':'.$this->getPort();
	$query[] = 'Authorization: Basic '.base64_encode($this->getCredentials());
    $query[] = "Connection: Close\r\n\r\n";
	$query	 = implode("\r\n", $query)."\r\n";

	$fp     = fsockopen($this->getHost(), $this->getPort(), $errno, $errstr, 1);
	$resp   = $this->sendREST($fp, $query, true);
			  fclose($fp);

	list($headers, $body) = explode("\r\n\r\n", $resp);
	$headers = $this->headerToArray($headers);

	return (array(
		'contentType'	=> (($headers['Content-Type'] != '') ? $headers['Content-Type'] : '??'),
		'headers'		=> $headers,
		'body'			=> $body
	));
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	... with CLASSIC
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
private function requestClassic($opt){

	$url	= $opt['url'];
	$verb	= $opt['verb'];
	$data	= $opt['data'];
	$debug	= $opt['debug'];

	$opts	= array(
		'http' => array(
			'header'			=> "Authorization: Basic " . base64_encode($this->getCredentials()),
			'method'			=> $verb,
			'content'			=> http_build_query($data),
			'follow_location'	=> true,
			'user_agent'		=> 'Mozilla/4.0 (compatible;)'
		)
	);

	$context	= stream_context_create($opts);
	$raw		= file_get_contents($url, false, $context);
	$headers	= $this->headerToArray($http_response_header);
	$result		= $raw; //utf8_decode($raw);

	return array(
		'contentType'	=> (($headers['Content-Type'] != '') ? $headers['Content-Type'] : '??'),
		'headers'		=> $headers,
		'body'			=> $result
	);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	... with CURL
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
private function requestCurl($opt){

	$url	= $opt['url'];
	$verb	= $opt['verb'];
	$data	= $opt['data'];
	$debug	= $opt['debug'];

	$this->_curlInit();

	curl_setopt_array($this->_handle, array(
		CURLOPT_URL				=> $url,
		CURLOPT_HEADER 			=> true,
		CURLOPT_VERBOSE 		=> true,
		CURLOPT_RETURNTRANSFER 	=> true,
		CURLOPT_FOLLOWLOCATION 	=> true,
		CURLOPT_USERAGENT      	=> "Mozilla/4.0 (compatible;)",
		CURLOPT_HTTPAUTH        => CURLAUTH_BASIC,
		CURLOPT_USERPWD         => $this->getCredentials(),
		CURLOPT_POSTFIELDS		=> http_build_query($data, '', '&'),
		CURLOPT_HTTPHEADER	    => array('Connection: close'),
		CURLOPT_CUSTOMREQUEST	=> $verb
	));
	
	if(!$this->isPersistent()) $this->_curlReset();

	$result = curl_exec($this->_handle);

	if($result !== false){


		$stats			= curl_getinfo($this->_handle);
    	$contentType	= curl_getinfo($this->_handle, CURLINFO_CONTENT_TYPE);
    	$size			= curl_getinfo($this->_handle, CURLINFO_HEADER_SIZE);

    	$headers		= mb_substr($result, 0, $size);
    	$body			= mb_substr($result,	$size);

		if(!$this->isPersistent()) curl_close($this->_handle);

		return array(
			'contentType'	=> $contentType,
			'headers'		=> $this->headerToArray(trim($headers)),
			'body'			=> $body
		);

	}else{
		throw new Exception('System error: ' . curl_error($this->_handle));
	}
}

}