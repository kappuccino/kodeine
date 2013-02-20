
<div id="sub_nav" class="text"><div class="wrapper clearfix">

	<ul class="left">
		<li class="clearfix<?php echo isMe('/config/$') ? ' me':'' ?>">
			<a href="/admin/config/"><span><?php echo _('Configuration') ?></span></a>
		</li>

		<?php if($app->userCan('config.backoffice')){ ?>
		<li class="clearfix<?php echo isMe('/config/admin') ? ' me':'' ?>">
			<a href="/admin/config/admin"><span><?php echo _('Back Office') ?></span></a>
		</li>
		<?php } ?>

		<?php if($app->userCan('config.theme')){ ?>
		<li class="clearfix<?php echo isMe('/theme/') ? ' me':'' ?>">
			<a href="/admin/theme/"><span><?php echo _('Thême') ?></span></a>
		</li>
		<?php } ?>

		<?php if($app->userCan('config.field')){ ?>
		<li class="clearfix<?php echo isMe('/config/field') ? ' me':'' ?>">
			<a href="/admin/config/field"><span><?php echo _('Champs') ?></span></a>
		</li>
		<?php } ?>

		<li class="clearfix<?php echo isMe('/config/offline') ? ' me':'' ?>">
			<a href="/admin/config/offline"><span><?php echo _('Hors ligne') ?></span></a>
		</li>

		<?php if($app->userCan('config.robot')){ ?>
		<li class="clearfix<?php echo isMe('/config/robots') ? ' me':'' ?>">
			<a href="/admin/config/robots"><span><?php echo _('Robots.txt') ?></span></a>
		</li>
		<?php } ?>

		<li class="clearfix<?php echo isMe('/config/language') ? ' me':'' ?>">
			<a href="/admin/config/language"><span><?php echo _('Langues') ?></span></a>
		</li>

		<li class="clearfix<?php echo isMe('/config/export') ? ' me':'' ?>">
			<a href="/admin/config/export"><span><?php echo _('Export') ?></span></a>
		</li>
	</ul>
	
	<ul class="right"></ul>

</div></div>
