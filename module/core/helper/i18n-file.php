<?php

	$mods = $app->moduleList();
	$i18n = $app->apiLoad('coreI18n');
	$out  = Array();

	foreach($mods as $mod){
		if($mod['key'] == $_GET['module']){

			foreach($mod['i18n'] as $m){
				$out[] = array(
					'id'    => $m,
					'file'  => str_replace('/app/module/'.$_GET['module'].'/', '', $m)
				);
			}
		}
	}

	$json   = $app->helperJsonEncode($out);
	$beauty = $app->helperJsonBeautifier($json);

	echo $beauty;

#$files = $i18n->parse(KROOT.$e);
#if(count($files) > 0 && is_array($files)){}