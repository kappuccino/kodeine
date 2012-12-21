<?php

class coreCache extends coreApp{

function __construct(){
	// Dummy autoload if memcache config allow it.
	if(!defined('MEMCACHE_READY') && defined('MEMCACHE_PREFIX') && defined('MEMCACHE_SERVER')){
		$this->memcacheInit();
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	MEMCACHE
	--
	Memcache servers are used to store data cache
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function memcacheInit(){
	if(!defined('MEMCACHE_PREFIX') && !defined('MEMCACHE_SERVER')) return false;
	
	$this->memcache = new Memcache();

	foreach(explode(',', MEMCACHE_SERVER) as $s){
		$this->memcache->addServer($s, 11211);
	}

	$this->memcache->setCompressThreshold(20000, 0.2);

	define('MEMCACHE_READY', true);
}

public function memcacheGet($key){
	if(!defined('MEMCACHE_PREFIX') && !defined('MEMCACHE_SERVER')) return false;

	$r = $this->memcache->get(MEMCACHE_PREFIX.$key);
	$this->memcacheLog('GET', $key, $r);

	return $r;
}

public function memcacheSet($key, $value, $replace=true, $ttl=20){
	if(!defined('MEMCACHE_PREFIX') && !defined('MEMCACHE_SERVER')) return false;

	if($replace && $this->memcacheGet($key) !== false){
		$r = $this->memcache->replace(MEMCACHE_PREFIX.$key, $value, MEMCACHE_COMPRESSED, $ttl);
		$this->memcacheLog('REP', $key, $r);

		return $r;
	}

	$r = $this->memcache->add(MEMCACHE_PREFIX.$key, $value, false, $ttl);
	$this->memcacheLog('ADD', $key, $r);

	return $r;
}

public function memcacheDelete($key){
	if(!defined('MEMCACHE_PREFIX') && !defined('MEMCACHE_SERVER')) return false;

	$r = $this->memcache->delete(MEMCACHE_PREFIX.$key);
	$this->memcacheLog('DEL', $key, $r);

	return $r;
}

public function memcacheLog($verb, $key, $result){
	if(!MEMCACHE_LOG) return false;

	$raw = date("Y-m-d H:i:s")." ".MEMCACHE_PREFIX.$key." ".$verb." ".(($result === false) ? 'NO' : 'OK')."\n";
	file_put_contents(DBLOG.'/memcache/M.'.date("Y-m-d-H").'h.log', $raw, FILE_APPEND);

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	SQLCACHE
	--
	Local MySQL database is used to store data cache
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function sqlcacheGet($key){

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep='sqlcacheGet('.$key.')');

	$this->dbQuery("DELETE FROM k_cache WHERE cacheTTL < ".time());
	$r = $this->dbOne("SELECT UNCOMPRESS(cacheValue) AS back FROM k_cache WHERE cacheKey='".addslashes($key)."'");
	$r = isset($r['back']) ? unserialize($r['back']) : false;

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep);

	return $r;
}

public function sqlcacheSet($key, $value, $ttl=60, $rem=false){

	$debug	= false;
	$ttl 	= ($ttl === 0) ? (time() + 60*60*24*30) : (time() + intval($ttl));
	$v   	= addslashes(serialize($value));
	$r	 	= ($rem) ? 1 : 0;

	$this->dbQuery("DELETE FROM k_cache WHERE cacheTTL < ".time());		#." OR cacheKey='".$key."'");
	if($debug) $this->pre($this->db_query, $this->db_error);
	
	$this->dbQuery("INSERT IGNORE INTO k_cache (cacheKey, cacheTTL, cacheValue, cacheFlagRemovable) VALUES ('".$key."', ".$ttl.", COMPRESS('".$v."'), ".$r.")");
	if($debug) $this->pre($this->db_query, $this->db_error);

	return true;
}

public function sqlcacheClean(){
	$this->dbQuery("DELETE FROM k_cache WHERE cacheFlagRemovable=1");
}

public function sqlcacheDelete($key){
	$this->dbQuery("DELETE FROM k_cache WHERE cacheTTL < ".time()." OR cacheKey='".$key."'");
	return true;
}


} ?>