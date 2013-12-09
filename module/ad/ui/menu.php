<div id="sub_nav" class="text"><div class="wrapper clearfix">
<ul class="left">
	<li class="<?php echo isMe('index') ?>"><a href="index" class="button button-blue">Liste</a></li>
	<li class="<?php echo isMe('archive') ?>"><a href="archive" class="button button-blue">Archive</a></li>
	<li class="<?php echo isMe('zone') ?>"><a href="zone" class="button button-blue">Zones</a></li>

	<?php if(isMe('index') == 'me'){ ?>
		<a href="data" class="button button-green">Cr&eacute;er une nouvelle publicit&eacute; maintenant</a>
	<?php } ?>
</ul>
</div></div>
