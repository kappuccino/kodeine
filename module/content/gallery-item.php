<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	if($_POST['action']){
		$do = true;

		// Core
		$def['k_content'] = array(
			'is_item'				=> array('value' => 1),
			'contentSee'			=> array('value' => $_POST['contentSee'], 			'zero' => true)
		);
		if(!$app->formValidation($def)) $do = false;

		// Data
		$dat['k_contentdata'] = array(
			'contentName'			=> array('value' => $_POST['contentName'], 			'check' => '.'),
		);
		if(!$app->formValidation($dat)) $do = false;


		// Item
		$itm['k_contentitem'] = array(
			'contentItemUrl'		=> array('value' => $_POST['contentItemUrl'], 		'check' => '.')
		);
		if(file_exists(KROOT.$_POST['contentItemUrl'])){

			list($type, $mime) = explode('/', $app->mediaMimeType(KROOT.$_POST['contentItemUrl']));

			$itm['k_contentitem']['contentItemType']		= array('value' => $type);
			$itm['k_contentitem']['contentItemMime']		= array('value' => $mime);
			$itm['k_contentitem']['contentItemWeight']		= array('value' => filesize(KROOT.$_POST['contentItemUrl']));

			if($type == 'image'){
				$size = getimagesize(KROOT.$_POST['contentItemUrl']);
				$itm['k_contentitem']['contentItemHeight']	= array('value' => $size[1]);
				$itm['k_contentitem']['contentItemWidth']	= array('value' => $size[0]);
			}
		}
		if(!$app->formValidation($itm)) $do = false;

		// Field
		if(!$app->apiLoad('field')->fieldValidation($_POST['field'])) $do = false;

		if($do){

			$result     = $app->apiLoad('content')->contentSet(array(
				'id_type'		=> $_REQUEST['id_type'],
				'language'		=> $_POST['language'],
				'id_content'	=> $_POST['id_content'],
				'debug'			=> false,

				// Les donnees
				'def'			=> $def,
				'data'			=> $dat,
				'field'			=> $_POST['field'],
				'item'			=> $itm,

				// Association
				'id_group'		=> $_POST['id_group'],
				'id_chapter'	=> $_POST['id_chapter'],
				'id_category'	=> $_POST['id_category'],
				'id_search'		=> $_POST['id_search']
			));
			$message    = ($result) ? 'OK: Enregistrement' : 'KO: Erreur APP:<br />'.$app->apiLoad('content')->db_error;

			if($result){
				if(!empty($_POST['goto'])){
					$app->go($_POST['goto']);
				}else{
					$app->go('gallery-item?id_content='.$app->apiLoad('content')->id_content);
				}
			}
		}else{
			$message = 'WA: Validation failed';
		}
	}

	$data   = $app->apiLoad('content')->contentGet(array(
		'debug'	 	 => false,
		'language'	 => 'fr',
		'id_content' => $_REQUEST['id_content'],
		'is_item'	 => true,
		'raw'		 => true
	));

	$type   = $app->apiLoad('type')->typeGet(array(
		'id_type' => $data['id_type']
	));

	$fields = $app->apiLoad('field')->fieldGet(array(
		'itemField'     => true,
		'id_type'		=> $type['id_type'],
		'fieldShowForm'	=> true,
		'debug'			=> false
	));

	$pref   = $app->configGet('content');

	if($data['id_album'] == 0){
		$items = $app->apiLoad('content')->contentGet(array(
			'id_type'		=> $_REQUEST['id_type'],
			'id_album'		=> 0,
			'is_item'		=> true,
			'language'		=> 'fr',
			'raw'			=> true,
			'debug'			=> false
		));
	}else{
		$album = $app->apiLoad('content')->contentGet(array(
			'id_content'	=> $data['id_album'],
			'id_type'		=> $data['id_type'],
			'is_album'		=> true,
			'language'		=> 'fr',
			'raw'			=> true,
			'debug'			=> false
		));

		$items = $app->apiLoad('content')->contentGet(array(
			'id_type'		=> $data['id_type'],
			'id_album'		=> $data['id_album'],
			'is_item'		=> true,
			'language'		=> 'fr',
			'raw'			=> true,
			'debug'			=> false,
			'noLimit'		=> true
		));

		$parent = $app->apiLoad('content')->contentGet(array(
			'debug'		 => false,
			'id_type'	 => $type['id_type'],
			'id_content' => $data['id_album'],
			'is_album'	 => true,
			'raw'		 => true,
		));
	}

	for($i=0; $i<sizeof($items); $i++){
		if($items[$i]['id_content'] == $data['id_content']){
			$previous = $items[$i-1];
			if($previous['id_content'] == NULL) unset($previous);

			$next = $items[$i+1];
			if($next['id_content'] == NULL) unset($next);
		}
	}

	if(is_array($previous)) $leftLink  = "gallery-item?id_content=".$previous['id_content'];
	if(is_array($next))     $rightLink = "gallery-item?id_content=".$next['id_content'];

	function previewMe($app, $item, $value, $link=NULL){

		if($item['contentItemType'] == 'image'){

			$img = $app->mediaUrlData(array(
				'url'	=> $item['contentItemUrl'],
				'mode'	=> 'width',
				'value'	=> $value,
				'cache'	=> true
			));

			$img = '<img src="'.$img['img'].'" height="'.$img['height'].'" width="'.$img['width'].'" class="isimg" />';
		}else
		if($item['contentItemType'] == 'video'){
			$img = '<img src="../media/ui/img/media-file_quicktime.png" />';
		}else
		if($item['contentItemType'] == 'audio'){
			$img = '<img src="../media/ui/img/media-file_audio.png" />';
		}else
		if($item['contentItemType'] == 'application' AND $item['contentItemMime'] == 'pdf'){
			$img = '<img src="../media/img/media-file_pdf.png" />';
		}else{
			$img = NULL;
		}

		if(isset($link)){
			echo "<a href=\"".$link."\">" .  $item['contentName'] . '<br /><br />' . $img . "</a>";
		}else{
			echo $item['contentName'] .'<br /><br /><a href="'.$item['contentItemUrl'].'" target="_blank">'.$img.'</a>';
		}
	}

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
    <link rel="stylesheet" type="text/css" href="ui/css/gallery.css">
    <link rel="stylesheet" type="text/css" href="../media/ui/css/media.css">
    <link rel="stylesheet" type="text/css" href="../content/ui/css/data.css" />
    <link rel="stylesheet" type="text/css" href="../core/vendor/datepicker/css/datepicker.css" />
    <link rel="stylesheet" type="text/css" href="../core/vendor/codemirror/lib/codemirror.css" />
    <link rel="stylesheet" type="text/css" href="../core/vendor/codemirror/theme/monokai.css" />
