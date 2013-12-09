
<div id="sub_nav" class="text"><div class="wrapper clearfix">

	<ul class="left">
		<li class="<?php echo isMe('/content/(index)?$') ? 'me':'' ?>">
			<a href="../content/"><span><?php echo _('List'); ?></span></a>
		</li>

		<li class="<?php echo isMe('/content/browse') ? 'me':'' ?>">
			<a href="../content/browse"><span><?php echo _('Browse'); ?></span></a>
		</li>

		<li class="<?php echo isMe('/category/') ? 'me':'' ?>">
			<a href="../category/"><span><?php echo _('Category'); ?></span></a>
		</li>
		
		<?php if($app->userCan('content.chapter')){ ?>
		<li class="<?php echo isMe('/chapter/') ? 'me':'' ?>">
			<a href="../chapter/"><span><?php echo _('Chapter'); ?></span></a>
		</li>
		<?php } ?>

		<?php if($app->userCan('content.type')){ ?>
		<li class="<?php echo isMe('/type/') ? 'me':'' ?>">
			<a href="../type/"><span><?php echo _('Type'); ?></span></a>
		</li>
		<?php } ?>

        <?php if($app->userCan('content.field')){ ?>
        <li class="<?php echo isMe('/field/.*') ? 'me':'' ?>">
            <a href="../field/asso"><span><?php echo _('Fields'); ?></span></a>
        </li>
        <?php } ?>

		<li class="<?php echo isMe('/comment/') ? 'me':'' ?>">
			<a href="../comment/"><span><?php echo _('Comments'); ?></span></a>
		</li>

		<?php if($app->userCan('content.search')){ ?>
		<li class="<?php echo isMe('/content/search') ? 'me':'' ?>">
			<a href="../content/search"><span><?php echo _('Search'); ?></span></a>
		</li>
		<?php } ?>

        <li class="<?php echo isMe('/localisation/') ? 'me':'' ?>">
            <a href="../localisation/"><span><?php echo _('Translate'); ?></span></a>
        </li>

        <li class="<?php echo isMe('/content/adzone') ? 'me':'' ?>">
            <a href="../content/adzone"><span><?php echo _('Ads'); ?></span></a>
        </li>

		<?php if($app->userCan('content.pref')){ ?>
        <li class="<?php echo isMe('/content/pref') ? 'me':'' ?>">
            <a href="../content/pref"><span><?php echo _('Preferences'); ?></span></a>
        </li>
		<?php } ?>
	</ul>

	<ul class="right"></ul>
	
</div></div>
