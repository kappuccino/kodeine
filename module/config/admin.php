<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	if($_POST['action']){

		$keys = array('brandName');
		foreach($keys as $k){
			$app->configSet('admin', $k, $_POST[$k]);
		}
		$app->go('admin');
	}

	$data	= $app->configGet('admin');
	$cookie = $app->filterGet('admin');

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

<div class="inject-subnav-right hide">
	<li><a href="./" class="btn btn-small"><?php echo _('Cancel') ?></a></li>
	<li><a onclick="$('#data').submit()" class="btn btn-small btn-success"><?php echo _('Save') ?></a></li>
</div>

<div id="app"><div class="wrapper">

	<?php if(isset($_GET['saved'])){
		echo '<div class="message messageValid">'._('Configuration updated').'</div>';
	} ?>
	
	<form action="admin" method="post" id="data">
		<input type="hidden" name="action" value="1" />
		
		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing">
			<thead>
				<tr>
					<th width="25%"><?php echo _('Parameter') ?></th>
					<th width="25%"><?php echo _('Value') ?></th>
					<th width="50%"><?php echo _('Explanation') ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php echo _('Brand name') ?></td>
					<td><input type="text" name="brandName" value="<?php echo $app->formValue($data['brandName'], $_POST['brandName']) ?>" style="width:80%;" /></td>
					<td><?php echo _('Top left name, default Kodeine') ?></td>
				</tr>
			</tbody>
		</table>
	</form>

</div></div>

<?php include(COREINC.'/end.php'); ?>
</body></html>