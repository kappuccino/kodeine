<?php
	if(!defined('COREINC')) die('@');

	$module = $app->moduleData($_GET['module'], ($_GET['core'] == 'true'));

	if($module['key'] != $_GET['module']){
		$js = array('success' => false, 'reason' => 'module not found');
	}else

	# ENABLED
	#
	if($_GET['enabled'] == 'true'){
		$app->configSet($module['key'], 'enabled', 'YES');
		$js = array('success' => true);
	}else

	# DISABLE
	#
	if($_GET['disabled'] == 'true'){
		$app->configSet($module['key'], 'enabled', 'NO');
		$js = array('success' => true);
	}else

	# INSTALL
	#
	if($_GET['install'] == 'true'){

		$app->dbDump(array(
			'file' => "export-".time()."-install-".$module['key'].".sql"
		));

		$done = true;
		$todo = array($module['key']);
		if(sizeof($module['dependencies']) > 0) $todo = array_merge($todo, $module['dependencies']);

		foreach($todo as $do){
			if($done){
				$source = KROOT.'/app/module/'.$do.'/config/install.xml';
				$done	= $app->apiLoad('corePatch')->installIt($source);

				if($done) $app->configSet($do, 'installed', 'YES');
			}
		}

		$js = array('success' => $done);
	}else

	# PATCH
	#
	if($_GET['patch'] == 'true'){
		$app->dbDump(array(
			'file' => DUMPDIR."/export-".time()."-patch.sql"
		));

		$before = KROOT.'/app/module/'.$module['key'].'/config/patch-todo.xml';
		$after  = KROOT.'/app/module/'.$module['key'].'/config/patch-done.xml';

		if($_GET['again'] == 'true' && file_exists($after)){
			$done = $app->apiLoad('corePatch')->patchIt($after);
		}else
		if(file_exists($before)){
			$done = $app->apiLoad('corePatch')->patchIt($before);
			if($done) $done = @rename($before, $after);
		}else{
			$done = true;
		}

		$js = array('success' => $done);
	}else

	# REPOSITORY
	#
	if($_GET['repository'] == 'true'){
		$raw = file_get_contents('http://kodeine.cloudapp.me?list');
		$raw = json_decode($raw);
		$js  = array('success' => true, 'data' => $raw);
	}else
	
	# DOWNLOAD
	#
	if($_GET['download'] == true){
		$mod	= file_get_contents('http://kodeine.cloudapp.me?mod='.$_GET['module']);
		$mod	= json_decode($mod, true);
		$local	= dirname(dirname(__DIR__)).'/zip__'.uniqid(true).'.zip';
		file_put_contents($local, base64_decode($mod['raw']));
		umask(0); chmod($local, 0755);
		
		// Move
		$off = APP.'/module-off';
		$cur = APP.'/module/'.$_GET['module'];
		$mov = $off.'/'.$_GET['module'].'-'.date('Y-m-d_h-i-s');
		if(!file_exists($off)) mkdir($off, 0755, true);
		rename($cur, $mov);

		// Unzip
		$zip = new ZipArchive();
		$res = $zip->open($local);
		if ($res === TRUE) {
			$zip->extractTo($cur);
			$zip->close();
			$js  = array('success' => true);
		}else{
			$js  = array('success' => false, 'reason' => 'zip failure');
		}
		
		unlink($local);
	}

	# ELSE
	#
	else{
		$js  = array('success' => false, 'unknown' => true);
	}


	echo json_encode($js);
	exit();
?>