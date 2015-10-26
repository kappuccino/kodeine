<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	# Data
	$myCmd = $app->apiLoad('business')->businessCartGet(array(
		'is_cmd'	=> true,
		'id_cart'	=> $_REQUEST['id_cart'],
		'debug'		=> false
	));

	# Update
	if($_POST['update']){	
		
		$def['k_businesscart'] = array(
			'cartStatus'         => array('value' => $_POST['cartStatus']),
			'cartSerial'         => array('value' => $_POST['cartSerial']),
			'cartDeliveryStatus' => array('value' => $_POST['cartDeliveryStatus']),
			'id_carrier'         => array('value' => $_POST['id_carrier'])
		);
		
		if($app->formValidation($def)){
			$app->dbQuery($app->dbUpdate($def)." WHERE id_cart=".$_POST['id_cart']);
		}	
		
		# HOOK
        $app->hookAction('businessCmdPaymentMail', $_POST['id_cart'], $myCmd['cartStatus'], $_POST['cartStatus']);

		$reload = true;
	}
	
	# Data
	$myCmd = $app->apiLoad('business')->businessCartGet(array(
		'is_cmd'	=> true,
		'id_cart'	=> $_REQUEST['id_cart'],
		'debug'		=> false
	));

    $res  = $app->hookFilter('businessCartEditMail', array('mailSent' => $mailSent, 'id_cart' => $_REQUEST['id_cart'], 'mailTemplate' => $_POST['mailTemplate']));

    $mailSent = $res['mailSent'];

	if($_POST['mailTemplate'] != '' && !$mailSent){

		if(!empty($myCmd['id_carrier']) && $myCmd['cartSerial']){
			$carrier = $app->apiLoad('business')->businessCarrierGet(['id_carrier' => $myCmd['id_carrier']]);
			$url = $carrier['carrierUrl'];
			$myCmd['trackingUrl'] = str_replace('{tracking}', $myCmd['cartSerial'], $url);
		}

		$split		= '{lines}';
		$message 	= file_get_contents(KROOT.'/user/mail/business/'.$_POST['mailTemplate']);

		if(preg_match_all("#{lines}(.*){lines}#s", $message, $m, PREG_SET_ORDER)){
			$tLine	= $m[0][1];
			foreach($myCmd['line'] as $e){
				$tmp .= $app->helperReplace($tLine, $e);
			}

			$message = str_replace($m[0][0], $tmp, $message);
		}

		if(preg_match_all("#{ifCoupon}(.*){ifCoupon}#s", $message, $m, PREG_SET_ORDER)){
			$message = (floatval($cmd['cartCoupon']) == 0)
				? str_replace($m[0][0], NULL, $message)
				: str_replace('{ifCoupon}', NULL, $message);
		}

		$myCmd['cartDeliveryAddress'] = nl2br($myCmd['cartDeliveryAddress']);
		$myCmd['cartBillingAddress']  = nl2br($myCmd['cartBillingAddress']);

		$message = $app->helperReplace($message, $myCmd);



		require_once(KROOT.'/app/plugin/phpmailer/class.phpmailer.php');
		$mail = new PHPMailer();
        $mail->CharSet = "UTF-8";
		$mail->SetFrom("noreply@".$_SERVER['HTTP_HOST']);
		$mail->AddReplyTo("noreply@".$_SERVER['HTTP_HOST']);
		$mail->AddAddress($myCmd['cartEmail']);


        $shop = $app->apiLoad('shop')->shopGet(array(
            'id_shop'	=> $myCmd['id_shop']
        ));

        // CC
        $mailCc		= $app->apiLoad('shop')->shopMailExtraction($shop['shopMailCc']);
        foreach($mailCc as $e){
            if(filter_var($e, FILTER_VALIDATE_EMAIL) !== FALSE) $mail->AddCC($e);
        }

        // BCC
        $mailBcc		= $app->apiLoad('shop')->shopMailExtraction($shop['shopMailBcc']);
        foreach($mailBcc as $e){
            if(filter_var($e, FILTER_VALIDATE_EMAIL) !== FALSE) $mail->AddBCC($e);
        }



		$mail->Subject	= "[".$_SERVER['HTTP_HOST']."] Votre commande";
		$mail->AltBody	= "Pour voir ce message, merci d'utiliser un client compatible html";
		$body = preg_replace("[\\\]",'', $message);
		$mail->MsgHTML($body);

		if(!$mail->Send()) {
			die("Mailer Error: " .$mail->ErrorInfo);
		}
		 
		 
	}
	if ($reload) {
		header("Location: ./");
		exit(0);
	}

?><!DOCTYPE html>
<html lang="fr">
<head>
    <?php include(COREINC.'/head.php'); ?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php');
?></header>

<?php //include('ressource/ui/menu.business.php'); ?>

<div class="app">

	<form action="edit" method="post">

	<input type="hidden" name="update" value="1" />
	<input type="hidden" name="id_cart" value="<?php echo $myCmd['id_cart'] ?>" />

	<div style="width:900px; margin:0 auto;">
		<h1><?php echo _('Order edit'); ?> #<?php echo $_REQUEST['id_cart'] ?></h1>

        <div class="clearfix"></div>

		<p><?php echo _('Payment status'); ?> : <select name="cartStatus"><?php
			foreach($app->apiLoad('business')->businessStatusGet() as $e){
				$sel = ($myCmd['cartStatus'] == $e) ? ' selected' : NULL;
				echo "<option value=\"".$e."\"".$sel.">".$e."</option>";
			}
		?></select>

        <p><?php echo _('Shipment status'); ?> : <select name="cartDeliveryStatus"><?php
            foreach($app->apiLoad('business')->businessCartDeliveryStatus() as $e){
                $sel = ($myCmd['cartDeliveryStatus'] == $e) ? ' selected' : NULL;
                echo "<option value=\"".$e."\"".$sel.">".$e."</option>";
            }
            ?></select></p>


		<p><?php echo _('Carrier'); ?> : <select name="id_carrier"><option value=""></option><?php
            foreach($app->apiLoad('business')->businessCarrierGet() as $e){
                $sel = ($myCmd['id_carrier'] == $e['id_carrier']) ? ' selected' : NULL;
                echo "<option value=\"".$e['id_carrier']."\"".$sel.">".$e['carrierName']."</option>";
            }
            ?></select></p>

		<p><?php echo _('Tracking code'); ?> :
			<input type="text" name="cartSerial" value="<?php echo $myCmd['cartSerial'] ?>" /></p>

		<p><?php echo _('Mail template'); ?> <select name="mailTemplate">
			<option value=""><?php echo _('Do not send email'); ?></option><?php
			$files = $app->fsFile(KROOT.'/user/mail/business/', 'business*.html');
			foreach($files as $e){
				$e = basename($e);
				echo "<option value=\"".$e."\">".$e."</option>";
			}
		?></select></p>

		<p><input type="submit" /></p>
	</div>
	</form>


</div>

<?php include(COREINC.'/end.php'); ?>
<script>
</script>


</body></html>