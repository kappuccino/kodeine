<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
	<title></title>
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<link rel="stylesheet" type="text/css" media="all" href="/media/ui/css/style.php" />
	<? $this->themeInclude('ui/html-head.php') ?>
</head>
<body>

<div class="container_12 container clearfix">

	<? $this->themeInclude('ui/nav.php'); ?>

	<div class="grid_12">
		<h1>Reglement par cheque</h1>

		<?php

			$myCart = $this->apiLoad('business')->businessCartGet(array(
				'is_cart'	=> true,
				'create'	=> false,
				'id_cart' 	=> $_SESSION['id_cart']
			));
			
			if($myCart['id_cart'] > 0){

				$this->apiLoad('business')->businessCmdNew(array(
					'debug'			=> true,
					'id_cart'		=> $myCart['id_cart'],
					'update'		=> array(
						'cartStatus'		=> 'WAIT',
						'cartPayment'		=> 'CHEQUE',
						'cartTransaction'	=> '-',
						'cartCertificate'	=> '-'
					)
				));

				$this->apiLoad('business')->businessCmdMail(array(
					'id_cart'	=> $myCart['id_cart']
				));
		?>
			
			<p>Merci d'envoyer votre reglement &agrave; l'adresse suivante en precisant au dos du ch&egrave;que
			la reference de votre commande (<?= $myCart['id_cart'] ?>), pour acc&eacute;lerer l'envois de votre commande</p>
			
			
			<p>Adresse,<br />
			add adresse,<br />
			34 route du Medoc<br />
			33520 Bruges</p>
			
			<p><a href="my.html">Mon compte</a></p>
			
			<?php }else{ ?>
			
			<p>Commande invalide myCart not found</p>
		
		<? } ?>

	</div>
</div>

<script type="text/javascript" src="/media/ui/js/script.php"></script> 
<? $this->themeInclude('ui/html-end.php') ?>

</body></html>