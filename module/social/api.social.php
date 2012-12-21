<?php

class social extends coreApp {

function __clone(){}
function social(){}


function socialUserCacheClean($id_user){
	$this->cache->sqlcacheDelete('USER:'.$id_user);
}



} ?>