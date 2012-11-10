<?php
	
	# Condition pour lesquelles on NE PEUT PAS RESTER SUR CETTE PAGE
	#
	if($_SESSION['id_cart'] == NULL) 	header("Location: ./");
	if($this->user['id_user'] == NULL) 	header("Location: ./");


	# Mise a jour des adresses et des prix
	#
	include(dirname(__FILE__).'/inc.share.php');


	# On verouille le CART
	#
	$this->dbQuery("UPDATE k_businesscart SET is_locked=1 WHERE id_cart=".$_SESSION['id_cart']);


	# Ajouter un COUPON	
	#
	if($_POST['couponCode'] != NULL){

		$coupon = $this->apiLoad('business')->businessCouponGet(array(
			'couponCode'	=> $_POST['couponCode'],
			'debug'			=> false
		));

		if($coupon['id_coupon'] == NULL){
			$COUPON_NOT_EXISTS = true;
		}else{
			$coupon_ = $this->apiLoad('business')->businessCouponGet(array(
				'couponCode'	=> $_POST['couponCode'],
				'id_user'		=> $this->user['id_user'],
				'is_used' 		=> '0',
				'debug'			=> false
			));

			if($coupon_['id_coupon'] == NULL){
				$this->apiLoad('business')->businessCouponUserSet(array(
					'id_user'	=> $this->user['id_user'],
					'id_coupon'	=> $coupon['id_coupon'],
					'debug'		=> false
				));
			}else{
				$ALREADY_INSERTED = true;
			}
		}		
	}


	# Mettre a jour le COUPON pour ce panier
	#
	if(isset($_POST['id_coupon'])){
		if($_POST['id_coupon'] !=  NULL){

			$coupon = $this->apiLoad('business')->businessCouponGet(array(
				'id_coupon'	=> $_POST['id_coupon'],
				'debug'		=> false
			));

			$tmpCart = $this->apiLoad('business')->businessCartGet(array(
				'is_cart'	=> true,
				'create'	=> false,
				'id_cart' 	=> $_SESSION['id_cart']
			));
			
			// Si le CART n'a pas de ID_SHOP
			if($tmpCart['id_shop'] == NULL){
				$set = "id_coupon=".$_POST['id_coupon'];
			}else{
				$set = @in_array($tmpCart['id_shop'], $coupon['id_shop'])
					? "id_coupon=".$_POST['id_coupon']
					: "id_coupon = NULL";
			}
		}else
		if($_POST['id_coupon'] ==  NULL){
			$set = "id_coupon = NULL";
		}
		
		if($set != NULL){
			$this->dbQuery("UPDATE k_businesscart SET ".$set." WHERE id_cart=".$_SESSION['id_cart']);
		}
	
		# Met a jour le panier pour le prix
		$this->apiLoad('business')->businessCartPrice($_SESSION['id_cart']);
	}


	# On recupere le panier, redirection si NOMBRE DE LIGNE = 0 (pas possible pour une commande)
	#
	$myCart = $this->apiLoad('business')->businessCartGet(array(
		'is_cart'	=> true,
		'create'	=> true,
		'id_cart' 	=> $_SESSION['id_cart'],
		'debug'		=> false
	));
	if(sizeof($myCart['line']) == 0) header("Location: cart.html");


	# On fixe le frais de port
	#
	$carriage = $this->apiLoad('business')->businessCartCarriageSet(array(
		'id_cart' 	=> $myCart['id_cart'],
		'debug'		=> false
	));

	if(!$carriage) $CARRIAGE_ERROR = true;


	# Mes coupons
	#
	$myCoupon = $this->apiLoad('business')->businessCouponGet(array(
		'id_user' => $this->user['id_user'],
		'is_used' => '0'
	));


	# My SHOP
	#
	if($myCart['id_shop'] != NULL){
		$myShop = $this->apiLoad('shop')->shopGet(array(
			'id_shop' => $myCart['id_shop']
		));
	}

?>