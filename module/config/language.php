<?php

	if(!defined('COREINC')) die('Direct access not allowed');
	$i18n = $app->apiLoad('coreI18n')->languageSet('fr')->load('config');

	// Remove
	if(sizeof($_POST['del']) > 0){
		foreach($_POST['del'] as $e){
			$app->dbQuery("DELETE FROM k_country WHERE iso='".$e."'");
		}

		# Cache Country
		$app->configSet('boot', 'jsonCacheCountry', json_encode($app->countryGet(array('is_used' => true))));

		header("Location: language");
	}else
	if($_POST['action']){
		$do = true;

		$_POST['iso'] = strtolower($_POST['iso']);
		if($_POST['iso_ref'] == NULL) $_POST['iso_ref'] = $_POST['iso'];

		$def['k_country'] = array(
			'iso'					=> array('value' => $_POST['iso'], 					'check' => '[a-z]{2}'),
			'iso_ref'				=> array('value' => $_POST['iso_ref'], 				'check' => '.'),
			'is_used'				=> array('value' => $_POST['is_used'], 				'zero' 	=> true),
			'is_delivered'			=> array('value' => $_POST['is_delivered'], 		'zero' 	=> true),
			'countryZone'			=> array('value' => $_POST['countryZone'], 			'check' => '.'),
			'countryName'			=> array('value' => $_POST['countryName'], 			'check' => '.'),
			'countryLanguage'		=> array('value' => $_POST['countryLanguage'], 		'check' => '.'),
			'countryLocale'			=> array('value' => $_POST['countryLocale'], 		'check' => '.'),
		);

		if(!$app->formValidation($def)) $do = false;

		if($do){
			$result = $app->countrySet($def);

			$message = ($result)
				? 'OK: Enregistrement dans la base'
				: 'KO: Une erreur est survenue, APP:<br />'.$app->db_error;

			# Cache Country
			$app->configSet('boot', 'jsonCacheCountry', json_encode($app->countryGet(array('is_used' => true))));

		}else{
			$message = 'KO: Validation failed';
		}

	}

	if($_REQUEST['iso'] != NULL){
		$data = $app->countryGet(array('iso' => $_REQUEST['iso']));
	}

	$country = $app->countryGet(array('byZone' => true));

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
	<li><a href="language-import" class="btn btn-mini"><?php echo $i18n->_('Importer des langues') ?></a></li>
	<li><a href="./" class="btn btn-small"><?php echo $i18n->_('Annuler') ?></a></li>
	<li><a onclick="$('#data').submit();" class="btn btn-small btn-success"><?php echo $i18n->_('Enregistrer') ?></a></li>
</div>

