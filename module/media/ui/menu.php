<div id="sub_nav" class="text <?php #$tmp = $app->filterGet('admin'); echo ($tmp['adminSubMenu'] == '') ? 'icon text' : $tmp['adminSubMenu']; unset($tmp) ?>"><div class="wrapper clearfix">

	<ul class="left">
		<?php if(!isMe('/media/pref')){ ?>
		<li><a id="button-folder">Actualiser</a>
        <?php if($app->userCan('media.create')) { ?>
		<li><a id="button-newdir">Nouveau dossier</a></li>
        <?php } ?>
        <?php if($app->userCan('media.upload')) { ?>
		<li><a id="button-upload">Envoyer des fichiers</a></li>
        <?php } ?>
		<li><a id="button-hidepanel">Masquer la zone</a></li>

		<li class="clearfix<?php echo isMe('/media/pref') ? ' me':'' ?>">
			<a href="pref" target="_blank">Pr&eacute;f&eacute;rences</a>
		</li>

		<?php }else{ ?>

		<li class="clearfix">
			<a href="./">Retour</a>
		</li>
		<?  } ?>
	</ul>
	
	<div class="right">
		<div id="slider"></div>
	</div>
	
</div></div>
