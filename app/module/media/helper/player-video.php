<?php
	# VIDEO
	require_once(KROOT.'/app/plugin/getID3/getid3/getid3.php');
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
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<script type="text/javascript" src="<?php echo KPROMPT.'/app/admin/ressource/plugin/flowplayer/flowplayer-3.2.6.min.js' ?>"></script>
	<style>
		body{
			font-family: Arial;
			font-size: 12px;
		}
	</style>
</head>
<body>

<a href="<?php echo $url ?>" style="<?php echo $style ?> margin:0 auto; display:block; width:<?php echo $width ?>px; height:<?php echo $height ?>px" id="player">
	<img src="<?php echo KPROMPT ?>/app/admin/ressource/plugin/flowplayer/play.png" height="83" width="83" style="margin:<?php echo round(($height - 83)/2) ?>px 0px 0px <?php echo round(($width - 83)/2) ?>px;" />
</a>

<p style="text-align:center">
	<?php echo basename($url) ?> &#8212; <?php echo $duree ?> &#8212; <?php echo $width.'x'.$height ?> &#8212; <?php echo round(filesize(KROOT.$url) / 1024 / 1024, 2); ?> Mo
	<a href="media.video.poster.php?url=<?php echo $url ?>">Modifier le Poster</a>
</p>

<script>
	flowplayer("player", "<?php echo KPROMPT ?>/app/admin/ressource/plugin/flowplayer/flowplayer-3.2.7.swf");
	
	function d(v){
		document.getElementById('log').innerHTML = v;
	}
</script>

</body></html>