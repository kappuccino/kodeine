<?php

	$i18n = $app->apiLoad('coreI18n')->languageSet('fr')->load('theme');

	// Remove
	if(sizeof($_POST['del']) > 0){
		foreach($_POST['del'] as $e){
			$app->dbQuery("DELETE FROM k_theme WHERE id_theme=".$e);
		}
		$app->configSet('boot', 'jsonCacheTheme', json_encode($app->apiLoad('theme')->themeGet()));
		header("Location: ./");
	}else
	if($_POST['action']){
		$do = true;

		$def['k_theme'] = array(
			'themeName'			=> array('value' => $app->helperNoAccent($_POST['themeName']), 'check' => '.'),
			'themeFolder'		=> array('value' => $_POST['themeFolder'])
		);

		if(!$app->formValidation($def)) $do = false;

		if($do){
			$result = $app->apiLoad('theme')->themeSet($_POST['id_theme'], $def);

			$message = ($result)
				? 'OK: Enregistrement dans la base'
				: 'KO: Une erreur est survenue, APP:<br />'.$app->db_error;

			if($result) $app->configSet('boot', 'jsonCacheTheme', json_encode($app->apiLoad('theme')->themeGet()));

		}else{
			$message = 'KO: Validation failed';
		}
	}

	if($_REQUEST['id_theme'] != NULL){
		$data = $app->apiLoad('theme')->themeGet(array(
			'id_theme'	=> $_REQUEST['id_theme'],
		));

		$var	= json_decode($data['themeVar'], true);
		$field	= $app->apiLoad('field')->fieldGet();
	}

	$theme = $app->apiLoad('theme')->themeGet();

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
    <link rel="stylesheet" type="text/css" href="ui/css/theme.css" />
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(dirname(__DIR__).'/config/ui/menu.php')
?></header>

<div class="inject-subnav-right hide">
	<li><a href="./" class="btn btn-small"><?php echo $i18n->_('Annuler') ?></a></li>
	<li>
	<?php if(isset($_REQUEST['dofield'])){ ?>
		<a onclick="sauver();" class="btn btn-mini"><?php echo $i18n->_('Enregistrer') ?></a>
	<?php }else{ ?>
		<a onclick="$('#data').submit()" class="btn btn-small btn-success"><?php echo $i18n->_('Enregistrer') ?></a>
	<?php } ?>
	</li>
</div>

<div id="app"><div class="wrapper"><div class="row-fluid">

	<form action="./" method="post" id="listing" class="span6">
		<table border="0" cellpadding="0" cellspacing="0" class="listing">
			<thead>
				<tr>
					<th width="30"></th>
					<th><?php echo $i18n->_('Nom') ?></th>
					<th width="120"><?php echo $i18n->_('Champs') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($theme as $e){ ?>
				<tr class="<?php if($e['id_theme'] == $_REQUEST['id_theme']) echo "selected" ?>">
					<td><input type="checkbox" name="del[]" value="<?php echo $e['id_theme'] ?>" class="del" /></td>
					<td><a href="./?id_theme=<?php echo $e['id_theme'] ?>"><?php echo $e['themeName'] ?></a></td>
					<td><a href="./?id_theme=<?php echo $e['id_theme'] ?>&dofield"><?php echo $i18n->_('Configurer') ?></a></td>
				</tr>
				<?php } ?>
			</tbody>
			<tfoot>
				<tr>
					<td width="25"><input type="checkbox" onchange="$('.listing .del').attr('checked', this.checked);" /></td>
					<td colspan="2"><a href="#" onClick="applyRemove();" class="btn btn-mini"><?php echo $i18n->_('Supprimer la selection') ?></a> </td>
				</tr>
			</tfoot>
		</table>
	</form>


	<form action="./" method="post" id="data" class="span6">
		<input type="hidden" name="action" value="1" />
		<input type="hidden" name="id_theme" value="<?php echo $data['id_theme'] ?>" />

		<?php
			if($message != NULL){
				list($class, $message) = $app->helperMessage($message);
				echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
			}

			if(!isset($_REQUEST['dofield'])){ ?>

			<table cellpadding="0" cellspacing="0" border="0" class="form">
				<tr>
					<td width="75"><?php echo $i18n->_('Nom') ?></td>
					<td><input type="text" name="themeName" value="<?php echo $app->formValue($data['themeName'], $_POST['themeName']); ?>" /></td>
				</tr>
				<tr>
					<td><?php echo $i18n->_('Dossier') ?></td>
					<td><select name="themeFolder"><?php
						$folders = $app->fsFolder(USER.'/theme', '', FLAT);
						foreach($folders as $e){
							$e = basename($e);
							$sel = ($e == $app->formValue($data['themeFolder'], $_POST['themeFolder'])) ? ' selected' : NULL;
							echo "<option value=\"".$e."\"".$sel.">".$e."</option>>";
						}
					?></select></td>
				</tr>
			</table>
		
		<?php }else{ ?>	

			<b><?php echo $i18n->_('Champs utilisés') ?></b>
			<ul id="la" class="myList clearfix"><?php
				if(sizeof($var) > 0){
					foreach($var as $e){ ?>
					<li id="<?php echo $e['id_field'] ?>"><?php echo $e['fieldName'] ?></li>
					<?php }
				}
			?></ul>

			<div class="mar-top-20"><b><?php echo $i18n->_('Autre champs utilisable') ?></b></div>
			<ul id="lb" class="myList clearfix"><?php
				if(sizeof($rest) > 0){
					foreach($rest as $e){ ?>
					<li  id="<?php echo $e['id_field'] ?>"><?php echo $e['fieldName'] ?></li>
					<?php }
				}
			?></ul>

			<input type="hidden" id="move" size="80" />
		
		<?php } ?>
	</form>

</div></div></div>

<?php include(COREINC.'/end.php'); ?>
<script src="/admin/theme/ui/js/theme.js"></script>

</body></html>