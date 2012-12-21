<?php

class coreMongoDb{

static $db = NULL;

static function getMongoCon(){
	if (self::$db === null){
		try {
			$host  = '127.0.0.1';
			$port  = 277;
			$mongo = new Mongo('mongodb://'.$host.':'.$port);

		} catch (MongoConnectionException $e) {
			die('Failed to connect to MongoDB '.$e->getMessage());
		}
		self::$db = $mongo;
	}else{
		return self::$db;
	}
}

} ?>