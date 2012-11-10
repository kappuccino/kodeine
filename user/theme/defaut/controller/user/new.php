<?php

	if(intval($this->user['id_user']) > 0){
		header("Location: my");
		exit();
	}

	if($_POST['insert']){
		$do = true;

		$def['k_user'] = array(
			'id_group'		=> array('value' 	=> $_POST['id_group'], 		'integer'	=> true),
			'userPasswd'	=> array('value'	=> $_POST['userPasswd'], 	'function'	=> "MD5('".$_POST['userPasswd']."')", 'check' => '([A-Za-z0-9]){4,16}'),
			'userMail'		=> array('value' 	=> $_POST['userMail'], 		'email'		=> true)
		);

		if(!$this->formValidation($def)){
			$do = false;
			$FORM_VALIDATION_FAILED = true;
		}

		if(!$this->apiLoad('field')->fieldValidation($_POST['field'])){
			$do = false;
			$FIELD_VALIDATION_FAILED = true;
		}	

		$exists	= $this->dbOne("SELECT 1 FROM k_user WHERE userMail='".$_POST['userMail']."'");
		if($do && $exists[1]){	
			$do = false;
			$USER_ALREADY_EXIST = true;
			$this->formErrorSet('USER_ALREADY_EXIST', true);
		}

		if($do){			
			$job = $this->apiLoad('user')->userSet(array(
				'id_user'		=> NULL, // Force la creation
				'debug'			=> false,
				'def'			=> $def,
				'field'			=> $_POST['field']
			));
	
			if($job){
				$USER_INSERTED 	= true;

				if($_POST['autologin']){
					$this->userLogin($_POST['userMail'], $_POST['userPasswd']);
					if($this->user['id_user'] != '') header("Location: my.html");
					exit();
				}
			}else{
				$ERROR_INSERT	= true;
			}
		}else{
			$ERROR_INSERT	= true;
		}
	}
?>