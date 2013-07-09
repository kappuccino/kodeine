<?php

	if(!defined('COREINC')) die('Direct access not allowed');

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
		echo _('You are logged in, but you do not have enought privileges to use the back office. <a href="?logout=1">Logout</a>');
		die();
	}

	if($_GET['t'] != ''){
		$app->dbQuery("DELETE FROM k_userlost WHERE lostTTL < ".time());
		$req = $app->dbOne("SELECT * FROM k_userlost WHERE lostToken = '".$_GET['t']."'");
		$usr = $app->apiLoad('user')->userGet(array(
			'id_user'	=> $req['id_user']
		));

		if(intval($usr['id_user']) > 0){
			$regen = true;
		}
	}

?><!DOCTYPE html> 
<html xml:lang="fr">
<head>
	<title>Kodeine</title>
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/login.css" />
</head>
<body>

<div id="login">
	<h1><?php echo _('Welcome,<br />login-in'); ?></h1>
	<?php if($app->userIsExpired){ echo "EXPIRED"; } ?>

	<div class="line">
		<div class="form">
			<form method="post" action="login">
				<?php echo _('Enter your'); ?>
				<input type="email" name="login" class="field" autocomplete="off" placeholder="<?php echo _('login'); ?>" value="<?php echo $usr['userMail'] ?>" />
				<?php echo _('and your'); ?>
				<input type="password" name="password" class="field" autocomplete="off" placeholder="<?php echo _('password'); ?>" />
				<?php echo _('and') ?>
				<a href="#" onclick="$('form').submit()"><?php echo _('connect you'); ?></a>
			</form>
		</div>

		<div>
			<?php echo _('Enter your'); ?>
			<input type="email" name="lost" class="field" autocomplete="off" placeholder="<?php echo _('email'); ?>" />
			<?php echo _('and'); ?>
			<a onclick="lostPwd();"><?php echo _('reset your password'); ?></a>
		</div>

		<div>
			<?php echo _('Check your mail, you will receive a link to reset your password'); ?>
		</div>

		<?php if($regen){ ?>
		<div class="regen">
			<?php echo $usr['userMail'] ?>, <?php echo _('enter your new '); ?>
			<input type="password" name="regen" class="field" autocomplete="off" placeholder="<?php echo _('password'); ?>" />
            <?php echo _('and'); ?>
			<a href="#" onclick="rgx('<?php echo $_GET['t'] ?>')"><?php echo _('save it'); ?></a>
		</div>
		<?php } ?>
	</div>

	<a class="lost" onclick="toggle();"></a>
</div>

<script src="vendor/jquery/jquery-1.7.2.min.js"></script>
<script src="ui/js/login.js"></script>

<body></html>