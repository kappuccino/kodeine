<?php

	# Supprimer le panier
	#
	if($_GET['empty']) $this->apiLoad('business')->businessCartRemove($_SESSION['id_cart'], true);


	# On recupere le panier
	#
	$myCart = $this->apiLoad('business')->businessCartGet(array(
		'is_cart'	=> true,
		'create'	=> true,
		'id_cart' 	=> $_SESSION['id_cart'],
		'debug'		=> false
	));


	# Mise a jour des adresses et des prix
	#
	include(dirname(__FILE__).'/inc.share.php');


	# Ajouter du CONTENT au CART (quantite = 1)
	#
	if($_GET['id_content'] > 0){

		$add		= false;
		$content	= $this->apiLoad('content')->contentGet(array(
			'id_content'	=> $_GET['id_content'],
			'debug'			=> false,
			'assoShop'		=> true
		));

		// Verifier si j'ai le droit d'ajouter le produit dans CE panier
		if($myCart['id_shop'] != ''){
			$id_shop	= $myCart['id_shop'];
			$add		= is_array($content['id_shop']) ? in_array($myCart['id_shop'], $content['id_shop']) : true;
		}else

		// J'ai mon panier sans id_shop
		if($_REQUEST['id_shop'] != NULL){
			$id_shop	= $_REQUEST['id_shop'];
			$add		= true;
		}

		// On ne gere pas le id_shop
		else{
			$add = true;
		}

		// Si j'ai le stock et que je peux ajouter
		if($content['contentStockNeg'] || (!$content['contentStockNeg'] && $content['contentStock'] > 0) && $add){
			$success = $this->apiLoad('business')->businessCartAdd(array(
				'id_cart'		=> $_SESSION['id_cart'],
				'id_content'	=> $_GET['id_content'],
				'debug'			=> true
			));
		
			if($success){

				// Definir le id_shop du CART
				if(is_numeric($id_shop)){
					$this->dbQuery("UPDATE k_businesscart SET id_shop=".$id_shop." WHERE id_cart=".$myCart['id_cart']);
				}

				// Recharger la page
				header("Location: cart");
			}
		}
	}


	# Mettre a jour les informations du panier
	#
	if($_POST['update'] && sizeof($_POST['line']) > 0){
		$myCart = $this->apiLoad('business')->businessCartGet(array(
			'id_cart' => $_SESSION['id_cart']
		));	
		
		foreach($_POST['line'] as $id_cartline => $line){
			if($line['remove']){
				$this->apiLoad('business')->businessCartLineRemove($id_cartline, true);
			}else
			if($line['contentQuantity'] != $myCart[$id_cartline]['contentQuantity']){
				$this->apiLoad('business')->businessCartQuantity($id_cartline, round($line['contentQuantity']));
			}
		}
		
		header("Location: cart");
	}
?>