<?php
	if(!defined('COREINC')) die('@');

	# REGEN BACK ?
	#
	if($_POST['regen'] != ''){

		$app->dbQuery("DELETE FROM k_userlost WHERE lostTTL < ".time());
		$req	= $app->dbOne("SELECT * FROM k_userlost WHERE lostToken = '".$_POST['token']."'");
		$usr	= $app->apiLoad('user')->userGet(array(
			'id_user'	=> $req['id_user']
		));

		if(intval($usr['id_user']) > 0){
			@$app->dbQuery("UPDATE k_user SET userPasswd = MD5('".addslashes($_POST['regen'])."') WHERE id_user = ".$usr['id_user']);

			if($app->db_error == NULL){
				die(json_encode(array('success' => true, 'query' => addslashes($app->db_query))));
			}else{
				die(json_encode(array('success' => false, 'reason' => $app->db_error."\n".$app->db_query)));
			}
		}else{
			die(json_encode(array('success' => false, 'reason' => 'no lost entry')));
		}
	}

	# Format
	#
	if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) === FALSE) die(json_encode(array('success' => false, 'reason' => 'format')));


	# Me ?
	#
	$user = $app->dbOne("SELECT * FROM k_user WHERE userMail='".$_POST['email']."'");
	if($user['userToken'] == '') die(json_encode(array('success' => false, 'reason' => 'user not found')));


	# Regen
	#
	$token	= strtoupper(sha1(md5(uniqid('K', true))));
	$query	= $app->dbInsert(array('k_userlost' => array(
		'id_user'	=> array('value' => $user['id_user']),
		'lostToken'	=> array('value' => $token),
		'lostTTL'	=> array('value' => strtotime("1 hours"))
	)));

	@$app->dbQuery($query);
	if($app->db_error != '') die(json_encode(array('success' => false, 'reason' => $app->db_error."\n".$app->db_query)));


	# Envoyer un mail
	#
	require_once(APP.'/plugin/phpmailer/class.phpmailer.php');
	$mail = new PHPMailer();
    $mail->CharSet = "UTF-8";

	$mail->AddReplyTo("no-reply@".$_SERVER['HTTP_HOST']);
	$mail->SetFrom("no-reply@".$_SERVER['HTTP_HOST']);
	$mail->AddReplyTo("no-reply@".$_SERVER['HTTP_HOST']);
	$mail->AddAddress($_POST['email']);

	$url  = "http://".$_SERVER['HTTP_HOST'];
	$url .= dirname(dirname($_SERVER['REQUEST_URI'])).'/login';
	$url .= "?t=".$token;

	$mail->IsHTML	= false;
	$mail->Subject	= _("[kodeine] Nouveau mot de passe");
	$mail->Body		= sprintf(_("Pour regenerer votre mot de passe, cliquez ici %s"), $url);

	if(!$mail->Send()) die(json_encode(array('success' => false, 'reason' => $mail->ErrorInfo)));


	# OK !!!
	#
	echo json_encode(array('success' => true, 'token' => $token));

?>