</head>
<body class="itemed" data-id_type="<?php echo $type['id_type'] ?>"
      data-id_item="<?php echo $data['id_content'] ?>"
      data-model="<?php echo $_GET['model'] ?>"
      data-album="<?php echo $data['id_album'] ?>"
      data-pick="<?php echo isset($_GET['pick']) ? 'true' : 'false' ?>"
      data-display="<?php echo $pref['display'] ?: 'grid' ?>">

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php');
?></header>

<div class="inject-subnav-right hide">
	<li><a onclick="$('#data').submit()" class="btn btn-small btn-success"><?php echo _('Save'); ?></a></li>
</div>

<div id="app" class="clearfix" style="background: #FFF;">

	<div class="wrapper"><?php
		if($message != NULL){
			list($class, $message) = $app->helperMessage($message);
			echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
		}
		?>
		<table width="100%" border="0" cellpadding="0" cellspacing="2" id="gCarrousel">
			<thead>
				<tr>
					<td class="previous">
						<a href="<?php echo ($leftLink  != '') ? $leftLink  : '#'; ?>" id="goToLeft">← <?php echo _('Previous item'); ?></a>
						<a id="saveAndGoToLeft"><?php echo _('(save)'); ?></a>
					</td>
					<td class="current">↑ <?php
						echo !isset($parent)
							? '<a id="goToAlbum" href="gallery?id_type='.$type['id_type'].'">'._('Root').'</a>'
							: '<a id="goToAlbum" href="gallery?id_type='.$type['id_type'].'#album/'.$parent['id_content'].'">Album '.$parent['contentName'].'</a>';
						?></td>
					<td width="25%" class="next">
						<a id="saveAndGoToRight"><?php echo _('(save)'); ?></a>
						<a href="<?php echo ($rightLink != '') ? $rightLink : '#'; ?>" id="goToRight"><?php echo _('Next item'); ?> →</a>
					</td>
				</tr>
			</thead>
		</table>
	</div>

	<form action="gallery-item" method="post" id="data">
	
		<input type="hidden" name="action"      value="1" />
		<input type="hidden" name="goto"        value="" />
		<input type="hidden" name="id_type"     value="<?php echo $data['id_type'] ?>" />
		<input type="hidden" name="id_content"  value="<?php echo $data['id_content'] ?>" id="id_content" />
		<input type="hidden" name="language"    value="fr" />

        <div class="tabset" style="margin-bottom:0px;">
            <div class="view view-tab">
                <ul class="is-sortable field-list">

                    <li class="clearfix form-item">
                        <div class="hand">&nbsp;</div>
                        <div class="toggle toggle-hidden">&nbsp;</div>

						<span class="<?php echo $app->formError('contentName', 'needToBeFilled') ?> clearfix">
							<label><?php echo _('Name'); ?></label>
							<div class="form">
                                <input type="text" name="contentName" id="contentNameField" value="<?php echo $app->formValue($data['contentName'], $_POST['contentName']); ?>" size="100" style="width:99%;" />
                            </div>
						</span>
                    </li>

                    <li class="clearfix form-item">
                        <div class="hand">&nbsp;</div>
                        <div class="toggle toggle-hidden">&nbsp;</div>

						<span class="<?php echo $app->formError('contentItemUrlInput', 'needToBeFilled') ?> clearfix">
							<label><?php echo _('Url'); ?></label>
							<div class="form">
                                <input type="text" id="contentItemUrlInput" name="contentItemUrl" value="<?php echo $app->formValue($data['contentItemUrl'], $_POST['contentItemUrl']); ?>" style="width:90%;" />
								&nbsp;
                                <a class="btn btn-mini" onclick="mediaOpen('line', 'contentItemUrlInput')"><?php echo _('Pick'); ?></a>
                            </div>
						</span>
                    </li>


                    <li class="clearfix form-item">
                        <div class="hand">&nbsp;</div>
                        <div class="toggle toggle-hidden">&nbsp;</div>

						<span class="<?php echo $app->formError('contentName', 'needToBeFilled') ?> clearfix">
							<label><?php echo _('Visibility'); ?></label>
							<div class="form">
                                <input type="checkbox" name="contentSee" id="contentSeeChkbox" value="1" <?php if ($app->formValue($data['contentSee'], $_POST['contentSee'])) echo "checked"; ?> />
                            </div>
						</span>
                    </li>

					<?php
						foreach ($fields as $e) {
							$app->apiLoad('field')->fieldTrace($data, $e, $f);
						}
					?>

                </ul>
            </div>
	    </div>
    </form>

