<?php

	if(!defined('COREINC')) die('Direct access not allowed');
	$i18n = $app->apiLoad('coreI18n')->languageSet('fr')->load('social');

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

<div id="app">
	...
</div>

</body></html>