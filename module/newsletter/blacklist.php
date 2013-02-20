<?php
	# Get data
	#
	$api	= $app->apiLoad('newsletter');
	$pref	= $app->configGet('newsletter');

	$rest	= new newsletterREST($pref['auth'], $pref['passw']);
	$black	= $rest->request('/controller.php', 'POST', array('blackList' => true));
	$black	= json_decode($black, true);


	# Mettre a jour les listes locales
	#
	if(isset($_GET['update']) && sizeof($black['list']) > 0){
		
		foreach($black['list'] as $e){

			# (1) Trouver le mail correspondant
			#
			$mail = $app->dbOne("SELECT * FROM k_newslettermail WHERE mail='".$e['mail']."'");
		
			# (2) Supprimer
			#
			if($mail['id_newslettermail'] > 0){
				if($e['reason'] == 'unsubscribe'){
					$flag = 'IGNORE';
				}else
				if($e['reason'] == 'hard'){
					$flag = 'BOUNCE';
				}

				if($flag != ''){
					$app->dbQuery("UPDATE k_newslettermail SET flag = '".$flag."' WHERE id_newslettermail = ".$mail['id_newslettermail']);
				}

				unset($flag);
			}

			$inlist[] = $e['mail'];
		}

		# (3) Indiquer au cloud qu'on a mis a jour
		#
		if(sizeof($inlist) > 0){
			$black	= $rest->request('/controller.php', 'POST', array(
				'blackListUpdate'	=> true,
				'mail'				=> $inlist
			));
		}

		header("Location: blacklist");
		exit();
	}

?><!DOCTYPE html>
<head>
	<?php include(COREINC.'/head.php'); ?>
</head>

<body>
<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div id="app"><div class="wrapper">
<a href="blacklist?all" class="btn btn-mini">Consulter la liste compl&egrave;te des adresses bloqu&eacute;es sur le serveur d'envois</a>

<?php if(isset($_GET['all'])){

	$all = $rest->request('/controller.php', 'POST', array('blackList' => true, 'full' => true));
	$all = json_decode($all, true);

?>
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing" style="margin-top:30px;">
		<thead>
			<tr>
				<th>mail</th>
				<th width="200">date</th>
				<th width="100">reason</th>
				<th width="100">inlist</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($all['list'] as $e){ ?>
			<tr>
				<td><?php echo $e['mail'] ?></td>
				<td><?php echo $e['date'] ?></td>
				<td><?php echo $e['reason'] ?></td>
				<td><?php echo $e['inlist'] ?></td>
			</tr>
		<?php } ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="4"></td>
			</tr>
		</tfoot>
	</table>

	<p>.</p>

<?php }else if(sizeof($black['list']) > 0){ ?>

	<div style="height:30px">
		<a href="newsletter.blacklist.php?update" class="button rButton">Mettre a jour mes listes</a>
	</div>
	
	<table border="0" width="100%" cellpadding="0" cellspacing="0" class="listing">
		<thead>
			<tr>
				<th>Mail</th>
				<th width="150">Date</th>
				<th width="150">Raison</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($black['list'] as $e){ ?>
			<tr>
				<td><?php echo $e['mail'] ?></td>
				<td><?php echo $e['date'] ?></td>
				<td><?php echo $e['reason'] ?></td>
			</tr>
			<?php } ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="3"></td>	
			</tr>
		</tfoot>
	</table>

<?php }else{ ?>

	<div style="font-weight:bold; font-size:14px; text-align:center; padding-top:50px; color:#808080;">
		Aucune nouvelle donnée, vos listes d'envois sont à jour
	</div>
	

<?php } ?>
</div>
</div>
</body></html>