</div>

<?php if($pref['galleryItemRoll'] != '1'){ ?>
	<div id="gCarrouselBottom">
		<div class="wrapper">
			<table width="100%" border="0" cellpadding="0" cellspacing="2" id="gCarrousel">
				<tr valign="top">
					<td class="previous"><?php
						echo ($leftLink != '') ? previewMe($app, $previous, 100, $leftLink) : _('No previous item');
					?></td>
					<td class="current"><?php previewMe($app, $data, 300); ?></td>
					<td class="next"><?php
						echo ($rightLink != '') ? previewMe($app, $next, 100, $rightLink) : _('No more item');
					?></td>
				</tr>
			</table>
		</div>
	</div>
<?php }else{ ?>
	<div id="gallery" class="itemed" style="min-height:100px;">
		<ul id="galleryPath" class="clearfix"></ul>
		<ul id="galleryView" data-id_album="<?php echo $data['id_album'] ?>" class="clearfix"></ul>
	</div>
<?php }

	if($pref['galleryItemRoll'] == '1') include(__DIR__.'/ui/tpl/gallery.tpl');
	include(COREINC.'/end.php');
?>

<script src="../core/vendor/ckeditor/ckeditor.js"></script>
<script src="../core/vendor/ckeditor/adapters/jquery.js"></script>
<!--<script src="../core/vendor/tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
<script src="../core/vendor/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>-->

<script src="ui/js/content.js"></script>
<script src="ui/js/gallery.nav.js"></script>

<?php if($pref['galleryItemRoll'] == '1'){ ?>
<script src="../core/vendor/jqueryui/jqui.dragdrop.js"></script>
<script src="../core/vendor/bootstrap/js/bootstrap-dropdown.js"></script>
<script src="../core/vendor/underscore/underscore-min.js"></script>
<script src="../core/vendor/backbone/backbone-min.js"></script>
<script src="../core/vendor/lazyload/jquery.lazyload.min.js"></script>
<script src="../media/ui/_uploadifive/jquery.uploadifive-v1.0.js"></script>
<script src="../media/ui/_uploadify/jquery.uploadify.js"></script>
<script src="ui/js/gallery.js"></script>
<?php } ?>

<script>
	actionNav		= true;
	language		= '<?php echo $data['language'] ?>';
	doMove  		= false;
	useEditor		= true;
	replace			= [];
	textarea		= "<?php echo @implode(',', $GLOBALS['textarea']) ?>";
	MceStyleFormats = [<?php echo @file_get_contents(USER.'/config/tinymceStyleFormats.php') ?>];
</script>

</body></html>