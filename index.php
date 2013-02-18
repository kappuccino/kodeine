<?php session_start();

	# Include all we need : apiLoader + constant
	#
	require(__DIR__.'/module/core/helper/app.php');

	# APP (Yeah !! app is created) NOW
	#
	$app = new coreApp();

	# URL (Common URL Rewriting rules)
	#
	require(__DIR__.'/module/core/helper/url.php');

	# Post action file (BEFORESTART must be inited in /user/conf/app.php
	#
	if(defined('BEFORESTART') && is_file(BEFORESTART)) include(BEFORESTART);


// --------------------------------------------------------------- //

	# Logout or Auto Login
	#
	if(isset($_REQUEST['logout'])) $app->userLogout();
	$app->userIsLoged($_REQUEST['login'], $_REQUEST['password']);

	# Init (main work here, init all process chapter, page etc...)
	#
	$app->kodeineInit($_GET);

	# Offline message
	#
	$app->offlineMessage();

	# Next step is in the theme file
	#
	$app->themeInclude('html.build.php');
	#include(KROOT . $app->kTalk('/{T}/html.build.php'));

// --------------------------------------------------------------- //


	# Final bench : Profile
	#
	if(BENCHME) $app->benchmarkProfiling();
