<?php

namespace Kodeine;

class app{

	private static  $_instance  = null;
	public          $me         = null;
	public          $kodeine    = null;

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public static function getInstance() {
		if(is_null(self::$_instance)) self::$_instance = new app();
		return self::$_instance;
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public static function register($name){

	#	echo 'regiter::'.$name.PHP_EOL;

		$app = self::getInstance();

		if($name == 'mysql' && !property_exists($app, $name)){
			$app->$name = appMysql::getInstance();
		}else
		if($name == 'mongo' && !property_exists($app, $name)){
			$app->$name = appMongo::getInstance();
		}else
		if($name == 'app'){
			return self::getInstance();
		}else
		if(!property_exists($app, $name)){
			$n   = 'Kodeine\app'.ucfirst($name);
			$app->$name = new $n();
		}

		return $app->$name;
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	function __get($name){
		$allowed = array('me', 'helper', 'media', 'mongo', 'mysql');
		if(in_array($name, $allowed)) return $this->register($name);
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public function load($api, $args=NULL){

		$file = autoloader::file($api);

		if(file_exists($file)){

			$c = __NAMESPACE__.'\\'.$api;
		//	echo "\n\nload:".$api." => ".$file." (".$c.")\n";
			$new = new $c();

			if(method_exists($new, '__invoke') && is_array($args)){
				call_user_func_array($new, $args);
			}

			return $new;

		}else{
			throw new Exception("Impossible de charger la classe : ".$api);
		}

	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function init(){
		$this->me       = new user();
		$this->kodeine  = new appKodeine();

		return $this;
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function setMe($me=NULL){
		if(is_object($me)){
			$this->me = $me;
		}else{
			$this->me = array();
		}
	}


}