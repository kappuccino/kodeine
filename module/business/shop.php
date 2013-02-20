<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	// Remove
	if(sizeof($_POST['del']) > 0){
		foreach($_POST['del'] as $e){
			$app->apiLoad('shop')->shopRemove($e);
		}
		header("Location: shop");
	}else
	if($_POST['action']){
		$do = true;

		$def['k_shop'] = array(
			'allow_card'		=> array('value' => $_POST['allow_card'],		'zero'  => true),
			'allow_cheque'		=> array('value' => $_POST['allow_cheque'],		'zero'  => true),
			'allow_coupon'		=> array('value' => $_POST['allow_coupon'],		'zero'  => true),
			'shopName' 			=> array('value' => $_POST['shopName'], 		'check' => '.'),
			'shopApiFolder' 	=> array('value' => $_POST['shopApiFolder'], 	'check' => '.'),
			'shopMailTo'		=> array('value' => $_POST['shopMailTo']),
			'shopMailCc'		=> array('value' => $_POST['shopMailCc']),
			'shopMailBcc'		=> array('value' => $_POST['shopMailBcc']),
			'shopMailTitle'		=> array('value' => $_POST['shopMailTitle'],	'check' => '.'),
			'shopMailTemplate'	=> array('value' => $_POST['shopMailTemplate'],	'check' => '.'),
			'shopChequeOrder'	=> array('value' => $_POST['shopChequeOrder']),
			'shopChequeAddress'	=> array('value' => $_POST['shopChequeAddress'])
		);

		if(!$app->formValidation($def)) $do = false;

		if($do){
			$result = $app->apiLoad('shop')->shopSet($_POST['id_shop'], $def);

			$message = ($result)
				? 'OK: Enregistrement dans la base'
				: 'KO: Une erreur est survenue, APP:<br />'.$app->apiLoad('shop')->db_error;

		}else{
			$message = 'KO: Validation failed';
		}
	}

	if($_REQUEST['id_shop'] != NULL){
		$data = $app->apiLoad('shop')->shopGet(array(
			'id_shop'		=> $_REQUEST['id_shop'],
			'debug'			=> false
		));
	}

	$shop = $app->apiLoad('shop')->shopGet(array(
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
		<form action="shop" method="post" id="listing">
			<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing sortable">
				<thead>
					<tr>
						<th width="30" class="icone"><i class="icon-remove icon-white"></i></th>
						<th class="filter">
							<span>Nom</span>
							<input type="text" id="filter" class="input-small" />
						</th>
					</tr>
				</thead>
				<tbody><?php
				if(sizeof($shop) > 0){
					foreach($shop as $e){ $countchk++ ?>
					<tr class="<?php if($e['id_shop'] == $_REQUEST['id_shop']) echo "selected" ?>">
						<td class="check check-red"><input type="checkbox" class="chk" id="del-<?php echo $countchk ?>" name="del[]" value="<?php echo $e['id_shop'] ?>" /></td>
						<td class="sniff" colspan="2"><a href="shop?id_shop=<?php echo $e['id_shop'] ?>"><?php echo $e['shopName'] ?></a></td>
					</tr>
					<?php }
				}else{ ?>
					<tr>
						<td colspan="3" style="font-weight:bold; padding-top:30px; padding-bottom:30px;" align="center">
							Auncun shop
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
		
		<form action="shop" method="post" id="data">
		<input type="hidden" name="action" value="1" />
		<input type="hidden" name="id_shop" value="<?php echo $data['id_shop'] ?>" />
		
		<table cellpadding="0" cellspacing="0" border="0" class="form">
			<tr>
				<td width="150">Nom</td>
				<td><input type="text" name="shopName" value="<?php echo $app->formValue($data['shopName'], $_POST['shopName']); ?>" /></td>
			</tr>
			<tr>
				<td>Dossier API</td>
				<td><input type="text" name="shopApiFolder" value="<?php echo $app->formValue($data['shopApiFolder'], $_POST['shopApiFolder']); ?>" /></td>
			</tr>
			<tr>
				<td colspan="2">Autoriser</td>
			</tr>
			<tr>
				<td style="padding-left:20px;">Carte bleue</td>
				<td class="check check-green"><input type="checkbox" name="allow_card" class="chk" id="chk-cb" value="1" <?php if($app->formValue($data['allow_card'], $_POST['allow_card'])) echo 'checked'; ?> /></td>
			</tr>
			<tr>
				<td style="padding-left:20px;" class="check check-green">Chèque</td>
				<td class="check check-green"><input type="checkbox" name="allow_cheque" class="chk" id="chk-cheque" value="1" <?php if($app->formValue($data['allow_cheque'], $_POST['allow_cheque'])) echo 'checked'; ?> /></td>
			</tr>
			<tr>
				<td style="padding-left:20px;" class="check check-green">Coupon</td>
				<td class="check check-green"><input type="checkbox" name="allow_coupon" class="chk" id="chk-coupon" value="1" <?php if($app->formValue($data['allow_coupon'], $_POST['allow_coupon'])) echo 'checked'; ?> /></td>
			</tr>
			<tr valign="top">
				<td>Destinataire(s) mail<br /><i>un mail par ligne</i></td>
				<td><textarea name="shopMailTo" style="height:40px; width:90%;"><?php echo $app->formValue($data['shopMailTo'], $_POST['shopMailTo']) ?></textarea></td>
			</tr>
			<tr valign="top">
				<td>En copie</td>
				<td><textarea name="shopMailCc" style="height:40px; width:90%;"><?php echo $app->formValue($data['shopMailCc'], $_POST['shopMailCc']) ?></textarea></td>
			</tr>
			<tr valign="top">
				<td>Envois en copie cachée</td>
				<td><textarea name="shopMailBcc" style="height:40px; width:90%;"><?php echo $app->formValue($data['shopMailBcc'], $_POST['shopMailBcc']) ?></textarea></td>
			</tr>
			<tr>
				<td>Template</td>
				<td><input type="text" name="shopMailTemplate" value="<?php echo $app->formValue($data['shopMailTemplate'], $_POST['shopMailTemplate']); ?>" /></td>
			</tr>
			<tr>
				<td>Titre</td>
				<td><input type="text" name="shopMailTitle" value="<?php echo $app->formValue($data['shopMailTitle'], $_POST['shopMailTitle']); ?>" /></td>
			</tr>
			<tr>
				<td>Ordre du ch&egrave;que</td>
				<td><input type="text" name="shopChequeOrder" value="<?php echo $app->formValue($data['shopChequeOrder'], $_POST['shopChequeOrder']); ?>" /></td>
			</tr>
			<tr>
				<td>Adresse pour le ch&egrave;que</td>
				<td><textarea name="shopChequeAddress" style="height:40px; width:90%;"><?php echo $app->formValue($data['shopChequeAddress'], $_POST['shopChequeAddress']) ?></textarea></td>
			</tr>
			<tr>
				<td></td>
				<td>
					<a href="javascript:$('#data').submit();" class="btn btn-mini">Enregistrer</a>
					<a href="shop" class="btn btn-mini">Nouveau</a>
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