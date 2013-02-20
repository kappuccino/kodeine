<?php

	require(dirname(dirname(dirname(dirname(__FILE__)))).'/api/core.admin.php');
	$app = new coreAdmin();

	$src  = $_GET['src'];
	$src_ = KROOT.$src;

?><!DOCTYPE html>
<html lang="fr">
<head>
	<title></title>
	<style>
		body{
			padding: 0px;
			margin: 0px;
			font-family: Arial;
			font-size: 12px;
			background: #000000;
		}
		.item{
			padding:5px;
			border-right:1px solid rgb(120,120,120);
			float: left;
		}
			.item .in{
				font-size: 10px;
				font-family: Arial;
				width: 40px;
				text-align: center;
				border: none;
				padding: 3px 2px 3px 2px;
				margin-left: 5px;
			}

		#end{
			left:0px;
			right:0px;
			bottom:0px;
			height: 33px;
			position:absolute;
			border-top:1px solid #666666;
		}

		#bottom{
			top:43px;
			left:0px;
			right:0px;
			bottom:43px;
			position:absolute;
		}
		
		#legend{
			padding:10px 5px 0px 0px;
			float: right;
		}
		#close{
			float: right;
			margin: 10px 10px 0px 0px;
			cursor: pointer;
		}
	</style>
</head>
<body>
<div style="height:32px; background:rgb(90,90,90); color:#FFFFFF; border-bottom:1px solid #666666;">
	<div class="item">
		Largeur <input type="text" id="inWidth" class="in" />
		<input type="button" id="updWidth" value="Fixer" />
	</div>
	<div class="item">
		Hauteur <input type="text" id="inHeight" class="in" />
		<input type="button" id="updHeight" value="Fixer" />
	</div>
	<div class="item">
		Carrée <input type="text" id="inSquare" class="in" />
		<input type="button" id="updSquare" value="Afficher" />
	</div>
	<div class="item">
		Portion <input type="text" id="inCropW" class="in" /><input type="text" id="inCropH" class="in" />
		<input type="button" id="updCrop" value="Afficher" />
	</div>
	<div class="item">
		<input type="button" id="cancel" value="Annuler" />
	</div>
	<a id="close">Fermer</a>
</div>

<div id="bottom"></div>

<div style="height:32px; background:rgb(90,90,90); color:#FFFFFF;" id="end">
	<div class="item">
		Enregistrer <input type="text" id="saveName" class="in" style="width:300px;" />
		<input type="button" id="save" value="Enregister cette image" disabled="disabled" />
	</div>
	<div id="legend"></div>
</div>

<script>

	function loadMe(url, w){

		if(w != ''){
			b = url.replace(/^.*[\/\\]/g, '');
			b =   b.replace('?noCache',   '');
	
			$('saveName').value = w+b;
			$('save').disabled = false;
		}

		if($('stage')) $('stage').destroy();

		var img = new Asset.image(url, {
			'id' : 'myImage',
			'onload' : function(){
	
				h = $('bottom').getStyle('height').toInt();
				w = $('bottom').getStyle('width').toInt();
	
				ratioA = (this.height <= h) ? 1 : h / this.height;
				ratioB = (this.width  <= w) ? 1 : w / this.width;
				ratio  = (ratioA > ratioB) ? ratioB : ratioA;
				

				width  = this.width;
				height = this.height;

				this.width  = this.width  * ratio;
				this.height = this.height * ratio;
				
				stage = new Element('div', {
					'id' : 'stage',
					'styles' : {
						'height'		: this.height,
						'width'			: this.width,
						'position' 		: 'absolute',
						'margin-top'	: ((h - this.height) / 2),
						'margin-left'	: ((w - this.width) / 2)
					}
				}).inject('bottom');

				this.inject(stage);

				more = (ratio < 1) ? ' (Affichage réduit : '+this.height+'x'+this.width+')' : '';
				$('legend').set('html', 'Image actuelle : '+height+'x'+width+more);
			}
		});
	}

	<?php $d = $app->mediaInfos($src); ?>
	loadMe('<?php echo $src ?>', '');
	$('inWidth').value  = '<?php echo $d['width'] ?>';
	$('inHeight').value = '<?php echo $d['height'] ?>';

	
	$('updWidth').addEvent('click', function(){
		loadMe('/w:'+$('inWidth').value+'<?php echo $src ?>?noCache', 'w'+$('inWidth').value);
	});

	$('updHeight').addEvent('click', function(){
		loadMe('/h:'+$('inHeight').value+'<?php echo $src ?>?noCache', 'h'+$('inHeight').value);
	});
	
	$('updSquare').addEvent('click', function(){
		loadMe('/s:'+$('inSquare').value+'<?php echo $src ?>?noCache', 's'+$('inSquare').value);
	});
	
	$('updCrop').addEvent('click', function(){
		loadMe('/c:'+$('inCropW').value+','+$('inCropH').value+'<?php echo $src ?>?noCache', 'c'+$('inCropW').value+'-'+$('inCropH').value);
	});

	$('close').addEvent('click', function(){
		parent.sizeKill();		
	});

	$('save').addEvent('click', function(){

		var remote = new Request.JSON({
			url: 'media.action.php',
	  		headers: {'contentType': 'text/html'},
			evalScripts: true,
			onComplete:function(data){
				if(data.success){
					alert("Image enregistrée");
				}else{
					alert("Error");
				}
			}
		}).get({
			'action'	: 'resize',
			'name'		: $('saveName').value,
			'src'		: $('myImage').src
		});

	});

	$('cancel').addEvent('click', function(){
		document.location = 'media.size.php?src=<?php echo urlencode($_GET['src']) ?>';
	});

</script>

</body></html>