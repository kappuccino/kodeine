<?php

class clientFtp extends coreApp {

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	Ouvre une connection sur le serveur distant 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function __construct(){
	$this->pref = $this->configGet('clientftp');
	$this->rest = new restFTP($this->pref['login'], $this->pref['password']);
	$this->conf	= $this->configCloudGet();
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function configCloudCheck(){
	$r = $this->configCloudGet();
	return $r['success'];
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function configCloudGet(){

	$q = array(
		'action' 	=> 'configCloudGet'
	);

	$v = $this->rest->request('/ftp.php', 'POST', $q);
	return $v;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Retourne la liste des comptes
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function accountGet($opt){

	$q = array(
		'action'	=> 'accountGet',
		'id_user'	=> $this->conf['id_user'],
		'opt'		=> $opt
	);

	$r = $this->rest->request('/ftp.php', 'POST', $q);

	return $r;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Ajoute ou Modifi un compte client
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function accountSet($opt){

	$q = array(
		'action' 	=> 'accountSet',
		'id_user'	=> $this->conf['id_user'],
		'opt' 		=> $opt
	);
	
	$r = $this->rest->request('/ftp.php', 'POST', $q);

	return $r;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Demande la suppression d'un compte
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function accountRemove($opt){

	$q = array(
		'action'	=> 'accountRemove',
		'id_user'	=> $this->conf['id_user'],
		'opt'		=> $opt
	);

	$r = $this->rest->request('/ftp.php', 'POST', $q);

	return $r;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function logGet($opt){

	$q = array(
		'action'	=> 'logGet',
		'id_user'	=> $this->conf['id_user'],
		'opt'		=> $opt
	);

	$r = $this->rest->request('/ftp.php', 'POST', $q);

	return $r;
}


}























































/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
class restFTP{

const RESTVERB_POST            = 'POST';
const RESTVERB_GET             = 'GET';
const ENCODING_MULTIPART       = 'multipart/form-data';
const ENCODING_FORM            = 'application/x-www-form-urlencoded';
const ENCODING_XML             = 'application/xml';
const ENCODING_JSON            = 'application/json';
const ENCODING_DEFAULT         = self::ENCODING_JSON;

protected $_credentials			= '';
protected $_host				= 'ftp.cloudapp.me';
protected $_connectionSecure	= false;
protected $_timeout				= 30;
protected $_persistent			= true;
protected $_curlHandle;

public function setPersistent($persistent){
    $this->_persistent = $persistent;
    return true;
}

public function isPersistent(){
    return $this->_persistent ? true : false;
}
 

/**
 * Constructor
 *
 * @param $userEmail user email that is used for login to Solve360
 * @param $userApiToken api token, may be seen in Workspace -> My Account -> API Token
 * @param $host host to connect to (if different than secure.solve360.com)
 * @param $connectionSecure is the connection secure (true by default)
 * @return Solve360Service
 */
public function __construct($userEmail, $userApiToken, $host=null, $connectionSecure=false){
	$this->setCredentials($userEmail, $userApiToken);
	if ($host !== null) {
		$this->setHost($host);
    }
	$this->setConnectionSecure($connectionSecure);
}

function __destruct(){
	$this->_curlReset();
}

/**
 * Sets the credentials authentication string 
 *
 * @param $userEmail
 * @param $userApiToken
 * @return boolean
 */
public function setCredentials($userEmail, $userApiToken){
	$this->_credentials = $userEmail . ':' . $userApiToken;
	$this->_curlReset();
	return true;
}

/**
 * Gets the credential base64 encoded string
 *
 * @return string
 */
public function getCredentials(){
	return $this->_credentials;
}

/**
 * Sets if the connection is secure or not
 *
 * @param $connectionSecure true of false
 * @return boolean
 */
public function setConnectionSecure($connectionSecure = true){
	$this->_connectionSecure = $connectionSecure ? true : false;
	$this->_curlReset();
    return true;
}

/**
 * Sets the host
 *
 * @param $host
 * @return boolean
 */
public function setHost($host){
	$this->_host = $host;
	$this->_curlReset();
	return true;
}

/**
 * Returns the host
 *
 * @return string
 */
public function getHost(){
	return $this->_host;
}

/**
 * Return wether the connection is secure or it is not
 *
 * @return boolean
 */
public function isConnectionSecure(){
	return $this->_connectionSecure ? true : false;
}

/**
 * Returns the timeout
 *
 * @return int
 */
public function getTimeout(){
	return $this->_timeout;
}

/**
 * Sets the timeout
 *
 * @param $timeout
 * @return boolean
 */
public function setTimeout($timeout){
	$this->_timeout = $timeout;
	$this->_curlReset();
	return true;
}

public function _curlReset(){
	if($this->_curlHandle != null){
		curl_close($this->_curlHandle);
		$this->_curlHandle = null;
    }
}

public function _curlInit(){
    if($this->_curlHandle == null) $this->_curlHandle = curl_init();
}

public function _curlSetDefaults(){
}

/**
 * Request to the Solve360 API server
 *
 * @param $uri URI of the resource
 * @param $restVerb REST verb to use (POST, GET, PUT, DELETE)
 * @param $data Data that will be converted to xml format and send to the server
 * @return SimpleXMLElement
 */
/**
 * @param $uri
 * @param $restVerb
 * @param $data
 * @return unknown_type
 */
public function request($uri, $restVerb, $data = array(), $debug=false){	#$encoding=self::ENCODING_DEFAULT){

	$this->_curlInit();

	$url = ($this->isConnectionSecure() == true)
		? 'https://' . $this->getHost() . $uri
        : 'http://'  . $this->getHost() . $uri;

	#if($debug) coreApp::pre($url, $data);

    curl_setopt($this->_curlHandle, CURLOPT_URL, $url);

    curl_setopt_array($this->_curlHandle, array(
        CURLOPT_HEADER 			=> false,
        CURLOPT_VERBOSE 		=> true,
        CURLOPT_RETURNTRANSFER 	=> true,
        CURLOPT_FOLLOWLOCATION 	=> true,
        CURLOPT_USERAGENT      	=> "Mozilla/4.0 (compatible;)",
        CURLOPT_HTTPAUTH        => CURLAUTH_BASIC,
        CURLOPT_USERPWD         => $this->getCredentials()
    ));

    if(is_array($data)){
    	curl_setopt($this->_curlHandle, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
    }else{
 		curl_setopt($this->_curlHandle, CURLOPT_POSTFIELDS, $data);
   }

    $headers = array();

   /* if($encoding == self::ENCODING_XML){
    	$headers[] = 'Content-Type: application/xml';
    	$headers[] = 'Accept: application/xml';
  	}else
    if($encoding == self::ENCODING_JSON){
    	$headers[] = 'Content-Type: application/json';
		$headers[] = 'Accept: application/json';
    }*/

    curl_setopt($this->_curlHandle, CURLOPT_HTTPHEADER, 	$headers);
    curl_setopt($this->_curlHandle, CURLOPT_CUSTOMREQUEST, 	$restVerb);

    $result = curl_exec($this->_curlHandle);

	/*if($debug){
		$infos = curl_getinfo($this->_curlHandle);
		coreApp::pre($infos);
	}*/

    if($result === false) throw new Exception('System error: ' . curl_error($this->_curlHandle));

    if(!$this->isPersistent()) $this->_curlReset();

	if($debug) print_r($result);

	return json_decode($result, true);
}
}

?>