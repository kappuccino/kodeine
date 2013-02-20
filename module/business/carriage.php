<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	// Remove
	if(sizeof($_POST['del']) > 0){
		foreach($_POST['del'] as $e){
			$app->dbQuery("DELETE FROM k_businesscarriage WHERE id_carriage=".$e);
		}
		$app->go('carriage');
	}else
	if($_POST['action']){
		$do = true;

		$def['k_businesscarriage'] = array(
			'is_gift'			=> array('value' => $_POST['is_gift'],			'zero'	=> true),
			'carriageName'		=> array('value' => $_POST['carriageName'], 	'check'	=> '.'),
			'carriagePrice'		=> array('value' => $_POST['carriagePrice']),
			'carriageRule'		=> array('value' => $_POST['carriageRule']),
            'carriageTax'      => array('value' => $_POST['carriageTax'])
		);

		if(!$app->formValidation($def)) $do = false;

		if($do){
			$result  = $app->apiLoad('business')->businessCarriageSet($_POST['id_carriage'], $def);
			$message = ($result)
				? 'OK: Enregistrement dans la base'
				: 'KO: Une erreur est survenue, APP:<br />'.$app->db_error;
		}else{
			$message = 'KO: Validation failed';
		}
	}

	if($_REQUEST['id_carriage'] != NULL){
		$data = $app->apiLoad('business')->businessCarriageGet(array(
			'id_carriage'	=> $_REQUEST['id_carriage'],
			'debug'			=> false
		));
	}

	$carriage = $app->apiLoad('business')->businessCarriageGet();

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
	<form action="carriage" method="post" id="listing">

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

		if(sizeof($carriage) > 0){
			foreach($carriage as $e){ $countchk++; ?>
			<tr class="<?php if($e['id_carriage'] == $_REQUEST['id_carriage']) echo "selected" ?>">
				<td><input type="checkbox" name="del[]" value="<?php echo $e['id_carriage'] ?>" class="cb chk" id="chk-del<?php echo $countchk ?>" /></td>
				<td class="sniff" colspan="2"><a href="carriage?id_carriage=<?php echo $e['id_carriage'] ?>"><?php echo $e['carriageName'] ?></a></td>
			</tr>
			<?php }
		}else{ ?>
			<tr>
				<td colspan="3" style="font-weight:bold; padding-top:30px; padding-bottom:30px;" align="center">
					Auncune donnée
				</td>
			</tr>
		<?php } ?>
		</tbody>
		<tfoot>
			<tr>
				<?php if(sizeof($carriage) > 0){ ?>
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
		
		<form action="carriage" method="post" id="data">
		<input type="hidden" name="action" value="1" />
		<input type="hidden" name="id_carriage" value="<?php echo $data['id_carriage'] ?>" />
		
		<table cellpadding="0" cellspacing="0" border="0" class="form">
			<tr>
				<td width="100">Nom</td>
				<td><input type="text" name="carriageName" value="<?php echo $app->formValue($data['carriageName'], $_POST['carriageName']); ?>" /></td>
			</tr>
			<tr>
				<td>Port offert</td>
				<td class="check check-green"><input type="checkbox" class="chk" name="is_gift" value="1" id="chk-port-offert" <?php if($app->formValue($data['is_gift'], $_POST['is_gift'])) echo ' checked' ?> /></td>
			</tr>
	        <tr>
	            <td>Prix fixe</td>
	            <td><input type="text" name="carriagePrice" value="<?php $carriagePrice = $app->formValue($data['carriagePrice'], $_POST['carriagePrice']); echo ($carriagePrice > 0) ? $carriagePrice : '' ?>" size="10" /></td>
	        </tr>
	        <tr>
	            <td>Taux TVA</td>
	            <td>
	                <select name="carriageTax"><option value="">Aucun</option>
	                    <?php
	                        foreach($app->apiLoad('business')->businessTaxGet() as $e){
	                            $sel = ($data['carriageTax'] == $e['tax']) ? ' selected' : NULL;
	                            echo "<option value=\"".$e['tax']."\"".$sel.">".$e['tax']." %</option>";
	                        }
	                    ?>
	                </select>
	            </td>
	        </tr>
			<tr>
				<td colspan="2">Regle</td>
			</tr>
			<tr>
				<td colspan="2">
				<textarea name="carriageRule" style="height:150px; width:90%;"><?php echo $app->formValue($data['carriageRule'], $_POST['carriageRule']); ?></textarea>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<a href="javascript:$('#data').submit();" class="btn btn-mini">Enregistrer</a>
					<a href="carriage" class="btn btn-mini">Nouveau</a>
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