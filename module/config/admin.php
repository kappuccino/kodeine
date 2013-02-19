<?php

	if(!defined('COREINC')) die('Direct access not allowed');
	$i18n = $app->apiLoad('coreI18n')->languageSet('fr')->load('config');

	if($_POST['action']){

		// TOUS
		$keys = array('brandName');
		foreach($keys as $k){
			$app->configSet('admin', $k, $_POST[$k]);

			/*$exi = $app->dbOne("SELECT 1 FROM k_config WHERE configModule='boot' AND configName='".$k."'");
			$q	 = ($exi[1])
				? "UPDATE k_config SET configValue='".addslashes($_POST[$k])."' WHERE configModule='boot' AND configName='".$k."'"
				: "INSERT INTO k_config (configModule, configName, configValue) VALUES ('boot', '".$k."', '".addslashes($_POST[$k])."')";

			$app->dbQuery($q);*/
		}
		
		// QUE MOI
		#$app->filterSet('admin', $_POST['adminSubMenu'], 'adminSubMenu');
		
		// REload
		$app->go('admin');
	}

	$data	= $app->configGet('admin');
	$cookie = $app->filterGet('admin');

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
	<li><a href="./" class="btn btn-small"><?php echo $i18n->_('Annuler') ?></a></li>
	<li><a onclick="$('#data').submit()" class="btn btn-small btn-success"><?php echo $i18n->_('Enregistrer') ?></a></li>
</div>

<div id="app"><div class="wrapper">

	<?php if(isset($_GET['saved'])){
		echo '<div class="message messageValid">'.$i18n->_('Mise à jour des paramètre de configuration').'</div>';
	} ?>
	
	<form action="admin" method="post" id="data">
		<input type="hidden" name="action" value="1" />
		
		<h4><?php echo $i18n->_('Ces paramètres sont commun à tous les utilisateurs de ce back office') ?></h4>
		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing">
			<thead>
				<tr>
					<th width="25%"><?php echo $i18n->_('Paramètre') ?></th>
					<th width="25%"><?php echo $i18n->_('Valeur') ?>'</th>
					<th width="50%"><?php echo $i18n->_('Explication') ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php echo $i18n->_('Brand name') ?></td>
					<td><input type="text" name="brandName" value="<?php echo $app->formValue($data['brandName'], $_POST['brandName']) ?>" style="width:80%;" /></td>
					<td><?php echo $i18n->_('Le nom qui est affiché en haut à gauche') ?></td>
				</tr>
			</tbody>
		</table>
	</form>

</div></div>

<?php include(COREINC.'/end.php'); ?>

</body>
</html>