<?php
	if(!defined('APP')) die('No direct access allowed');

	# Logout
	if(isset($_REQUEST['logout'])){
		$app->userLogout();
		$app->go('./');
	}

	# Login
	if(isset($_POST['login'])){
		$app->userLogin($_POST['login'], $_POST['password']);
		$log = true;
	}

	if($app->userIsLogged && $app->userIsAdmin){
		if($log){
			$app->apiLoad('coreLog')->logAdd(array(
				'logName' 	=> 'Login admin',
				'logValue' 	=> $_POST['login']
			));
		}

		$app->go('./');
	}else
	if($app->userIsLogged && !$app->userIsAdmin){
		die("Vous etes identifi&eacute; mais vous ne pouvez pas acc&eacute;der a l'admin : <a href=\"?logout=1\">se d&eacute;connecter</a>");
	}

	if($_GET['t'] != ''){
		$app->dbQuery("DELETE FROM k_userlost WHERE lostTTL < ".time());
		$req	= $app->dbOne("SELECT * FROM k_userlost WHERE lostToken = '".$_GET['t']."'");
		$usr	= $app->apiLoad('user')->userGet(array(
			'id_user'	=> $req['id_user']
		));

		if(intval($usr['id_user']) > 0){
			$regen = true;
		}
	}

?><!DOCTYPE html> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
	<title>Kodeine</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/login.css" />
</head>
<body>

<div id="login">
	<h1>Bienvenue,<br />identifiez-vous</h1>
	<? if($app->userIsExpired){ echo "EXPIRED"; } ?>

	<div class="line">
		<div class="form">
			<form method="post" action="login">
				Renseignez votre
				<input type="email" name="login" class="field" autocomplete="off" placeholder="identifiant" value="<?php echo $usr['userMail'] ?>" />
				et votre
				<input type="password" name="password" class="field" autocomplete="off" placeholder="mot de passe" />
				pour vous <a href="#" onclick="$('form').submit()">connecter</a>
			</form>
		</div>

		<div>
			Renseignez votre
			<input type="email" name="lost" class="field" autocomplete="off" placeholder="identifiant" />
			et demandez un <a onclick="lostPwd();">nouveau mot de passe</a>
		</div>

		<div>
			Vous allez recevoir un email avec la proc&eacute;dure pour changer votre mot de passe
		</div>

		<?php if($regen){ ?>
		<div class="regen">
			<?php echo $usr['userMail'] ?>, indiquez votre nouveau 
			<input type="password" name="regen" class="field" autocomplete="off" placeholder="mot de passe" />
			et <a href="#" onclick="rgx('<?php echo $_GET['t'] ?>')">valider le</a>
		</div>
		<?php } ?>
	</div>

	<a class="lost" onclick="toggle();"></a>
</div>

<script src="ui/_jquery/jquery-1.7.2.min.js"></script>
<script src="ui/js/login.js"></script>

<body></html>