<?php session_start();

	#include(__DIR__.'/module/core/helper/bench.php');

// BOOTSTRAP ///////////////////////////////////////////////////////////////////////////////////////////////////////////

	require(__DIR__.'/module/core/helper/app.php');
	$app = new coreApp();

	// URL (Common URL Rewriting rules)
	require(__DIR__.'/module/core/helper/url.php');

	// Post action file (BEFORESTART must be inited in /user/config/app.php
	if(defined('BEFORESTART') && is_file(BEFORESTART)) include(BEFORESTART);

// INIT KODEINE ////////////////////////////////////////////////////////////////////////////////////////////////////////

	// Logout or Auto Login
	if(isset($_REQUEST['logout'])) $app->userLogout();
	$app->userIsLoged($_REQUEST['login'], $_REQUEST['password']);

	// Init (main work here, init all process chapter, page etc...)
	$app->kodeineInit($_GET);

// THEME ///////////////////////////////////////////////////////////////////////////////////////////////////////////////

	// Post action file (AFTERINIT must be inited in /user/config/app.php
	if(defined('AFTERINIT') && is_file(AFTERINIT)) include(AFTERINIT);

	// Offline message
	$app->offlineMessage();

	// Next step is in the theme file
	$app->themeInclude('html.build.php');

// PROFILE /////////////////////////////////////////////////////////////////////////////////////////////////////////////

	if(BENCHME && defined(BENCHALLOW) && constant('BENCHALLOW')) $app->benchmarkProfiling();
