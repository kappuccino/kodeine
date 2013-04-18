<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	if(isset($_GET['language'])){
		$app->filterSet('core', $_GET['language'], 'language');
		$app->go('./');
	}

	$languages = array(
		'fr_FR.utf-8' => 'Français',
		'en_EN'       => 'English'
	);

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/menu.php')
?></header>

<div class="inject-subnav-right hide">
    <li>
        <a href="module"><span><?php echo _('Module manager') ?></span></a>
    </li>
	<li>
		<div class="btn-group">
            <a class="btn btn-mini dropdown-toggle" data-toggle="dropdown" href="#"><i class="icon-flag"></i> <?php echo _('Change language') ?> <span class="caret"></span></a>
            <ul class="dropdown-menu"><?php
				foreach($languages as $loc => $n){
					echo '<li><a href="./?language='.$loc.'">'.$n.'</a></li>';
				}
			?></ul>
		</div>
	</li>
</div>

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
<script src="../core/vendor/bootstrap/js/bootstrap-dropdown.js"></script>
<script src="ui/js/dashboard.js"></script>

<script>
	$(function(){
	//	dashboardLoad('dash-data', 'content', 'last');
	//	dashboardLoad('dash-cmd', 'business', 'last');
	//	dashboardLoad('dash-news', 'newsletter', 'last');
	});
</script>			

</body></html>
