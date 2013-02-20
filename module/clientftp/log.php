<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	if(!$app->apiLoad('clientftp')->configCloudCheck()) header("Location: pref.php?test");

	$dir	= ($_GET['dir'] == '')		? 'DESC' : $_GET['dir'];
	$d		= ($dir == 'ASC')			? 'DESC' : 'ASC';

	$order	= ($_GET['order'] == NULL)	? 'date' : $_GET['order'];
	$offset	= ($_GET['offset'] == NULL) ? 0 	 : $_GET['offset'];
	$limit	= 200;

	$data	= $app->apiLoad('clientftp')->logGet(array(
		'offset'	=> $offset,
		'limit'		=> $limit,
		'order'		=> $order,
		'direction'	=> $dir
	));
	
	$data['log'] = is_array($data['log']) ? $data['log'] : array();

	$conf = $app->apiLoad('clientftp')->conf;

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

<div id="app">

	<table width="100%" border="0" cellpadding="3" cellspacing="0" class="listing">
		<thead>
			<tr>
				<th width="100"	class="order <?php if($order == 'ip') 		echo 'order'.$d; ?>" onClick="document.location='log.php?dir=<?php echo $d; ?>&order=ip'">IP</th>
				<th width="150"	class="order <?php if($order == 'user') 	echo 'order'.$d; ?>" onClick="document.location='log.php?dir=<?php echo $d; ?>&order=user'">Compte</th>
				<th				class="order <?php if($order == 'file') 	echo 'order'.$d; ?>" onClick="document.location='log.php?dir=<?php echo $d; ?>&order=file'">Fichier</th>
				<th width="80"	class="order <?php if($order == 'cmd') 		echo 'order'.$d; ?>" onClick="document.location='log.php?dir=<?php echo $d; ?>&order=cmd'">CMD</th>
				<th width="70"	class="order <?php if($order == 'bytes')	echo 'order'.$d; ?>" onClick="document.location='log.php?dir=<?php echo $d; ?>&order=bytes'">Poids</th>
				<th width="70"	class="order <?php if($order == 'during')	echo 'order'.$d; ?>" onClick="document.location='log.php?dir=<?php echo $d; ?>&order=during'"><span>Temps</span></th>
				<th width="150"	class="order <?php if($order == 'date')		echo 'order'.$d; ?>" onClick="document.location='log.php?dir=<?php echo $d; ?>&order=date'"><span>Date</span<</th>
			</tr>
		</thead>
		<tbody><?php foreach($data['log'] as $e){ ?>
			<tr>
				<td><?php echo $e['ip'] ?></td>
				<td><?php echo str_replace($conf['prefixe'], '', $e['user']) ?></td>
				<td><?php echo str_replace($conf['homedir'], '', $e['file']) ?></td>
				<td><?php echo $e['cmd'] ?></td>
				<td><?php echo $e['bytes'] ?></td>
				<td><?php echo $e['during'] ?></td>
				<td><?php echo $e['date'] ?></td>
			</tr>
		<?php } ?></tbody>
		<?php if(sizeof($data['log']) > 0){ ?>
		<tfoot>
			<tr>
				<td class="pagination" colspan="7">
					<?php $app->pagination($data['total'], $limit, $offset, 'log.php?offset=%s'); ?>
				</td>
			</tr>
		</tfoot>
		<?php } ?>
	</table>

</div>

</body></html>