<?php

	# SUBSCRIBE
	# Action effectuee depuis le lien envoyé par mail (DOUBLE OPT IN)
	#
	if(isset($_GET['subscribe'])){
		$mod		= $this->apiLoad('newsletter');
		$SUBSCRIBED = $mod->newsletterSubscribe(array(
			'email'	=> urldecode($_GET['email']),
			'list'  => explode(',', $_GET['id_newsletterlist'])
		));
	}else



	# UNSUBSCRIBE
	# Suppression de l'abonnement depuis le mail (footer du mail)
	#
	if(isset($_GET['unsubscribe'])){
		$this->dbQuery("UPDATE k_newslettermail SET flag='IGNORE' WHERE mail='".$_GET['mail']."'");
	#	$this->pre($this->db_query, $this->db_error);

		$UNSUBSCRIBED = true;
	}else



	# SUBSCRIBE
	# Demande d'abonnement depuis le site
	#
	if($_POST['subscribe']){

		if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) === FALSE){
			$NEED_VALIDE_EMAIL = true;
		}else
		if(sizeof($_POST['id_newsletterlist']) > 0){

			$message = $this->helperReplace(
				file_get_contents(USER.'/mail/newsletter.subscribe.html'),
				array(
					'email'			=> $_POST['email'],
					'subscribeLink'	=> 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'?subscribe&email='.urlencode($_POST['email']).'&id_newsletterlist='.implode(',', $_POST['id_newsletterlist'])
				)
			);

			$conf = $this->configGet('newsletter');
			$send = ($conf['sender'] == NULL) ? 'ml@'.$_SERVER['HTTP_HOST'] : $conf['sender'];

			require_once(KROOT.'/app/plugin/phpmailer/class.phpmailer.php');
			$mail = new PHPMailer();

			$mail->SetFrom($send);
			$mail->AddAddress($_POST['email']);

			$mail->Subject	= "Inscription à notre newsletter";
			$mail->AltBody	= "To view the message, please use an HTML compatible email viewer!";
			$body = eregi_replace("[\]",'',$message);
			$mail->MsgHTML($body);

			if(!$mail->Send()) die('core::api::newsletter::plugin::phpmailer::' .$mail->ErrorInfo);

			$PLEASE_CHECK_INBOX = true;

		}else{
			$NEED_TO_SELECT_LIST = true;
		}
	}
?>