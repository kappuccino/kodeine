<?php

	if(intval($this->user['id_user']) == 0){
		header("Location: login");
		exit();
	}

	$myCmd	= $this->apiLoad('business')->businessCartGet(array(
		'is_cmd'	=> 1,
		'id_user'	=> $this->user['id_user'],
		'id_cart'	=> $_GET['id_cmd']
	));
	
?>