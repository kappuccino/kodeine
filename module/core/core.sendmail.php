<?php

class coreSendMail extends coreApp{

/*-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
 @arg (opt)
 - template   'toto.html' (implicitement USER.'/mail/toto.html' ou une URL absolue
 - body       array KEY/VALUE ou un vout de HTML
 - to         un mail unique
 - cc + bcc   un array de mail
 - domain     domaine utilisé pour le mail.from ou _SERVER.HTTP_HOST
 - replyTo    le mail utilisé pour mail.reply ou contact@domain
 - return     si TRUE retourne le body

 @return true|false
-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -*/
	public function send($opt){

		require_once(APP.'/plugin/phpmailer/class.phpmailer.php');
		$mail = new PHPMailer();
		$mail->CharSet = 'UTF-8';

		$domain = $opt['domain'] ?: $_SERVER['HTTP_HOST'];

		$mail->SetFrom('ne-pas-repondre@'.$domain);


		$reply = $opt['replyTo'] ?: 'contact@'.$domain;
		$mail->ClearReplyTos();
		$mail->AddReplyTo($reply);

		// Destinataire
		$mail->AddAddress($opt['to']);

		// Copie
		if(!empty($opt['cc'])){
			foreach($opt['cc'] as $e){
				if(!empty($e)) $mail->AddCC($e);
			}
		}

		// Copie cachee
		if(!empty($opt['bcc'])){
			foreach($opt['bcc'] as $e){
				if(!empty($e)) $mail->AddBCC($e);
			}
		}

		// Title
		$mail->Subject = $opt['title'];

		// Data
		$template = file_exists($opt['template']) ? $opt['template'] : USER.'/mail/'.$opt['template'];
		if(is_array($opt['body']) && file_exists($template) && is_file($template)){
			$body = $this->helperReplace(file_get_contents($template), $opt['body']);
		}else{
			$body = $opt['body'];
		}

		$mail->AltBody = strip_tags($body);
		$mail->MsgHTML(preg_replace("/\\\\/", '', $body));

		if($opt['return'] === true) return $body;

		return $mail->Send();
	}

}
