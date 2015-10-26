<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	// Remove
	if(sizeof($_POST['del']) > 0){
		foreach($_POST['del'] as $e){
			$app->dbQuery("DELETE FROM k_businesscarrier WHERE id_carrier=".$e);
		}
		$app->go('carrier');
	}else
	if($_POST['action']){
		$do = true;

		$def['k_businesscarrier'] = array(
			'carrierName' => array('value' => $_POST['carrierName'], 'check' => '.'),
			'carrierUrl'  => array('value' => $_POST['carrierUrl'], 'check' => '.')
		);

		if(!$app->formValidation($def)) $do = false;

		if($do){
			$result  = $app->apiLoad('business')->businessCarrierSet($_POST['id_carrier'], $def);
			$message = ($result)
				? 'OK: Enregistrement dans la base'
				: 'KO: Une erreur est survenue, APP:<br />'.$app->db_error;
		}else{
			$message = 'KO: Validation failed';
		}
	}

	if($_REQUEST['id_carrier'] != NULL){
		$data = $app->apiLoad('business')->businessCarrierGet(array(
			'id_carrier'	=> $_REQUEST['id_carrier'],
			'debug'			=> false
		));
	}

	$carrier = $app->apiLoad('business')->businessCarrierGet();

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
	<form action="carrier" method="post" id="listing">

	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing sortable">
		<thead>
			<tr>
				<th width="30" class="icone"><i class="icon-remove icon-white"></i></th>
				<th colspan="2" class="filter">
					<span>Nom</span>
					<input type="text" class="input-small" id="filter"/>
				</th>
			</tr>
		</thead>
		<tbody><?php

		if(sizeof($carrier) > 0){
			foreach($carrier as $e){ $countchk++; ?>
			<tr class="<?php if($e['id_carrier'] == $_REQUEST['id_carrier']) echo "selected" ?>">
				<td><input type="checkbox" name="del[]" value="<?php echo $e['id_carrier'] ?>" class="cb chk" id="chk-del<?php echo $countchk ?>" /></td>
				<td class="sniff" colspan="2"><a href="carrier?id_carrier=<?php echo $e['id_carrier'] ?>"><?php echo $e['carrierName'] ?></a></td>
			</tr>
			<?php }
		}else{ ?>
			<tr>
				<td colspan="3" style="font-weight:bold; padding-top:30px; padding-bottom:30px;" align="center">
					Auncune donnée
				</td>
			</tr>
		<?php } ?>
		</tbody>
		<tfoot>
			<tr>
				<?php if(sizeof($carrier) > 0){ ?>
				<td height="25"><input type="checkbox" id="chk-del-all" class="chk" onchange="cbchange($(this))" /></td>
				<td><a href="#" onClick="apply();" class="btn btn-mini">Supprimer la selection</a></td>
			</tr>
			<?php }else{ ?>
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>
			<?php } ?>
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
		
		<form action="carrier" method="post" id="data">
		<input type="hidden" name="action" value="1" />
		<input type="hidden" name="id_carrier" value="<?php echo $data['id_carrier'] ?>" />
		
		<table cellpadding="0" cellspacing="0" border="0" class="form">
			<tr>
				<td width="100">Nom</td>
				<td><input type="text" name="carrierName" value="<?php echo $app->formValue($data['carrierName'], $_POST['carrierName']); ?>" /></td>
			</tr>
			<tr>
				<td>Url</td>
				<td><input type="text" name="carrierUrl" value="<?php echo $app->formValue($data['carrierUrl'], $_POST['carrierUrl']); ?>" /></td>
			</tr>
			<tr>
				<td></td>
				<td>
					<a href="javascript:$('#data').submit();" class="btn btn-mini">Enregistrer</a>
					<a href="carrier" class="btn btn-mini">Nouveau</a>
				</td>
			</tr>
		</table>
		</form>
	</div>
	
</div></div>

<?php include(COREINC.'/end.php'); ?>
<script src="../core/vendor/datatables/jquery.dataTables.js"></script>
<script>
	function cbchange(that) {
		var state = that.prop('checked');
		if (state) {
			$('.cb').prop('checked', true).siblings('label').addClass('ui-state-active');
		} else {
			$('.cb').prop('checked', false).siblings('label').removeClass('ui-state-active');
		}
	}
	function cschange(that) {
		var state = that.prop('checked');
		if (state) {
			$('.cs').prop('checked', true).siblings('label').addClass('ui-state-active');
		} else {
			$('.cs').prop('checked', false).siblings('label').removeClass('ui-state-active');
		}
	}
	
	function apply(){
		if(confirm("SUPPRIMER ?")){
			$('#listing').submit();
		}
	}

</script>

</body>
</html>