<div id="app"><div class="wrapper"><div class="row-fluid">
			
	<?php if(!$app->userCan('core.language')){ ?>
		<div class="message messageError"><?php echo $i18n->_('Profile insufisant') ?></div>
	<?php }else{ ?>

	<div class="span6">
		<form action="language" method="post" id="form">
		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing">
			<thead>
				<tr>
					<th width="30" class="icone"><i class="icon-remove icon-white"></i></th>
					<th><?php echo $i18n->_('Pays') ?></th>
					<th><?php echo $i18n->_('Langue') ?></th>
					<th width="40" class="icone"><i class="icon-globe icon-white"></i></th>
					<th width="40" class="icone"><i class="icon-shopping-cart icon-white"></i></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($country as $zone){ ?>
				<tr class="separator">
					<td width="30"></td>
					<td colspan="4" style="font-weight: bold;"><?php echo $zone[0]['countryZone'] ?></td>
				</tr>
				<?php foreach($zone as $e){ $chkdel++; ?>
				<tr class="<?php if($e['iso'] == $_REQUEST['iso']) echo "selected" ?>">
					<td class="check-red"><input type="checkbox" class="chk" name="del[]" id="chkdel<?php echo $chkdel ?>" value="<?php echo $e['iso'] ?>" /></td>
					<td><a href="language?iso=<?php echo $e['iso'] ?>"><?php echo $e['countryName'] ?></a></td>
					<td><?php echo $e['countryLanguage'] ?></td>
					<td><img src="/admin/core/ui/img/_img/boxcheck<?php if($e['is_used']) 		echo "ed"; ?>.png" align="absmiddle" /></td>
					<td><img src="/admin/core/ui/img/_img/boxcheck<?php if($e['is_delivered'])	echo "ed"; ?>.png" align="absmiddle" /></td>
				</tr>
			<?php }} ?>
			</tbody>
			<tfoot>
				<tr>
					<td height="30"></td>
					<td colspan="4"><a onClick="apply();" class="btn btn-mini"><?php echo $i18n->_('Supprimer la selection') ?></a></td>
				</tr>
			</tfoot>
		</table>
		</form>
	</div>

	<div class="span6">

		<?php
			if($message != NULL){
				list($class, $message) = $app->helperMessage($message);
				echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
			}
		?>
		
		<form action="language" method="post" id="data">
		<input type="hidden" name="action" value="1" />
		<input type="hidden" name="iso" value="<?php echo $data['iso'] ?>" />
		
		<table cellpadding="0" cellspacing="0" border="0" class="form">
			<tr>
				<td width="100">Code</td>
				<td><input type="text" name="iso" value="<?php echo $app->formValue($data['iso'], $_POST['iso']); ?>" />
					<?php echo $i18n->_('Utiliser par l\'URL /fr/') ?>
				</td>
			</tr>
			<tr>
				<td><?php echo $i18n->_('Nom') ?></td>
				<td><input type="text" name="countryName" value="<?php echo $app->formValue($data['countryName'], $_POST['countryName']); ?>" />
					<?php echo $i18n->_('Exemple : France, Belgique, Italie') ?>
				</td>
			</tr>
			<tr>
				<td><?php echo $i18n->_('Langue') ?></td>
				<td><input type="text" name="countryLanguage" value="<?php echo $app->formValue($data['countryLanguage'], $_POST['countryLanguage']); ?>" />
					<?php echo $i18n->_('Exemple : Français, Anglais, Allemand') ?>
				</td>
			</tr>
			<tr>
				<td><?php echo $i18n->_('Variante locale') ?></td>
				<td><input type="text" name="countryLocale" value="<?php echo $app->formValue($data['countryLocale'], $_POST['countryLocale']); ?>" />
					<?php echo $i18n->_('Exemple: fr_FR, fr_CH, en_EN, en_US') ?>
				</td>
			</tr>
			<tr>
				<td><?php echo $i18n->_('Reférence') ?></td>
				<td><select name="iso_ref"><?php
					if($data['iso'] == $data['iso_ref']) $selSame = ' selected';
					echo "<option value=\"\"".$selSame.">Pas de référence</option>";
	
					$all = $app->countryGet();
					foreach($all as $e){
						$sel = ($e['iso'] == $app->formValue($data['iso_ref'], $_POST['iso_ref']) && $selSame == '') ? ' selected' : NULL;
						echo "<option value=\"".$e['iso']."\"".$sel.">".strtoupper($e['iso'])." : ".$e['countryName']."</option>";
					}
				?></select></td>
			</tr>
			<tr>
				<td><?php echo $i18n->_('Zone') ?></td>
				<td><select name="countryZone" id="countryZone"><?php

					$zone = $app->dbMulti("SELECT DISTINCT countryZone FROM k_country");
					
					foreach($zone as $e){
						$sel = ($app->formValue($data['countryZone'], $_POST['countryZone']) == $e['countryZone']) ? ' selected' : NULL;
						echo "<option value=\"".$e['countryZone']."\"".$sel.">".$e['countryZone']."</option>";
					}
					
				?></select>
				<a href="javascript:addZone();" class="btn btn-mini"><?php echo $i18n->_('Ajouter une zone') ?></a>
				</td>
			</tr>
			<tr>
				<td><?php echo $i18n->_('Traduction') ?></td>
				<td><input type="checkbox" name="is_used" value="1" <?php echo $app->formValue($data['is_used'], $_POST['is_used']) ? ' checked' : ''; ?> />
					<?php echo $i18n->_('Permet de traduire du contenu dans cette langue (dans l\'admin)') ?>
				</td>
			</tr>
			<tr>
				<td><?php echo $i18n->_('Livraison') ?></td>
				<td><input type="checkbox" name="is_delivered" value="1" <?php echo $app->formValue($data['is_delivered'], $_POST['is_delivered']) ? ' checked' : ''; ?> />
					<?php echo $i18n->_('Apparait dans la liste des pays autorisé pour livraison (eBusiness)') ?>
				</td>
			</tr>
		</table>
		
		<p><?php echo $i18n->_('La <u>référence</u> est la langue pour laquelle les traduction seront demandé.<br />
		Par exemple CH (suisse) a pour référence FR (france), ce qui permet de ne pas avoir 
		a gérer deux fois le contenu dans la même langue') ?></p>
	
		</form>
	</div>
	<?php } ?>
	
</div></div></div>

<?php include(COREINC.'/end.php'); ?>
<script>

	function apply(){
		if(confirm("<?php echo addslashes($i18n->_('SUPPRIMER ?')) ?>")) $('#form').submit();
	}
	
	function addZone(){
		zone = prompt("<?php echo addslashes($i18n->_('Quel nom voulez vous donner à cette zone ?')) ?>");

		if(zone.length > 0){
			$('#countryZone').append('<option value="'+zone+'" selected="selected">'+zone+'</option>');
		}
	}

</script>

</body></html>