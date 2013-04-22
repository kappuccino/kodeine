<?php session_start();

// BOOTSTRAP ///////////////////////////////////////////////////////////////////////////////////////////////////////////

	require(__DIR__.'/module/core/helper/app.php');
	$app = new coreApp();

	// BENCH
	if(BENCHME && defined('BENCHALLOW') && BENCHALLOW) include(__DIR__.'/module/core/helper/bench.php');

	// CUSTOM CONFIG
	if(file_exists(CONFIG.'/app.php')) include(CONFIG.'/app.php');

	// URL (Common URL Rewriting rules)
	require(__DIR__.'/module/core/helper/url.php');

// INIT KODEINE ////////////////////////////////////////////////////////////////////////////////////////////////////////

	// Logout or Auto Login
	if(isset($_REQUEST['logout'])) $app->userLogout();
	$app->userIsLoged($_REQUEST['login'], $_REQUEST['password']);

	// Init (main work here, init all process chapter, page etc...)
	$app->kodeineInit($_GET);

// THEME ///////////////////////////////////////////////////////////////////////////////////////////////////////////////

	// Offline message
	$app->offlineMessage();

	// Next step is in the theme file
	$app->themeInclude('html.build.php');

// PROFILE /////////////////////////////////////////////////////////////////////////////////////////////////////////////

	if(BENCHME && defined('BENCHALLOW') && BENCHALLOW) $app->benchmarkProfiling();
