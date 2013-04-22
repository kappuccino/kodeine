<?php

	$config = array();
	require(USER.'/config/config.php');
	$conf = $config['externalAuth'];

	# Inspiration
	# http://blog.studiovitamine.com/actualite,107,fr/php-cryptage-php-d-une-chaine-de-caractere-et-decryptage,304,fr.html?id=164
	

	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
	function crypter($maCleDeCryptage, $maChaineACrypter){
		$maCleDeCryptage = md5($maCleDeCryptage);
		$letter = -1;
		$newstr = '';
		$strlen = strlen($maChaineACrypter);
		for($i = 0; $i < $strlen; $i++ ){
			$letter++;
			if ( $letter > 31 ){
				$letter = 0;
			}
			$neword = ord($maChaineACrypter{$i}) + ord($maCleDeCryptage{$letter});
			if ( $neword > 255 ){
				$neword -= 256;
			}
			$newstr .= chr($neword);
		}
		return base64_encode($newstr);
	}

	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */


	# Clean
	#
	$app->dbQuery("DELETE FROM k_userauth WHERE authExpire < ".time());


	# On demander le authToken pour se connecter ensuite
	#
	if(intval($_REQUEST['id_user']) > 0 && $_REQUEST['userToken'] != '' && $_REQUEST['siteToken'] == $conf['token']){

		$me = $app->dbOne("SELECT * FROM k_user WHERE userToken='".$_REQUEST['userToken']."' AND id_user=".$_REQUEST['id_user']);
		if(intval($me['id_user']) > 0){

			$key = strtoupper(uniqid().uniqid());

			$app->dbQuery("INSERT INTO k_userauth (id_user, authToken, authExpire) VALUES (".$_REQUEST['id_user'].", '".$key."', ".(time()+120).")");

			$out = array(
				'success'	=> true,
				'token' 	=> crypter($conf['key'], $key)
			);

		}else{
			$out = array('success' => false);
		}

	}else


	# On essaye de loger la personne
	#
	if($_GET['authToken']){
		$me = $app->dbOne("SELECT * FROM k_userauth WHERE authToken='".$_GET['authToken']."'");

		if(intval($me['id_user']) > 0){
			$_SESSION['id_user'] = $me['id_user'];
			$app->go('/');
		}else{
			$out = array('success' => false, 'error' => 'authToken');
		}
	}

	
	# Si non c'est une erreur
	#
	else{
		$out = array('success' => false);
	}


	# Sortie
	#
	echo json_encode($out);
