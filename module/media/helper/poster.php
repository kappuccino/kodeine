<?php

	function video_info ($app, $url_video) {
		try {
			$url_video = KROOT.$url_video;
			if (!file_exists($url_video)) {
				throw new Exception('file don\'t exist !');		
			} else {
				$cmd = '/usr/bin/ffmpeg -i '.$url_video;
				$raw = $app->helperPipeExec($cmd);
				if($raw['return'] == 127) {
					throw new Exception('ffmpeg not found !');
				} else {
					// extraction de la duree de la video
					$duration = explode(':',substr($raw['stderr'],strpos($raw['stderr'],'Duration: ') + 10,11));
					$duration_sec = intval($duration[0])*3600 + intval($duration[1])*60 + intval($duration[2]);
					$info_video['duration'] = $duration_sec;
					
					// extraction des dimensions de la video
					$pos_video = strpos($raw['stderr'],'Video: ');
					$raw_video = explode(', ',substr($raw['stderr'],strpos($raw['stderr'],'Video: ') + 7));
					
					$size = explode('x',$raw_video[2]);
					
					$info_video['height'] = $size[0];
					$info_video['width'] = $size[1];
					
					return $info_video;
				}
			}
		}  catch (Exception $e) {
			$app->pre($e->getMessage());
			return false;
		}
	}
	

	function video_frame ($app, $url_video) {
		try {
		
			$info = video_info($app, $url_video);

			if($info != false){

				if(filesize(KROOT.$url_video) < 419430400) {

					$name 	= basename($url_video);
					$folder	= dirname($url_video)."/".$name.".poster";
					$image	= str_replace('.'.pathinfo($url_video, PATHINFO_EXTENSION), NULL, $name);
					$tmp	= KROOT.$folder.'/processing';

					if(!file_exists(KROOT.$folder)) {
						mkdir(KROOT.$folder, 0755);
					}else{
						$files = $app->fsFile(KROOT.$folder, NULL, FLAT);
						foreach($files as $e){
							unlink($e);
						}
					}

					$cmd  = "/usr/bin/nice -n 19 /usr/bin/ffmpeg -i ".escapeshellarg(KROOT.$url_video).' -sameq -f image2 ';
					
					if($info['duration'] < 20){
						$cmd .= "-r 1 ";
					}else
					if($info['duration'] > 20 && $info['duration'] < 60) {
						$cmd .= "-r 0.5 ";
					}else{
						$cmd .= "-r 0.25 ";
					}
					
					$cmd .= escapeshellarg(KROOT.$folder.'/'.$image."-%05d.jpg"); #" > ".escapeshellarg($tmp);

					#$app->pre($cmd);
					$app->helperPipeExec($cmd);	
									
					return true;
					
				} else {
					throw new Exception('size can\'t be computed !');
				}
				
			}
			
		} catch (Exception $e) {
			$app->pre($e->getMessage());
			return false;
		}
	}

	/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */


	if($_GET['generate']){
		video_frame($app, $_GET['url']);
		exit();
	}


	$url 	= $_GET['url'];
	$poster = str_replace('.'.pathinfo($url, PATHINFO_EXTENSION), '.jpg', $url);
	$folder	= $url.'.poster';

	if(file_exists(KROOT.$folder)){
		chmod(KROOT.$folder, 0755);
		$files = $app->fsFile(KROOT.$folder, '*.jpg', FLAT_NOROOT);
		sort($files);
	}

	if($_GET['pick'] && sizeof($files) > 0){

		$ext	= pathinfo($_GET['url'], PATHINFO_EXTENSION);
		$picked = $folder.'/'.str_replace('.'.$ext, '-'.$_GET['pick'].'.jpg', basename($_GET['url']));
		$end 	= dirname($_GET['url']).'/'.str_replace($ext, 'jpg', basename($_GET['url']));
		
		foreach($files as $e){
			if($e == $picked){
				if(file_exists(KROOT.$end)) unlink(KROOT.$end);
				copy(KROOT.$e, KROOT.$end);
				chmod(KROOT.$end, 0755);
			}			
		}

		$app->go('poster?url='.$_GET['url']);
	}
	
	if(file_exists(KROOT.$poster) && isset($_GET['noPoster'])){
		unlink(KROOT.$poster);
		$app->go('poster?url='.$_GET['url']);
	}

