<?php

	if(!defined('COREINC')) die('Direct access not allowed');
	if(isset($_GET['n'])) die();

    $root = '';
    $last = '';

    if($app->userCan('media.root') != '') {
        $tmproot = $app->userCan('media.root');
        if(file_exists(MEDIA.$tmproot)) {
            $root = rtrim($tmproot, '/');
            $tmp  = explode("/", $root);
            $last = '/'.array_pop($tmp);
            $root = implode('/', $tmp);
        }
    }

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
    <link rel="stylesheet" type="text/css" href="ui/css/media.css" />
	<link rel="stylesheet" type="text/css" href="../core/vendor/jqueryui/jqui.slider.css" />
	<link rel="stylesheet" type="text/css" href="../core/vendor/flowplayer/skin/functional.css" />
</head>
<body class="pictued <?php if($_GET['popMode']) echo 'popMode '; if(isset($_GET['embed'])) echo 'embed '; ?>">

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php');
?></header>

<div id="app">
	<ul id="path" class="clearfix"></ul>
	<ul id="view" class="clearfix"></ul>
</div>

<!-- /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// -->

<div id="fade-wall"></div>

<div id="modal-upload">
	<form id="uploadembed">
		<?php echo _('Drag & drop files here to upload them.'); ?><br />
		<?php echo _('If your browser do not support this features, click "Browse" button.'); ?>

		<input id="file_upload" name="file_upload" type="file" multiple="true">
        <div id="queue" class="clearfix"></div>
		<!-- <a class="btn" href="javascript:$('#file_upload').uploadify('upload')">Envoyer les fichiers</a> -->

		<?php echo _('Remote URL'); ?>.<br />
		<div class="wrapp">
			<textarea id="distantUpload" placeholder="<?php echo _('One URL a line'); ?>"></textarea>
		</div>

		<a class="btn btn-small" id="distantDownload"><?php echo _('Download'); ?></a>

	</form>
</div>

<div id="modal-newdir">
	<p><?php echo _('Create a new folder'); ?></p>
	<input type="text" placeholder="<?php echo _('Folder name...'); ?>" />
	<a class="btn btn-mini" id="newDir"><?php echo _('Validate'); ?></a>
	<a class="btn btn-mini" id="cancelDir"><?php echo _('Cancel'); ?></a>
</div>

<!-- /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// -->

<script type="text/template" id="view-folder">

    <div class="media">
        <div class="icone"><img src="ui/img/media-folder.png" /></div>
    </div>
    <div class="action">
        <img class="delete" src="ui/img/media-delete.png" title="<?php echo _('Delete'); ?>" />
        <span class="lock <% if(is_locked){ %>locked<% } %>"></span>
        <img class="select" src="ui/img/media-select.png" title="<?php echo _('Select'); ?>" />
    </div>
    <div class="title">
	    <input type="text" value="<%- url %>" />
    </div>

</script>

<script type="text/template" id="view-item">

    <div class="media">
        <div class="icone">

	        <% if(kind == 'pdf'){ %>
		        <img src="ui/img/media-file_pdf.png" />
	        <% }else if(kind == 'video'){ %>
                <img src="ui/img/media-file_video.png" />
	        <% }else if(kind == 'audio'){ %>
               <img src="ui/img/media-file_audio.png" />
	        <% } %>

        </div>
    </div>
    <div class="action">
        <img class="delete" src="ui/img/media-delete.png" title="<?php echo _('Delete this file'); ?>" />
        <img class="duplicate" src="ui/img/media-duplicate.png" title="<?php echo _('Duplicate this file'); ?>" />
        <img class="fullsize" src="ui/img/media-wide.png" title="<?php echo _('Fullscreen'); ?>" />
        <img class="meta" src="ui/img/media-rename.png" title="<?php echo _('Add a legend'); ?>" />
        <img class="uri" src="ui/img/media-copy.png" title="<?php echo _("Afficher le chemin d'accès"); ?>" />

		<% if(kind == 'pdf'){ %>
				<img class="pdfCover" src="ui/img/media-flip.png" title="<?php echo _('Generate thumbnail'); ?>" />
	    <% }else
	       if(kind == 'video'){ %>
				<img class="poster" src="ui/img/media-flip.png" title="<?php echo _('Generate thumbnail'); ?>" />
				<img class="playVideo" src="ui/img/media-play.png" title="<?php echo _('Play'); ?>" />
	    <% }else
		   if(kind == 'audio'){ %>
				<img class="playAudio" src="ui/img/media-play.png" title="<?php echo _('Play'); ?>" />
	    <% } %>

        <img class="select" src="ui/img/media-select.png" title="<?php echo _('Select'); ?>" />
    </div>
    <div class="title">
        <input type="text" value="<%- url %>" />
    </div>

</script>

<script type="text/template" id="tree-item">

    <div class="item clearfix">
        <span class="toggle"></span>
        <span class="name"><%- url %></span>
    </div>
    <ul></ul>

</script>

<script type="text/template" id="path-item">
    <span class="name"><%- url %></span>
</script>

<script type="text/template" id="path-sep">
    <span class="name">/</span>
</script>

<script type="text/template" id="modal-meta">

    <div class="data">
        <?php echo _('Title'); ?>
        <div class="wrapp"><textarea name="title"></textarea></div>

        <?php echo _('Description'); ?>
        <div class="wrapp"><textarea name="caption"></textarea></div>

	    <div class="group">
		    <div class="btn-group">
				<a class="btn save"><?php echo _('Save'); ?></a>
				<a class="btn close"><?php echo _('Close'); ?></a>
		    </div>
	    </div>
    </div>

</script>


<!-- /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// -->

<?php include(COREINC.'/end.php'); ?>
<script type="text/javascript" src="../core/vendor/underscore/underscore-min.js"></script>
<script type="text/javascript" src="../core/vendor/backbone/backbone-min.js"></script>
<script type="text/javascript" src="../core/vendor/jqueryui/jqui.dragdrop.js"></script>
<script type="text/javascript" src="../core/vendor/jqueryui/jqui.slider.js"></script>
<script type="text/javascript" src="ui/_uploadifive/jquery.uploadifive-v1.0.js"></script>
<script type="text/javascript" src="ui/_uploadify/jquery.uploadify.js"></script>
<script type="text/javascript" src="ui/js/media.js"></script>

<script>
    phpsid      = "<?php echo session_id() ?>";
    method		= '<?php echo $_REQUEST['method'] ?>';		// Maniere dont l'insertion se fait
    field		= '<?php echo $_REQUEST['field'] ?>';		// field
    myPrompt	= '<?php echo KPROMPT ?>';
    useData		= 'true';
</script>

</body></html>