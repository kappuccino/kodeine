<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	# Data
	$myCmd = $app->apiLoad('business')->businessCartGet(array(
		'is_cmd'	=> true,
		'id_cart'	=> $_GET['id_cart'],
		'debug'		=> false
	));
	
    # Bases TVA - Totaux TVA
    $tva = array();
    
    foreach($myCmd['line'] as $l){
        if($l['contentTax'] > 0){
            $tva[$l['contentTax']]['total'] += $l['contentPriceQuantity'];
            $tva[$l['contentTax']]['base'] += $l['contentPriceTaxQuantity'] - $l['contentPriceQuantity'];
        }
    }
    # Tri par ordre croissant TVA
    ksort($tva);
    
	$flag = $app->dbMulti("SELECT * FROM k_businesscartflag WHERE id_cart=".$myCmd['id_cart']);

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>


<div id="app"><div class="wrapper">

	<div style="width:900px; margin:0 auto;">
		<h1>Detail de la commande # <?php echo $myCmd['cartCmdNumber'] ?></h1>
				
		<table width="100%" border="1">
			<tr valign="top">
				<td width="40%">
					N° Commande 	: <?php echo $myCmd['cartCmdNumber'] ?><br />
					Code client 		: <?php echo $myCmd['id_user'] ?><br />
					Date				: <?php echo $myCmd['cartDateCmd'] ?><br />
					<?php					
                        //Statut
                        $statut = $app->apiLoad('business')->businessConfigGet(array('configField' => 'cartStatus', 'configKey' => $myCmd['cartStatus']));            
                        if($statut[0]['configCustom'] != '')$myCmd['cartStatus'] = $statut[0]['configCustom'];
                    ?>
					Statut				: <?php echo $myCmd['cartStatus'] ?><br />
                    <?php                   
                        //Règlement
                        $statut = $app->apiLoad('business')->businessConfigGet(array('configField' => 'cartPayment', 'configKey' => $myCmd['cartPayment']));            
                        if($statut[0]['configCustom'] != '')$myCmd['cartPayment'] = $statut[0]['configCustom'];
                    ?>
                    Mode de paiement    : <?php echo $myCmd['cartPayment'] ?><br />
                    <?php                   
                        //Statut de livraison
                        $statut = $app->apiLoad('business')->businessConfigGet(array('configField' => 'cartDeliveryStatus', 'configKey' => $myCmd['cartDeliveryStatus']));            
                        if($statut[0]['configCustom'] != '')$myCmd['cartDeliveryStatus'] = $statut[0]['configCustom'];
                    ?>
                    Statut livraison    : <?php echo $myCmd['cartDeliveryStatus'] ?>
				</td>
				<td width="30%">
					<b>Adresse de livraison</b><br /><?php echo str_replace("\n", '<br />', $myCmd['cartDeliveryName'])."<br />".str_replace("\n", '<br />', $myCmd['cartDeliveryAddress']); ?>
				</td>
				<td width="30%">
					<b>Adresse de facturation</b><br /><?php echo str_replace("\n", '<br />', $myCmd['cartBillingName'])."<br />".str_replace("\n", '<br />', $myCmd['cartBillingAddress']); ?>
				</td>
			</tr>
		</table>
		
		<p>&nbsp;</p>
		
		<table width="100%" border="1">
			<tr>
				<td width="100">Ref</td>
				<td>Produit</td>

                <td width="90">Prix Unit HT</td>
                <td width="90">Remise</td>
                <td width="90">TVA</td>
				<td width="90">Prix Unit TTC</td>
                <td width="90">Compte</td>
				<td width="90">Qt&eacute;.</td>
				<td width="90">Total HT</td>
				<td width="90">Total TTC</td>
			</tr>
			<?php foreach($myCmd['line'] as $l){ ?>
			<tr>
				<td><?php echo $l['contentRef'] ?></td>
				<td><?php echo $l['contentName'] ?></td>

				<td><?php echo $l['contentPrice'] ?></td>
				<td><?php echo $l['contentPriceCut'] ?></td>
				<td><?php echo $l['contentTax'] ?> %</td>
				<td><?php echo $l['contentPriceTax'] ?></td>
				<td><?php $account = $app->apiLoad('business')->businessAccountGet(array('id_account' => $l['accountNumber']));echo $account['accountNumber']; ?></td>
				<td><?php echo $l['contentQuantity'] ?></td>
				<td><?php echo $l['contentPriceQuantity'] ?></td>
				<td><?php echo $l['contentPriceTaxQuantity'] ?></td>
			</tr>
			<?php } ?>
			<tr>
				<td colspan="8"></td>
				<td><?php echo $myCmd['cartTotal'] ?></td>
				<td><?php echo $myCmd['cartTotalTax'] ?></td>
			</tr>
		</table>
		
		<p>&nbsp;</p>
		
		<table border="1" width="250" align="right">
            <?php foreach($tva as $t=>$v){if($v['total'] > 0){ ?>
            <tr class="totaltva">
                <td>Total HT <?php echo $t ?> %</td>
                <td width="50%" align="right"><?php echo $v['total'] ?></td>
            </tr>                   
            <?php }} ?>
			<tr>
				<td><b>Total Général HT</b></td>
				<td width="50%" align="right"><?php echo $myCmd['cartTotal'] ?></td>
			</tr>
                <?php foreach($tva as $t=>$v){if($v['base'] > 0){  ?>
                <tr class="basetva">
                    <td>TVA à <?php echo $t ?> %</td>
                    <td width="50%" align="right"><?php echo $v['base'] ?></td>
                </tr>                   
                <?php }} ?>
			<tr>
				<td><b>Total TVA</b></td>
				<td align="right"><?php echo number_format(($myCmd['cartTotalTax'] - $myCmd['cartTotal']), 2, '.', ' ') ?></td>
			</tr>
			<tr>
				<td><b>Total TTC</b></td>
				<td align="right"><?php echo $myCmd['cartTotalTax'] ?></td>
			</tr>
            <tr >
                <td>Frais de port</td>
                <td align="right"><?php echo $myCmd['cartCarriage'] ?></td>
            </tr>
            <tr >
                <td>TVA <?php echo $myCmd['cartCarriageTax']; ?> %</td>
                <td align="right"><?php echo number_format($myCmd['cartCarriageTotalTax']-$myCmd['cartCarriage'], 2, '.', ' '); ?></td>
            </tr>
			<tr>
				<td><b>Total commande</b></td>
				<td align="right"><?php echo $myCmd['cartTotalFinal'] ?></td>
			</tr>
		</table>
	
		<p>&nbsp;</p>
	
		<table border="1" width="250">
		<?php foreach($flag as $e){ ?>
			<tr>
				<td width="50%"><?php echo $e['cartFlagName'] ?></td>
				<td><?php echo $e['cartFlagValue'] ?></td>
			</tr>
		<?php } ?>
		</table>
	</div>

</div></div>

</body></html>
