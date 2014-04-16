<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	// Remove
	if(sizeof($_POST['del']) > 0){
		foreach($_POST['del'] as $e){
			$app->apiLoad('business')->businessCartRemove($e, true);
		}
	}

	# Filter
	if(isset($_GET['cf'])){
		$app->filterSet('business', $_GET);
		$filter = array_merge($app->filterGet('business'), $_GET);	
	}else
	if(isset($_POST['filter'])){
		$app->filterSet('business', $_POST['filter']);
		$filter = array_merge($app->filterGet('business'), $_POST['filter']);	
	}else{
		$filter = $app->filterGet('business');
	}

	# Data
	$cmd = $app->apiLoad('business')->businessCartGet(array(
		'is_cmd'	=> true,
		'debug'		=> false,
		'id_shop'	=> $filter['id_shop'],
		'limit'		=> $filter['limit'],
		'offset'	=> $filter['offset'],
		'order'		=> $filter['order'],
		'direction'	=> $filter['direction']
	));

	$dir = ($filter['direction'] == 'ASC') ? 'DESC' : 'ASC';



    $conf	= $app->configGet('business', 'row');
    if(is_array($conf)) $rows = array();
    elseif(is_array(json_decode($conf))) $rows = json_decode($conf, true);
    else $rows = array();
///$app->pre($rows);

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php');
?></header>

<div class="inject-subnav-right hide">
	<li><a onclick="filterToggle('business');" class="btn btn-mini"><?php echo _('Display settings'); ?></a></li>
</div>

