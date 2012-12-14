<?php
	# VIDEO
	require_once(KROOT.'/app/plugin/getID3/getid3/getid3.php');
	$url	= $_GET['url'];
	$flv	= substr($url, 0, -4);
	$getID3 = new getID3;

	$info 	= $getID3->analyze(KROOT.$url);
	$duree	= $info['playtime_string'];

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
	<title></title>
	<script type="text/javascript" src="<?php echo KPROMPT.'/app/admin/ressource/plugin/flowplayer/flowplayer-3.2.6.min.js' ?>"></script>
	<style>
		body{
			font-family: Arial;
			font-size: 12px;
		}
	</style><
</head>
<body>

	<a href="<?php echo $url ?>" style="background:#000000; margin:0 auto; display:block; width:480px; height:320px" id="player">
		<img src="<?php echo KPROMPT ?>/app/admin/ressource/plugin/flowplayer/play.png" height="83" width="83" style="margin:<?php echo round((320 - 83)/2) ?>px 0px 0px <?php echo round((480 - 83)/2) ?>px;" />
	</a>

<script>
	flowplayer("player", "<?php echo KPROMPT ?>/app/admin/ressource/plugin/flowplayer/flowplayer-3.2.7.swf");
</script>

	<p style="text-align:center;">
		<?php echo basename($url) ?>
		&#8212; 
		<?php echo $duree ?>
		&#8212;
		<?php echo round(filesize(KROOT.$url) / 1024 / 1024, 2); ?> Mo
	</p>


</body></html>