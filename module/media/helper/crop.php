<?php
	$url  = $_GET['url'];
	$url_ = KROOT.$url;
	$ext  = pathinfo($url, PATHINFO_EXTENSION);

	if(!file_exists($url_)) die('File <b>'.$url.'</b> is not found');

	$preview = $app->mediaUrlData(array(
		'url'	=> $url,
		'mode'	=> 'width',
		'value'	=> 900,
		'debug'	=> false
	));

	# Rotate
	#
	if($_GET['direction'] != ''){

		$end		= dirname($url_).'/'.str_replace('.'.$ext, '__'.uniqid().'__.'.$ext, basename($url));
		$degrees	= ($_GET['direction'] == 'left') ? 90 : -90;

		$s = GetImageSize($url_);
		list($orgWidth, $orgHeight) = $s;

		if($s['mime'] == 'image/png'){
			$isPNG	= true;
			$src	= imagecreatefrompng($url_);
		}else
		if($s['mime'] == 'image/gif'){
			$isGIF	= true;
			$src	= imagecreatefromgif($url_);
		}else
		if($s['mime'] == 'image/jpeg'){
			$isJPG	= true;
			$src	= imagecreatefromjpeg($url_);
		}
		
		$dst = imagerotate($src, $degrees, 0);

		umask(0);
		if($isJPG){
			imagejpeg($dst, $end, 100);
		}else
		if($isPNG){
			imagepng($dst, $end);
		}else
		if($isGIF){
			imagegif($dst, $end);
		}

		// Pour toutes les cache en BDD, verifier si les caches sont a jour
		$old = $app->dbMulti("SELECT * FROM k_cachemedia WHERE cacheSource='".$url."'");
		if(sizeof($old) > 0){
			foreach($old as $e){
				$del[] = $e['id_cache'];
				if(file_exists(KROOT.$e['cacheUrl'])) unlink(KROOT.$e['cacheUrl']);
			}
			if(sizeof($del) > 0){
				$app->dbQuery("DELETE FROM k_cachemedia WHERE id_cache IN(".implode(',', $del).")");
			}
		}

		unlink($url_);
		rename($end, $url_);
		touch($url_);
		
		header("Location: crop?url=".$url);

	}else

	# Crop
	#
	if($_GET['a'] != NULL && intval($_GET['height']) > 0 && intval($_GET['width']) > 0){
		$end	= dirname($url_).'/'.uniqid().'.'.$ext;

		$a		= explode(',', $_GET['a']);	
		$b		= explode(',', $_GET['b']);

		$a_[0]	= round($a[0] * (1 / $preview['ratio']));
		$a_[1]	= round($a[1] * (1 / $preview['ratio']));
		$b_[0]	= round($b[0] * (1 / $preview['ratio']));
		$b_[1]	= round($b[1] * (1 / $preview['ratio']));

		$newHeight	= round($_GET['height'] * (1 / $preview['ratio']));
		$newWidth	= round($_GET['width']  * (1 / $preview['ratio']));

		$s = GetImageSize($url_);
		list($orgWidth, $orgHeight) = $s;

		if($s['mime'] == 'image/png'){
			$isPNG	= true;
			$src	= imagecreatefrompng($url_);
		}else
		if($s['mime'] == 'image/gif'){
			$isGIF	= true;
			$src	= imagecreatefromgif($url_);
		}else
		if($s['mime'] == 'image/jpeg'){
			$isJPG	= true;
			$src	= imagecreatefromjpeg($url_);
		}

 		$dst = imagecreatetruecolor($newWidth, $newHeight);

		if($isPNG){
			imagealphablending($dst, false);
			imagesavealpha($dst, true);
		}

	 	imagecopyresampled(
	 		$dst, $src,
		 	0, 0, $a_[0], $a_[1],
	 		$newWidth, $newHeight,
	 		$newWidth, $newHeight
	 	);

		umask(0);
		if($isJPG){
			imagejpeg($dst, $end, 85);
		}else
		if($isPNG){
			imagepng($dst, $end);
		}else
		if($isGIF){
			imagegif($dst, $end);
		}
		
		header("Location: crop?url=".str_replace(KROOT, NULL, $end));
	}

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
	<link rel="stylesheet" type="text/css" href="../ui/_imgareaselect/css/imgareaselect-default.css" />
	<link rel="stylesheet" type="text/css" href="../ui/css/crop.css" />
