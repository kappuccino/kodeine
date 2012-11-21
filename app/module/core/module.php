<?php
	if(!defined('COREINC')) die('@');

?><!DOCTYPE html>
<html lang="fr">
<head>
	<title>Kodeine</title>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/menu.php')
?></header>

<div class="inject-subnav-right hide">
	<li>
		<span><?php if(file_exists(APP.'/module/core/ui/css/_style.css')){ ?>
		<a href="/admin/core/less?nocss">Utiliser le LESS</a>
		<?php }else{ ?>
		<a href="/admin/core/less?compile">Utiliser le CSS</a>
		<?php } ?></span>
	</li>
	<li>
		<a class="btn btn-small btn-success" onclick="checkRepo()">Verifier les mise &agrave; jour</a>
	</li>
</div>

<div id="app" class="clearfix"><div class="wrapper">

	<table cellpadding="0" cellspacing="0" border="0" class="listing">
		<thead>
			<tr>
				<th>Module</th>
				<th width="100">Installé</th>
				<th width="100">Activé</th>
				<th width="100">Patch</th>

				<th width="80">Version</th>
				<th width="80">Dernière</th>
				<th width="100"></th>
			</tr>
		</thead>
		<tbody>

		<?php
			
		$mods = $app->moduleList(array(
			'dependencies'	=> true,
			'all'			=> true
		));

		foreach($mods as $e){ $core = $e['isCore'] ? 'true' : 'false'; ?>
			<tr>
				<td>
					<div class="left"><?php echo $e['name']; ?></div>
					<div class="right" id="log-<?php echo $e['key']; ?>"></div>
				</td>
				<td><?php
					if($e['install'] == 'YES' && ($e['key'] != 'user' OR $e['key'] != 'core')){
						echo ($e['config']['installed'] != 'YES')
							? '<a onclick="install(this, \''.$e['key'].'\', '.$core.')" class="btn btn-small btn-install">Install</a>'
							: '<i class="icon-ok"></i>';
					}
				?></td>
				<td><?php
					if($e['key'] != 'user' && $e['key'] != 'core'){

						$class  = ($e['config']['enabled'] == 'YES') ? "on" : "off";

						echo '<a onclick="toggleSlider($(this), enabled, disabled)" class="toggleslider-small '.$class.'" data-mod="'.$e['key'].'" data-core="'.$core.'"></a>';
					}
				?></td>
				<td><?php

					if($e['needPatch']){
						echo '<a onclick="patch(this, \''.$e['key'].'\', false, '.$core.')" class="btn btn-small btn-patch">Patch</a>';
					}else
					if($e['rePatch']){
						echo '<a onclick="patch(this, \''.$e['key'].'\', true, '.$core.')" class="btn btn-small btn-patch">rePatch</a>';
					}
				?></td>

				<td id="<?php echo 'this-'.$e['key'] ?>"><?php echo ($e['version'] != '') ? $e['version'] : '-'; ?></td>
				<td id="<?php echo 'repo-'.$e['key'] ?>"></td>
				<td id="<?php echo 'upgd-'.$e['key'] ?>"></td>
			</tr>
		<?php if(sizeof($e['dependencies']) > 0){ foreach($e['dependencies'] as $d){ $core = $d['isCore'] ? 'true' : 'false'; ?>
			<tr>
				<td style="padding-left:30px"><?php echo $d['name']; ?></td>
				<td><?php if($d['config']['installed'] == 'YES') echo '<i class="icon-ok"></i>'; ?></td>
				<td><?php
					if($e['key'] != 'core'){

						$class = ($d['config']['enabled'] == 'YES') ? "on" : "off";

						echo '<a onclick="toggleSlider($(this), enabled, disabled)" class="toggleslider-small '.$class.'" data-mod="'.$d['key'].'" data-core="'.$core.'"></a>';
				}
				?></td>
				<td><?php
					if($d['needPatch'] == 'YES'){
						echo '<a onclick="patch(this, \''.$d['key'].'\', false, '.$core.')" class="btn btn-small btn-patch">Patch</a>';
					}else
					if($e['rePatch']){
						echo '<a onclick="patch(this, \''.$d['key'].'\', true, '.$core.')" class="btn btn-small btn-patch">rePatch</a>';
					}
				?></td>

				<td id="<?php echo 'this-'.$d['key'] ?>"><?php echo ($d['version'] != '') ? $d['version'] : '-'; ?></td>
				<td id="<?php echo 'repo-'.$d['key'] ?>"></td>
				<td id="<?php echo 'upgd-'.$d['key'] ?>"></td>
			</tr>
		<?php }}} ?>
		</tbody>
	</table>

	<?php #$app->pre($mods); ?>

</div></div>

<?php include(COREINC.'/end.php'); ?>
<script src="/admin/core/ui/js/module.js" type="text/javascript"></script>

</body>
</html>