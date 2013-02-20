
<div id="sub_nav" class="text"><div class="wrapper clearfix">

	<ul class="left">
		<li class="<?php echo isMe('/business/$') ? 'me':'' ?>">
			<a href="../business/"><span>Commande</span></a>
		</li>

		<li class="<?php echo isMe('/business/carriage') ? 'me':'' ?>">
			<a href="../business/carriage"><span>Frais de port</span></a>
		</li>

		<?php if($app->userCan('business.coupon')){ ?>
		<li class="<?php echo isMe('/business/coupon') ? 'me':'' ?>">
			<a href="../business/coupon"><span>Coupon</span></a>
		</li>
		<?php } ?>

		<li class="<?php echo isMe('/business/hist') ? 'me':'' ?>">
			<a href="../business/hist"><span>Historique</span></a>
		</li>

		<li class="<?php echo isMe('/business/shop') ? 'me':'' ?>">
			<a href="../business/shop"><span>Shop</span></a>
		</li>

		<?php if($app->userCan('business.account')){ ?>
		<li class="<?php echo isMe('/business/account') ? 'me':'' ?>">
			<a href="../business/account"><span>Comptes</span></a>
		</li>
		<?php } ?>

		<?php if($app->userCan('business.tva')){ ?>
		<li class="<?php echo isMe('/business/tax') ? 'me':'' ?>">
			<a href="../business/tax"><span>TVA</span></a>
		</li>
		<?php } ?>

		<?php if($app->userCan('business.config')){ ?>
		<li class="<?php echo isMe('/business/config') ? 'me':'' ?>">
			<a href="../business/config"><span>Config</span></a>
		</li>
		<?php } ?>
	</ul>
	
	<ul class="right"></ul>

</div></div>
