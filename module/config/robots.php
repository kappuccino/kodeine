<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	# Save
	#
	if($_POST['action']){
		$keys = array(
			array('robots.txt',	'contentFile'),
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
		}

		header("Location: robots");
		exit();
	}

	# Data
	#
	$db = $app->dbMulti("SELECT * FROM k_config WHERE configModule='robots.txt'");
	foreach($db as $e){
		$data[$e['configName']] = $e['configValue'];
	}

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>

<div class="inject-subnav-right hide">
	<li><a href="./" class="btn btn-small"><?php echo _('Cancel') ?></a></li>
	<li><a onclick="$('#data').submit();" class="btn btn-small btn-success"><?php echo _('Save') ?></a></li>
</div>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div id="app"><div class="wrapper">

	<form action="robots" method="post" id="data">
	<input type="hidden" name="action" value="1" />

	<textarea name="contentFile" rows="20" style="width:100%;"><?php
		echo $app->formValue($data['contentFile'], $_POST['contentFile']);
	?></textarea>

</div></div>

<?php include(COREINC.'/end.php'); ?>

</body></html>