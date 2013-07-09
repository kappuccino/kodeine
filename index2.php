<?php session_start();

// BOOTSTRAP ///////////////////////////////////////////////////////////////////////////////////////////////////////////

	require(__DIR__ . '/module/app/helper/app.php');
	$app = Kodeine\app::getInstance()->init();

	// BENCH
	if(BENCHME) include(__DIR__ . '/module/app/helper/bench.php');

	// CUSTOM CONFIG
	#if(file_exists(CONFIG.'/app.php')) include(CONFIG.'/app.php');

	// URL (Common URL Rewriting rules)
	require(__DIR__ . '/module/core/helper/url.php');

// INIT KODEINE ////////////////////////////////////////////////////////////////////////////////////////////////////////

	// Logout or Auto Login
	if(isset($_REQUEST['logout'])) $app->me->logout();
	$app->me->login($_REQUEST['login'], $_REQUEST['password']);

	// Init (main work here, init all process chapter, page etc...)
	$app->kodeine->init($_GET);

// THEME ///////////////////////////////////////////////////////////////////////////////////////////////////////////////

	// Offline message
	#$app->offlineMessage();

	// Next step is in the theme file
	$file = $app->load('theme')->file('index.php');
	echo $file;
