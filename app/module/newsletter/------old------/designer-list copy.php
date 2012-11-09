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
	<link rel="stylesheet" type="text/css" media="all" href="/app/admin/ressource/css/newsleter.editor.css" />
</head>
<body>
<div id="pathway">
	<a href="core.panel.php">Admin</a> &raquo;
	<a href="newsletter.index.php">Newsletter</a> &raquo;
	<a href="newsletter.index.php">Template</a>
	<?php include(ADMINUI.'/pathway.php'); ?>
</div>

<?php include('ressource/ui/menu.newsletter.php'); ?>

<div class="app">

<div class="clearfix">

	<div id="form" style="float:left; width:40%;">
		
	</div>

	<div style="float:right; width:60%;">
		<input type="checkbox" onclick="setMode(this.checked)">
		<div style="width:100%; border:1px solid #000000">
			<!--
			<iframe id="view" width="100%" height="100%" src="about:blank" frameborder="0"></iframe>
			-->

			<div id="html"><?php
				echo $app->apiLoad('newsletterdesigner')->extractBody($file);	
			?></div>

		</div>
	</div>

</div>


</div>

<script type="text/javascript" src="/app/admin/ressource/js/newsletter.designer.js"></script>
<script>


data = {
	
html : [],
		
		
template: <?php echo $editor->indent($editor->extract($file)); ?>
	
};




	document.addEvent('domready', function(){
		start();
	});
/*
	view.document.designMode = "On";
	view.document.body.innerHTML = "<b>Lol...</b>";
	function setMode(bMode) {
		var sTmp;
		isHTMLMode = bMode;
		if (isHTMLMode) {
			sTmp = view.document.body.innerHTML;
			view.document.body.innerText = sTmp;
		} else {
			sTmp = view.document.body.innerText;
			view.document.body.innerHTML = sTmp;
		}
		view.focus();
	}

	function cmdExec(cmd,opt) {
		if(isHTMLMode) {
			alert("Please uncheck 'Edit HTML'");
			return;
		}
		view.document.execCommand(cmd,"",opt);
		view.focus();
	}
*/
</script>

</body></html>