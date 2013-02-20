
<div id="sub_nav" class="text <?php #$tmp = $app->filterGet('admin'); echo ($tmp['adminSubMenu'] == '') ? 'icon text' : $tmp['adminSubMenu']; unset($tmp) ?>"><div class="wrapper clearfix">
	
    <?php $pref = $app->configGet('newsletter'); ?>
	<ul class="left">
		<li class="<?php echo isMe('/newsletter/$') ? 'me':'' ?>">
			<a href="/admin/newsletter/">
				<!-- <img src="/admin/newsletter/ui/img/newsletter.png" /> -->
				<span>Liste</span>
			</a>
		</li>

        <?php if($pref['connector'] == 'cloudApp') { ?>
		<li class="<?php echo isMe('/newsletter/list') ? 'me':'' ?>">
			<a href="/admin/newsletter/list">
				<!-- <img src="/admin/newsletter/ui/img/template.png" /> -->
				<span>Listes</span>
			</a>
		</li>
        <?php } ?>

		<li class="<?php echo isMe('/newsletter/template') ? 'me':'' ?>">
			<a href="/admin/newsletter/template">
				<!-- <img src="/admin/newsletter/ui/img/template.png" /> -->
				<span>Gabarits</span>
			</a>
		</li>

        <?php if($pref['connector'] == 'cloudApp') { ?>
		<li class="<?php echo isMe('/newsletter/blacklist') ? 'me':'' ?>">
			<a href="/admin/newsletter/blacklist">
				<!-- <img src="/admin/newsletter/ui/img/blackandwhite.png" /> -->
				<span>Black list</span>
			</a>
		</li>

		<li class="<?php echo isMe('/newsletter/report') ? 'me':'' ?>">
			<a href="/admin/newsletter/report">
				<!-- <img src="/admin/newsletter/ui/img/report.png" /> -->
				<span>Consommation</span>
			</a>
		</li>
        <?php } ?>

		<li class="<?php echo isMe('/newsletter/pref') ? 'me':'' ?>">
			<a href="/admin/newsletter/pref">
				<!-- <img src="/admin/newsletter/ui/img/pref.png" /> -->
				<span>Préférences</span>
			</a>
		</li>
	</ul>
	
	<ul class="right">
	</ul>

</div></div>
