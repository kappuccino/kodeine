<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	$id_album	= ($_REQUEST['id_album'] > 0) ? $_REQUEST['id_album'] : 0;
	$id_type    = $_REQUEST['id_type'];

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

				$app->go("gallery?id_type=".$_REQUEST['id_type'].'#album/'.$_REQUEST['id_album']);
			}
		}
	}

	if($id_album > 0){
		$data = $app->apiLoad('content')->contentGet(array(
			'id_content' 	=> $_REQUEST['id_album'],
			'is_album'		=> true,
			'language'		=> 'fr',
			'raw'			=> true
		));

		$title = $data['contentName'];
	}else{
		$title = _('Root');
	}

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/gallery.css" />
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php');
?></header>

<div class="inject-subnav-right hide">
    <li><a href="gallery?id_type=<?php echo $id_type ?>#album/<?php echo $id_album ?>" class="btn btn-small"><?php echo _('Back to album'); ?></a></li>
	<?php if($id_album > 0){ ?>
    <li><a href="gallery-album?id_content=<?php echo $id_album ?>" class="btn btn-small"><?php echo _('Edit'); ?></a></li>
	<?php } ?>
</div>

<div class="app"><div class="wrapper" id="import">

	<?php
		if($message != NULL){
			list($class, $message) = $app->helperMessage($message);
			echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
		}
	?>

	<h1 style="float:none;"><?php printf(_('Current album <i>%s</i>'), $title) ?></h1>

	<section>
		<form action="gallery-import" method="post">

			<input type="hidden" name="action"		value="1" />
			<input type="hidden" name="language"	value="fr" />
			<input type="hidden" name="id_type"		value="<?php echo $id_type ?>" />
			<input type="hidden" name="id_album"	value="<?php echo $id_album ?>" />

			<h2><?php echo _('Import item from /media folder') ?></h2>
			<input type="text" name="importFolder" id="importFolder" style="width:80%" />

			<div class="btn-group" style="clear:left; margin-top:20px;">
				<a onclick="mediaOpen('line', 'importFolder')" class="btn"><?php echo _('Pick'); ?></a>
				<a onclick="$(this).parents('form').submit();" class="btn"><?php echo _('Validate') ?></a>
			</div>

		</form>
    </section>

    <section>
        <form action="gallery-import" method="post" enctype="multipart/form-data">

            <h2><?php echo _('Upload to this album'); ?></h2>

            <input id="file_upload" name="file_upload" type="file" multiple="true" />
            <div id="queue" class="clearfix"></div>

        </form>
    </section>

    <section class="mass">
        <form action="gallery-import" method="post" id="mass">

            <input type="hidden" name="action"		value="1" />
            <input type="hidden" name="language"	value="fr" />
            <input type="hidden" name="id_type"		value="<?php echo $type['id_type'] ?>" />
            <input type="hidden" name="id_album"	value="<?php echo ($_REQUEST['id_album']) ? $_REQUEST['id_album'] : 0 ?>" />


            <h2><?php echo _('Create folder and sub folders') ?></h2>

            <input type="text" name="discoverFolder" id="discoverFolder" value="<?php echo $_GET['sync'] ?>" style="width:80%" />

	        <br />

            <input type="radio" name="sel" value="inside" checked="checked" />
	        <?php echo _('Insert data into this album'); ?>

	        <br />

	        <input type="radio" name="sel" value="create" />
	        <?php echo _('Create a new ablum  into this album'); ?>

            <br />

            <div class="btn-group" style="clear:left; margin-top:20px;">
		        <a onclick="mediaOpen('line', 'discoverFolder')" class="btn"><?php echo _('Pick') ?></a>
	            <a onclick="discoverInit();" class="btn"><?php echo _('Validate') ?></a>
	        </div>

            <div class="progress" id="progress" style="width: 82%; margin: 10px 0 10px 0;">
                <div class="bar" style="width: 5%;"></div>
            </div>

            <div id="log"></div>


        </form>
    </section>

</div>


<?php include(COREINC.'/end.php'); ?>
<script type="text/javascript" src="../media/ui/_uploadifive/jquery.uploadifive-v1.0.js"></script>
<script type="text/javascript" src="ui/js/gallery.discover.js"></script>
<script type="text/javascript" src="ui/js/gallery.upload.js"></script>

<script>
    id_album = <?php echo $_REQUEST['id_album'] ?>;
    id_type	 = <?php echo $_REQUEST['id_type'] ?>;
</script>

</body></html>