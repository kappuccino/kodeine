<?php

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

<div id="app"><div class="wrapper"><?php

	$mods = $app->moduleList();
	$i18n = $app->apiLoad('coreI18n');

	foreach($mods as $name => $mod){
		if(count($mod['i18n']) > 0){

			echo '<h2>'.$name.'</h2>';
			echo '<ul>';
			foreach($mod['i18n'] as $e){
				echo '<li>';

				$i18n->parse(KROOT.$e);

				echo '</li>';
			}
			echo '</ul>';

		}
	}



	?></div></div>

<?php include(COREINC.'/end.php'); ?>


</body></html>