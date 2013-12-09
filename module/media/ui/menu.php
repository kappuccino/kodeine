<div id="sub_nav" class="text"><div class="wrapper clearfix">

	<ul class="left">
		<?php if(!isMe('/media/pref')){ ?>

			<li><a id="button-folder"><?php echo _('Refresh'); ?></a>

	        <?php if($app->userCan('media.create')) { ?>
			<li><a id="button-newdir"><?php echo _('New folder'); ?></a></li>
	        <?php } ?>

	        <?php if($app->userCan('media.upload')) { ?>
			<li><a id="button-upload"><?php echo _('Upload files'); ?></a></li>
	        <?php } ?>

			<li class="clearfix<?php echo isMe('/media/pref') ? ' me':'' ?>">
				<a href="pref" target="_blank"><?php echo _('Settings'); ?></a>
			</li>

		<?php }else{ ?>

			<li class="clearfix">
				<a href="./"><?php echo _('Back'); ?></a>
			</li>
		<?php } ?>
	</ul>
	
	<div class="right">
		<input type="range" id="slider" value="150" min="150" max="300" step="10"  />
	</div>
	
</div></div>
