<?php

	if(!defined('COREINC')) die('Direct access not allowed');
	$i18n = $app->apiLoad('coreI18n')->languageSet('fr')->load('core');

?><!DOCTYPE html>
<html lang="fr">
<head>
	<title>Kodeine</title>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/menu.php')
?></header>

<div id="app"><div class="wrapper">

	@@
	
</div></div>

</body></html>
