<?php
	$pref = $app->configGet('newsletter');

	if(isset($_GET['test'])){
		$api 		= $app->apiLoad('newsletter');
		$rest		= new newsletterREST($pref['auth'], $pref['passw']);
		$prev		= $rest->request('/check.php', 'POST');
		$prev 		= json_decode($prev, true);
		$success	= $prev['success'];
	}

	if($_POST['action']){
		foreach($_POST['pref'] as $k => $p){
			$app->configSet('newsletter', $k, $p);
		}
		header("Location: pref");
		exit();
	}

?><!DOCTYPE html>
<head>
	<?php include(COREINC.'/head.php'); ?>
</head>

<body>
<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div id="app"><div class="wrapper">
<form action="pref" method="post">

	<input type="hidden" name="action" value="1" />

	<?php if(isset($_GET['test']) && $success){ ?>
	<div class="message messageValid">
		Compte parametré correctement, vous pouvez dialoguer avec le serveur de Kappuccino
	</div>
	<?php }else if(isset($_GET['test']) && !$success){ ?>
	<div class="message messageWarning">
		Mauvais parametres, impossible de dialoguer avec le serveur de Kappuccino
	</div>
	<?php } ?>

	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing">
		<thead>
			<tr>
				<th width="20%">Paramètre</th>
				<th width="30%">Valeur</th>
				<th width="50%">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Adresses email de test</td>
				<td><input type="text" name="pref[test]" value="<?php echo $pref['test'] ?>" size="50" /></td>
				<td>Séparer les adresses par des virgules pour envoyer les test sur plusieurs adresses</td>
			</tr>


			<tr class="separator">
				<td><input type="radio" name="pref[connector]" value="cloudApp" <?php if($pref['connector'] == '' OR $pref['connector'] == 'cloudApp') echo 'checked="checked"' ?> />
					<b>Cloud-App</b>
				</td>
				<td colspan="2"><i>Vos information de connection pour utiliser Kappuccino cloudapp</i></td>
			</tr>
			<tr>
				<td>Login</td>
				<td><input type="text" name="pref[auth]" value="<?php echo $pref['auth'] ?>" size="50" /></td>
				<td></td>
			</tr>
			<tr>
				<td>Mot de passe</td>
				<td><input type="password" name="pref[passw]" value="<?php echo $pref['passw'] ?>" size="50" /></td>
				<td><a href="pref?test" class="btn btn-mini">Tester</a></td>
			</tr>




			<tr class="separator">
				<td>
					<input type="radio" name="pref[connector]" value="mailChimp" <?php if($pref['connector'] == 'mailChimp') echo 'checked="checked"' ?>  />
					<b>MailChimp</b>
				</td>
				<td colspan="2"><i>Vos information de connection pour utiliser la plateforme MailChimp</i></td>
			</tr>
			<tr>
				<td>Login</td>
				<td><input type="text" name="pref[mailchimpAuth]" value="<?php echo $pref['mailchimpAuth'] ?>" size="50" /></td>
				<td></td>
			</tr>
			<tr>
				<td>Mot de passe</td>
				<td><input type="password" name="pref[mailchimpPass]" value="<?php echo $pref['mailchimpPass'] ?>" size="50" /></td>
				<td><a href="pref?test" class="btn btn-mini">Tester</a></td>
			</tr>
		</tbody>
	</table>

	<p>
		<input type="submit" class="btn btn-mini" value="Enregistrer" />
	</p>
	
</form>

</div></div>

</body></html>