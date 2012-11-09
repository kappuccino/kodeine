
<div id="sub_nav" class="text <?php #$tmp = $app->filterGet('admin'); echo ($tmp['adminSubMenu'] == '') ? 'icon text' : $tmp['adminSubMenu']; unset($tmp) ?>"><div class="wrapper clearfix">

	<ul class="left">
		<li class="<?php echo isMe('/business/$') ? 'me':'' ?>">
			<a href="/admin/business/">
				<!-- <img src="/admin/business/ui/img/business.png" /> -->
				<span>Commande</span>
			</a>
		</li>
		<li class="<?php echo isMe('/business/carriage') ? 'me':'' ?>">
			<a href="/admin/business/carriage">
				<!-- <img src="/admin/business/ui/img/carriage.png" /> -->
				<span>Frais de port</span>
			</a>
		</li>
		<li class="<?php echo isMe('/business/coupon') ? 'me':'' ?>">
			<a href="/admin/business/coupon">
				<!-- <img src="/admin/business/ui/img/coupon.png" /> -->
				<span>Coupon</span>
			</a>
		</li>
		<li class="<?php echo isMe('/business/hist') ? 'me':'' ?>">
			<a href="/admin/business/hist">
				<!-- <img src="/admin/business/ui/img/hist.png" /> -->
				<span>Historique</span>
			</a>
		</li>
		<li class="<?php echo isMe('/business/shop') ? 'me':'' ?>">
			<a href="/admin/business/shop">
				<!-- <img src="/admin/shop/ui/shop.png" /> -->
				<span>Shop</span>
			</a>
		</li>
		<li class="<?php echo isMe('/business/account') ? 'me':'' ?>">
			<a href="/admin/business/account">
				<!-- <img src="/admin/business/ui/img/account.png" /> -->
				<span>Comptes</span>
			</a>
		</li>
		<li class="<?php echo isMe('/business/tax') ? 'me':'' ?>">
			<a href="/admin/business/tax">
				<!-- <img src="/admin/business/ui/img/tax.png" /> -->
				<span>TVA</span>
			</a>
		</li>
		<li class="<?php echo isMe('/business/config') ? 'me':'' ?>">
			<a href="/admin/business/config">
				<!-- <img src="/admin/business/ui/img/config.png" /> -->
				<span>Config</span>
			</a>
		</li>
	</ul>
	
	<ul class="right"></ul>

</div></div>
