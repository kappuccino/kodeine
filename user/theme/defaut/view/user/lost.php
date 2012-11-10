<!DOCTYPE html> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
	<title></title>
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<link rel="stylesheet" type="text/css" media="all" href="/media/ui/css/style.php" />
	<?php include(MYTHEME.'/ui/html-head.php') ?>
</head>
<body class="body">

<div class="container_12 container clearfix">

	<div class="col grid_3 alpha">
		<?php include(MYTHEME.'/ui/menu.php') ?>
	</div>

	<div class="grid_9 omega center">

		<div class="center-item">

			<h1>Vous avez perdu votre mot de passe ?</h1>
		
			<p><?
				if($NEED_VALIDE_EMAIL) 		echo "NEED_VALIDE_EMAIL";
				if($USER_NOT_EXIST)			echo "USER_NOT_EXIST";
				if($UNSUBSCRIBED)			echo "UNSUBSCRIBED";
				if($PLEASE_CHECK_INBOX)		echo "PLEASE_CHECK_INBOX ";
				if($PASSWORD_RESET)			echo "PASSWORD_RESET : Nouveau mot de pass : ".$password;
			?></p>

			<? if($this->user['id_user'] != NULL){ ?>
		
				<p>Bonjour <?= $this->user['userMail'] ?>,
					vous êtes déjà identifié, pour modifier votre mot de passe <a href="my">aller sur votre compte</a>
				</p>
		
			<? }else{ ?>
				
				<p>Afin de regénérer votre mot de passe, merci de saisir votre adresse email</p>
				<p>Nous vous enverrons un courrier électronique contenant votre nouveau mot de passe.</p>
			
				<form action="lost" method="post">	
					<input type="hidden" name="mailTitle" 	value="Regeneration de votre mot de passe" />
					<input type="hidden" name="debug"		value="0" />
					<input type="hidden" name="cart" />
					
				
					email <input type="text" name="email" /><input type="submit" value="Valider" />		
				</form>
		
			<? } ?>
			
		</div>

	</div>

</div>

</body></html>