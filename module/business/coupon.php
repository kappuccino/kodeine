<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	// Remove
	if(sizeof($_POST['del']) > 0){
		foreach($_POST['del'] as $e){
			$app->dbQuery("DELETE FROM k_businesscoupon WHERE id_coupon=".$e);
		}
		$app->go('coupon');
	}else
	if($_POST['action']){
		$do = true;

		$def['k_businesscoupon'] = array(
			'couponName'		=> array('value' => $_POST['couponName'],	'check' => '.'),
			'couponCode'		=> array('value' => $_POST['couponCode']),
			'couponMode'		=> array('value' => $_POST['couponMode']),
			'couponAmount'		=> array('value' => $_POST['couponAmount']),
		);

		if(!$app->formValidation($def)) $do = false;

		if($do){
			$result = $app->apiLoad('business')->businessCouponSet(array(
				'id_coupon'	=> $_POST['id_coupon'],
				'def'		=> $def,
				'id_shop'	=> $_POST['id_shop']
			));

			$message = ($result)
				? 'OK: Enregistrement dans la base'
				: 'KO: Une erreur est survenue, APP:<br />'.$app->db_error;

		}else{
			$message = 'KO: Validation failed';
		}
	}

	if($_REQUEST['id_coupon'] != NULL){
		$data = $app->apiLoad('business')->businessCouponGet(array(
			'id_coupon'		=> $_REQUEST['id_coupon'],
			'debug'			=> false
		));
	}

	$coupon = $app->apiLoad('business')->businessCouponGet(array(
		'debug' => false
	));

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
	
	<div class="span6">
	<form action="coupon" method="post" id="listing">
	
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing sortable">
		<thead>
			<tr>
				<th width="30" class="icone"><i class="icon-remove icon-white"></i></th>
				<th class="filter">
					<span>Nom</span>
					<input type="text" class="input-small" id="filter" />
				</th>
			</tr>
		</thead>
		<tbody><?php
		if(sizeof($coupon) > 0){
			foreach($coupon as $e){ 
				$countchk++;	
				?>
			<tr class="<?php if($e['id_coupon'] == $_REQUEST['id_coupon']) echo "selected" ?>">
				<td><input type="checkbox" name="del[]" id="chk-del<?php echo $countchk ?>" value="<?php echo $e['id_coupon'] ?>" class="chk" /></td>
				<td class="sniff" colspan="2"><a href="coupon?id_coupon=<?php echo $e['id_coupon'] ?>"><?php echo $e['couponName'] ?></a></td>
			</tr>
			<?php }
		}else{ ?>
			<tr>
				<td colspan="3" style="font-weight:bold; padding-top:30px; padding-bottom:30px;" align="center">
					Auncun coupon
				</td>
			</tr>
		<?php } ?>
		</tbody>
		<tfoot>
			<tr>
				<td></td>
				<td height="30">
					<a href="#" onClick="apply();" class="btn btn-mini">Supprimer la selection</a>
				</td>
			</tr>
		</tfoot>
	</table>
	</form>
	</div>
	
	<div class="span6">
		<?php
			if($message != NULL){
				list($class, $message) = $app->helperMessage($message);
				echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
			}
		?>
		
		<form action="coupon" method="post" id="data">
		<input type="hidden" name="action" value="1" />
		<input type="hidden" name="id_coupon" value="<?php echo $data['id_coupon'] ?>" />
		
		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="form">
			<tr>
				<td width="100">Nom</td>
				<td><input type="text" name="couponName" value="<?php echo $app->formValue($data['couponName'], $_POST['couponName']); ?>" /></td>
			</tr>
			<tr>
				<td>Code</td>
				<td><input type="text" name="couponCode" value="<?php echo $app->formValue($data['couponCode'], $_POST['couponCode']); ?>" /></td>
			</tr>
			<tr>
				<td>Mode</td>
				<td><select name="couponMode"><?php
					foreach(array('FIXE' => 'Reduction fixe', 'PERCENT' => 'Reduction (%)', 'CARRIAGE' => 'Frais de port offert') as $k => $v){
						$sel = ($app->formValue($data['couponMode'], $_POST['couponMode']) == $k) ? ' selected' : NULL;
						echo "<option value=\"".$k."\"".$sel.">".$v."</option>\n";
					}
				?></select></td>
			</tr>
			<tr>
				<td>Montant</td>
				<td><input type="text" name="couponAmount" value="<?php echo $app->formValue($data['couponAmount'], $_POST['couponAmount']); ?>" /></td>
			</tr>
			<tr valign="top">
				<td>Shop</td>
				<td><?php
					echo $app->apiLoad('shop')->shopSelector(array(
						'name'		=> 'id_shop[]',
						'id'		=> 'id_shop',
						'multi' 	=> true,
						'style' 	=> 'width:100%; height:100px',
						'profile'	=> true,
						'value'		=> $app->formValue($data['id_shop'], $_POST['id_shop'])
					));
				?></td>
			</tr>
			<tr>
				<td></td>
				<td>
					<a href="javascript:$('#data').submit();" class="btn btn-mini">Enregistrer</a>
					<a href="coupon" class="btn btn-mini">Nouveau</a>
				</td>
			</tr>
		</table>
	
		</form>
	</div>
	
</div></div>	

<?php include(COREINC.'/end.php'); ?>	
<script src="../core/vendor/datatables/jquery.dataTables.js"></script>
<script>

	function apply(){
		if(confirm("SUPPRIMER ?")){
			$('#listing').submit();
		}
	}

</script>

</body>
</html>