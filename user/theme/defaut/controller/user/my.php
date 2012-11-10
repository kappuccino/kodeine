<?php

#	$url = parse_url($_SERVER['REQUEST_URI']);
#	if($this->user['id_user'] == NULL && basename($url['path']) == 'my'){

	if(intval($this->user['id_user']) == 0){
		header("Location: new");
		exit();
	}

	if($_POST['update']){
		$do = true;

		$def['k_user'] = array(
			'userDateUpdate' 	=> array('function' => 'NOW()')
		);
		
		# Update Password
		if($_POST['userPasswd'] != ''){
			$def['k_user']['userPasswd'] = array('function' => "MD5('".$_POST['userPasswd']."')");
		}

		# Update Login
		if($_POST['userMail'] != ''){
			$def['k_user']['userMail'] = array('value' => $_POST['userMail'], 'email' => true);
		}

		if(!$this->apiLoad('field')->fieldValidation($_POST['field'])){
			$do = false;
			$FIELD_VALIDATION_FAILED = true;
		}

		if(!$this->formValidation($def)){
			$do = false;
			$FORM_VALIDATION_FAILED = true;
		}

		if($do){			

			$job = $this->apiLoad('user')->userSet(array(
				'id_user'		=> $this->user['id_user'],
				'debug'			=> false,
				'def'			=> $def,
				'field'			=> $_POST['field'],
				'community'		=> $_POST['id_community']
			));

			$this->apiLoad('newsletter')->newsletterSubscribe(array(
				'email'			=> $_POST['userMail'],
				'list'			=> $_POST['id_newsletterlist'],
				'clean'			=> true
			));

			if($job){
				$USER_UPDATED 	= true;
			}else{
				$ERROR_UPDATE 	= true;
			}

		}else{
			$ERROR_UPDATE 	= true;
		}

	}

	
?>