<?php

	if(!defined('COREINC')) die('Direct access not allowed');
	$i18n = $app->apiLoad('coreI18n')->languageSet('fr')->load('media');
	if(isset($_GET['off'])) die('-*-');

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
	<link rel="stylesheet" type="text/css" href="/admin/core/vendor/jqueryui/jqui.slider.css" />
	<link rel="stylesheet" type="text/css" href="/admin/media/ui/_uploadifive/uploadifive.css">
</head>
<body>
<div id="media" class="clearfix">

	<div id="action" class="clearfix">

		<div class="clearfix" style="margin: 4px 0 4px 4px;">
			<div id="myButton">
				<a id="button-folder" class="btn btn-mini">Actualiser</a>
                <?php if($app->userCan('media.create')) { ?>
				<a id="button-newdir" class="btn btn-mini">Nouveau dossier</a>
                <?php } ?>
                <?php if($app->userCan('media.upload')) { ?>
				<a id="button-upload" class="btn btn-mini">Envoyer des fichiers</a>
                <?php } ?>
				<!--<a id="button-maintenance">Maintenance</a>-->
				<a id="button-hidepanel" class="btn btn-mini">Masquer la zone</a>
				<a id="button-pref" class="btn btn-mini">Préférence</a>
			</div>
			<div id="slider">
				<!-- <div id="sliderLine">
					<div id="sliderPlot"><img src="<?php echo KPROMPT ?>/app/admin/ressource/img/media-slider-small-knob.png" height="12" width="12" /></div>
				</div> -->
			</div>
			<div id="viewMode">
				<a id="viewModeIcon">Icone</a> |
				<a id="viewModeList">Liste</a>
			</div>
		</div>
	
		<div id="panel">
			<iframe id="panelFrame" name="panelFrame" style="background:none;" src="index?n" height="100%" width="100%" frameborder="0" scrolling="no"></iframe>
		</div>
	</div>

	<div id="main_" style="position:absolute;">
		<div id="folderWay" class="clearfix">
			<div class="start">Dossier ouvert</div>
			<div id="path"></div>
		</div>

		<div id="main" class="clearfix" style="margin-top: 28px;"></div>	
	</div>
</div>
<?php include(COREINC.'/end.php'); ?>
<script type="text/javascript" src="/admin/media/ui/_uploadifive/jquery.uploadifive-v1.0.js"></script>
<script type="text/javascript" src="/admin/media/ui/_uploadify/jquery.uploadify.js"></script>
<script type="text/javascript" src="/admin/core/vendor/jqueryui/jqui.dragdrop.js"></script>
<script type="text/javascript" src="/admin/core/vendor/jqueryui/jqui.slider.js"></script>
<script type="text/javascript" src="/admin/media/ui/js/media.js"></script>
<script>
	$(function(){
		paneltop = $('#action').height();
	
		init();
		$('#panel').css('top', paneltop);
		$('#main_').css('top', paneltop);
	
		method		= 'sort-embed';						// Maniere dont l'insertion se fait
		field		= '<?php echo $_REQUEST['field'] ?>';		// field + #
		askType		= '<?php echo $askType ?>';
		useData		= 'true';
		myPrompt	= '<?php echo KPROMPT ?>';
        url  	= '<?php echo ($last != '') ? $last : '/media'; ?>';
        root    = '<?php echo ($last != '') ? '/media'.$root : ''; ?>';

		folderNav(url);
	});

</script>
<div id="fade-wall" style="display: none;"></div>
<div id="modal-upload" style="display: none;">
	<div class="uploadcontainer clearfix">
		<form id="uploadembed">
			<div class="left clearfix">
				<p>Glissez des fichiers dans la fenetre pour les télécharger.</p>
				<p>Si votre navigateur ne supporte pas cette fonctionalité, cliquez sur le bouton "Télécharger".</p>
				<br /><br />
				<input id="file_upload" name="file_upload" type="file" multiple="true">
				<!-- <a class="btn" href="javascript:$('#file_upload').uploadify('upload')">Envoyer les fichiers</a> -->
			</div>
			<div id="queue" class="clearfix">
			</div>
		</form>
	</div>
</div>
<div id="modal-meta" style="display: none"></div>
<div id="modal-pref" style="display: none"></div>
<div id="modal-newdir" style="display: none">
	<p>Ajouter un nouveau dossier</p>
	<input type="text" placeholder="Nom du dossier..." />
	<a href="#" class="btn btn-mini" id="newdir">Valider</a>
	<a href="#" class="btn btn-mini" onclick="modalHideUpload()">Annuler</a>
</div>
</body></html>