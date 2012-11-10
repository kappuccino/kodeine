<?php

	# Figer le CART pour le SHOP 1
	#
	/*if($myCart['id_shop'] != '1'){
		$this->apiLoad('business')->businessCartShopSet($myCart['id_cart'], 1);
		header("Location: cart");
	}*/

?><!DOCTYPE html> 
<html lang="<?php echo LOC ?>">
<head>
	<title></title>
	<meta name="description" content="" />
	<meta name="keywords" content="" />

	<?php include(UI.'/html-head.php') ?> 
</head>
<body class="body">


<div id="main">
	<div class="clearfix row-fluid show-grid">
		<div class="span3"><?php include(UI.'/menu.php') ?></div>

		<div class="span9">
			<h1>Panier</h1>
			
			<a href="cart?empty=1">Vider</a>
			
			<?php if(sizeof($myCart['line']) > 0){ ?>
				<form action="cart" method="post">
					<table border="1" width="100%" class="__dev__Table">
					<tr>
						<th>Name</th>
						<th>Prix unit HT</th>
						<th>Prix unit TTC</th>
						<th>Quantity</th>
						<th>Total HT</th>
						<th>Total TTC</th>
						<th>Remove</th>
					</tr>
					<?php foreach($myCart['line'] as $line){ $id_cartline = $line['id_cartline'] ?>
					<tr>
						<td><?php echo  $line['contentName'] ?></td>
						<td><?php echo $line['contentPrice'] ?></td>
						<td><?php echo $line['contentPriceTax'] ?></td>
						<td><input type="text" name="line[<?php echo $id_cartline ?>][contentQuantity]" value="<?php echo $line['contentQuantity'] ?>" /></td>
						<td><?php echo $line['contentPriceQuantity'] ?></td>
						<td><?php echo $line['contentPriceTaxQuantity'] ?></td>
						<td><input type="checkbox" name="line[<?php echo $id_cartline ?>][remove]" value="1" /></td>
					</tr>
					<?php } if($myCart['cartCoupon'] > 0){ ?>
					<tr>
						<td colspan="5">Coupon de réduction : <?php echo $myCart['cartCouponName'] ?></td>
						<td>-<?php echo $myCart['cartCoupon'] ?></td>
						<td></td>
					</tr>
					<?php } if($myCart['cartCarriage'] != NULL){ ?>
					<tr>
						<td>Frais de port</td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td><?php echo $myCart['cartCarriage'] ?></td>
						<td></td>
					</tr>
					<?php } ?>
					<tr>
						<td>Total</td>
						<td></td>
						<td></td>
						<td></td>
						<td><?php echo $myCart['cartTotal'] ?></td>
						<td><?php echo $myCart['cartTotalFinal'] ?></td>
						<td></td>
					</tr>
					</table>
					
					<input type="submit" />
					<input type="hidden" name="update" value="1" />
				</form>
	
				<?php if($this->user['id_user'] == NULL){ ?>
				<a href="login?cart">Identifiez-vous pour calculer les frais de livraison</a>, 
				<?php } ?>
				
				<a href="overview">Suite</a>
	
			<?php }else{ ?>
				<p>Panier vide</p>
			<?php } ?>
	
			<?php
				$this->pre($myCart);
			?>

				
			
		</div>
	</div>
</div>


</body></html>