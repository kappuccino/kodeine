<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
	<title></title>
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<link rel="stylesheet" type="text/css" media="all" href="/media/ui/css/style.php" />
	<? $this->themeInclude('ui/html-head.php') ?>
</head>
<body>

<div class="container_12 container clearfix">

	<? $this->themeInclude('ui/nav.php'); ?>

	<div class="grid_12">
		<h1>Annulation de la commande</h1>
		<?php

			include(KROOT.'/_api/'.APIBANK.'/_cancel.php');
			if($apiError) $this->pre($apiRaw);
		?>

		<p>Annulation de la commande : <?= $apiIdCart ?></p>
		<p>Le panier est encore actif</p>

	</div>
</div>

<script type="text/javascript" src="/media/ui/js/script.php"></script> 
<? $this->themeInclude('ui/html-end.php') ?>

</body>
</html>