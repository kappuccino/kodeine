
<div id="sub_nav" class="text <?php #$tmp = $app->filterGet('admin'); echo ($tmp['adminSubMenu'] == '') ? 'icon text' : $tmp['adminSubMenu']; unset($tmp) ?>"><div class="wrapper clearfix">

	<ul class="left">
		<li class="clearfix<?php echo isMe('/user/$') ? ' me':'' ?>">
			<a href="/admin/user/">
				<!-- <img src="/admin/user/ui/img/user.png" /> -->
				<span>Liste</span>
			</a>
		</li>
		<li class="clearfix">
			<a href="/admin/field/asso?id_type=user">
				<!-- <img src="/admin/field/ui/img/field.png" /> -->
				<span>Champs</span>
			</a>
		</li>
		<li class="clearfix<?php echo isMe('/user/group') ? ' me':'' ?>">
			<a href="/admin/user/group">
				<!-- <img src="/admin/user/ui/img/group.png" /> -->
				<span>Groupe</span>
			</a>
		</li>
		<li class="clearfix<?php echo isMe('/user/profile') ? ' me':'' ?>">
			<a href="/admin/user/profile">
				<!-- <img src="/admin/user/ui/img/profile.png" /> -->
				<span>Profil</span>
			</a>
		</li>
		<li class="clearfix<?php echo isMe('/user/search') ? ' me':'' ?>">
			<a href="/admin/user/search">
				<!-- <img src="/admin/user/ui/img/search.png" /> -->
				<span>Recherche</span>
			</a>
		</li>
		<li class="clearfix<?php echo isMe('/user/export') ? ' me':'' ?>">
			<a href="/admin/user/export">
				<!-- <img src="/admin/user/ui/img/export.png" /> -->
				<span>Export</span>
			</a>
		</li>
		<li class="clearfix<?php echo isMe('/user/import$') ? ' me':'' ?>">
			<a href="/admin/user/import">
				<!-- <img src="/admin/user/ui/img/import.png" /> -->
				<span>Import</span>
			</a>
		</li>
		<li class="clearfix<?php echo isMe('/user/import-book') ? ' me':'' ?>">
			<a href="/admin/user/import-book">
				<!-- <img src="/admin/user/ui/img/addressbook.png" /> -->
				<span>Import carnets d'adresses</span>
			</a>
		</li>
	</ul>

	<ul class="right"></ul>

</div></div>



