
<?php
	$cfg = $app->configGet('admin');
?>
<div id="top"><div class="wrapper clearfix">
	<div class="logo"><?php echo ($cfg['brandName'] == '') ? 'Kodeine' : $cfg['brandName']; ?></div>

	<ul class="right">
		<li><a href="/admin/core/login?logout">Se déconnecter</a></li>
		<li><a href="/admin/core/support" target="_blank">Support</a></li>
		<li><a href="/" target="_blank">Ouvrir le site</a></li>
	</ul>

</div></div>

<div id="nav"><div class="wrapper clearfix">
	<ul><?php

	echo '<li class="home"><a href="/admin/"><span>Home</span></a></li>';

	$mods = $app->moduleList();
	foreach($mods as $e){

		/*$e['config']['installed'] == 'YES' &&*/ 
		//if($app->userCan($e['key'].'.index') && $e['menu'] == 'YES' && ($e['config']['enabled'] == 'YES') OR ($e['key'] == 'user')){
		//if($app->userCan($e['key'].'.index') && $e['menu'] == 'YES' && ($e['config']['enabled'] == 'YES') OR ($e['key'] == 'user')){
        //$app->pre($e);
		if(($app->userCan($e['key'].'.index') && $e['menu'] == 'YES') && ($e['config']['enabled'] == 'YES' || $e['key'] == 'user')){

			$class = (is_array($e['dependencies']) && sizeof($e['dependencies']) > 0)
				? '/('.implode('|', array_merge(array($e['key']), $e['dependencies'])).'){1,}/'
				: '/'.$e['key'].'/';

			$class = isMe($class) ? 'me' : NULL;			
			echo '<li class="'.$class.'"><a href="/admin/'.$e['key'].'/">'.$e['name'].'</a></li>';
		}
	}

	unset($mods, $e, $class);

	?></ul>
</div></div>
<?php unset($cfg); ?>
