<?php

	$mods = $app->moduleList();
	$i18n = $app->apiLoad('coreI18n');
	$out  = Array();

	foreach($mods as $mod){
		if($mod['key'] == $_GET['module']){

			$file = MODULE.'/'.$_GET['module'].'/'.$_GET['file'];
			$labels = $i18n->parse($file);

			foreach($labels as $e){
				$out[] = array(
					'label' => $e
				);
			}

		}
	}

	$json   = $app->helperJsonEncode($out);
	$beauty = $app->helperJsonBeautifier($json);

	echo $beauty;

