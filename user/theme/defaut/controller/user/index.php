<?php

	if(isset($this->user['id_user'])){
		header("Location: my");
	}else{
		header("Location: login");
	}

	exit();

?>