</head>

<header><?php
	#include(COREINC.'/top.php');
	#include(dirname(__DIR__).'/ui/menu.php');
?></header>

<body>
<div class="media">
	
	<div class="action clearfix">
		<div class="l">
			<a id="crop"><img src="../ui/img/media-crop-blue.png" height="16" width="16" /></a>

			<div id="fields">
				<a id="save-crop">Sauver</a>
				
				<div id="field-bar">
					<div>X</div>		<input type="text" size="2" id="fx" onkeyup="up()" />
					<div>Y</div>		<input type="text" size="2" id="fy" onkeyup="up()" />
					<div>Hauteur</div>	<input type="text" size="3" id="fh" onkeyup="up()" />
					<div>Largeur</div>	<input type="text" size="3" id="fw" onkeyup="up()" />
				</div>

				<a onclick="up()" id="valider">Valider</a>
			</div>
		</div>

		<div class="r">
			<a id="rotate-right"><img src="../ui/img/media-rotate-right.png" height="16" width="16" /></a>
			<a id="rotate-left"><img src="../ui/img/media-rotate-left.png" height="16" width="16" /></a>
		</div>
	</div>

	<div class="img">
		<img src="<?php echo $preview['img']; echo preg_match("#\?#", $preview['img']) ? '&'.time() : '?'.time();  ?>" height="<?php echo $preview['height'] ?>" width="<?php echo $preview['width'] ?>" id="photo" />
	</div>

	<p style="text-align:center">
		Taille d'orgine <?php echo $preview['source']['height'].' x '.$preview['source']['width'] ?> px
	</p>

</div>

<?php include(COREINC.'/end.php'); ?>
<script type="text/javascript" src="../ui/_imgareaselect/scripts/jquery.imgareaselect.pack.js"></script>
<script type="text/javascript">

	img = null;
	$(document).ready(function () {

	    $('a#crop').click(function(){
	
		    $('img#photo').imgAreaSelect({
		        handles: true,
				keys: { arrows:10, ctrl:10, shift:'resize' },
			    onSelectChange: function (i, s) {
			    	img = i;
			    	$('#fx').val(s.x1);
			    	$('#fy').val(s.y1);
			    	$('#fw').val(s.width);
			    	$('#fh').val(s.height);
			    }
			});

		    $('a#save-crop, #fields').css('display', 'block');
		    
		    $('#crop').css('visibility', 'hidden');

		    $('a#save-crop').click(function(){
				var i = $('#photo').imgAreaSelect({ instance: true });
				var s = i.getSelection();
				document.location='crop?url=<?php echo $url ?>&a='+s.x1+','+s.y1+'&b='+s.x2+','+s.y2+'&height='+s.height+'&width='+s.width;
		    })
		});
		
		$('a#rotate-right').click(function(){
			document.location='crop?url=<?php echo $url ?>&direction=left';
		});

		$('a#rotate-left').click(function(){
			document.location='crop?url=<?php echo $url ?>&direction=right';
		});
	    
	});
	
	function up(){

		var ias = $('img#photo').imgAreaSelect({ instance: true });
		var r	= ($('#fx').val() + $('#fw').val());
	
		var w	= parseInt($('#fx').val()) + parseInt($('#fw').val());
		var h	= parseInt($('#fy').val()) + parseInt($('#fh').val());

		ias.setSelection(
			parseInt($('#fx').val()),
			parseInt($('#fy').val()),

			w, h,

			true
		);

		ias.setOptions({ show: true });
		ias.update();
	}
</script>

</body></html>