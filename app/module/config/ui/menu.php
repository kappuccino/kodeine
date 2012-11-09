
<div id="sub_nav" class=" text <?php #$tmp = $app->filterGet('admin'); echo ($tmp['adminSubMenu'] == '') ? 'icon text' : $tmp['adminSubMenu']; unset($tmp) ?>"><div class="wrapper clearfix">

	<ul class="left">
		<li class="clearfix<?php echo isMe('/config/$') ? ' me':'' ?>">
			<a href="/admin/config/">
				<!-- <img src="/admin/config/ui/img/config.png" /> -->
				<span>Configuration</span>
			</a>
		</li>
		<li class="clearfix<?php echo isMe('/config/admin') ? ' me':'' ?>">
			<a href="/admin/config/admin">
				<!-- <img src="/admin/config/ui/img/config.png" /> -->
				<span>Back Office</span>
			</a>
		</li>
		<li class="clearfix<?php echo isMe('/theme/') ? ' me':'' ?>">
			<a href="/admin/theme/">
				<!-- <img src="/admin/theme/ui/img/theme.png" /> -->
				<span>Theme</span>
			</a>
		</li>
		<li class="clearfix<?php echo isMe('/config/field') ? ' me':'' ?>">
			<a href="/admin/config/field">
				<!-- <img src="/admin/config/ui/img/field.png" /> -->
				<span>Champs</span>
			</a>
		</li>
		<li class="clearfix<?php echo isMe('/config/offline') ? ' me':'' ?>">
			<a href="/admin/config/offline">
				<!-- <img src="/admin/config/ui/img/offline.png" /> -->
				<span>Hors ligne</span>
			</a>
		</li>
		<li class="clearfix<?php echo isMe('/config/robots') ? ' me':'' ?>">
			<a href="/admin/config/robots">
				<!-- <img src="/admin/config/ui/img/robot.png" /> -->
				<span>Robots.txt</span>
			</a>
		</li>
		<li class="clearfix<?php echo isMe('/config/language') ? ' me':'' ?>">
			<a href="/admin/config/language">
				<!-- <img src="/admin/config/ui/img/language.png" /> -->
				<span>Langues</span>
			</a>
		</li>
		<li class="clearfix<?php echo isMe('/config/export') ? ' me':'' ?>">
			<a href="/admin/config/export">
				<!-- <img src="/admin/config/ui/img/export.png" /> -->
				<span>Export</span>
			</a>
		</li>
	</ul>
	
	<ul class="right">
		
	</ul>

</div></div>
