<?php

namespace Kodeine;

class appMongo extends appModule{

	private static $_instance = null;
	protected $db         = '';
	protected $collection = '';

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function __construct(){

		$config = array();
		require USER . '/config/config.php';
		$config = $config['mongodb'];

		try {
			$host       = $config['host'];
			$timeout    = ($config['timeout'] > 0) ? $config['timeout'] : 5000;
			$port       = ($config['port'] > 0) ? $config['port'] : 27017;
			$sdn        = 'mongodb://' . $host . ':' . $port . '?connectTimeoutMS=' . $timeout;

			$mongo      = new \Mongo($sdn);
			$database   = $mongo->selectDB($config['database']);

			$salted     = $config['login'].':mongo:'.$config['password'];
			$hash       = md5($salted);
			$nonce      = $database->command(array("getnonce" => 1));
			$saltedHash = md5($nonce["nonce"].$config['login'].$hash);

			$database->command(array(
				"authenticate" => 1,
				"user"         => $config['login'],
				"nonce"        => $nonce["nonce"],
				"key"          => $saltedHash
			));

		} catch (MongoConnectionException $e) {
			throw new Exception('Failed to connect to MongoDB '.$e->getMessage());
		}

		$this->mongo = $mongo;

		$this->selectDB($config['database']);

		return $this;
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public static function getInstance(){
		if(is_null(self::$_instance)) self::$_instance = new self();
		return self::$_instance;
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function selectDB($db=NULL){
		$this->db = $this->mongo->selectDB($db);
		return $this->db;
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function selectCollection($col=NULL){
		$this->collection = $this->db->selectCollection($col);
		return $this->collection;
	}


}