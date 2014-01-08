<?php

class social extends coreApp {


function socialUserCacheClean($id_user){

	$this->cache->sqlcacheDelete('USER:'.$id_user);
}



}