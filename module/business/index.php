<?php

	$i18n = $app->apiLoad('coreI18n')->languageSet('fr')->load('business');

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

?><!DOCTYPE html>
<html lang="fr">
<head>
	<title>Kodeine</title>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php');
?></header>

<div class="inject-subnav-right hide">
	<li><a onclick="filterToggle('business');" class="btn btn-mini">Options d'affichage</a></li>
</div>

<div id="app"><div class="">

	<div class="quickForm clearfix" style="display:<?php echo $filter['open'] ? 'block' : 'none;' ?>;">
	<form action="./" method="post" class="form-horizontal">
		<input type="hidden" name="optForm"			value="1" />
		<input type="hidden" name="filter[open]"	value="1" />
		<input type="hidden" name="filter[offset]"	value="0" />
	
		<label for="txt-combien">Combien</label>
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
		<button type="submit" class="btn btn-mini">Valider</button>
			
	</form>
	</div>
	
	<form method="post" action="/admin/business/" id="listing">
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing sortable align-left">
		<thead>
			<tr>
				<th width="30" class="icone"><i class="icon-remove icon-white"></i></th>
				<th width="50" class="order <?php if($filter['order'] == 'k_businesscart.id_cart') 		echo 'order'.$dir; ?>"><span>#</span></th>
				<th width="150"class="order <?php if($filter['order'] == 'k_businesscart.cartDateCmd') 	echo 'order'.$dir; ?>"><span>Date</span></th>
				<th width="200">Nom</th>
				<th></th>
	
				<th width="100">Status</th>
				<th width="100">Moyen</th>
				<th width="100" style="text-align:right;">Total</th>
			</tr>
		</thead>
		<tbody><?php
		if(sizeof($cmd)){
			foreach($cmd as $e){ 
				$chkdel++; # count chkbox 
			?>
			<tr>
				<td><input type="checkbox" name="del[]" class="chk" id="chk-del<?php echo $chkdel ?>" value="<?php echo $e['id_cart'] ?>" <?php echo $disabled ?> /></td>
				<td><a href="/admin/business/detail?id_cart=<?php echo $e['id_cart'] ?>"><?php echo $e['id_cart'] ?></a></td>
				<td><a href="/admin/business/detail?id_cart=<?php echo $e['id_cart'] ?>"><?php echo $app->helperDate($e['cartDateCmd'], '%d %B %G %Hh%M'); ?></a></td>
				<td><?php echo $e['cartDeliveryName'] ?></td>
				<td></td>
				<td><a href="/admin/business/edit?id_cart=<?php echo $e['id_cart'] ?>"><?php echo $e['cartStatus'] ?></a></td>
				<td><?php echo $e['cartPayment'] ?></td>
				<td align="right"><?php echo $e['cartTotalFinal'] ?></td>
			</tr>
			<?php }
		}else{ ?>
			<tr>
				<td colspan="8" style="padding-top:30px; padding-bottom:30px; font-weight:bold;" align="center">Aucune commande</td>
			</tr>
		<?php } ?>
		</tbody>
		<tfoot>
			<tr>
				<td height="30"></td>
				<td colspan="5"><?php
				if(sizeof($cmd)){ ?>
					<a href="#" onClick="apply();" class="btn btn-mini">Supprimer les commandes selectionnés</a>
					<span class="pagination"><?php $app->pagination($app->apiLoad('business')->total, $app->apiLoad('business')->limit, $filter['offset'], '/admin/business/index?cf&offset=%s'); ?></span>
				<?php } ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
		</tfoot>
	</table>
	
	
	</form>

</div>

<?php include(COREINC.'/end.php'); ?>
<script src="/app/module/core/vendor/datatables/jquery.dataTables.js"></script>
<script>
	function apply(){
		if(confirm("Confirmer la suppression des commandes selectionnées et la recreditation du stock ?")){
			$('#listing').submit();
		}
	}
</script>
	
	
</body></html>