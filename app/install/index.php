<?php

	ini_set('display_errors',	'On');
	ini_set('html_errors', 		'On');
	ini_set('error_reporting',	E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_WARNING);

	$OK = "<span style=\"color:green;\">[OK]</span>\t";
	$NO = "<span style=\"color:red;\">[NO]</span>\t";

?><!DOCTYPE HTML>
<html lang="fr">
<head>
	<title>Kodeine</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="style.css" />
	<link href='http://fonts.googleapis.com/css?family=Droid+Sans:400,700' rel='stylesheet' type='text/css'>
</head>
<body>

<h1>Kodeine, installation</h1>

<pre class="step"><?php

	require(dirname(__DIR__).'/module/core/helper/app.php');
	$config = USER.'/config/config.php';

	if(file_exists($config)){
		echo $OK."Le fichier ".$config." existe.\n";
		include($config);

		echo "\tMySQL : ".$config['db']['login'].":*******@".$config['db']['host']."\n\n";

		try{
			$con = @(new mysqli($config['db']['host'], $config['db']['login'], $config['db']['password'], $config['db']['database']));

			var_dump($con);
			echo $OK."Connection avec le serveur UP";

		} catch(Exception $e){
			echo $NO."Impossible de se connecter au serveur: ".$con->connect_error;
		}

	#	var_dump($con);
	
		
		
	}else{
		echo $NO."Le fichier ".$config." n'existe pas\n";
	}

?></pre>




</body>
</html>