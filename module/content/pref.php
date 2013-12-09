<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	if($_POST['action']){
		foreach($_POST['pref'] as $k => $p){
			$app->configSet('content', $k, $p);
		}
		$app->go('pref');
	}

	$pref = $app->configGet('content');

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
	<li><a onclick="$('#f').submit();" class="btn btn-success btn-mini"><?php echo _('Save'); ?></a></li>
</div>

<div id="app"><div class="wrapper">
		
	<form action="pref" method="post" id="f">
		<input type="hidden" name="action" value="1" />

		<table border="0" cellpadding="0" cellspacing="0" class="listing">
			<thead>
				<tr>
					<th width="20%"><?php echo _('Parameter'); ?></th>
					<th width="5%"><?php echo _('Value'); ?></th>
					<th width="75%">&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php echo _('Remove item'); ?></td>
					<td class="check-green">
						<input type="hidden"   name="pref[galleryItemRemove]" value="0" />
						<input type="checkbox" name="pref[galleryItemRemove]" value="1" <?php if($pref['galleryItemRemove']) echo 'checked' ?> id="usecache" />
					</td>
					<td><?php echo _('Remove a gallery item, remove the real file linked to it. Could be dangerous, if two items use the same file. Use with caution'); ?></td>
				</tr>
				<tr>
					<td><?php echo _('Item roll'); ?></td>
					<td class="check-green">
						<input type="hidden"   name="pref[galleryItemRoll]" value="0" />
						<input type="checkbox" name="pref[galleryItemRoll]" value="1" <?php if($pref['galleryItemRoll']) echo 'checked' ?> id="usecache" />
					</td>
					<td><?php echo _('Display the entire roll'); ?></td>
				</tr>
				<tr>
					<td><?php echo _('Generate chao in upload file name'); ?></td>
					<td class="check-green" colspan="2">
						<input type="radio" name="pref[galleryUploadChao]" value=""      <?php if($pref['galleryUploadChao'] == '')      echo 'checked' ?> /> Before &nbsp;&nbsp;
						<input type="radio" name="pref[galleryUploadChao]" value="after" <?php if($pref['galleryUploadChao'] == 'after') echo 'checked' ?> /> After &nbsp;&nbsp;
						<input type="radio" name="pref[galleryUploadChao]" value="none"  <?php if($pref['galleryUploadChao'] == 'none')  echo 'checked' ?> /> None
					</td>
				</tr>
			</tbody>
		</table>

	</form>
		
</div></div>

<?php include(COREINC.'/end.php'); ?>

</body></html>