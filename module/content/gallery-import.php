<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	if($_POST['importFolder'] != ''){

		$folder = KROOT.$_POST['importFolder'];

		if(preg_match("#^http(s)?:#", $_POST['importFolder'])){
			$isEmbeded	= true;
			$embedUrl	= trim($_POST['importFolder']); 
			$external	= true;
		}else{
			$external	= false;
		}

		if(file_exists($folder) or $isEmbeded){
	
			if($isEmbeded){
				$files = array($embedUrl);
			}else{
				$files = is_file($folder)
					? array(str_replace(KROOT, NULL, $folder))
					: @$app->fsFile($folder, '', 'FLAT_NOROOT_NOHIDDEN');
			}

			if(sizeof($files) > 0){
				foreach($files as $e){

					$name = basename($e);
					$indb = $app->dbOne("SELECT * FROM k_contentitem WHERE id_album=".$_REQUEST['id_album']." AND contentItemUrl='".$e."'");

					$opt  = array(
						'debug'		=> false,
						'id_type'	=> $_REQUEST['id_type'],
						'language'	=> 'fr',

						'def' 		=> array('k_content' => array(
							'id_user'			=> array('value' => $app->user['id_user']),
							'is_item'			=> array('value' => '1'),
							'contentSee'		=> array('value' => '1')
						)),

						'data'		=> array('k_contentdata' => array(
							'contentName' 		=> array('value' => $name)
						)),

						'item'		=> array('k_contentitem' => array(
							'id_album'			=> array('value' => $_REQUEST['id_album']),
							'contentItemUrl'	=> array('value' => $e),
						#	'contentItemType' 	=> array('value' => $type),
						#	'contentItemPos'	=> array('value' => ($last + 1))
						))
					);

					if($indb['id_content'] > 0){
						$opt['id_content'] = $indb['id_content'];
					}else{
						$last = $app->dbOne("SELECT MAX(contentItemPos) AS la FROM k_contentitem WHERE id_album=".$_REQUEST['id_album']);
						$opt['item']['k_contentitem']['contentItemPos']	= array('value' => ($last['la'] + 1));
					}

					if($isEmbeded){
						$og		= $app->apiLoad('openGraph')->openGraphVideo($e);
						$weight = 0;

						$opt['data']['k_contentdata']['contentName']['value'] = $og[0]['og:title'];

						list($type, $mime) = explode('/', $og[0]['og:video:type']);

						if($og[0]['og:video:height'] > 0){
							$height = $og[0]['og:video:height'];
							$width  = $og[0]['og:video:width'];
						}else
						if($og[0]['og:image:height'] > 0){
							$height = $og[0]['og:image:height'];
							$width  = $og[0]['og:image:width'];
						}

					}else{
						list($type, $mime) = explode('/', $app->mediaMimeType($e));

						if($type == 'image'){
							$size = getimagesize(KROOT.$e);
							$height = $size[1];
							$width  = $size[0];
							$weight = filesize(KROOT.$e);
						}
					}

					$opt['item']['k_contentitem']['contentItemExternal']	= array('value' => $external);
					$opt['item']['k_contentitem']['contentItemHeight']		= array('value' => $height);
					$opt['item']['k_contentitem']['contentItemWidth']		= array('value' => $width);
					$opt['item']['k_contentitem']['contentItemType']		= array('value' => $type);
					$opt['item']['k_contentitem']['contentItemMime']		= array('value' => $mime);
					$opt['item']['k_contentitem']['contentItemWeight']		= array('value' => $weight);

					$result = $app->apiLoad('content')->contentSet($opt);
					unset($height, $width, $type, $mime, $weight, $name);
				}

				header("Location: content.gallery.index.php?id_type=".$_REQUEST['id_type'].'#'.$_REQUEST['id_album']);
			}
		}
	}

	if($_REQUEST['id_album'] > 0){
	
		$data = $app->apiLoad('content')->contentGet(array(
			'id_content' 	=> $_REQUEST['id_album'],
			'is_album'		=> true,
			'language'		=> 'fr',
			'debug'	 		=> false,
			'raw'			=> true
		));
	}


	# Path Way
	$id_album	= $_GET['id_album'];
	$type		= $app->apiLoad('type')->typeGet(array(
		'id_type'	=> $_REQUEST['id_type'],
		'debug'		=> false
	)); 

	if($id_album > 0){
		$touched = ($id_album == '0');
		$loop	 = 0;

		while(!$touched && $loop < 50){
			$me = $app->apiLoad('content')->contentGet(array(
				'raw'			=> true,
				'id_type'		=> $type['id_type'],
				'id_content'	=> $id_album,
				'is_album'		=> true,
				'debug'			=> false
			));

			$pathway[]	= "<a href=\"content.gallery.index.php?id_type=".$me['id_type']."#".$me['id_content']."\">".$me['contentName']."</a>";
			$id_album	= $me['id_album'];			

			if($me['id_album'] == '0'){
				$touched	= true;
				$loop		= 60;
			}
			
			$loop++;
		}
	}

	$pathway[] 	= "/<a href=\"content.gallery.index.php?id_type=".$type['id_type']."\">Racine</a>";
	$title		= implode('/', array_reverse($pathway)).$title;

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(ADMINUI.'/head.php'); ?>
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/gallery.import.css" />
</head>
<body>
<div id="pathway">
	<a href="core.panel.php">Admin</a> &raquo;
	<a href="content.index.php">Contenu</a> &raquo;
	<a href="content.gallery.index.php?id_type=<?php echo $type['id_type'] ?>"><?php echo $type['typeName'] ?></a> &raquo;
	<?php echo $title ?> &raquo; Import
