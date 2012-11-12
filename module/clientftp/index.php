<?php
#	if(!defined('APP')) die('No direct access allowed');
#
	if(!$app->apiLoad('clientftp')->configCloudCheck()) header("Location: pref?test");

	// Remove
	if(sizeof($_POST['del']) > 0){
		foreach($_POST['del'] as $e){
			$app->apiLoad('clientftp')->accountRemove(array('id' => $e));
		}
		header("Location: ./");
	}else
	if($_POST['action']){
		$do = true;

		if($_POST['userid'] == NULL) $do = false;
		if($_POST['passwd'] == NULL && $_POST['id'] == NULL) $do = false;
		if($_POST['homedir'] == NULL) $do = false;

		if($do){

			$def = array(
				'id'		=> $_POST['id'],
				'userid'	=> $_POST['userid'],
				'passwd'	=> $_POST['passwd'],
				'homedir'	=> $_POST['homedir'],
				'allowed'	=> $_POST['allowed']
			);

			$rest = $app->apiLoad('clientftp')->accountSet($def);

			$message = ($rest['success'])
				? 'OK: Enregistrement '
				: 'KO: Une erreur est survenue, APP:<br />'.$app->apiLoad('shop')->db_error;
				
			if($rest['success']){
				header("Location: ./?id=".$rest['id']."&message=".$message);
			}

		}else{
			$message = 'KO: Validation failed';
		}
	}

	if($_REQUEST['id'] != NULL){
		$data = $app->apiLoad('clientftp')->accountGet(array(
			'id'	=> $_REQUEST['id'],
			'debug'	=> true
		));
	}

	$account = $app->apiLoad('clientftp')->accountGet(array());

?><!DOCTYPE html>
<html lang="fr">
<head>
	<title>Kodeine</title>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>	

<div class="inject-subnav-right hide">
	<li>
		<a href="./" class="btn btn-mini">Nouveau</a>
	</li>
	<li>
		<a onclick="$('#data').submit();" class="btn btn-mini btn-success">Enregistrer</a>
	</li>
</div>

<div id="app"><div class="wrapper"><div class="row-fluid">
	
	<div class="span6">
		<form action="./" method="post" id="listing">
		
		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing sortable">
			<thead>
				<tr>
					<th width="30" class="icone"><i class="icon-remove icon-white"></i></th>
					<th>Nom</th>
					<th class="filter"><input type="text" class="input-small" placeholder="filtrer..." id="filter"/></th>
				</tr>
			</thead>
			<tbody><?php
			if(sizeof($account) > 0){
				foreach($account as $e){ ?>
				<tr class="<?php if($e['id'] == $_REQUEST['id']) echo "selected" ?>">
					<td><input type="checkbox" name="del[]" value="<?php echo $e['id'] ?>" /></td>
					<td class="sniff" colspan="2"><a href="./?id=<?php echo $e['id'] ?>"><?php echo ($e['LoginAllowed'] == 'true') ? $e['userid'] : "<strike>".$e['userid']."</strike>" ?></a></td>
				</tr>
				<?php }
			}else{ ?>
				<tr>
					<td colspan="3" style="font-weight:bold; padding-top:30px; padding-bottom:30px;" align="center">
						Auncun compte
					</td>
				</tr>
			<?php } ?>
			</tbody>
			<tfoot>
				<tr>
					<td></td>
					<td height="30"><a href="#" onClick="remove();" class="btn btn-mini">Supprimer la selection</a></td>
				</tr>
			</tfoot>
		</table>
		</form>
	</div>
	
	<div class="span6">
	
		<?php
			if(isset($_GET['message'])) $message = $_GET['message'];
	
			if($message != NULL){
				list($class, $message) = $app->helperMessage($message);
				echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
			}
		?>
	
		<form action="./" method="post" id="data">
		<input type="hidden" name="action" value="1" />
		<input type="hidden" name="id" value="<?php echo $data['id'] ?>" />
		
		<table cellpadding="3" border="0" width="600">
			<tr>
				<td width="150">Identifiant</td>
				<td width="70" align="right"><?php echo $app->apiLoad('clientftp')->conf['prefixe']; ?></td>
				<td><input type="text" name="userid" value="<?php echo $app->formValue($data['userid'], $_POST['userid']); ?>" /></td>
			</tr>
			<tr valign="top">
				<td colspan="2">Mot de passe</td>
				<td>
					<input type="text" name="passwd" value="<?php echo $app->formValue('', $_POST['passwd']); ?>" /><br />
					<?php if($data['id']){ ?>
					<i>Laisser ce champ vide pour ne pas modifier le mot de passe</i>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<td colspan="2">Dossier</td>
				<td><input type="text" name="homedir" value="<?php echo $app->formValue($data['homedir'], $_POST['homedir']); ?>" size="40" /></td>
			</tr>
			<tr>
				<td colspan="2">Autorisé</td>
				<td><input type="checkbox" name="allowed" value="true" <?php if($app->formValue($data['LoginAllowed'], $_POST['LoginAllowed']) == 'true') echo 'checked'; ?> /></td>
			</tr>
			<?php if($_REQUEST['id']){ ?>
			<tr>
				<td colspan="3" height="25">&nbsp;</td>
			</tr>
			<tr>
				<td>Nombre de connexion</td>
				<td colspan="2"><?php echo $data['count'] ?></td>
			</tr>
			<tr>
				<td>Dernière connection</td>
				<td colspan="2"><?php echo $data['accessed'] ?></td>
			</tr>
			<? } ?>
		</table>
		</form>
	</div>
	
</div></div></div>

<?php include(COREINC.'/end.php'); ?>
<script src="/app/module/core/ui/_datatables/jquery.dataTables.js"></script>
<script>

	function remove(){
		if(confirm("SUPPRIMER ?")){
			$('#listing').submit();
		}
	}

</script>


</body></html>