<div id="app"><div class="">

	<div class="quickForm clearfix" style="display:<?php echo $filter['open'] ? 'block' : 'none;' ?>;">
	<form action="./" method="post" class="form-horizontal">
		<input type="hidden" name="optForm"			value="1" />
		<input type="hidden" name="filter[open]"	value="1" />
		<input type="hidden" name="filter[offset]"	value="0" />
	
		<label for="txt-combien"><?php echo _('Limit'); ?></label>
		<input type="text" name="filter[limit]" id="txt-combien" size="5" value="<?php echo $filter['limit'] ?>" />

		<label for="shop-select">Shop</label><?php
			echo $app->apiLoad('shop')->shopSelector(array(
			   'name'		=> 'filter[id_shop]',
			   'value'		=> $filter['id_shop'],
			   'language'	=> 'fr',
			   'one'		=> true,
			   'id'			=> 'shop-select',
			   'empty'		=> true
			));
		?>
		<button type="submit" class="btn btn-mini"><?php echo _('Filter'); ?></button>
			
	</form>
	</div>
	
	<form method="post" action="./" id="listing">
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing align-left">
		<thead>
			<tr>
				<th width="30" class="icone"><i class="icon-remove icon-white"></i></th>

				<th width="50" class="order <?php if($filter['order'] == 'k_businesscart.id_cart') 		echo 'order'.$dir; ?>" onClick="document.location='index?cf&order=k_businesscart.id_cart&direction=<?php echo $dir ?>'"><span>#</span></th>
                <th width="150" class="order <?php if($filter['order'] == 'k_businesscart.cartDateCmd') 		echo 'order'.$dir; ?>" onClick="document.location='index?cf&order=k_businesscart.cartDateCmd&direction=<?php echo $dir ?>'"><span>Date</span></th>
                <th width="200" class="order <?php if($filter['order'] == 'k_businesscart.cartDeliveryName') 		echo 'order'.$dir; ?>" onClick="document.location='index?cf&order=k_businesscart.cartDeliveryName&direction=<?php echo $dir ?>'"><span><?php echo _('Name'); ?></span></th>
				<th></th>
                <?php
                $colspan = 0;
                if(is_array($rows)) {
                    $colspan ++;
                    foreach($rows as $r) {

                        if(is_numeric($r['field'])) {
                            $field	    = $app->apiLoad('field')->fieldGet(array('id_field' => $r['field']));
                            $fieldbdd = 'k_businesscart.field'.$r['field'];
                        }else {
                            $field = array('fieldName' => $r['field']);
                            $fieldbdd = 'k_businesscart.'.$r['field'];
                        }
                        $name = $field['fieldName'];
                        if($r['title'] != '') $name = $r['title'];
                        ?>
                        <th width="<?php echo $r['width']; ?>" class="order <?php if($filter['order'] == $fieldbdd) 	echo 'order'.$dir; ?>" onClick="document.location='index?cf&order=<?php echo $fieldbdd; ?>&direction=<?php echo $dir ?>'">
                            <span><?php echo $name; ?></span>
                        </th>

                        <?php
                    }
                }
                ?>


                <th width="100" class="order <?php if($filter['order'] == 'k_businesscart.cartDeliveryStatus') 		echo 'order'.$dir; ?>" onClick="document.location='index?cf&order=k_businesscart.cartDeliveryStatus&direction=<?php echo $dir ?>'"><span><?php echo _('Status'); ?></span></th>
                <th width="100" class="order <?php if($filter['order'] == 'k_businesscart.cartPayment') 		echo 'order'.$dir; ?>" onClick="document.location='index?cf&order=k_businesscart.cartPayment&direction=<?php echo $dir ?>'"><span><?php echo _('Mode'); ?></span></th>

				<th width="100" style="text-align:right;"><?php echo _('Total'); ?></th>
			</tr>
		</thead>
		<tbody><?php
		if(sizeof($cmd)){
			foreach($cmd as $e){
				$chkdel++; # count chkbox 
			?>
			<tr>
				<td><input type="checkbox" name="del[]" class="chk" id="chk-del<?php echo $chkdel ?>" value="<?php echo $e['id_cart'] ?>" <?php echo $disabled ?> /></td>
				<td><a href="detail?id_cart=<?php echo $e['id_cart'] ?>"><?php echo $e['id_cart'] ?></a></td>
				<td><a href="detail?id_cart=<?php echo $e['id_cart'] ?>"><?php echo $app->helperDate($e['cartDateCmd'], '%d %B %Y %Hh%M'); ?></a></td>
				<td><?php echo $e['cartDeliveryName'] ?></td>
				<td></td>
                <?php
                if(is_array($rows)) {
                    foreach($rows as $r) {
                        $value = '';
                        if(is_numeric($r['field'])) {
                            $field	    = $app->apiLoad('field')->fieldGet(array('id_field' => $r['field']));
                            $value      = $e['field'.$r['field']];
                        }elseif($r['field'] == 'cartWeight') {
                            $value = 0;
                            foreach($e['line'] as $l) {
                                $value += ($l['contentWeight'] * $l['contentQuantity']);
                            }
                            if($value >= 0) $value = round(($value / 1000), 3);
                            $value .= ' kg';
                        }elseif($r['field'] == 'cartDeliveryStatus') {
                            $value = '<a href="edit?id_cart='.$e['id_cart'].'">'.$e['cartDeliveryStatus'].'</a>';
                        } elseif(substr($r['field'], 0, 8) == 'DELIVERY') {
                            $address = $app->apiLoad('user')->userAddressBookGet(array( 'id_user' => $e['id_user'], 'id_addressbook' => $e['id_delivery'] ));
                            $value = $address[str_replace('DELIVERY', '', $r['field'])];
                        }  elseif(substr($r['field'], 0, 7) == 'BILLING') {
                            $address = $app->apiLoad('user')->userAddressBookGet(array( 'id_user' => $e['id_user'], 'id_addressbook' => $e['id_billing'] ));
                            $value = $address[str_replace('BILLING', '', $r['field'])];
                        } else {
                            $field = array('fieldName' => $r['field']);
                            $value = $e[$field['fieldName']];
                        }
                        ?>
                        <td>
                            <span><?php echo $value; ?></span>
                        </td>

                        <?php
                    }
                }
                ?>

				<td><a href="edit?id_cart=<?php echo $e['id_cart'] ?>"><?php echo $e['cartStatus'] ?></a></td>
				<td><?php echo $e['cartPayment'] ?></td>
				<td align="right"><?php echo $e['cartTotalFinal'] ?></td>
			</tr>
			<?php }
		}else{ ?>
			<tr>
				<td colspan="<?php echo ($colspan + 8); ?>" style="padding-top:30px; padding-bottom:30px; font-weight:bold;" align="center"><?php echo _('No order'); ?></td>
			</tr>
		<?php } ?>
		</tbody>
		<tfoot>
			<tr>
				<td height="30"></td>
				<td colspan="<?php echo ($colspan + 5); ?>"><?php
				if(sizeof($cmd)){ ?>
					<a href="#" onClick="apply();" class="btn btn-mini"><?php echo _('Remove selected orders'); ?></a>
					<span class="pagination"><?php $app->pagination($app->apiLoad('business')->total, $app->apiLoad('business')->limit, $filter['offset'], 'index?cf&offset=%s'); ?></span>
				<?php } ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
		</tfoot>
	</table>
	
	
	</form>

</div>

<?php include(COREINC.'/end.php'); ?>
<script src="../core/vendor/datatables/jquery.dataTables.js"></script>
<script>
	function apply(){
		if(confirm("Confirmer la suppression des commandes selectionnées et la recreditation du stock ?")){
			$('#listing').submit();
		}
	}
</script>
	
	
</body></html>