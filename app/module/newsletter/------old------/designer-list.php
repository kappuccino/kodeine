<?php
	require(dirname(dirname(__FILE__)).'/api/core.admin.php');
	$app = new coreAdmin();

	if(!$app->userIsAdmin) header("Location: ./");
	
	$file 	= MEDIA.'/newsletter/temp/index.html';
	$editor = $app->apiLoad('newsletterdesigner');

	include(ADMINUI.'/doctype.php');
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
	<title>Kodeine</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<?php include(ADMINUI.'/head.php'); ?>
	<link href="/app/admin/ressource/css/newsleter.editor.css" rel="stylesheet" type="text/css">

	<!-- TINY MCE -->
	<script type="text/javascript" src="ressource/plugin/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
</head>
<body>
<div id="pathway">
	<a href="core.panel.php">Admin</a> &raquo;
	<a href="newsletter.index.php">Newsletter</a> &raquo;
	<a href="newsletter.index.php">Template</a>
	<?php include(ADMINUI.'/pathway.php'); ?>
</div>

<?php include('ressource/ui/menu.newsletter.php'); ?>

<div class="clearfix" style="margin-top:30px;">
	<div id="form" style="float:left; width:400px; background:#808080;">
		<div class="data">
			...
		</div>
		<a onclick="apply()">APPLY</a>
	</div>

	<div style="float:right; width:900px; margin-right:20px;">
		<textarea id="html" style="height:100px; width:100%;"><?php
			echo file_get_contents($file);
		?></textarea>

		<iframe id="view" src="about:blank" width="100%" height="600" frameborder="0";></iframe>
	</div>

</div>


<script type="text/javascript" src="/app/admin/ressource/js/newsletter.designer.js"></script>
<script>

	data = {	
		template: <?php echo $editor->indent($editor->extract($file)); ?>
	};
	
	document.addEvent('domready', function(){
	//	view.document.designMode = "On";
		view.document.body.innerHTML = $('html').value;

		start();
	});

</script>

</body></html>