<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	# Filter
	if(isset($_GET['cf'])){
		$app->filterSet('hist', $_GET);
		$filter = array_merge($app->filterGet('hist'), $_GET);	
	}else
	if(isset($_GET['filter'])){
		$app->filterSet('hist', $_GET['filter']);
		$filter = array_merge($app->filterGet('hist'), $_GET['filter']);	
	}else{
		$filter = $app->filterGet('hist');
	}

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
	<link rel="stylesheet" type="text/css" href="../content/ui/css/dropdowns.css" />
	<link rel="stylesheet" type="text/css" href="../core/vendor/datepicker/css/datepicker.css" />
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div id="app"><div class="wrapper">


	<div class="clearfix">
		<form action="hist" method="get">
			<div class="left">
				Date de début <input type="text" class="input-small nomargin" name="rangeStart" id="rangeStart" value="<?php echo $_GET['rangeStart'] ?>" /><i id="clearstart" class="clearfield icon-remove-sign"></i>&nbsp;
				Date de fin <input type="text" class="input-small nomargin" name="rangeEnd" id="rangeEnd" value="<?php echo $_GET['rangeEnd'] ?>" /><i id="clearend" class="clearfield icon-remove-sign"></i>&nbsp;
				Shop
				<?php echo $app->apiLoad('shop')->shopSelector(array(
						'name'		=> 'filter[id_shop]',
						'class'		=> 'select-small nomargin',
						'value'		=> $filter['id_shop'],
						'language'	=> 'fr',
						'one'		=> true,
						'empty'		=> true
				)); ?>
			</div>
			
			<div class="left margin-left" style="margin-left: 15px;">
				<button type="submit" class="btn btn-mini">Valider</button><button href="hist" class="btn btn-mini">Annuler</button>
			</div>
		</form>
	</div>
	
	<table border="0" cellpadding="0" cellspacing="0" class="listing" style="width:50%; margin-top:10px;">
		<thead>
			<tr>
				<th>#</th>
				<th width="25%">Nombre total</th>
				<th width="25%">Montant total</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($app->apiLoad('business')->businessStatusGet() as $e){
				$count = 0;
				$total = 0;
	
				$cmd_ = $app->apiLoad('business')->businessCartGet(array(
					'create'		=> false,
					'debug'			=> false,
					'is_cmd' 		=> 'true',
                    'noLimit'   => true,
					'cartStatus'	=> $e,
					'id_shop'		=> $filter['id_shop'],
					'range'			=> array($_GET['rangeStart'], $_GET['rangeEnd'])
				));
				
				if(sizeof($cmd_) > 0){
					foreach($cmd_ as $c_){
						$count++;
						$total += $c_['cartTotalFinal'];
					}
				}
		
		?>
		<tr>
			<td><?php echo $e ?></td>
			<td><?php echo $count ?></td>
			<td><?php echo $total ?></td>
		</tr>
		<?php } ?>
		</tbody>
	</table>
		
	<?php
		$cmd = $app->apiLoad('business')->businessCartGet(array(
			'is_cmd'	=> true,
			'debug'		=> false,
            'noLimit'   => true,
			'range'		=> array($_GET['rangeStart'], $_GET['rangeEnd']),
			'id_shop'	=> $filter['id_shop'],
			'order'		=> 'k_businesscart.id_cart',
			'direction'	=> 'DESC'
		));
		
		if(sizeof($cmd) > 0){
	?>
	
	<br />
	<h3>Détail des commandes sur la période</h3>

	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing">
		<thead>
			<tr>
				<th width="50">#</th>
				<th width="150">Date</th>
				<th width="200">Nom</th>
				<th></th>
				<th width="100">Status</th>
				<th width="100">Moyen</th>
				<th width="100" style="text-align:right;">Total</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($cmd as $e){ ?>
			<tr>
				<td><a href="detail?id_cart=<?php echo $e['id_cart'] ?>"><?php echo $e['id_cart'] ?></a></td>
				<td><a href="detail?id_cart=<?php echo $e['id_cart'] ?>"><?php echo $e['cartDateCmd'] ?></a></td>
				<td><?php echo $e['cartDeliveryName'] ?></td>
				<td></td>
				<td><?php echo $e['cartStatus'] ?></td>
				<td><?php echo $e['cartPayment'] ?></td>
				<td align="right"><?php echo $e['cartTotalFinal'] ?></td>
			</tr>
			<?php } ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="7">
					<span class="pagination"><?php $app->pagination($app->total, $app->limit, $filter['offset'], 'hist?cf&offset=%s'); ?></span>
				</td>
			</tr>
		</tfoot>
	</table>
	<?php } ?>

</div></div>

<?php include(COREINC.'/end.php'); ?>
<script src="../vendor/datatables/jquery.dataTables.js"></script>
<script>

	$(function() {
		var start = "<?php echo $_GET['rangeStart'] ?>";
		var end	  = "<?php echo $_GET['rangeEnd'] ?>";
		var today = new Date();
		var mydate = today.getFullYear()+'-'+(today.getMonth()+1)+'-'+(today.getDay()+1);		
		
		
		if (start !== "") {
			$('#rangeStart').val(start);
		} else {
			$('#rangeStart').val(mydate);
		}
		
		if (end !== "") {
			$('#rangeEnd').val(end);
		} else {
			$('#rangeEnd').val(mydate);
		}
		
		$('#rangeStart').datepicker({
			format: 'yyyy-mm-dd'
		});
		$('#rangeEnd').datepicker({
			format: 'yyyy-mm-dd'
		});
		
		$('#clearstart').on('click', function() {
			$('#rangeStart').val('');
		});
		$('#clearend').on('click', function() {
			$('#rangeEnd').val('');
		});
	});
</script>
</body>
</html>