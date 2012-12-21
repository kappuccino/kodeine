
<div id="sub_nav" class="text <?php #$tmp = $app->filterGet('admin'); echo ($tmp['adminSubMenu'] == '') ? 'icon text' : $tmp['adminSubMenu']; unset($tmp) ?>"><div class="wrapper clearfix">

	<ul class="left">
		<li class="clearfix<?php echo isMe('/config/$') ? ' me':'' ?>">
			<a href="./">
				<span>Liste</span>
			</a>
		</li>

	</ul>

	<ul class="right"></ul>

</div></div>
