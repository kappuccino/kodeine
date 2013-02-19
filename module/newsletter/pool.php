<?php
	require(dirname(dirname(__FILE__)).'/api/core.admin.php');
	$app = new coreAdmin();
	$api = $app->apiLoad('newsletter');

	if(!$app->userIsAdmin) header("Location: ./");

	$pref = $app->configGet('newsletter');
	$rest = new newsletterREST($pref['auth'], $pref['passw']);

	# Vider les newsletter
	#
	if(sizeof($_POST['empty']) > 0){
		foreach($_POST['empty'] as $e){
			if($e != '') $ids[] = $e;
		}

		if(sizeof($ids) > 0){
			$raw = $rest->request('/controller.php', 'POST', array(
				'poolClear' 	=> true,
				'id_newsletter'	=> $ids
			));
			$app->dbQuery("UPDATE k_newsletter SET newsletterSendDate=NULL WHERE id_newsletter IN(".implode(',', $ids).")");
			header("Location: newsletter.pool.php");
		}
	}

	$pool = $rest->request('/controller.php', 'POST', array(
		'poolStatus' => true
	));

	$pool = json_decode($pool, true);

	include(ADMINUI.'/doctype.php');
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
	<?php include(ADMINUI.'/head.php'); ?>
</head>
<body>
<div id="pathway">
	<a href="core.panel.php">Admin</a> &raquo;
	<a href="newsletter.index.php">Newsletter</a> &raquo;
	<a href="newsletter.pool.php">Pool</a>
</div>

<?php include('ressource/ui/menu.newsletter.php'); ?>

<div class="app">

<?php if(sizeof($pool['pool']) > 0){ ?>

	<form method="post" aciton="newsletter.pool.php" id="pool" name="pool">
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing">
		<thead>
			<tr>
				<th width="30" class="icone"><img src="ressource/img/ico-delete-th.png" height="20" width="20" /></th>
				<th width="50"></th>
				<th width="100">En attente</th>
				<th>Titre</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($pool['pool'] as $e){ ?>
			<tr>
				<td class="select"><input type="checkbox" class="checkbox checkboxDel" name="empty[]" value="<?php echo $e['id_newsletter']; ?>" /></td>
				<td></td>
				<td><?php echo $e['total'] ?></td>
				<td><a href="newsletter.data.php?id_newsletter=<?php echo $e['id_newsletter'] ?>"><?php echo $e['title'] ?></a></td>
			</tr>
			<?php } ?>
		</tbody>
		<tfoot>
			<tr>
				<td height="25"><input type="checkbox" onClick="$$('.checkboxDel').set('checked', this.checked);" /></td>
				<td colspan="3"><a href="javascript:confirmClean();" class="button rButton">Vider le pool des mailings selectionnées</a></td>	
			</tr>
		</tfoot>
	</table>
	</form>

<?php }else{ ?>
	<div style="font-weight:bold; font-size:14px; text-align:center; padding-top:50px; color:#808080;">
		Il n'y a aucun mail en attente dans le pool
	</div>

<?php } ?>

<script>

	function confirmClean(){
		if(confirm("Voulez vous vraiment vider la queue des newsletter selectionnés ?\n\nNOTE\nCELA SUPPRIMATE TOUS LES MAILS EN ATTENTE, aucun mail ne sera envoyé")){
			$('pool').submit();
		}else{
			return false;
		}
	}

</script>


</div></body></html>