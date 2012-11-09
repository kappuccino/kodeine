<div id="sub_nav" class="text <?php #$tmp = $app->configGet('boot'); echo $tmp['adminSubMenu']; ?>"><div class="wrapper clearfix">

	<ul class="left">
		<li class="<?php echo isMe('/clientftp/$') ? "me" : "" ?>">
			<a href="./">
				<!-- <img src="/admin/clientftp/ui/img/ico-list.png"/> -->
				<span>Liste</span>
			</a>
		</li>

		<li class="<?php echo isMe('/clientftp/log') ? "me" : "" ?>">
			<a href="log">
				<!-- <img src="/admin/clientftp/ui/img/ico-calendar.png"/> -->
				<span>Journal</span>
			</a>
		</li>
	
		<li class="<?php echo isMe('/clientftp/pref') ? "me" : "" ?>">
			<a href="pref">
				<!--<img src="/admin/clientftp/ui/img/ico-preference.png"/> -->
				<span>Pr&eacute;f&eacute;rences</span>
			</a>
		</li>
	</ul>

	<ul class="right">
		
	</ul>

</div></div>