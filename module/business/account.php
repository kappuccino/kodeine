<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	// Remove
	if(sizeof($_POST['del']) > 0){
		foreach($_POST['del'] as $e){
			$app->dbQuery("DELETE FROM k_businessaccount WHERE id_account=".$e);
		}
		$app->go('account');
	}else
	if($_POST['action']){
		$do = true;

		$def['k_businessaccount'] = array(
			'accountName'		=> array('value' => $_POST['accountName'], 	'check'	=> '.'),
			'accountNumber'		=> array('value' => $_POST['accountNumber'])
		);

		if(!$app->formValidation($def)) $do = false;

		if($do){
			$result = $app->apiLoad('business')->businessAccountSet($_POST['id_account'], $def);

			$message = ($result)
				? 'OK: Enregistrement dans la base'
				: 'KO: Une erreur est survenue, APP:<br />'.$app->db_error;

		}else{
			$message = 'KO: Validation failed';
		}

	}

	if($_REQUEST['id_account'] != NULL){
		$data = $app->apiLoad('business')->businessAccountGet(array(
			'id_account'	=> $_REQUEST['id_account'],
			'debug'			=> false
		));
	}

	$account = $app->apiLoad('business')->businessAccountGet();

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
		<form action="account" method="post" id="listing">
		
		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing sortable">
			<thead>
				<tr>
					<th width="30" class="icone"><i class="icon-remove icon-white"></i></th>
		            <th width="120">Intitul&eacute;</th>
					<th class="filter">
						<span>Num&eacute;ro</span>
						<input type="text" id="filter" class="input-small"/>
					</th>
				</tr>
			</thead>
			<tbody><?php
			if(sizeof($account) > 0){
				foreach($account as $e){ $countchk++ ?>
				<tr class="<?php if($e['id_account'] == $_REQUEST['id_account']) echo "selected" ?>">
					<td class="check check-red"><input type="checkbox" name="del[]" value="<?php echo $e['id_account'] ?>" class="cb chk" id="chkdel-<?php echo $countchk ?>" /></td>
					<td class="sniff" ><a href="../business/account?id_account=<?php echo $e['id_account'] ?>"><?php echo $e['accountName'] ?></a></td>
					<td class="sniff" colspan="2"><a href="../business/account?id_account=<?php echo $e['id_account'] ?>"><?php echo $e['accountNumber'] ?></a></td>
				</tr>
				<?php }
			}else{ ?>
				<tr>
					<td colspan="4" style="font-weight:bold; padding-top:30px; padding-bottom:30px;" align="center">
						Auncune donn&eacute;e
					</td>
				</tr>
			<?php } ?>
			</tbody>
			<tfoot>
				<tr>
					<?php if(sizeof($account) > 0){ ?>
					<td height="25" class="check check-red"><input id="chkdel-all" class="chk" type="checkbox" onchange="cbchange($(this));" /></td>
					<td colspan="2"><a href="#" onClick="apply();" class="btn btn-mini">Supprimer la s&eacute;l&eacute;ction</a></td>
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
		
		<form action="../business/account" method="post" id="data">
		<input type="hidden" name="action" value="1" />
		<input type="hidden" name="id_account" value="<?php echo $data['id_account'] ?>" />
		
		<table cellpadding="3" border="0" width="600">
	        <tr>
	            <td width="150">Intitul&eacute; du compte</td>
	            <td><input type="text" name="accountName" value="<?php echo $app->formValue($data['accountName'], $_POST['accountName']); ?>" /></td>
	        </tr>
			<tr>
				<td width="150">Num&eacute;ro du compte</td>
				<td><input type="text" name="accountNumber" value="<?php echo $app->formValue($data['accountNumber'], $_POST['accountNumber']); ?>" /></td>
			</tr>
			<tr>
				<td></td>
				<td>
					<a href="javascript:$('#data').submit();" class="btn btn-mini">Enregistrer</a>
					<a href="../business/account" class="btn btn-mini">Nouveau</a>
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
	
	function apply(){
		if(confirm("SUPPRIMER ?")){
			$('#listing').submit();
		}
	}
</script>

</body>
</html>