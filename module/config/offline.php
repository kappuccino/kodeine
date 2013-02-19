<?php

	$i18n = $app->apiLoad('coreI18n')->languageSet('fr')->load('config');

# Save
	#
	if($_POST['action']){
		$keys = array(
			array('offline',	'offlineMessage'),
			array('boot',		'offlineGroup')
		);

		foreach($keys as $conf){

			$tag = $conf[0];
			$key = $conf[1];
			$val = is_array($_POST[$key]) ? implode(',', $_POST[$key]) : $_POST[$key];

			$exi = $app->dbOne("SELECT 1 FROM k_config WHERE configModule='".$tag."' AND configName='".$key."'");
			$q	 = ($exi[1])
				? "UPDATE k_config SET configValue='".addslashes($val)."' WHERE configModule='".$tag."' AND configName='".$key."'"
				: "INSERT INTO k_config (configModule, configName, configValue) VALUES ('".$tag."', '".$key."', '".addslashes($val)."')";
				
			$app->dbQuery($q);
		#	$app->pre($app->db_query, $app->db_error);
		}

		header("Location: offline");
		exit();
	}

	# Data
	#
	$db = $app->dbMulti("SELECT * FROM k_config WHERE configModule IN('boot', 'offline')");
	foreach($db as $e){
		$data[$e['configName']] = $e['configValue'];
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
	<li><a href="./" class="btn btn-small"><?php echo $i18n->_('Annuler') ?></a></li>
	<li><a onclick="$('#data').submit();" class="btn btn-small btn-success"><?php echo $i18n->_('Enregistrer') ?></a></li>
</div>

<div id="app"><div class="wrapper">

	<form action="offline" method="post" id="data">
	<input type="hidden" name="action" value="1" />

	<div class="row-fluid clearfix">
		<div class="span3">
			<table class="listing">
				<thead>
					<tr>
						<th><?php echo $i18n->_('Groupes concernés') ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php
							echo $app->apiLoad('user')->userGroupSelector(array(
								'name'		=> 'offlineGroup[]',
								'multi' 	=> true,
								'style' 	=> 'width:100%; height:100px; display:block;',
								'value'		=> explode(',', $app->formValue($data['offlineGroup'], $_POST['offlineGroup']))
							));
						?></td>
					</tr>
				</tbody>
			</table>
		</div>
		
		<div class="span9">
			<textarea name="offlineMessage" id="offlineMessage" style="width:100%; height:500px;"><?php
				echo $app->formValue($data['offlineMessage'], $_POST['offlineMessage']);
			?></textarea>

		</div>
	</div>

	</form>
	
</div></div>

<?php include(COREINC.'/end.php'); ?>
<script src="/admin/core/vendor/tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
<script src="/admin/core/vendor/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script src="/admin/content/ui/js/content.js"></script>

<script>
	$(function() {
		useEditor = true;
		textarea = 'offlineMessage';
		MceStyleFormats = [<?php echo @file_get_contents(USER.'/config/tinymceStyleFormats.php') ?>];
		setRichEditor();
	});
</script>

</body></html>