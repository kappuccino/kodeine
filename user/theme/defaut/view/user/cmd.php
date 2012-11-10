<?php

	if(intval($this->user['id_user']) == 0) header("Location: login");

?><!DOCTYPE html> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
	<title></title>
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<link rel="stylesheet" type="text/css" media="all" href="/media/ui/css/style.php" />
	<?php include(MYTHEME.'/ui/html-head.php') ?>
</head>
<body class="body">

<div class="container_12 container clearfix">

	<div class="col grid_3 alpha">
		<?php include(MYTHEME.'/ui/menu.php') ?>
	</div>

	<div class="grid_9 omega center">

		<div class="center-item">

			<h1>Mes commandes</h1>
			
			<p><a href="my">Revenir a mon compte</a></p>
			
			<? if(sizeof($myCmd) > 0){ ?>
				<table width="100%" border="1">
					<tr>
						<td>#</td>
						<td>Date</td>
						<td>Montant</td>
						<td>Etat</td>
					</tr>
					<? foreach($myCmd as $cmd){ ?>
					<tr>
						<td><a href="cmddetail.html?id_cmd=<?= $cmd['id_cart'] ?>"><?= $cmd['id_cart'] ?></a></td>
						<td><?= $cmd['cartDateUpdate'] ?></td>
						<td><?= $cmd['cartTotalFinal'] ?> TTC</td>
						<td></td>
					<tr>
					<? } ?>
				</table>
			
			<? }else{ ?>
				<p>Pas de commande actuellement</p>
			<? } ?>

		</div>
	</div>

</div>

</body></html>