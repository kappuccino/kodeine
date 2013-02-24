<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	$country 	= $app->countryGet(array('is_used' => true));
	$slave 		= $app->apiLoad('localisation')->localisationGet(array(
		'getSlave'	=> true,
		'master'	=> $_REQUEST['master'],
		'debug' 	=> false
	));

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(dirname(__DIR__).'/content/ui/menu.php')
?></header>

<div class="app"><div class="wrapper">

	<div class="mar-top-20 mar-bot-20">
		<a href="./" class="button rButton"><?php echo _('Back to the list'); ?></a>
	</div>

	<table border="0" cellpadding="0" cellspacing="0" class="listing">
		<thead>
			<tr>
				<th width="100"><i><?php echo $_REQUEST['master'] ?></i</th>
				<?php $colspan=0;foreach($country as $e){ ?>
				<th><?php echo $e['countryLanguage']; ?></th>
				<?php $colspan++; } ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach($slave as $e){
				$data = $app->apiLoad('localisation')->localisationGet(array(
					'label'	=> $_GET['master'].'_'.$e,
					'empty'	=> true,
					'debug' => false
				));
			?>
			<tr valign="top">
				<td><a href="./?master=<?php echo $_GET['master'] ?>&slave=<?php echo $e ?>"><?php echo $e ?></a></td>
				<?php foreach($data as $d){ ?>
				<td><?php echo $d['translation'] ?></td>
				<?php } ?>
			</tr>
			<?php } ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="<?php echo $colspan + 2 ?>"></td>
			</tr>
		</tfoot>
	</table>
	
</div></body>

</body></html>