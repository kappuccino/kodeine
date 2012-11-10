<?php

	# On recupere le panier
	#
	$myCart = $this->apiLoad('business')->businessCartGet(array(
		'is_cart'	=> true,
		'create'	=> true,
		'id_cart' 	=> $_SESSION['id_cart']
	));

	# Si la panier est vide alors rediriger vers la panier
	#
	if(sizeof($myCart['line']) == 0){
		header("Location: cart");
		exit();
	}

?>