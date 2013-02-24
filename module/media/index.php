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
<body>

<?php if(!$_GET['popMode']){ ?>
	
<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php');
?>
</header>
<div id="app" style="position: absolute; top: 120px;left: 0; right: 0; bottom: 0; margin:0;">
<?php } ?>

<div id="media" class="clearfix">


	<div id="action" class="clearfix" style="display:<?php echo ($_GET['popMode']) ? "block" : "none"; ?>">
		
		<div class="clearfix" style="margin: 4px 0 4px 4px;">
			<div id="myButton">
				<a id="button-folder" class="btn btn-mini"><?php echo _('Refresh'); ?></a>
                <?php if($app->userCan('media.create')) { ?>
				<a id="button-newdir" class="btn btn-mini"><?php echo _('New folder'); ?></a>
                <?php } ?>
                <?php if($app->userCan('media.upload')) { ?>
				<a id="button-upload" class="btn btn-mini"><?php echo _('Upload files'); ?></a>
                <?php } ?>
				<a id="button-pref" class="btn btn-mini"><?php echo _('Setting'); ?></a>
			</div>
			<div id="slider"></div>
			<div id="viewMode">
				<a id="viewModeIcon"><?php echo _('Icon'); ?></a> |
				<a id="viewModeList"><?php echo _('List'); ?></a>
			</div>
		</div>
		
		<div id="panel">
			<iframe id="panelFrame" name="panelFrame" style="background:none;display: none;" src="index?n" height="100%" width="100%" frameborder="0"></iframe>
		</div>
	</div>

	<div id="folderWay" class="clearfix">
		<div class="start"><?php echo _('Current folder'); ?></div>
		<div id="path"></div>
	</div>

	<div id="main_" style="top: <?php echo ($_GET['popMode']) ? "60px" : "28px" ?>">
		<div id="main" class="clearfix"></div>	
	</div>

</div>

<?php if(!$_GET['popMode']) echo '</div>'; ?>

<div id="fade-wall" style="display: none;"></div>
<div id="modal-upload" style="display: none;">
	<div class="uploadcontainer clearfix">
		<form id="uploadembed">
			<div class="left clearfix">
				<div class="caption-up">
					<?php echo _('Drag & drop files here to upload them.'); ?><br /><br />
					<?php echo _('If your browser do not support this features, click "Browse" button.'); ?><br /><br />
					<input id="file_upload" name="file_upload" type="file" multiple="true">
					<!-- <a class="btn" href="javascript:$('#file_upload').uploadify('upload')">Envoyer les fichiers</a> -->
				</div>

				<div class="caption-down">
					<?php echo _('Remote URL'); ?>.<br />
					<a class="btn" onclick="distantDownload();"><?php echo _('Download'); ?></a>
				</div>
			</div>

			<div id="queue" class="clearfix"></div>

			<div class="uploadUrl">
				<textarea id="distantUpload" placeholder="<?php echo _('One URL a line'); ?>"></textarea>
			</div>

		</form>
	</div>
</div>

<div id="modal-meta" style="display:none;"></div>
<div id="modal-pref" style="display:none;"></div>

<div id="modal-newdir" style="display: none">
	<p><?php echo _('Create a new folder'); ?></p>
	<input type="text" placeholder="<?php echo _('Folder name...'); ?>" />
	<a href="#" class="btn btn-mini" id="newdir"><?php echo _('Validate'); ?></a>
	<a href="#" class="btn btn-mini" onclick="modalHideUpload()"><?php echo _('Cancel'); ?></a>
</div>

<?php include(COREINC.'/end.php'); ?>

<script type="text/javascript" src="../core/vendor/jqueryui/jqui.dragdrop.js"></script>
<script type="text/javascript" src="../core/vendor/jqueryui/jqui.slider.js"></script>
<script type="text/javascript" src="ui/_uploadifive/jquery.uploadifive-v1.0.js"></script>
<script type="text/javascript" src="ui/_uploadify/jquery.uploadify.js"></script>
<script type="text/javascript" src="ui/js/media.js"></script>

<script>
    paneltop    = <?php echo ($_REQUEST['popMode']) ? '28' : '62' ?>;
    phpsid      = "<?php echo session_id() ?>";
    method		= '<?php echo $_REQUEST['method'] ?>';		// Maniere dont l'insertion se fait
    field		= '<?php echo $_REQUEST['field'] ?>';		// field
    askType		= '<?php echo $askType ?>';
    myPrompt	= '<?php echo KPROMPT ?>';

    useData		= 'true';
    hash        = getHash();
    url         = (hash == '' || hash == '/') ? '<?php echo ($last != '') ? $last : '/media'; ?>' : hash;
    root        = '<?php echo ($last != '') ? '/media'.$root : ''; ?>';


    $(function(){
        init();
        //	$('#panel').css('top', paneltop);
        //	$('#main_').css('top', paneltop);
        folderNav(url);
    });

</script>

</body></html>