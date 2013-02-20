<?php

	if(!defined('COREINC')) die('Direct access not allowed');
	$i18n = $app->apiLoad('coreI18n')->languageSet('fr')->load('core');

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
    <link rel="stylesheet" type="text/css" href="ui/css/i18n.css" />
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/menu.php')
?></header>

<div id="app"><div class="wrapper"><div id="i18n" data-languages="fr,us">

	<section id="modules">
		<ul><?php

		$mods = $app->moduleList();
		foreach($mods as $e){
			echo '<li data-module="'.$e['key'].'">'.$e['name'].'</li>';
		}

		?></ul>
	</section>

    <section id="files">
		<ul></ul>
    </section>

    <section id="labels">
	    <ul></ul>
    </section>

	<section id="form">

		<fieldset>
			<label>Module</label>
			<select id="inputModule"><?php
				echo '<option value="core">Core</option>';
				echo '<option value="core">--</option>';
				foreach($mods as $e){
					echo '<option value="'.$e['key'].'">'.$e['name'].'</option>';
				}
			?></select>
		</fieldset>

        <fieldset>
            <label>Key</label>
	        <input id="inputKey" class="form-text" />
        </fieldset>

		<div class="languages">
	        <fieldset>
	            <label>Value</label>
		        <textarea id="inputValue" class="form-text"></textarea>
	        </fieldset>
        </div>


        <aside>
			<a class="btn" id="buttonValidate">Valider</a>
		</aside>

	</section>

</div></div></div>


<?php include(COREINC.'/end.php'); ?>
<script src="<?php echo COREVENDOR ?>/backbone/test/vendor/underscore.js"></script>
<script src="<?php echo COREVENDOR ?>/backbone/backbone.js"></script>
<script src="ui/js/i18n.js"></script>


</body></html>