<?php

	require APP.'/plugin/getid3/getid3/getid3.php';
	$url	= $_GET['url'];
	$flv	= substr($url, 0, -4);
	$getID3 = new getID3;
	$info 	= $getID3->analyze(KROOT.$url);

	$duree	= $info['playtime_string'];

?><!DOCTYPE html>
<html lang="fr">
<head>
	<title></title>
	<style>
		body{
			font-family: Arial;
			font-size: 12px;
		}
	</style>
</head>
<body>

<div style="text-align:center;">
	<audio controls>
	    <source src="<?php echo $url ?>">
	    Your browser does not support the audio element.
	</audio>
</div>

<p style="text-align:center;">
	<?php echo basename($url) ?>
	&#8212;
	<?php echo $duree ?>
	&#8212;
	<?php echo round(filesize(KROOT.$url) / 1024 / 1024, 2); ?> Mo
</p>

</body></html>