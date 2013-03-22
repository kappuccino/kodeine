<?php

class coreMongo extends coreApp{

	static      $mongoDB    = NULL;
	protected   $host       = '';
	protected   $collection = '';
	protected   $db         = '';
	private     $login      = '';
	private     $passwd     = '';

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function __construct(){

		$config = array();
		require USER . '/config/config.php';

		$this->login    = $config['mongodb']['login'];
		$this->passwd   = $config['mongodb']['password'];
		$this->host     = $config['mongodb']['host'];
		$this->db       = $config['mongodb']['database'];
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	static  function mongoCon(){

		if (self::$mongoDB === NULL){

			$config = array();
			require USER . '/config/config.php';

			try {
				$host       = $config['mongodb']['host'];
				$timeout    = ($config['mongodb']['timeout'] > 0) ? $config['mongodb']['timeout'] : 5000;
				$port       = ($config['mongodb']['port']    > 0) ? $config['mongodb']['port']    : 27017;

				$mongo      = new Mongo('mongodb://'.$host.':'.$port.'?connectTimeoutMS='.$timeout);

			} catch (MongoConnectionException $e) {
				die('Failed to connect to MongoDB '.$e->getMessage());
			}

			self::$mongoDB = $mongo;
		}

		return self::$mongoDB;
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function mongoAuth(){

		$mon = $this->mongoCon();
		$db  = $mon->selectDB($this->db);

		$salted     = $this->login.':mongo:'.$this->passwd;
		$hash       = md5($salted);

		$nonce      = $db->command(array("getnonce" => 1));
		$saltedHash = md5($nonce["nonce"].$this->login.$hash);

		$db->command(array(
			"authenticate" => 1,
			"user"         => $this->login,
			"nonce"        => $nonce["nonce"],
			"key"          => $saltedHash
		));

		return $mon;
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function db($db=NULL){
		if($db == NULL) return $this->db;
		$this->db = $db;
		return $this;
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function collection($col=NULL){
		if($col == NULL) return $this->collection;
		$this->collection = $col;
		return $this;
	}
}