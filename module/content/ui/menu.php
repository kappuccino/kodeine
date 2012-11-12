
<div id="sub_nav" class="text <?php #$tmp = $app->filterGet('admin'); echo ($tmp['adminSubMenu'] == '') ? 'icon text' : $tmp['adminSubMenu']; unset($tmp) ?>"><div class="wrapper clearfix">

	<ul class="left">
		<li class="<?php echo isMe('/content/(index)?$') ? 'me':'' ?>">
			<a href="/admin/content/">
				<!-- <img src="/admin/content/ui/img/list.png" /> -->
				<span>Liste</span>
			</a>
		</li>
		<li class="<?php echo isMe('/content/browse') ? 'me':'' ?>">
			<a href="/admin/content/browse">
				<!-- <img src="/admin/content/ui/img/browse.png" /> --> 
				<span>Parcourir</span>
			</a>
		</li>
		<li class="<?php echo isMe('/category/') ? 'me':'' ?>">
			<a href="/admin/category/">
				<!-- <img src="/admin/category/ui/img/category.png" /> -->
				<span>Cat&eacute;gories</span>
			</a>
		</li>
		<li class="<?php echo isMe('/chapter/') ? 'me':'' ?>">
			<a href="/admin/chapter/">
				<!-- <img src="/admin/chapter/ui/img/chapter.png" /> -->
				<span>Arborescence</span>
			</a>
		</li>
		<li class="<?php echo isMe('/content/type') ? 'me':'' ?>">
			<a href="/admin/content/type">
				<!-- <img src="/admin/content/ui/img/type.png" /> -->
				<span>Type</span>
			</a>
		</li>
		<li class="<?php echo isMe('/field/.*') ? 'me':'' ?>">
			<a href="/admin/field/asso">
				<!-- <img src="/admin/field/ui/img/field.png" /> -->
				<span>Champs</span>
			</a>
		</li>
		<li class="<?php echo isMe('/comment/') ? 'me':'' ?>">
			<a href="/admin/comment/">
				<!-- <img src="/admin/comment/ui/img/comment.png" /> -->
				<span>Commentaire</span>
			</a>
		</li>
		<li class="<?php echo isMe('/content/search') ? 'me':'' ?>">
			<a href="/admin/content/search">
				<!-- <img src="/admin/content/ui/img/search.png" /> -->
				<span>Recherche</span>
			</a>
		</li>
		<li class="<?php echo isMe('/localisation/') ? 'me':'' ?>">
			<a href="/admin/localisation/">
				<!-- <img src="/admin/localisation/ui/img/localisation.png" /> -->
				<span>Traduction</span>
			</a>
		</li>
	</ul>

	<ul class="right"></ul>
	
</div></div>