</div>

<?php include('ressource/ui/menu.content.php'); ?>

<div class="app">
<?php
	if($message != NULL){
		list($class, $message) = $app->helperMessage($message);
		echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
	}
?>

<form action="content.gallery.import.php" method="post" id="data1">

	<input type="hidden" name="action"		value="1" />
	<input type="hidden" name="id_type"		value="<?php echo $type['id_type'] ?>" />
	<input type="hidden" name="language"	value="fr" />
	<input type="hidden" name="id_album"	value="<?php echo ($_REQUEST['id_album']) ? $_REQUEST['id_album'] : 0 ?>" />

	<h2>Importer une image ou un dossier</h2>
	<input type="text" name="importFolder" id="importFolder" size="70" />
	<a href="#" onclick="mediaOpen('line', 'importFolder')">choisir</a>
	<a href="javascript:$('data1').submit()" class="button rButton">Valider</a>

</form>


<br /><br /><br />


<form action="content.gallery.import.php" method="post" id="data2">

	<input type="hidden" name="action"		value="1" />
	<input type="hidden" name="id_type"		value="<?php echo $type['id_type'] ?>" />
	<input type="hidden" name="language"	value="fr" />
	<input type="hidden" name="id_album"	value="<?php echo ($_REQUEST['id_album']) ? $_REQUEST['id_album'] : 0 ?>" />


	<h2>Générer une arborescence</h2>
	<p>
		<input type="radio" id="inside" name="sel" value="inside" checked="checked" /> Insérer les elements dans le dossier <?php echo strip_tags($title) ?><br />
		<input type="radio" id="create" name="sel" value="create" /> Créer un album dans <?php echo strip_tags($title) ?> porant le nom du dossier choisi
	</p>
	<input type="text" name="discoverFolder" id="discoverFolder" size="70" value="<?php echo $_GET['sync'] ?>" />
	<a href="#" onclick="mediaOpen('line', 'discoverFolder')">choisir</a>
	<a href="#" onclick="discoverInit();" class="button rButton">Valider</a>

	<div id="log"></div>
</form>

<br /><br /><br />

<form action="ressource/lib/gallery.upaction.php?id_album=<?php echo $_REQUEST['id_album'] ?>" method="post" enctype="multipart/form-data" id="form-demo">

	<h2>Envoyer des fichiers</h2>

	<fieldset id="demo-fallback">
		<legend>File Upload</legend>
		<p>This form is just an example fallback for the unobtrusive behaviour of FancyUpload. If this part is not changed, something must be wrong with your code.</p>
		<label for="demo-photoupload">
			Upload a Photo:
			<input type="file" name="Filedata" />
			<input type="submit" />
		</label>
	</fieldset>

	<div id="demo-status" class="hide">

		<a href="#" id="demo-browse">Parcourir</a> -
		<a href="#" id="demo-clear">Nettoyer la liste</a> -
		<a href="#" id="demo-upload">Envoyer</a> - 
		<a href="content.gallery.index.php?id_type=<?php echo $_GET['id_type'] ?>#<?php echo $_GET['id_album'] ?>">Afficher l'album</a>

		<div style="margin-top:10px;">
			<strong class="overall-title"></strong><br />
			<img src="ressource/plugin/fancyupload/assets/bar.gif" class="progress overall-progress" />
		</div>

		<div style="margin-top:10px;">
			<strong class="current-title"></strong><br />
			<img src="ressource/plugin/fancyupload/assets/bar.gif" class="progress current-progress" />
		</div>
		<div class="current-text">&nbsp;</div>

	</div>

	<div id="demo-list-holder">
		<ul id="demo-list"></ul>
	</div>

