<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	if($_REQUEST['id_type'] == NULL){
		$type = $app->apiLoad('type')->typeGet(array('profile' => true));
		$goto = ($type[0]['is_gallery']) ? 'gallery' : 'index';
		$app->go($goto.'?id_type='.$type[0]['id_type']);
	}

	$id_type = $_REQUEST['id_type'];
	$type    = $app->apiLoad('type')->typeGet(array('id_type' => $id_type));
	$filter  = $app->filterGet('content'.$id_type);

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
    <link rel="stylesheet" type="text/css" href="ui/css/gallery.css" />
</head>
<body data-id_type="<?php echo $id_type ?>"
      data-model="<?php echo $_GET['model'] ?>"
      data-album="<?php echo $_GET['album'] ?>"
      data-pick="<?php echo isset($_GET['pick']) ? 'true' : 'false' ?>"
      data-display="<?php echo $filter['display'] ?: 'grid' ?>">

<header><?php
	if(!isset($_GET['pick'])) include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php');
?></header>

<div class="inject-subnav-right hide">
    <li>
        <div class="btn-group">
            <a class="btn btn-mini dropdown-toggle" data-toggle="dropdown" href="#">
	            <i class="icon-list"></i> <?php echo $type['typeName']; ?> <span class="caret"></span>
            </a>

            <ul class="dropdown-menu"><?php
				foreach($app->apiLoad('type')->typeGet(array('profile' => true)) as $e){
					echo '<li class="clearfix">';
					echo '<a href="'.(($e['is_gallery']) ? 'gallery' : 'index').'?id_type='.$e['id_type'].'" class="left">'.$e['typeName'].'</a>';
					echo '<a href="'.(($e['is_gallery']) ? 'gallery-album' : 'data' )."?id_type=".$e['id_type'].'" class="right"><i class="icon icon-plus-sign"></i></a>';
					echo '</li>';
				}
			?></ul>
        </div>
	</li>
    <li><a id="buttonImport" class="btn btn-small"><?php echo _('Import'); ?></a></li>
    <li><a id="buttonEdit" class="btn btn-small"><?php echo _('Edit'); ?></a></li>
    <li><a id="buttonAdd" class="btn btn-small"><?php echo _('New'); ?></a></li>
    <li><a id="buttonUpload" class="btn btn-small"><?php echo _('Upload'); ?></a></li>
</div>

<div id="gallery">

	<div id="galleryAction">
		<a id="toggleGrid"><i class="icon icon-th"></i> <?php echo _('Grid') ?></a>
		<a id="toggleList"><i class="icon icon-list"></i> <?php echo _('List') ?></a>
		<a id="sortAZ"><i class="icon icon-arrow-down"></i> <?php echo _('A-Z') ?></a>
		<a id="sortZA"><i class="icon icon-arrow-up"></i> <?php echo _('Z-A') ?></a>
		<a id="removeAllItems"><i class="icon icon-remove"></i> <?php echo _('Remove all items') ?></a>
	</div>

	<ul id="galleryPath" class="clearfix"></ul>

	<ul id="galleryTree"></ul>

	<ul id="galleryView" data-id_album="<?php echo $_GET['id_album'] ?>" class="clearfix"></ul>

</div>


<?php
	include(__DIR__.'/ui/tpl/gallery.tpl');
	include(COREINC.'/end.php');
?>
<script src="../core/vendor/jqueryui/jqui.dragdrop.js"></script>
<script src="../core/vendor/bootstrap/js/bootstrap-dropdown.js"></script>
<script src="../core/vendor/underscore/underscore-min.js"></script>
<script src="../core/vendor/backbone/backbone-min.js"></script>
<script src="../core/vendor/lazyload/jquery.lazyload.min.js"></script>
<script src="../media/ui/_uploadifive/jquery.uploadifive-v1.0.js"></script>
<script src="../media/ui/_uploadify/jquery.uploadify.js"></script>
<script src="ui/js/gallery.js"></script>

</body></html>