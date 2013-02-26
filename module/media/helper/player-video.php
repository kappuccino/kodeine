<?php

	require APP.'/plugin/getid3/getid3/getid3.php';
	$url	= $_GET['url'];
	$flv	= substr($url, 0, -4);
	$getID3 = new getID3;
	$info 	= $getID3->analyze(KROOT.$url);
	
	$height	= round($info['video']['resolution_y']);
	$width	= round($info['video']['resolution_x']);
	$duree	= $info['playtime_string'];
	$mime	= $info['mime_type'];

	$poster = str_replace('.'.pathinfo($url, PATHINFO_EXTENSION), '.jpg', $url);

	$style  = (file_exists(KROOT.$poster))
		? "background:#000000 url('".$poster."');"
		: "background:#000000;";

?><!DOCTYPE html>
<html lang="fr">
<head>
	<title></title>
	<link rel="stylesheet" type="text/css" href="../../core/vendor/flowplayer/skin/functional.css" />
	<style>
		body{
			font-family: Arial;
			font-size: 12px;
		}
	</style>
</head>
<body>

<div id="player" style="<?php echo $style ?> margin:0 auto; display:block; width:<?php echo $width ?>px; height:<?php echo $height ?>px">
	<video src="<?php echo $url ?>"></video>
</div>

<p style="text-align:center">
	<?php echo basename($url) ?> &#8212; <?php echo $duree ?> &#8212; <?php echo $width.'x'.$height ?> &#8212; <?php echo round(filesize(KROOT.$url) / 1024 / 1024, 2); ?> Mo
	<a href="poster?url=<?php echo $url ?>">Modifier le Poster</a>
</p>

<script type="text/javascript" src="../../core/vendor/jquery/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="../../core/vendor/flowplayer/flowplayer.min.js"></script>

<script>
	$(function() {
		$('#player').flowplayer({ swf: "../core/vendor/flowplayer/flowplayer.swf" });
		function d(v){
			document.getElementById('log').innerHTML = v;
		}
	});
</script>

</body></html>