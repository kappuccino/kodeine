
<div id="sub_nav" class="text"><div class="wrapper clearfix">

	<ul class="left">
		<li class="<?php echo isMe('/content/(index)?$') ? 'me':'' ?>">
			<a href="../content/"><span>Liste</span></a>
		</li>

		<li class="<?php echo isMe('/content/browse') ? 'me':'' ?>">
			<a href="../content/browse"><span>Parcourir</span></a>
		</li>

		<li class="<?php echo isMe('/category/') ? 'me':'' ?>">
			<a href="../category/"><span>Cat&eacute;gories</span></a>
		</li>
		
		<?php if($app->userCan('content.chapter')){ ?>
		<li class="<?php echo isMe('/chapter/') ? 'me':'' ?>">
			<a href="../chapter/"><span>Arborescence</span></a>
		</li>
		<?php }  ?>

		<?php if($app->userCan('content.type')){ ?>
		<li class="<?php echo isMe('/content/type') ? 'me':'' ?>">
			<a href="../content/type"><span>Type</span></a>
		</li>
		<?php }  ?>

		<?php if($app->userCan('content.field')){ ?>
		<li class="<?php echo isMe('/field/.*') ? 'me':'' ?>">
			<a href="../field/asso"><span>Champs</span></a>
		</li>
		<?php }  ?>

		<li class="<?php echo isMe('/comment/') ? 'me':'' ?>">
			<a href="../comment/"><span>Commentaire</span></a>
		</li>

		<?php if($app->userCan('content.search')){ ?>
		<li class="<?php echo isMe('/content/search') ? 'me':'' ?>">
			<a href="../content/search"><span>Recherche</span></a>
		</li>
		<?php }  ?>

		<li class="<?php echo isMe('/localisation/') ? 'me':'' ?>">
			<a href="../localisation/"><span>Traduction</span></a>
		</li>
	</ul>

	<ul class="right"></ul>
	
</div></div>
