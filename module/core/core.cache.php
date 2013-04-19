<?php

class coreCache{

	private $prefix, $memcache, $log = false;
	private static $core;
	private static $_instance = null;

// SINGLETON ///////////////////////////////////////////////////////////////////////////////////////////////////////////

	private function __construct() {
		$this->memecacheInit();
	}

	public  static function getInstance($core) {
		if(is_null(self::$_instance)) self::$_instance = new coreCache();
		self::$core = $core;
		return self::$_instance;
	}

// MEMCACHE ////////////////////////////////////////////////////////////////////////////////////////////////////////////

	private function memecacheInit(){

		if (file_exists(USER . '/config/config.php')) {
			$config = array();
			include(USER . '/config/config.php');

			if(is_array($config['memcache'])){
				$this->prefix   = $config['memcache']['prefix'];
				$this->log      = $config['memcache']['log'];
				$this->memcache = new Memcache();

				foreach (explode(',', $config['memcache']['server']) as $s) {
					$this->memcache->addServer($s, $config['memcache']['port']);
				}

				$this->memcache->setCompressThreshold(20000, 0.2);
			}
		}
	}

	public  function memcacheGet($key){
		if(!isset($this->memcache)) return false;

		$r = $this->memcache->get($this->prefix.$key);
		$this->memcacheLog('GET', $key, $r);

		return $r;
	}

	public  function memcacheSet($key, $value, $replace=true, $ttl=20){
		if(!isset($this->memcache)) return false;

		if($replace && $this->memcacheGet($key) !== false){
			$r = $this->memcache->replace($this->prefix.$key, $value, MEMCACHE_COMPRESSED, $ttl);
			$this->memcacheLog('REP', $key, $r);

			return $r;
		}

		$r = $this->memcache->add($this->prefix.$key, $value, false, $ttl);
		$this->memcacheLog('ADD', $key, $r);

		return $r;
	}

	public  function memcacheDelete($key){
		if(!isset($this->memcache)) return false;

		$r = $this->memcache->delete($this->prefix.$key);
		$this->memcacheLog('DEL', $key, $r);

		return $r;
	}

	public  function memcacheLog($verb, $key, $result){
		if(!isset($this->memcache) OR !$this->log) return false;

		$raw = date("Y-m-d H:i:s")." ".$this->prefix.$key." ".$verb." ".(($result === false) ? 'NO' : 'OK')."\n";
		file_put_contents(DBLOG.'/memcache/M.'.date("Y-m-d-H").'h.log', $raw, FILE_APPEND);

		return true;
	}

// SQLCACHE ////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public  function sqlcacheGet($key){

		if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep='sqlcacheGet('.$key.')');

		self::$core->dbQuery("DELETE FROM k_cache WHERE cacheTTL < ".time());
		$r = self::$core->dbOne("SELECT UNCOMPRESS(cacheValue) AS back FROM k_cache WHERE cacheKey='".addslashes($key)."'");
		$r = isset($r['back']) ? unserialize($r['back']) : false;

		if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep);

		return $r;
	}

	public  function sqlcacheSet($key, $value, $ttl=60, $rem=false){

		$deb = false;
		$ttl = ($ttl === 0) ? (time() + 60*60*24*30) : (time() + intval($ttl));
		$v   = addslashes(serialize($value));
		$r	 = ($rem) ? 1 : 0;

		self::$core->dbQuery("DELETE FROM k_cache WHERE cacheTTL < ".time());		#." OR cacheKey='".$key."'");
		if($deb) self::$core->pre(self::$core->db_query, self::$core->db_error);

		self::$core->dbQuery("INSERT IGNORE INTO k_cache (cacheKey, cacheTTL, cacheValue, cacheFlagRemovable) VALUES ('".$key."', ".$ttl.", COMPRESS('".$v."'), ".$r.")");
		if($deb) self::$core->pre(self::$core->db_query, self::$core->db_error);

		return true;
	}

	public  function sqlcacheClean(){
		self::$core->dbQuery("DELETE FROM k_cache WHERE cacheFlagRemovable=1");
		return true;
	}

	public  function sqlcacheDelete($key){
		self::$core->dbQuery("DELETE FROM k_cache WHERE cacheTTL < ".time()." OR cacheKey='".$key."'");
		return true;
	}

}