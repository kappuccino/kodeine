
<div id="sub_nav" class="text"><div class="wrapper clearfix">
	
    <?php $pref = $app->configGet('newsletter'); ?>
	<ul class="left">
		<li class="<?php echo isMe('/newsletter/$') ? 'me':'' ?>">
			<a href="../newsletter/"><span>Mes newsletters</span></a>
		</li>

        <?php //if($pref['connector'] == 'cloudApp') { ?>
		<li class="<?php echo isMe('/newsletter/list') ? 'me':'' ?>">
			<a href="../newsletter/list"><span>Listes d'abonnés</span></a>
		</li>
        <?php //} ?>

		<li class="<?php echo isMe('/newsletter/blocs') ? 'me':'' ?>">
			<a href="../newsletter/blocs"><span>Modifier mes blocs</span></a>
		</li>

		<li class="<?php echo isMe('/newsletter/template') ? 'me':'' ?>">
			<a href="../newsletter/template"><span>Assembler un gabarit</span></a>
		</li>

        <?php if($pref['connector'] == 'cloudApp') { ?>
		<li class="<?php echo isMe('/newsletter/blacklist') ? 'me':'' ?>">
			<a href="../newsletter/blacklist"><span>Blacklist Cloudapp</span></a>
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
		<?php if (isMe('/newsletter/dsnr')) {
			$blocs = $app->dbMulti('SELECT * FROM `@nlblocs` LIMIT 0, 100');
			$layout = $_GET['layout'];
		?>
		<div class="btn-group blocselect">
			<a class="btn btn-mini btn-info dropdown-toggle" data-toggle="dropdown" href="#" style="color:white;"><i style="vertical-align:middle;" class="icon-list"></i>
				Blocs <span class="caret" style="margin-top:0;"></span></a>
			<ul class="dropdown-menu"><?php
			foreach($blocs as $b) {
				echo '<li class="clearfix"><a href="?bloc='.$b['id_bloc'].'&layout='.$layout.'" class="left">'.$b['blocName'].'</a></li>';
			}
			?></ul>
		</div>
		<?php } ?>
	</ul>

</div></div>
