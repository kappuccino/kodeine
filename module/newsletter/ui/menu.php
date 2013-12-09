
<div id="sub_nav" class="text"><div class="wrapper clearfix">
	
    <?php $pref = $app->configGet('newsletter'); ?>
	<ul class="left">
		<li class="<?php echo isMe('/newsletter/$') ? 'me':'' ?>">
			<a href="../newsletter/"><span>Liste</span></a>
		</li>

        <?php //if($pref['connector'] == 'cloudApp') { ?>
		<li class="<?php echo isMe('/newsletter/list') ? 'me':'' ?>">
			<a href="../newsletter/list"><span>Listes</span></a>
		</li>
        <?php //} ?>

		<li class="<?php echo isMe('/newsletter/template') ? 'me':'' ?>">
			<a href="../newsletter/template"><span>Gabarits</span></a>
		</li>

        <?php if($pref['connector'] == 'cloudApp') { ?>
		<li class="<?php echo isMe('/newsletter/blacklist') ? 'me':'' ?>">
			<a href="../newsletter/blacklist"><span>Black list</span></a>
		</li>

		<li class="<?php echo isMe('/newsletter/report') ? 'me':'' ?>">
			<a href="../newsletter/report"><span>Consommation</span></a>
		</li>
        <?php } ?>

		<li class="<?php echo isMe('/newsletter/pref') ? 'me':'' ?>">
			<a href="../newsletter/pref"><span>Préférences</span></a>
		</li>
	</ul>
	
	<ul class="right">
	</ul>

</div></div>
