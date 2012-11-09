<!DOCTYPE html>
<html lang="fr">
<head>
	<title>Kodeine</title>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/menu.php')
?></header>

<div id="app"><div class="wrapper">

	<div class="row-fluid">
		<div class="span12 mar-bot-20" id="dash-data"></div>
	</div>

	<div class="row-fluid">
		<div class="span8 mar-bot-20" id="dash-cmd"></div>
		<div class="span4 mar-bot-20" id="dash-news"></div>
	</div>

</div></div>

<?php include(COREINC.'/end.php'); ?>
<script src="/admin/core/ui/js/dashboard.js" type="text/javascript"></script>
<script>
	$(function(){
	//	dashboardLoad('dash-data', 'content', 'last');
	//	dashboardLoad('dash-cmd', 'business', 'last');
	//	dashboardLoad('dash-news', 'newsletter', 'last');
	});
</script>			

</body></html>
