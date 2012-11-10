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

			<h1>Mes coupons</h1>
			
			<p><a href="my">Revenir a mon compte</a></p>
			
			<?
				if($COUPON_NOT_EXISTS) 	$m = 'COUPON_NOT_EXISTS';
				if($ALREADY_INSERTED)	$m = 'ALREADY_INSERTED';
				
				if($m != '') $this->pre($m)
			?>
			
			<? if(sizeof($myCoupon) > 0){ ?>
			
			<table width="100%" border="1">
				<tr>
					<td>Date d'ajout</td>
					<td>Coupon</td>
					<td>Reduction</td>
					<td>Utilisable</td>
				</tr>
				<? foreach($myCoupon as $e){ ?>
				<tr>
					<td><?= $e['couponAdded'] ?></td>
					<td><?= $e['couponCode'] ?></td>
					<td><?
						switch($e['couponMode']){
							case 'CARRIAGE' : $w = 'Port offert'; 				break;
							case 'FIXE' 	: $w = '-'.$e['couponAmount']; 		break;
							case 'PERCENT'	: $w = '-'.$e['couponAmount'].'%'; 	break;
						}
						echo $w;
					?></td>
					<td><?= ($e['is_used']) ? 'non' : 'oui' ?></td>
				<tr>
				<? } ?>
			</table>
			
			<? }else{ ?>
				<p>Pas de coupon pour le moment</p>
			<? } ?>
			
			<p>&nbsp;</p>
			
			<h1>Ajouter un nouveau coupon</h1>
			
			<form action="coupon" method="post">
				<input type="hidden" name="action" value="1" />
				Code du coupon <input type="text" name="couponCode" />
				<input type="submit" />
			</form>
		</div>

	</div>

</div>

</body></html>