</form>






















<script type="text/javascript" src="ui/js/gallery.discover.js"></script>
<script>
	id_album	= <?php echo $_GET['id_album'] ?>;
	id_type		= <?php echo $_GET['id_type'] ?>;
</script>




<script type="text/javascript" src="ressource/plugin/fancyupload/source/Swiff.Uploader.js"></script>
<script type="text/javascript" src="ressource/plugin/fancyupload/source/Fx.ProgressBar.js"></script>
<script type="text/javascript" src="ressource/plugin/fancyupload/source/FancyUpload2.js"></script>

<script>
document.addEvent('domready', function(){


	up = new FancyUpload2($('demo-status'), $('demo-list'), {
		verbose: true,					// we console.log infos, remove that in production!!
		url: $('form-demo').action,		// url is read from the form, so you just have to change one place
		
		
		path: 'ressource/plugin/fancyupload/source/Swiff.Uploader.swf',	// path to the SWF file
		
		// remove that line to select all files, or edit it, add more items
		typeFilter: {
		//	'Images (*.jpg, *.jpeg, *.gif, *.png)': '*.jpg; *.jpeg; *.gif; *.png'
		},
		
		// this is our browse button, *target* is overlayed with the Flash movie
		target: 'demo-browse',
		
		// graceful degradation, onLoad is only called if all went well with Flash
		onLoad: function() {
			$('demo-status').removeClass('hide'); 	// we show the actual UI
			$('demo-fallback').destroy(); 			// ... and hide the plain form
			
			// We relay the interactions with the overlayed flash to the link
			this.target.addEvents({
				click: function() {
					return false;
				},
				mouseenter: function() {
					this.addClass('hover');
				},
				mouseleave: function() {
					this.removeClass('hover');
					this.blur();
				},
				mousedown: function() {
					this.focus();
				}
			});

			// Interactions for the 2 other buttons

			$('demo-clear').addEvent('click', function() {
				up.remove(); // remove all files
				return false;
			});

			$('demo-upload').addEvent('click', function() {
				up.start(); // start upload
				return false;
			});
		},
		
		// Edit the following lines, it is your custom event handling
		
		/**
		* Is called when files were not added, "files" is an array of invalid File classes.
		* 
		* This example creates a list of error elements directly in the file list, which
		* hide on click.
		**/
		onSelectFail: function(files) {
			files.each(function(file) {
				new Element('li', {
					'class': 'validation-error',
					html: file.validationErrorMessage || file.validationError,
					title: MooTools.lang.get('FancyUpload', 'removeTitle'),
					events: {
						click: function() {
							this.destroy();
						}
					}
				}).inject(this.list, 'top');
			}, this);
		},
		
		/**
		* This one was directly in FancyUpload2 before, the event makes it
		* easier for you, to add your own response handling (you probably want
		* to send something else than JSON or different items).
		**/
		onFileSuccess: function(file, response) {
			var json = new Hash(JSON.decode(response, true) || {});

			if(json.get('status') == '1') {
				file.remove();
			//	file.element.addClass('file-success');
			//	file.info.set('html', 'Fichier en ligne');
			}else{
				file.element.addClass('file-failed');
				file.info.set('html', 'Erreur (' + (json.get('error') ? (json.get('error') + ' #' + json.get('code')) : response));
			}
		},
		
		/**
		* onFail is called when the Flash movie got bashed by some browser plugin
		* like Adblock or Flashblock.
		**/
		onFail: function(error) {
			switch (error) {
				case 'hidden': // works after enabling the movie and clicking refresh
					alert('To enable the embedded uploader, unblock it in your browser and refresh (see Adblock).');
					break;
				case 'blocked': // This no *full* fail, it works after the user clicks the button
					alert('To enable the embedded uploader, enable the blocked Flash movie (see Flashblock).');
					break;
				case 'empty': // Oh oh, wrong path
					alert('A required file was not found, please be patient and we fix this.');
					break;
				case 'flash': // no flash 9+ :(
					alert('To enable the embedded uploader, install the latest Adobe Flash plugin.')
			}
		},

		onComplete:function(){	
		}

	});
	
});
</script>

</div></body></html>