<?php
	/*require(dirname(dirname(dirname(dirname(__FILE__)))).'/api/core.admin.php');
	$app = new coreAdmin();*/

	$api 			= $app->apiLoad('newsletter');
	$id_newsletter	= $_REQUEST['id_newsletter'];

	# On a tout PUSHER
	#
	if($_REQUEST['end'] == 'true'){
		$app->dbQuery("UPDATE k_newsletter SET newsletterSendDate=NOW(), newsletterConnector='cloudapp' WHERE id_newsletter=".$id_newsletter);
		$out = array('success' => true);
		echo json_encode($out);
	}else


	# On PUSH nos mails vers le serveur
	#
	if($_REQUEST['send'] == 'true'){
		$newsletter	= $api->newsletterGet(array('id_newsletter' => $id_newsletter));
		$body 	= $api->newsletterPrepareBody($id_newsletter, $newsletter['newsletterHtml']);
		$sent	= 0;

		$mails	= $app->apiLoad('newsletter')->newsletterPoolPopulation($id_newsletter);

		$mails	= array_chunk($mails, $_REQUEST['chunk']);
		$mails	= $mails[$_REQUEST['current']];

		# Verifier si la newsletter est deja envoyee ou pas
		#
		if($newsletter['newsletterSendDate'] != ''){
			echo json_encode(array('already' => true));
			exit();
		}


		# Si on ne trouve pas de lien pour le desabonnement, alors le forcer en pieds de mail
		#
		if(substr_count($body, '<unsubscribe') == 0){
			$unsubscribe =
			"<div style=\"text-align:center; padding:5px; margin-top:5px; color:#000000; background:#FFFFFF; font-family:Verdana; font-size:12px;\">".
				"<unsubscribe>Cliquer ici pour ne plus recevoir de mail</unsubscribe>".
			"</div>\n\n";

			$body = preg_match("#(.*)</body>#msU", $body, $pm)
				? str_replace($pm[1], $pm[1].$unsubscribe, $body)
				: $body.$unsubscribe;
		}


		# Boucler et remplir le POOL pour cette POPULATION
		#
		foreach($mails as $user){
			$raw[]	= array(
				'userMail'			=> $user['userMail'],
				'id_newsletter'		=> $id_newsletter,
				'newsletterName'	=> $newsletter['newsletterTitle'],
				'newsletterHtml'	=> $body,
				'webVersion'		=> "http://".$_SERVER['SERVER_NAME'].'/read-newsletter-'.$id_newsletter
			);
			$sent++;
		}

		# PUSHER les infos au serveur
		#
		$pref = $app->configGet('newsletter');
		$rest = new newsletterREST($pref['auth'], $pref['passw']);
		$push = $rest->request('/push.php', 'POST', array('raw' => $raw));
		$push = json_decode($push, true);

		echo json_encode(array(
			'already'	=> false,
			'pushed'	=> $push['pushed'],
			'sent'		=> $sent,
#			'raw'		=> $push
		));
	}


?>