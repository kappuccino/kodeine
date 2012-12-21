<?php
	# VIDEO
	require_once(KROOT.'/app/plugin/getid3/getid3/getid3.php');
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
	
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
	<title></title>
	<script src="/admin/core/ui/_jquery/jquery-1.7.2.min.js"></script>
	<link rel="stylesheet" type="text/css" href="/admin/core/ui/_flowplayer5/skin/functional.css" />
	<script type="text/javascript" src="/admin/core/ui/_flowplayer5/flowplayer.min.js"></script>
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
<!-- <a href="<?php echo $url ?>"  id="player">
	<img src="/admin/media/ui/img/play.png" height="100" width="100" style="margin:<?php echo round(($height - 83)/2) ?>px 0px 0px <?php echo round(($width - 83)/2) ?>px;" />
</a> -->

<p style="text-align:center">
	<?php echo basename($url) ?> &#8212; <?php echo $duree ?> &#8212; <?php echo $width.'x'.$height ?> &#8212; <?php echo round(filesize(KROOT.$url) / 1024 / 1024, 2); ?> Mo
	<a href="/admin/media/helper/video-poster?url=<?php echo $url ?>">Modifier le Poster</a>
</p>

<script>
	//flowplayer("player", "/admin/core/ui/_flowplayer/flowplayer-3.2.12.swf");
	$(function() {
		$('#player').flowplayer({ swf: "/admin/core/ui/_flowplayer5/flowplayer.swf" });
		function d(v){
			document.getElementById('log').innerHTML = v;
		}
	});
</script>

</body></html>