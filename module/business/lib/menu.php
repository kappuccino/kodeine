<ul class="menu-icon clearfix">
	<li class="<?php echo isMe('') ?>"><a href="/admin/business/index" class="button button-blue">Commandes</a></li>
	<li class="<?php echo isMe('carriage.php') ?>"><a href="/admin/business/carriage" class="button button-blue">Frais Expedition</a></li>
	<li class="<?php echo isMe('coupon.php') ?>"><a href="/admin/business/coupon" class="button button-blue">Coupon</a></li>
	<li class="<?php echo isMe('hist.php') ?>"><a href="/admin/business/hist" class="button button-blue">Historique</a></li>
	<li class="<?php echo isMe('shop.php') ?>"><a href="/admin/business/shop" class="button button-blue">Shop</a></li>
    <li class="<?php echo isMe('account.php') ?>"><a href="/admin/business/account" class="button button-blue">Comptes</a></li>
    <li class="<?php echo isMe('tax.php') ?>"><a href="/admin/business/tax" class="button button-blue">TVA</a></li>
    <li class="<?php echo isMe('config.php') ?>"><a href="/admin/business/config" class="button button-blue">Config</a></li>

	<?php if(isMe('') == 'me'){ ?>
	<div style="float:right;">
        <a href="/admin/business/data" class="button button-green">Ajouter une commande</a>
        <a href="/admin/business/data-index" class="button button-green">Commandes en cours</a>
	</div>
	<?php } ?>
</ul>
<br style="clear:both" />