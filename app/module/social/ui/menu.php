
<div id="sub_nav" class="text <?php #$tmp = $app->filterGet('admin'); echo ($tmp['adminSubMenu'] == '') ? 'icon text' : $tmp['adminSubMenu']; unset($tmp) ?>"><div class="wrapper clearfix">

	<ul class="left">
		<li class="<?php echo isMe('/content/$') ? 'me':'' ?>">
			<a href="/admin/social/">
				<span>Posts</span>
			</a>
		</li>
		<li class="<?php echo isMe('/social/forum') ? 'me':'' ?>">
			<a href="/admin/social/forum">
				<span>Forums</span>
			</a>
		</li>
		<li class="<?php echo isMe('/social/groupe/') ? 'me':'' ?>">
			<a href="/admin/social/groupe">
				<span>Groupes</span>
			</a>
		</li>
		<li class="<?php echo isMe('/social/album') ? 'me':'' ?>">
			<a href="/admin/social/album">
				<span>Albums</span>
			</a>
		</li>
	</ul>

</div></div>
