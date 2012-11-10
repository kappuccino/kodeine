<?php

	if(intval($this->user['id_user']) == 0){
		header("Location: login");
		exit();
	}

	$myCmd = $this->apiLoad('business')->businessCartGet(array(
		'id_user'	=> $this->user['id_user'],
		'is_cmd'	=> 1,
		'debug'		=> false
	));

?>