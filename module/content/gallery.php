<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	if($_REQUEST['id_type'] == NULL){
		$type = $app->apiLoad('type')->typeGet(array('profile' => true));
		$goto = ($type[0]['is_gallery']) ? 'gallery' : 'index';
		$app->go($goto.'?id_type='.$type[0]['id_type']);
	}

	$id_type = $_REQUEST['id_type'];
	$type    = $app->apiLoad('type')->typeGet(array('id_type' => $id_type));

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
    <link rel="stylesheet" type="text/css" href="ui/css/gallery.css" />
</head>
<body data-id_type="<?php echo $id_type ?>" data-model="<?php echo $_GET['model'] ?>" data-album="<?php echo $_GET['album'] ?>" data-pick="<?php echo isset($_GET['pick']) ? 'true' : 'false' ?>">

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

	<ul id="galleryPath" class="clearfix"></ul>

	<ul id="galleryTree"></ul>

	<ul id="galleryView" data-id_album="<?php echo $_GET['id_album'] ?>" class="clearfix"></ul>

</div>

<!-- ///   ///   ///   ///   ///   ///   ///   ///   ///   ///   ///   ///   ///   ///   ///   ///   ///   ///  ///  -->

<script type="text/template" id="view-album">

    <div class="media">
	    <div class="icone"><img src="ui/img/gallery-folder.png" /></div>
    </div>

	<div class="action">
        <img class="delete" src="../media/ui/img/media-delete.png" />
        <a href="gallery-album?id_content=<%- id_content %>"><img src="../media/ui/img/media-edit.png"></a>
        <img class="visibility <% if(contentSee == '0'){ %>off<% } %>" src="../media/ui/img/media-eye.png" />
		<% if(!gallery.pickMode){ %>
	    <img class="poster <% if(!hasPoster){ %>off<% } %>" src="../media/ui/img/media-star.png" />
		<% } %>
    </div>

    <div class="title"><%- contentName %></div>

</script>

<script type="text/template" id="view-item">

	<div class="media">
	    <div class="icone"></div>
    </div>

    <div class="action">
        <img class="delete" src="../media/ui/img/media-delete.png" />
        <a href="gallery-item?id_content=<%- id_content %>"><img src="../media/ui/img/media-edit.png"></a>
        <img class="visibility <% if(contentSee == '0'){ %>off<% } %>" src="../media/ui/img/media-eye.png" />
	    <% if(contentItemType == 'image' && !gallery.pickMode){ %>
	        <img class="poster <% if(!is_poster){ %>off<% } %>" src="../media/ui/img/media-star.png" />
	    <% } %>
    </div>

    <div class="title"><%- contentName %></div>

</script>

<script type="text/template" id="tree-item">

	<div class="item clearfix">
		<span class="toggle"></span>
		<span class="name"><%- contentName %></span>
	</div>
	<ul></ul>

</script>

<script type="text/template" id="path-item">
	<span class="name"><%- contentName %></span>
</script>

<script type="text/template" id="path-sep">
    <span class="name">/</span>
</script>

<!-- ///   ///   ///   ///   ///   ///   ///   ///   ///   ///   ///   ///   ///   ///   ///   ///   ///   ///  ///  -->

<div id="modal-upload">
	<form id="uploadembed">
		<?php echo _('Drag & drop files here to upload them.'); ?><br />
		<?php echo _('If your browser do not support this features, click "Browse" button.'); ?>

		<input id="file_upload" name="file_upload" type="file" multiple="true">
		<!-- <a class="btn" href="javascript:$('#file_upload').uploadify('upload')">Envoyer les fichiers</a> -->
		<div id="upqueue" class="clearfix"></div>

		<?php echo _('Remote URL'); ?>.<br />
		<div class="wrapp">
			<textarea id="distantUpload" placeholder="<?php echo _('One URL a line'); ?>"></textarea>
		</div>

		<a class="btn btn-small" id="distantDownload"><?php echo _('Download'); ?></a>
		<a class="btn btn-small" id="buttonCloseUpload"><?php echo _('Cancel'); ?></a>

	</form>
</div>

<!-- ///   ///   ///   ///   ///   ///   ///   ///   ///   ///   ///   ///   ///   ///   ///   ///   ///   ///  ///  -->

<?php include(COREINC.'/end.php'); ?>
<script src="../core/vendor/jqueryui/jqui.dragdrop.js"></script>
<script src="../core/vendor/bootstrap/js/bootstrap-dropdown.js"></script>
<script src="../core/vendor/underscore/underscore-min.js"></script>
<script src="../core/vendor/backbone/backbone-min.js"></script>
<script src="../media/ui/_uploadifive/jquery.uploadifive-v1.0.js"></script>
<script src="../media/ui/_uploadify/jquery.uploadify.js"></script>
<script src="ui/js/gallery.js"></script>

</body></html>