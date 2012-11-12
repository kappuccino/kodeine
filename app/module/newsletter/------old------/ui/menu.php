
<div id="sub_nav" class="<?php $tmp = $app->configGet('boot'); echo $tmp['adminSubMenu']; ?>"><div class="wrapper clearfix">
	

	<ul class="left">
		<li class="<?php echo isMe('/newsletter/$') ? 'me':'' ?>">
			<a href="/admin/newsletter/">
				<img src="/admin/newsletter/ui/img/newsletter.png" />
				<span>Liste</span>
			</a>
		</li>

		<li class="<?php echo isMe('/newsletter/template') ? 'me':'' ?>">
			<a href="/admin/newsletter/template">
				<img src="/admin/newsletter/ui/img/template.png" />
				<span>Gabarits</span>
			</a>
		</li>

		<li class="<?php echo isMe('/newsletter/blacklist') ? 'me':'' ?>">
			<a href="/admin/newsletter/blacklist">
				<img src="/admin/newsletter/ui/img/blackandwhite.png" />
				<span>Black list</span>
			</a>
		</li>

		<li class="<?php echo isMe('/newsletter/report') ? 'me':'' ?>">
			<a href="/admin/newsletter/report">
				<img src="/admin/newsletter/ui/img/report.png" />
				<span>Consommation</span>
			</a>
		</li>

		<li class="<?php echo isMe('/newsletter/pref') ? 'me':'' ?>">
			<a href="/admin/newsletter/pref">
				<img src="/admin/newsletter/ui/img/pref.png" />
				<span>Préférences</span>
			</a>
		</li>

		<li class="<?php echo isMe('/newsletter/designer') ? 'me':'' ?>">
			<a href="/admin/newsletter/designer">
				<img src="/admin/newsletter/ui/img/newsletter.png" />
				<span>Designer</span>
			</a>
		</li>
	</ul>
	
	<ul class="right">
	</ul>

</div></div>
