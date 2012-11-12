<?php

	$actions = array(
		
		'1' => array('url' => '/admin/business/index', 'title' => 'Commandes'),
		'2' => array('url' => '/admin/business/carriage', 'title' => 'Frais d\'exp&eacute;dition'),
		'3' => array('url' => '/admin/business/coupon', 'title' => 'Coupon'),
		'4' => array('url' => '/admin/business/hist', 'title' => 'Historique'),
		'5' => array('url' => '/admin/business/shop', 'title' => 'Shop'),
		'6' => array('url' => '/admin/business/account', 'title' => 'Comptes'),
		'7' => array('url' => '/admin/business/tax', 'title' => 'TVA'),
		'8' => array('url' => '/admin/business/config', 'title' => 'Config')
	); 
	
	echo json_encode($actions);

?>