
<div id="top"><div class="wrapper clearfix">

	<div class="logo"><?php
		$cfg = $app->configGet('admin');
		echo ($cfg['brandName'] == '') ? 'Kodeine' : $cfg['brandName'];
	?></div>

	<ul class="right">
		<li><a href="../core/login?logout"><?php echo _('Logout') ?></a></li>
		<li><a href="../core/support" target="_blank"><?php echo _('Support'); ?></a></li>
		<li><a href="/" target="_blank"><?php echo _('Front office'); ?></a></li>
	</ul>

</div></div>

<div id="nav"><div class="wrapper clearfix">
	<ul><?php

	echo '<li class="home"><a href="../"><span>Home</span></a></li>';

	$mods = $app->moduleList();
	foreach($mods as $e){
		if(($app->userCan($e['key'].'.index') && $e['menu'] == 'YES') && ($e['config']['enabled'] == 'YES' || $e['key'] == 'user')){

			$class = (is_array($e['dependencies']) && sizeof($e['dependencies']) > 0)
				? '/('.implode('|', array_merge(array($e['key']), $e['dependencies'])).'){1,}/'
				: '/'.$e['key'].'/';

			$class = isMe($class) ? 'me' : NULL;
			echo '<li class="'.$class.'"><a href="../'.$e['key'].'/">'.$e['name'].'</a></li>';
		}
	}

	unset($mods, $e, $class);

	?></ul>
</div></div>
<?php unset($cfg); ?>
