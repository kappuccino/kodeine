<!DOCTYPE html> 
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
	
		<?
			if($CARRIAGE_ERROR) $this->pre('CARRIAGE_ERROR');
		?>

		<div style="float:left; width:45%;">
			<h1>Adresse de livraison</h1>
			<p><?
				echo $myCart['cartDeliveryName']."<br />";
				echo str_replace("\n", '<br />', $myCart['cartDeliveryAddress']);
			?></p>
		</div>
		
		
		<div style="float:right; width:45%;">
			<h1>Adresse de facturation</h1>
			<p><?
				echo $myCart['cartBillingName']."<br />";
				echo str_replace("\n", '<br />', $myCart['cartBillingAddress']);
			?></p>
		</div>
		
		<br class="clear" />
		
		<p><a href="/fr/user/addressbook.html">Modifier ces adresses</a></p>
		
		<h1>Recapitulatif de la commande #<?= $myCart['id_cart'] ?></h1>
		
		<table border="1" width="100%" class="__dev__Table">
			<tr>
				<th>Name</th>
				<th>Prix unit HT</th>
				<th>Prix unit TTC</th>
				<th>Quantity</th>
				<th>Total HT</th>
				<th>Total TTC</th>
			</tr>
			<? foreach($myCart['line'] as $line){ $id_cartline = $line['id_cartline'] ?>
			<tr>
				<td><?= $line['contentName'] ?></td>
				<td><?= $line['contentPrice'] ?></td>
				<td><?= $line['contentPriceTax'] ?></td>
				<td><?= $line['contentQuantity'] ?></td>
				<td><?= $line['contentPriceQuantity'] ?></td>
				<td><?= $line['contentPriceTaxQuantity'] ?></td>
			</tr>
			<? } if($myCart['cartCoupon'] > 0){ ?>
			<tr>
				<td colspan="5">Coupon de réduction : <?= $myCart['cartCouponName'] ?></td>
				<td>-<?= $myCart['cartCoupon'] ?></td>
			</tr>
			<? } if($myCart['cartCarriage'] != NULL){ ?>
			<tr>
				<td>Frais de port</td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td><?= $myCart['cartCarriage'] ?></td>
			</tr>
			<? } ?>
			<tr>
				<td>Total</td>
				<td></td>
				<td></td>
				<td></td>
				<td><?= $myCart['cartTotal'] ?></td>
				<td><?= $myCart['cartTotalFinal'] ?></td>
			</tr>
		</table>
		<p><a href="cart.html">Modifier le panier</a></p>
		
		<h1>Coupon de réduction</h1>
		<form  action="overview.html" method="post">
		<table border="1" class="__dev__Table">
			<? if(sizeof($myCoupon) > 0){ ?>
			<tr>
				<td>Coupon</td>
				<td><select name="id_coupon">
					<option value="">Ne pas utiliser de coupon</option><?
					
					foreach($myCoupon as $e){
						$sel = ($myCart['id_coupon'] == $e['id_coupon']) ? ' selected' : NULL;
						echo "<option value=\"".$e['id_coupon']."\"".$sel.">".$e['couponName']."</option>";
					}
				?></select></td>
			</tr>
			<? } ?>
			<tr>
				<td>Ajouter</td>
				<td><input type="text" name="couponCode" size="10" /></td>
			</tr>
			<tr>
				<td></td>
				<td><input type="submit" /></td>
			</tr>
		</table>
		</form>

			
		<h1>Reglement par carte banquaire</h1>
		<?php
			include(USER.'/bank/paybox/_pay.php');
			echo $apiOutput;
		?>

		<h1>Reglement par chèque</h1>
		<p style="text-align:center;"><a href="cheque.html">Cliquer ici pour regler votre commande par chèque</a></p>
		<?php
			#$this->pre($myCart);

		?>

	</div>
</div>

<script type="text/javascript" src="/media/ui/js/script.php"></script> 
<? $this->themeInclude('ui/html-end.php') ?>

</body>
</html>