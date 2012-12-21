<?php

	require(__DIR__.'/module/core/helper/app.php');
	$app = new coreApp();

	$exe = isset($_GET['do']) ? 'OK' : 'XX';

	$cmd = 'sh '.KROOT.'/app/iso-to-utf8-linux-php '.KROOT.'/user/test '.posix_getuid().' '.posix_getgid().' '.$exe;
	echo $cmd."\n\n\n";
#	die();

	var_export(
	#	exec($cmd, $out, $ret)
		$app->helperPipeExec($cmd)
	);

	echo "\n";
	var_export($out);

	echo "\n";
	var_export($ret);

?>