?><!DOCTYPE html> 
<html lang="fr">
<head>
	<title>Extraction des posters de la video</title>
	<style>
		.main{
			margin: 0 auto;
			width: 900px;
			margin-top: 50px;
		}
		.zone{
			width: 900px;
			background: #515151;
			color:#FFFFFF;
			-moz-border-radius:6px; -webkit-border-radius:6px;

		}
			.zone a{
				color:#FFFFFF;
				text-decoration: none;
			}
		.under{
			margin-top: 15px;
			text-align: center;
			background: #FFFFFF;
		}
		.under a{
			color:#515151;
			text-decoration: none;
		}
	</style>
</head>
<body>

<div class="main"><?php

	if(file_exists(KROOT.$poster)){
		$info = $app->mediaInfos(KROOT.$poster);

		if($info['width'] > 900 OR $info['height'] > 900){
			$ratio = ($info['width'] > $info['height'])
				? (880 / $info['width'])
				: (500 / $info['height']);
		}else{
			$ratio = 1;
		}

		$height = round($info['height'] * $ratio);
		$width  = round($info['width']  * $ratio);
		
		echo "<div class=\"zone\" style=\"padding:10px 0px 10px 0px;\">";
			echo "<img src=\"".$poster."\" height=\"".$height."\" width=\"".$width."\" align=\"center\" style=\"display:block; margin:0 auto;\" />";
		echo "</div>";
		
		echo "<div class=\"under\">";
		echo "<a href=\"player-video?url=".$_GET['url']."\">Visionner cette vidéo</a> &nbsp; | &nbsp; ";
		echo "<a href=\"#\" onClick=\"posterRemove()\">Supprimer ce poster</a>";
		echo "</div>";

	}else
	if(sizeof($files) > 0){

		$info = $app->mediaInfos(KROOT.$files[0]);

		if($info['width'] > 900 OR $info['height'] > 900){
			$ratio = ($info['width'] > $info['height'])
				? (900 / $info['width'])
				: (520 / $info['height']);
		}else{
			$ratio = 1;
		}

		$height = round($info['height'] * $ratio);
		$width  = round($info['width']  * $ratio);
		
		echo "<div class=\"zone\">";
		echo "<div style=\"padding:10px 0px 15px 10px;\">Cliquez sur une image pour choisir le poster de la vidéo</div>";

		echo "<div style=\"text-align:center;\">";
		foreach($files as $n => $img){
			chmod(KROOT.$img, 0755);
			$n = substr(substr(strrchr(basename($img), '-'), 1), 0, -4);

			echo "<a href=\"javascript:pick('".$n."');\" style=\"display:block; margin-bottom:10px;\">";
			echo "<img height=\"".$height."\" width=\"".$width."\" src=\"".$img."\" />";
			echo "</a>";
		}
		echo "</div>";
		echo "</div>";

	}else{
		echo "<div class=\"zone\" style=\"height:520px;\">";
			echo "<div style=\"text-align:center; font-size:24px; padding:220px 0px 0px 0px; font-weight:bold;\">";
				echo "Il n'y a pas encore de poster de defini pour cette video.<br /><br />";
				echo "<div id=\"log\"><a href=\"#\" onClick=\"extraction();\">Extraire les images de la vidéo</a></div>";
			echo "</div>";
		echo "</div>";
	}
	

?></div>


<?php include(COREINC.'/end.php'); ?>
<script>

	function pick(n) {
		if(confirm("Garder cette image comme poster de la vidéo ?")) {
		   document.location = 'poster?&url=<?php echo $_GET['url'] ?>&pick='+n;
		}
	}

	function posterRemove(){
		if(confirm("Supprimer ce poster ?")) {
		   document.location = 'poster?&url=<?php echo $_GET['url'] ?>&noPoster';
		}
	}

	function extraction(){
		$('#log').html('Generation en cours des images : NE PAS FERMER CETTE FENETRE');

		$.ajax({
			url: 'poster',
			type: 'get',
			data: {
				'generate': true,
				'url': '<?php echo $_GET['url'] ?>'
			}
		}).done(function(data){
		  	document.location = 'poster?&url=<?php echo $_GET['url'] ?>';
		});
	}

</script>

</body></html>