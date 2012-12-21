<?php
	if(!defined('COREINC')) die('@');

#	require(dirname(dirname(dirname(dirname(__FILE__)))).'/api/core.admin.php');
#	$app = new coreAdmin();

	$open = ($_GET['open'] == 'true') ? '1' : '0';

	$app->filterSet($_GET['mod'], $open, 'open');

	echo json_encode(array('success' => true));
?>