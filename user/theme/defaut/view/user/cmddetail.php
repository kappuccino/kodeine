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

			<h1>Detail de la commande #<?= $_GET['id_cmd'] ?></h1>
			
			<p><a href="cmd">Revenir a la liste des commandes</a></p>
			
			<table width="100%" border="1">
				<tr valign="top">
					<td width="40%">
						Commande numero 	: <?= $myCmd['id_cart'] ?><br />
						Code client 		: <?= $myCmd['id_user'] ?><br />
						Date				: <?= $myCmd['cartDateCmd'] ?><br />
						Status				: <?= $myCmd['cartStatus'] ?>
					</td>
					<td width="30%">
						<b>Adresse de livraison</b><br /><?= $myCmd['cartDeliveryName']."<br />".str_replace("\n", '<br />', $myCmd['cartDeliveryAddress']); ?>
					</td>
					<td width="30%">
						<b>Adersse de facturation</b><br /><?= $myCmd['cartBillingName']."<br />".str_replace("\n", '<br />', $myCmd['cartBillingAddress']); ?>
					</td>
				</tr>
			</table>
			
			<p>&nbsp;</p>
			
			<table width="100%" border="1">
				<tr>
					<td width="100">Ref</td>
					<td>Produit</td>
	
					<td width="90">Prix Unit HT</td>
					<td width="90">Prix Unit TTC</td>
					<td width="90">Qt&eacute;.</td>
					<td width="90">Total HT</td>
					<td width="90">Total TTC</td>
				</tr>
				<? foreach($myCmd['line'] as $l){ ?>
				<tr>
					<td><?= $l['contentRef'] ?></td>
					<td><?= $l['contentName'] ?></td>
	
					<td><?= $l['contentPrice'] ?></td>
					<td><?= $l['contentPriceTax'] ?></td>
					<td><?= $l['contentQuantity'] ?></td>
					<td><?= $l['contentPriceQuantity'] ?></td>
					<td><?= $l['contentPriceTaxQuantity'] ?></td>
				</tr>
				<? } ?>
				<tr>
					<td colspan="5"></td>
					<td><?= $myCmd['cartTotal'] ?></td>
					<td><?= $myCmd['cartTotalTax'] ?></td>
				</tr>
			</table>
			
			<p>&nbsp;</p>
			
			<table border="1">
				<tr>
					<td>Total HT</td>
					<td width="100" align="right"><?= $myCmd['cartTotal'] ?></td>
				</tr>
				<tr>
					<td>Montant des taxes</td>
					<td align="right"><?= number_format(($myCmd['cartTotalTax'] - $myCmd['cartTotal']), 2, '.', ' ') ?></td>
				</tr>
				<tr>
					<td>Total TTC</td>
					<td align="right"><?= $myCmd['cartTotalTax'] ?></td>
				</tr>
				<tr >
					<td>Frais de port</td>
					<td align="right"><?= $myCmd['cartCarriage'] ?></td>
				</tr>
				<tr>
					<td>Total commande</td>
					<td align="right"><?= $myCmd['cartTotalFinal'] ?></td>
				</tr>
			</table>



		</div>
	</div>

</div>

</body></html>
