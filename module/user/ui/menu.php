
<div id="sub_nav" class="text"><div class="wrapper clearfix">

	<ul class="left">
		<li class="clearfix<?php echo isMe('/user/$') ? ' me':'' ?>">
			<a href="../user/"><span>Liste</span></a>
		</li>

		<?php if($app->userCan('user.field')){ ?>
		<li class="clearfix">
			<a href="../field/asso?id_type=user"><span>Champs</span></a>
		</li>
		<?php } ?>

		<?php if($app->userCan('user.group')){ ?>
		<li class="clearfix<?php echo isMe('/user/group') ? ' me' : '' ?>">
			<a href="../user/group"><span>Groupe</span></a>
		</li>
		<?php } ?>

		<?php if($app->userCan('user.profile')){ ?>
		<li class="clearfix<?php echo isMe('/user/profile') ? ' me' : '' ?>">
			<a href="../user/profile"><span>Profil</span></a>
		</li>
		<?php } ?>

		<?php if($app->userCan('user.search')){ ?>
		<li class="clearfix<?php echo isMe('/user/search') ? ' me' : '' ?>">
			<a href="../user/search"><span>Recherche</span></a>
		</li>
		<?php } ?>

		<li class="clearfix<?php echo isMe('/user/export') ? ' me' : '' ?>">
			<a href="../user/export"><span>Export</span></a>
		</li>

		<li class="clearfix<?php echo isMe('/user/import$') ? ' me' : '' ?>">
			<a href="../user/import"><span>Import</span></a>
		</li>

		<li class="clearfix<?php echo isMe('/user/import-book') ? ' me' : '' ?>">
			<a href="../user/import-book"><span>Import carnets d'adresses</span></a>
		</li>
	</ul>

	<ul class="right"></ul>

</div></div>



