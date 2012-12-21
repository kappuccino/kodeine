<?php

	if(!defined('APP')) die('No direct access allowed');

	if($_POST['action']){
		foreach($_POST['pref'] as $k => $p){
			$app->configSet('clientftp', $k, $p);
		}
		header("Location: pref");
	}

	$pref = $app->configGet('clientftp');

	# Check Config
	#
	if(isset($_GET['test'])){
		$success = $app->apiLoad('clientftp')->configCloudCheck();
	}

?><!DOCTYPE html>
<html lang="fr">
<head>
	<title>Kodeine</title>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>	

<div class="inject-subnav-right hide">
	<li>
		<a href="pref" class="btn btn-mini">Annuler</a>
	</li>
	<li>
		<a onclick="$('#data').submit();" class="btn btn-mini btn-success">Enregistrer</a>
	</li>
</div>


<div id="app"><div class="wrapper">
<form action="pref" method="post" id="data">

	<input type="hidden" name="action" value="1" />

	<?php if(isset($_GET['test']) && $success){ ?>
	<div class="message messageValid">
		Compte parametré correctement
	</div>
	<?php }else if(isset($_GET['test']) && !$success){ ?>
	<div class="message messageWarning">
		Mauvais parametres
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
				<td width="100">Identifiant</td>
				<td><input type="text" name="pref[login]" value="<?php echo $pref['login'] ?>" size="50" autocomplete="off" /></td>
				<td>L'identifiant utilisé pour se connecter au serveur</td>
			</tr>
			<tr>
				<td>Mot de passe</td>
				<td><input type="password" name="pref[password]" value="<?php echo $pref['password'] ?>" size="30" /></td>
				<td>Le mot de passe associé à ce compte</td>
			</tr>
			<tr>
				<td></td>
				<td><a href="pref?test" class="btn btn-small">Tester ces paramètres</a></td>
				<td></td>
			</tr>
		</tbody>
	</table>

</form>
</div></div>

<?php include(COREINC.'/end.php'); ?>

</body></html>