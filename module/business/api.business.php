<?php

class business extends coreApp {

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function __loaded(){
    $this->businessCartTTL();
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function businessCartTTL(){

    if($GLOBALS['jobTTL'] !== true) {

        $ttl  = $this->hookFilter('businessCartTTL', 86400);

        $cart = $this->dbMulti("SELECT id_cart, cartTTL FROM k_businesscart WHERE is_cart=1 AND is_locked=0 AND cartTTL<=" . (time() - $ttl));


	    if(sizeof($cart) > 0){
            foreach($cart as $c){
                $this->businessCartRemove($c['id_cart'], true);
            }
        }

        if($_SESSION['id_cart'] > 0){
            $this->dbQuery("UPDATE k_businesscart SET cartTTL=".time().", cartDateUpdate=NOW() WHERE id_cart=".$_SESSION['id_cart']);
            #$this->pre($this->db_query, $this->db_error);
        }

        $GLOBALS['jobTTL'] = true;
    }
}
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function businessCartNew($opt=array()){
	
    if($opt['id_user'] > 0){
    	$force = true;
	    $id_user = $opt['id_user'];
    }else{
        $id_user = $this->user['id_user'];
    }

	$token = strtoupper(md5(uniqid().rand()));

	# Si le USER est defini
	if($id_user != NULL){
		if(!$force) $o = $this->dbOne("SELECT id_cart FROM k_businesscart WHERE is_cart=1 AND id_user='".$id_user."'");

		# METTRE A JOUR ou CREER le panier pour ce USER
		$q = ($o['id_cart'] != NULL)
			? "UPDATE k_businesscart SET cartTTL=NOW(), cartDateUpdate=NOW() WHERE id_cart=".$o['id_cart']
			: "INSERT INTO k_businesscart (is_cart, cartDateCreate, cartDateUpdate, id_user, cartTTL, cartToken) VALUES (1, NOW(), NOW(), ".$id_user.", '".time()."', '".$token."')";    	

		if($o['id_cart'] != NULL) $this->businessCartUserSet($o['id_cart'], $id_user);
		
	# Creation d'un CART sans le relier au USER
	}else{
		$q = "INSERT INTO k_businesscart (is_cart, cartDateCreate, cartDateUpdate, cartTTL, cartToken) VALUES (1, NOW(), NOW(), '".time()."', '".$token."')";
	}

	$this->dbQuery($q);
	$id_cart = ($o['id_cart'] != NULL) ? $o['id_cart'] : $this->db_insert_id;

	$this->hookAction('businessCartNew', $id_cart);

	return $id_cart;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function businessCartGet($opt=array()){

	$userAffect = array_key_exists('userAffect', $opt) ? $opt['userAffect'] : true;

	# Si on a un ID , on verifie si l'ID qu'on nous donne est encore existant
	if(intval($opt['id_cart']) > 0){
		$my = $this->dbOne("SELECT * FROM k_businesscart WHERE is_cart=1 AND id_cart='".$opt['id_cart']."'");
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	}

    $id_user    = ($opt['id_user'] > 0) ? $opt['id_user'] : $this->user['id_user'];
    $userCheck = ($opt['userCheck'] === false) ? false : true;
	
	# On cherche a recupere un CART pas tous les moyens
	#
	if($opt['create']){
		# par défaut, on considere qu'on doit créer un panier...
		$create = true;

		# ... Si je suis connecté, verifié si j'ai pas un panier existant (et l'utiliser)
		if($id_user > 0 && $userCheck){
			$check = $this->dbOne("SELECT id_cart FROM k_businesscart WHERE is_cart=1 AND id_user='".$id_user."'");
			if(!empty($check)){
				$opt['id_cart'] = $check['id_cart'];
				$create = false;
			}
		}
		/*else
		# Si le id_cart n'est pas renseigne
		if(intval($opt['id_cart']) == 0){
		#	$opt['id_cart'] = $this->businessCartNew(array('id_user' => $id_user, 'debug' => $opt['debug']));
			$create = true;
		}*/

		if(!empty($my)){
			$opt['id_cart'] = $my['id_cart'];
			$create = false;
		}

		# Si le CART existe PAS/PLUS alors on en GENERE un nouveau
		if($create){
			$opt['id_cart'] = $this->businessCartNew(array('id_user' => $id_user, 'debug' => $opt['debug']));
		}

		if(intval($opt['id_cart']) == 0 && $opt['is_cart']) die("Erreur critique businessCartGet: pas de id_cart");
	}

	
	# On force le CART a son USER si les 2 sont definit
	#	
	if($userAffect && $id_user > 0 && $opt['id_cart'] > 0 && $my['id_user'] == 0){
		$this->dbQuery("UPDATE k_businesscart SET id_user=".$id_user." WHERE is_cart=1 AND id_cart=".$opt['id_cart']);		
	 	if($opt['debug']) $this->pre($this->db_query, $this->db_error);		
	}


	# On fige les valeur de ID_CART en SESSION
	#
    if($opt['is_cart'] === true) {
        if(intval($opt['id_cart']) > 0) $_SESSION['id_cart'] = $opt['id_cart'];
    }

	#
	# A ce stade on doit avoir un panier identifier par ID_CART
	# On ne peut pas etre certaine de ID_USER (pas necesserement logged)
	#

	if($opt['id_cart'] != '') 		$cond[] = 'id_cart='.$opt['id_cart'];
	if($opt['is_cmd']) 				$cond[] = 'is_cmd=1';
	if($opt['is_cart']) 			$cond[] = 'is_cart=1';
	if(intval($opt['id_shop']) > 0)	$cond[] = 'id_shop='.$opt['id_shop'];
	if(intval($opt['id_user']) > 0) $cond[] = 'id_user='.$opt['id_user'];
	if(isset($opt['cartStatus']))	$cond[] = 'cartStatus=\''.$opt['cartStatus'].'\'';
	$limit		= ($opt['limit'] != '') 		? $opt['limit']			: 30;
	$offset		= ($opt['offset'] != '') 		? $opt['offset']		: 0;
	
	if(is_array($opt['range'])){
		if($opt['range'][0] != ''){
			$cond[] = " cartDateCmd >= '".$opt['range'][0]."'";
		}
		if($opt['range'][1] != ''){
			$cond[] = " cartDateCmd <= '".$opt['range'][1]."'";
		}
	}

	if(sizeof($cond) > 0) $where = "WHERE ".implode(" AND ", $cond);
	
	if(!$opt['noLimit']) $sqlLimit = "\nLIMIT ".$offset.",".$limit;
	
	# On demande UN SEUL CART
	#
	if($opt['id_cart'] != NULL){
		$cart = $this->dbOne("SELECT * FROM k_businesscart ".$where);
		if($opt['debug']) $this->pre('Solo', $this->db_query, $this->db_error);

		$cart['cartTax'] = ($cart['cartTaxJSON'] != '') ? json_decode($cart['cartTaxJSON'], true) : array();

		// Mapping (1)
		$cart = $this->apiLoad('field')->fieldMapping(array(
			'fields'	=> $this->apiLoad('field')->fieldGet(array('businessCart' => true)),
			'data'		=> $cart
		));

		// Lines
		$cart['line'] = $this->dbMulti("SELECT * FROM k_businesscartline WHERE id_cart=".$opt['id_cart']);
		if($opt['debug']) $this->pre('Solo', $this->db_query, $this->db_error);

		// Mapping (2)	
		foreach($cart['line'] as $n => $line){
			$cart['line'][$n] = $this->apiLoad('field')->fieldMapping(array(
				'fields'	=> $this->apiLoad('field')->fieldGet(array('businessCartLine' => true)),
				'data'		=> $line
			));
		}
	}else
	
	# On demande DES COMMANDES
	#
	if($opt['is_cmd']){

		if($opt['order'] != NULL && $opt['direction'] != NULL){
			$order = " ORDER BY ".$opt['order']." ".$opt['direction'];
		}
		$cart = $this->dbMulti("SELECT SQL_CALC_FOUND_ROWS * FROM k_businesscart ". $where . $order . $sqlLimit);

		$this->total	= $this->db_num_total;
		$this->limit	= $limit;

		if($opt['debug']) $this->pre('Multi', $this->db_query, $this->db_error);
		
		foreach($cart as $idx => $e){
			$cart[$idx]['line']	= $this->dbMulti("SELECT * FROM k_businesscartline WHERE id_cart=".$e['id_cart']);
			if($opt['debug']) $this->pre('Multi', $this->db_query, $this->db_error);
			$cart[$idx]['cartTax'] = array();
			if($e['cartTaxJSON'] != '') $cart[$idx]['cartTax'] = json_decode($e['cartTaxJSON'], true);	
		}
	}


	return $cart;
}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function businessCartUserSet($id_cart, $id_user){

	    # On fixe l'id_user et les adresses BILLLING et DELIVERY
	    #
	    if($id_user > 0){
		    $billing  = $this->apiLoad('user')->userAddressBookGet(array('id_user' => $id_user, 'billing' => true));
		    $delivery = $this->apiLoad('user')->userAddressBookGet(array('id_user' => $id_user, 'delivery' => true));

		    // Adresses
	        $adBilling  = $this->apiLoad('user')->userAddressBookFormat($billing);
	        $adDelivery = $this->apiLoad('user')->userAddressBookFormat($delivery);

	        // Noms
            $billingName  = $this->apiLoad('user')->userAddressBookFormat($billing, array('name' => true));
            $deliveryName = $this->apiLoad('user')->userAddressBookFormat($delivery, array('name' => true));

	        $def['k_businesscart'] = array(
	            'id_user'               => array('value' => $id_user),
	            'id_delivery'           => array('value' => $delivery['id_addressbook']),
	            'cartDeliveryName'      => array('value' => $deliveryName),
	            'cartDeliveryAddress'   => array('value' => $adDelivery),

	            'id_billing'            => array('value' => $billing['id_addressbook']),
	            'cartBillingName'       => array('value' => $billingName),
	            'cartBillingAddress'    => array('value' => $adBilling),
	            'cartBillingTVAIntra'   => array('value' => $billing['addressbookTVAIntra'])
	        );

			$this->dbQuery($this->dbUpdate($def)." WHERE id_cart=".$id_cart);
	        return true;
	    }

	    return false;
	}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function businessCartPrice($id_cart, $opt=array()){
	
	$cart	= $this->dbOne("SELECT * FROM k_businesscart WHERE id_cart=".$id_cart);
	$line	= $this->dbMulti("SELECT * FROM k_businesscartline WHERE id_cart=".$id_cart);
	
	foreach($line as $e){
		$cartTotal 		+= $e['contentPriceQuantity'];
		$cartTotalTax 	+= $e['contentPriceTaxQuantity'];
	}

	$cartTotalFinal 	= $cartTotalTax + $cart['cartCarriageTotalTax'];

	if($cart['id_coupon'] > 0){
		$coupon	= $this->dbOne("SELECT * FROM k_businesscoupon WHERE id_coupon=".$cart['id_coupon']);
		if($coupon['couponMode'] == 'FIXE'){
			$cartCoupon	= $coupon['couponAmount'];
		}else
		if($coupon['couponMode'] == 'PERCENT'){
			$cartCoupon	= $cartTotalFinal * ($coupon['couponAmount'] / 100);
		}else
		if($coupon['couponMode'] == 'CARRIAGE'){
			$cartCoupon	= $cart['cartCarriage'];
		}
		
		$cartCouponName	= $coupon['couponName'];
	}elseif($opt['couponMode'] != '' && $opt['couponName'] != '') {

        if($opt['couponMode'] == 'FIXE'){
            $cartCoupon	= $opt['couponAmount'];
        }else
        if($opt['couponMode'] == 'PERCENT'){
            $cartCoupon	= $cartTotalFinal * ($opt['couponAmount'] / 100);
        }else
        if($opt['couponMode'] == 'CARRIAGE'){
            $cartCoupon	= $cart['cartCarriage'];
        }

        $cartCouponName	= $opt['couponName'];
    }else
    {
		$cartCoupon 	= 0;
		$cartCouponName	= '';
	}

	$cartTotalFinal = $cartTotalFinal - $cartCoupon;

	# Mettre a jour les TOTAUX du CART
	$def['k_businesscart'] = array(
		'cartCouponName'	=> array('value' => $cartCouponName),
		'cartCoupon'		=> array('value' => number_format($cartCoupon, 		2, '.', '')),
		'cartTotal' 		=> array('value' => number_format($cartTotal, 		2, '.', '')), 
		'cartTotalTax'		=> array('value' => number_format($cartTotalTax, 	2, '.', '')),
		'cartTotalFinal'	=> array('value' => number_format($cartTotalFinal, 	2, '.', '')),
	);
	
	$this->dbQuery($this->dbUpdate($def)." WHERE id_cart=".$id_cart);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);	
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function businessCartTaxJSON($id_cart, $array = NULL){
	
	$myCmd = $this->apiLoad('business')->businessCartGet(array(
		'id_cart' 	=> $id_cart,
		'debug'		=> false
	));
    
    # Bases TVA - Totaux TVA
    $tva = array();
    
    foreach($myCmd['line'] as $l){
        if($l['contentTax'] > 0){
            $tva[$l['contentTax']]['total'] += $l['contentPriceQuantity'];
            $tva[$l['contentTax']]['base'] += $l['contentPriceTaxQuantity'] - $l['contentPriceQuantity'];
            $tva[$l['contentTax']]['final'] += $tva[$l['contentTax']]['total'] + $tva[$l['contentTax']]['base'];
        }
    }
    # Tri par ordre croissant TVA
    ksort($tva);	
	
	$json = json_encode($tva);
	
	# Mettre a jour les TOTAUX du CART
	$def['k_businesscart'] = array(
		'cartTaxJSON'	=> array('value' => $json)
	);
	
	$this->dbQuery($this->dbUpdate($def)." WHERE id_cart=".$id_cart);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function businessCartAdd($opt=array()){

	if($opt['debug']) $this->pre("OPT", $opt);

	# Options
	$id_cart	= $opt['id_cart'];
	$id_content	= $opt['id_content'];
	$quantity	= isset($opt['quantity']) ? $opt['quantity'] : 1;

	# Verifier que le stock permet d'ajouter cette quantity
	$c = $this->apiLoad('content')->contentGet(array(
		'id_content'	=> $id_content,
		'debug'			=> false
	));

	if($c['contentStockNeg']){
		$quantity = $quantity;
	}else
	if($c['contentStock'] - $quantity < 0){
		$quantity = $c['contentStock'];
	}

	# Verifier qu'il n'existe pas deja une LINE dans le CART pour ce CONTENT
	$exists = $this->dbOne("SELECT 1 FROM k_businesscartline WHERE id_cart=".$id_cart." AND id_content=".$id_content);
	$query	= $exists[1]
		?	"UPDATE k_businesscartline SET contentQuantity = contentQuantity + (".$quantity.") WHERE id_cart=".$id_cart." AND id_content=".$id_content		
		: 	"INSERT INTO k_businesscartline (id_cart, id_content, contentQuantity) VALUES (".$id_cart.", ".$id_content.", ".$quantity.")";

	$this->dbQuery($query);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);

	$line = $this->dbOne("SELECT * FROM k_businesscartline WHERE id_cart=".$id_cart." AND id_content=".$id_content);

	# Mettre a jour les valeurs du CARLTLINE (prix, ref, etc...)
    $def['k_businesscartline'] = array(
        'id_carriage'               => array('value' => $c['id_carriage']),
        'contentPrice'              => array('value' => $c['contentPrice']), 
        'contentPriceQuantity'      => array('value' => number_format(($c['contentPrice']    * $line['contentQuantity']), 2, '.', '')),
        'contentPriceTax'           => array('value' => $c['contentPriceTax']),
        'contentPriceTaxQuantity'   => array('value' => number_format(($c['contentPriceTax'] * $line['contentQuantity']), 2, '.', '')),
        'contentTax'                => array('value' => $c['contentTax']),
        'contentRef'                => array('value' => $c['contentRef']),
        'contentName'               => array('value' => $c['contentName']),
        'contentWeight'             => array('value' => $c['contentWeight']),
        'accountNumber'             => array('value' => $c['accountNumber'])
    );
   
	$this->dbQuery($this->dbUpdate($def)." WHERE id_cart=".$id_cart." AND id_content=".$id_content);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	

	# Mettre a jour le STOCK pour le CONTENT
	$this->dbQuery("UPDATE k_content SET contentStock = contentStock - ".$quantity." WHERE id_content=".$id_content);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);

	# Update CART
	$this->businessCartPrice($line['id_cart']);

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function businessCartAddRaw($opt=array()){

    if($opt['debug']) $this->pre("OPT", $opt);

    # Options
    $id_cart	    = $opt['id_cart'];
    $id_cartline	= $opt['id_cartline'];
    $def            = array('k_businesscartline' => $opt['data']);

    # Mettre a jour les valeurs du CARLTLINE
    if($id_cartline != NULL) $this->dbQuery($this->dbUpdate($def)." WHERE id_cartline=".$id_cartline);
    else  $this->dbQuery($this->dbInsert($def));

    if($opt['debug']) $this->pre($this->db_query, $this->db_error);

    # Update CART
    $this->businessCartPrice($id_cart);

    return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function businessCartRemove($id_cart, $reset=false){

    $cart = $this->businessCartGet(array('id_cart' => $id_cart));

    if(sizeof($cart['line']) > 0){
        foreach($cart['line'] as $line){
            $this->businessCartLineRemove($line['id_cartline'], $reset);
        }
    }

    $this->dbQuery("DELETE FROM k_businesscart WHERE id_cart = ".$id_cart);

    return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
// Insertion ou modif manuelle d'une ligne d'une commande (pas d'id_content)

public function businessCartLineSet($opt=array()){
        
    # Options
    $id_cart        = $opt['id_cart'];
    $id_content     = ($opt['id_content'] > 0) ? $opt['id_content'] : 0;
    $msg = '';
    
    # id_cartline
    if($opt['id_cartline'] > 0)
    {
        $id_cartline = $opt['id_cartline'];         
    }else {
            
        if(!isset($opt['contentQuantity']))$opt['contentQuantity'] = 1;
        
        if($id_content > 0){
            $exists = $this->dbOne("SELECT 1 FROM k_businesscartline WHERE id_cart=".$id_cart." AND id_content=".$id_content);
            if(!$exists[1])$query = "INSERT INTO k_businesscartline (id_cart, id_content) VALUES (".$id_cart.", ".$id_content.")";
            else {
                //die($this->pre($exists));
                return 'Le produit a dÈja ÈtÈ ajoutÈ ‡ la commande';
                die();
            }                    
        }else {
            $query = "INSERT INTO k_businesscartline (id_cart,contentQuantity) VALUES (".$id_cart.",".$opt['contentQuantity'].")";
            //die($query);
        }
        $this->dbQuery($query);
        if($opt['debug']) $this->pre($this->db_query, $this->db_error);
        $id_cartline = $this->db_insert_id;
    }
    
    # Suppression
    if($opt['remove']){
        $this->businessCartLineRemove($id_cartline);
        
    # Update
    }else {
        
        $coeff_tax = 1;
        if($opt['contentTax'] > 0) $coeff_tax = 1+($opt['contentTax']/100);
        
        //echo '<br>'.$coeff_tax;
        
        # QuantitÈ
        if($opt['contentQuantity'] < 0)$opt['contentQuantity'] = 0;        
        
        if($id_content > 0){
            
            # Ajouter cette quantity
            $msg = $this->apiLoad('business')->businessCartQuantity($id_cartline,$opt['contentQuantity'],false);

            $c = $this->apiLoad('content')->contentGet(array(
                'id_content'    => $id_content,
                'id_group'      => $this->user['id_group'],
                'contentSee'    => 'ALL',
                'debug'         => false
            ));
            $myCmd = $this->apiLoad('business')->businessCartGet(array(
                'is_cart'   => true,
                'id_cart'   => $id_cart,
                'debug'     => false
            ));
            foreach($myCmd['line'] as $l){
                if($l['id_cartline'] == $id_cartline)$opt['contentQuantity'] = $l['contentQuantity'];
            }            
            
            $opt['contentRef'] = $c['contentRef'];
            if($opt['create']){
                $opt['contentName'] = $c['contentName'];
                $opt['contentPrice'] = $c['contentPrice'];
            }

        }            
        # Prix unitaire
        if($opt['contentPrice'] < 0)$opt['contentPrice'] = 0;
            
        # Remise
        if($opt['contentPriceDiscount'] < 0)$opt['contentPriceDiscount'] = 0;
        
		
		if($opt['contentPriceDiscountMode'] == "PERCENT"){
        	if($opt['contentPriceDiscount'] > 100)$opt['contentPriceDiscount'] = 100;
	        # Prix Unitaire TTC
	        $prix_unitaire_ttc = number_format($coeff_tax * ($opt['contentPrice'] * (1-($opt['contentPriceDiscount'] / 100))), 2, '.', '');
	        
	        # Total HT
	        $total_ht = number_format(($opt['contentPrice'] * (1-($opt['contentPriceDiscount'] / 100))) * $opt['contentQuantity'], 2, '.', '');
        }else			
		if($opt['contentPriceDiscountMode'] == "FIXE"){
        	if($opt['contentPriceDiscount'] > $opt['contentPrice'])$opt['contentPriceDiscount'] = $opt['contentPrice'];
	        # Prix Unitaire TTC
	        $prix_unitaire_ttc = number_format($coeff_tax * ($opt['contentPrice'] - $opt['contentPriceDiscount']), 2, '.', '');
	        
	        # Total HT
	        $total_ht = number_format(($opt['contentPrice'] - $opt['contentPriceDiscount']) * $opt['contentQuantity'], 2, '.', '');
			
		}else{
	        # Prix Unitaire TTC
	        $prix_unitaire_ttc = number_format($coeff_tax * ($opt['contentPrice']), 2, '.', '');
	        
	        # Total HT
	        $total_ht = number_format(($opt['contentPrice']) * $opt['contentQuantity'], 2, '.', '');
			
		}
        # Total TTC
        $total_ttc = number_format($coeff_tax * $total_ht, 2, '.', '');
        
        //echo '<br>'.$total_ttc;
        
        # Mettre a jour les valeurs du CARLTLINE (prix, ref, etc...)
        $def['k_businesscartline'] = array(
            'contentPrice'              => array('value' => $opt['contentPrice']), 
            'contentPriceDiscount'      => array('value' => $opt['contentPriceDiscount']), 
            'contentPriceDiscountMode'  => array('value' => $opt['contentPriceDiscountMode']), 
            'contentPriceTax'           => array('value' => $prix_unitaire_ttc),
            'contentTax'                => array('value' => $opt['contentTax']),
            'contentRef'                => array('value' => $opt['contentRef']),
            'contentName'               => array('value' => $opt['contentName']),
            'contentQuantity'           => array('value' => $opt['contentQuantity']),
            'accountNumber'             => array('value' => $opt['accountNumber']),
            'contentPriceQuantity'      => array('value' => $total_ht),
            'contentPriceTaxQuantity'   => array('value' => $total_ttc)
        );
        
        //die($this->pre($def));
        
        if($id_cartline > 0){
            $this->dbQuery($this->dbUpdate($def)." WHERE id_cartline=".$id_cartline);
            if($opt['debug']) $this->pre($this->db_query, $this->db_error);
        }
    }

    # Update CART
    $this->businessCartPrice($id_cart);
    
    return $msg;
    //return $id_cartline;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function businessCartLineRemove($id_cartline, $reset=true){

	if($reset){
		$line = $this->dbOne("SELECT * FROM k_businesscartline WHERE id_cartline=".$id_cartline);

		if($line['id_content'] != NULL){
			$this->dbQuery("UPDATE k_content SET contentStock = contentStock + ".$line['contentQuantity']." WHERE id_content = ".$line['id_content']);
		}
	}

	$this->dbQuery("DELETE FROM k_businesscartline WHERE id_cartline = ".$id_cartline);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function businessCartLineField($opt){

	$field			= $opt['field']; 		if(sizeof($field) == 0) 		return false;
	$id_cartline	= $opt['id_cartline']; 	if(intval($id_cartline) == 0) 	return false;

	$field_ 	= $this->apiLoad('field')->fieldGet(array(
		'businessCartLine' => true
	));

	$format		= $this->apiLoad('field')->fieldFormating(array(
		'debug'		=> false,
		'fields'	=> $field_,
		'data'		=> $opt['field']
	));

	if(is_array($format) && sizeof($format) > 0){
		$this->dbQuery($this->dbUpdate(array('k_businesscartline' => $format))."\nWHERE id_cartline=".$id_cartline);
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
		return true;
	}
	
	return false;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function businessCartQuantity($id_cartline, $quantity, $remove=true){

	# Si la quanite est OUT-OF
	if($quantity < 1 && $remove){
		$this->businessCartLineRemove($id_cartline);
	}else{

		# Prod -> Line
		$line		= $this->dbOne("SELECT * FROM k_businesscartline WHERE id_cartline = ".$id_cartline);
		$content	= $this->dbOne("SELECT * FROM k_content WHERE id_content = ".$line['id_content']);
		$v 			= $quantity - $line['contentQuantity'];

		if($v != '0'){

			if($content['contentStockNeg']){
			#	$this->pre("Pas de soucis de stock");
				$d = -$v;
			}else
			if($content['contentStock'] - $v < 0){
				$quantity = $line['contentQuantity'] + $content['contentStock'];
				$d = -$content['contentStock'];
			}else{
				$d = -$v;
			}

			# Reset CONTENT stock
			$this->dbQuery("UPDATE k_content SET contentStock = contentStock + (".$d.") WHERE id_content = ".$line['id_content']);
			#$this->pre($this->db_query, $this->db_error);

			# Update CARTLINE
			$def['k_businesscartline'] = array(
				'contentQuantity'			=> array('value' => $quantity),
				'contentPrice' 				=> array('value' => number_format($line['contentPrice'], 2, '.', '')), 
				'contentPriceQuantity'		=> array('value' => number_format(($line['contentPrice'] * $quantity), 2, '.', '')),
				'contentPriceTax'			=> array('value' => number_format($line['contentPriceTax'], 2, '.', '')),
				'contentPriceTaxQuantity'	=> array('value' => number_format(($line['contentPriceTax'] * $quantity), 2, '.', ''))
			);

			$this->dbQuery($this->dbUpdate($def)." WHERE id_cartline = ".$id_cartline); 
			#$this->pre($this->db_query, $this->db_error);
			
			# Update CART
			$this->businessCartPrice($line['id_cart']);
		}

	}
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function businessCartFlagSet($id_cart, $name, $value){

	$this->dbQuery(
		"INSERT IGNORE k_businesscartflag ".
		"(id_cart, cartFlagName, cartFlagValue) ".
		"VALUES ".
		"(".$id_cart.", '".$name."', '".$value."')"
	);

}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function businessCartCarriageGet($id_cart, $opt=array()){

	if($this->user['id_user'] != NULL){

		$delivery	= $this->apiLoad('user')->userAddressbookGet(array(
			'id_user'	=> $this->user['id_user'],
			'delivery'	=> true
		));
		
		$iso	= $delivery['addressbookCountryCode'];
		$pays	= $this->countryGet(array('iso' => $iso));
		$zone 	= strtoupper($pays['countryZone']);
		$iso	= strtoupper($iso);

		$cart 	= ($opt['cart'] == NULL) ? $this->businessCartGet(array('id_cart' => $id_cart)) : $opt['cart'];
	
		if(sizeof($cart['line']) > 0){
	
			foreach($cart['line'] as $line){
				$rule[$line['id_carriage']]['weight']	+= ($line['contentWeight'] * $line['contentQuantity']);
				$rule[$line['id_carriage']]['content'][] = $line['id_content'];
			}
	
			foreach($rule as $id_carriage => $rule){
				$carriage	= $this->dbOne("SELECT * FROM k_businesscarriage WHERE id_carriage=".$id_carriage);
	
				if($carriage['is_gift']){
					$this->pre("GIFT");
					$cost	= 0;
				}else
				if($carriage['carriagePrice'] > 0){
					$cost	= $carriage['carriagePrice'];
				}else{
					$lines	= explode("\n", $carriage['carriageRule']);
					$me		= NULL;
					$i		= 0;
	
					foreach($lines as $n => $line){
						list($num, $prices) = explode("|", $line);
						$num	= trim($num);
						$prices = trim($prices);
			
						if($num > $rule['weight'] && $me == NULL){
							$me = $prices;
						}
						
						if($i == sizeof($lines)-1 && $me == NULL){
							if($prices != NULL){
								$me = $prices;
							}
						}
						$i++;
					}
				
					if($me == NULL){
						#$this->pre("Cette regle ne peux pas s'appliquer pour ce produit poids > limit");
					}else{
						if($iso != NULL){
							$es = explode(';', $me);
							foreach($es as $e){
								list($pays, $prix) = explode(':', $e);
								if($pays == $iso)	$cost = $prix;
								if($pays == $zone)	$cost = $prix;
							}
						}
					}
				}
			}

			return (float) number_format($cost, 2, '.', '');
		}

		return '';

	}else{
		return false;
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function businessCartCarriageSet($opt){

	if($opt['id_cart'] == NULL) die("FATAL ERROR : NO ID_CART GIVEN TO App:Business:businessCartCarriageSet()");

	$carriage = ($opt['carriage'])
		? $opt['carriage']
		: $this->businessCartCarriageGet($opt['id_cart']);

	if(is_float($carriage)){

        // TVA
        $cartCarriageTax        = ($opt['carriageTax'] > 0) ? $opt['carriageTax'] : '20.00';
        $cartCarriageTotalTax   = $carriage;
        $cartCarriage           = $cartCarriageTotalTax / (1 + ($cartCarriageTax / 100));

        $def = array();
        $def['k_businesscart'] = array(
            'cartCarriageTax'           => array('value' => number_format($cartCarriageTax,      2, '.', '')),
            'cartCarriage'              => array('value' => number_format($cartCarriage,         2, '.', '')),
            'cartCarriageTotalTax'      => array('value' => number_format($cartCarriageTotalTax, 2, '.', ''))
        );

        $this->dbQuery($this->dbUpdate($def)." WHERE id_cart='".$opt['id_cart']."'");

        //$this->pre($this->db_query);
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	
		$this->businessCartPrice($opt['id_cart'], array('debug' => $opt['debug']));
		
		return true;
	}else{
		return false;
	}
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function businessCartShopSet($id_cart, $id_shop){
	if(intval($id_cart) <= 0 OR intval($id_shop) <= 0) return false;
	$this->dbQuery("UPDATE k_businesscart SET id_shop=".$id_shop." WHERE id_cart=".$id_cart);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function businessCartField($opt){

	$field		= $opt['field']; 		if(sizeof($field) == 0) 	return false;
	$id_cart	= $opt['id_cart']; 		if(intval($id_cart) == 0) 	return false;

	$field_ 	= $this->apiLoad('field')->fieldGet(array(
		'businessCart' => true
	));

	$format		= $this->apiLoad('field')->fieldFormating(array(
		'debug'		=> false,
		'fields'	=> $field_,
		'data'		=> $opt['field']
	));

	if(is_array($format) && sizeof($format) > 0){
		$this->dbQuery($this->dbUpdate(array('k_businesscart' => $format))."\nWHERE id_cart=".$id_cart);
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
		return true;
	}
	
	return false;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function businessCmdNew($opt){

	if(!is_array($opt['update'])) $opt['update'] = array();

	$cart = $this->businessCartGet(array(
		'id_cart' 	=> $opt['id_cart'],
		'is_cart'	=> true,
		'create'	=> false
	));

	# On recupere le USER qui est relie au panier
	$user = $this->apiLoad('user')->userGet(array(
		'id_user'	=> $cart['id_user'],
		'debug'		=> false
	));

	# Cas fatal ID_CART pas connu ou il s'agit deja d'une CMD
	if($cart['id_cart'] == NULL || $cart['is_cmd']) die("businessCmdNew, erreur pannier errone (NULL ou IS_CMD)");

	# On change les CART en CMD et on fixe quelques valeurs
	$this->dbQuery("UPDATE k_businesscart SET is_cmd=1, is_cart=0, cartDateCmd=NOW(), cartEmail='".$user['userMail']."' WHERE id_cart=".$opt['id_cart']);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);

	# On met a jour les valeurs des champs opt[update]
	foreach($opt['update'] as $k => $v){
		if($k != NULL && $v != NULL){
			$this->dbQuery("UPDATE k_businesscart SET `".$k."`='".addslashes($v)."' WHERE id_cart=".$opt['id_cart']);
			if($opt['debug']) $this->pre($this->db_query, $this->db_error);
		}
	}
	
	# On s'occupe du numero de la facture.
	$this->businessCmdIncrement($opt);

	# On signale que le COUPON ne peut plus etre utilise IS_USED=1
	if($cart['id_coupon'] > 0){
		$this->dbQuery("UPDATE k_usercoupon SET is_used=1 WHERE id_coupon=".$cart['id_coupon']." AND id_user=".$cart['id_user']);
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	}

	# HOOK
	$this->hookAction('businessCmdNew', $opt['id_cart']);

	return true;
}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function businessCmdIncrement($opt){

		$id_cart = $opt['id_cart']; if(intval($id_cart) == 0) return false;

	    # HOOK
		$this->hookAction('businessCmdIncrement', $id_cart);

		# GET
		$cart = $this->dbOne("SELECT * FROM k_businesscart WHERE id_cart=".$id_cart);
		if($cart['id_cart'] != $id_cart) return false; // Ce cas ne devrait jamais arrivé
		if($cart['cartStatus'] != 'OK')	 return false; // Si le panier est OK = relge = facture
		if($cart['cartCmdNumber'] != '') return false; // Si j'ai deja un numerod de facture on evite !

		# LAST
		$last = ($cart['id_shop'] > 0)
			? $this->dbOne("SELECT MAX(cartCmdNumber) AS h FROM k_businesscart WHERE id_shop=".$cart['id_shop'])
			: $this->dbOne("SELECT MAX(cartCmdNumber) AS h FROM k_businesscart WHERE id_shop IS NULL");

	    if(is_null($last)) $last = 0;

		# LAST
		$this->dbQuery("UPDATE k_businesscart SET cartCmdNumber=".(intval($last['h']) + 1)." WHERE id_cart=".$id_cart);
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function businessCmdMail($opt){

		require_once(KROOT . '/app/plugin/phpmailer/class.phpmailer.php');

		# Cmmand
		#
		$cmd = $this->businessCartGet(array(
			'id_cart'	=> $opt['id_cart'],
			'is_cmd'	=> true,
			'debug'		=> false
		));
		if(!is_numeric($cmd['id_cart'])) return false;


		# Shop
		#
		if($cmd['id_shop'] > 0){
			$shop = $this->apiLoad('shop')->shopGet(array(
				'id_shop'	=> $cmd['id_shop']
			));

			$mailTo		= $this->apiLoad('shop')->shopMailExtraction($shop['shopMailTo']);
			$mailCc		= $this->apiLoad('shop')->shopMailExtraction($shop['shopMailCc']);
			$mailBcc	= $this->apiLoad('shop')->shopMailExtraction($shop['shopMailBcc']);

			if($opt['mailTo'] != '')		$mailTo[] = $opt['mailTo'];
			if($opt['mailCc'] != '')		$mailCc[] = $opt['mailCc'];
			if(is_array($opt['mailBcc']))	$mailBcc  = array_merge($mailBcc, $opt['mailBcc']);

	        $mailTitle 	= ($opt['mailTitle'] != '') ? $opt['mailTitle'] : $shop['shopMailTitle'];
	        $template 	= ($opt['mailTemplate'] != '') ? USER.'/mail/'.$opt['mailTemplate'] : USER.'/mail/'.$shop['shopMailTemplate'];
		}else{
			/*$mailTo		= array($opt['mailTo']);
			$mailCc		= array($opt['mailCc']);
			$mailBcc	= $opt['mailBcc'];*/
			$mailTo		= is_array($opt['mailTo'])  ? $opt['mailTo']  : array($opt['mailTo']);
			$mailCc		= is_array($opt['mailCc'])  ? $opt['mailCc']  : array($opt['mailCc']);
			$mailBcc	= is_array($opt['mailBcc']) ? $opt['mailBcc'] : array($opt['mailBcc']);

			$mailTitle 	= $opt['mailTitle'];
			$template	= USER.'/mail/'.$opt['mailTemplate'];
		}

		$mailTo	 = is_array($mailTo)  ? $mailTo  : array();
		$mailCc	 = is_array($mailCc)  ? $mailCc  : array();
		$mailBcc = is_array($mailBcc) ? $mailBcc : array();

		# Mail
		#
		$mail = new PHPMailer();
	    $mail->CharSet = "UTF-8";
        $fromName = '';
        if($opt['mailFromName'] != '') $fromName = $opt['mailFromName'];
	    if($opt['mailFrom'] != '') $mail->SetFrom($opt['mailFrom'], $fromName);
		else $mail->SetFrom('noreply@'.$_SERVER['HTTP_HOST'], $fromName);

		// TO
		foreach($mailTo as $e){
			if(filter_var($e, FILTER_VALIDATE_EMAIL) !== FALSE) $mail->AddAddress($e);
		}
		$mail->ClearReplyTos();
	    if($opt['mailReplyTo'] != '') $mail->AddReplyTo($opt['mailReplyTo']);
	    else $mail->AddReplyTo('noreply@'.$_SERVER['HTTP_HOST']);

		// CC
		foreach($mailCc as $e){
			if(filter_var($e, FILTER_VALIDATE_EMAIL) !== FALSE) $mail->AddCC($e);
		}

		// BCC
		foreach($mailBcc as $e){
			if(filter_var($e, FILTER_VALIDATE_EMAIL) !== FALSE) $mail->AddBCC($e);
		}

		if(file_exists($template)){
			$split		= '{lines}';
			$message 	= file_get_contents($template);

			if(is_array($opt['replace'])){
				$message = $this->helperReplace($message, $opt['replace']);
			}

			if(preg_match_all("#{lines}(.*){lines}#s", $message, $m, PREG_SET_ORDER)){
				$tLine	= $m[0][1];

				foreach($cmd['line'] as $e){
					$tmp .= $this->helperReplace($tLine, $e);
				}

				$message = str_replace($m[0][0], $tmp, $message);
			}

			if(preg_match_all("#{ifCoupon}(.*){ifCoupon}#s", $message, $m, PREG_SET_ORDER)){
				$message = (floatval($cmd['cartCoupon']) == 0)
					? str_replace($m[0][0], NULL, $message)
					: str_replace('{ifCoupon}', NULL, $message);
			}

			$cmd['cartDeliveryAddress']	= nl2br($cmd['cartDeliveryAddress']);
			$cmd['cartBillingAddress']	= nl2br($cmd['cartBillingAddress']);

			$message = $this->helperReplace($message, $cmd);
		}else{
			$message = 'Template not found '.$template;
		}

		$mail->Subject	= $this->helperReplace($mailTitle, $cmd);
		$mail->AltBody	= strip_tags($message);
		$mail->MsgHTML(preg_replace("[\\\]",'',$message));;

		if($opt['debug']){
			$this->pre("mailto", $mailTo, 'mailCc', $mailCc, 'mailBcc', $mailBcc, 'mailTitle', $mailTitle, 'message', $message, 'cmd', $cmd, 'mail', $mail);
		}

		# HOOK
		$custom = $this->hookAction('businessCmdMail', $opt['id_cart'], $mailTo, $mail->Subject, $message);

		if(is_array($custom)){
			if($custom['mailTitle'] != ''){
				$mail->Subject = $custom['mailTitle'];
			}
			if($custom['mailBody'] != ''){
				$mail->AltBody = strip_tags($custom['mailBody']);
				$mail->MsgHTML(preg_replace("[\\\]",'',$custom['mailBody']));
			}
		}

		if(!$opt['return']){
			if(@$mail->send()){
				return true;
			}else
			if($opt['debug']){
				$this->pre($mail->ErrorInfo);
				return false;
			}
		}

		if($opt['return']) return $mail;

		return false;
	}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function businessCouponSet($opt){

	$id_coupon	= $opt['id_coupon'];
	$def		= $opt['def'];

	if($id_coupon  > 0){
		$q = $this->dbUpdate($def)." WHERE id_coupon=".$id_coupon;
	}else{
		$q = $this->dbInsert($def);
	}

	@$this->dbQuery($q);
	if($this->db_error != NULL) return false;

	$this->id_coupon = ($id_coupon > 0) ? $id_coupon : $this->db_insert_id;

	if(array_key_exists('id_shop', $opt)){
		$this->dbAssoSet('k_businesscouponshop', 'id_coupon', 'id_shop', $this->id_coupon, $opt['id_shop'], 'ALL');
	}

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function businessCouponUserSet($opt=array()){

	$def['k_usercoupon'] = array(
		'id_user'	=> array('value' => $opt['id_user']),
		'id_coupon'	=> array('value' => $opt['id_coupon'])
	);

	$this->dbQuery($this->dbInsert($def));
	if($opt['debug']) $this->pre($this->db_query, $this->db_error, $opt);

	$this->id_coupon = ($opt['id_coupon'] > 0) ? $opt['id_coupon'] : $this->db_insert_id;

	if(array_key_exists('id_shop', $opt)){
		$this->dbAssoSet('k_businesscouponshop', 'id_coupon', 'id_shop', $this->id_coupon, $opt['id_shop'], 'ALL');
	}

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function businessCouponGet($opt=array()){

	if($opt['id_user']){
		$join[] = "INNER JOIN k_usercoupon ON k_businesscoupon.id_coupon = k_usercoupon.id_coupon";
		$cond[] = 'id_user='.$opt['id_user'];
	}

	if($opt['couponCode'] != ''){
		$dbMode	= 'dbOne';
		$cond[] = 'couponCode=\''.$opt['couponCode'].'\'';
	}else
	if($opt['id_coupon'] > 0){
		$dbMode	= 'dbOne';
		$cond[] = 'id_coupon='.$opt['id_coupon'];
	}
	
	if($dbMode == NULL) $dbMode = 'dbMulti';

	if(isset($opt['is_used'])) $cond[] = 'is_used='.$opt['is_used'];

	if(sizeof($join) > 0) $join  = "\n".implode("\n", $join)."\n";
	if(sizeof($cond) > 0) $where = "\nWHERE ".implode(" AND ", $cond)."\n";

	$coupon = $this->$dbMode("SELECT * FROM k_businesscoupon ".$join." ".$where);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error, $coupon);

	// Shop
	if($dbMode == 'dbOne'){
		$shops = $this->dbMulti("SELECT id_shop FROM k_businesscouponshop WHERE id_coupon='".$coupon['id_coupon']."'");
		foreach($shops as $e){
			$coupon['id_shop'][] = $e['id_shop'];
		}
	}

	return $coupon;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function businessConfigGet($opt=array()){
    $sqlWhere = array();
    $where = '';
    if($opt['configField'] != '')$sqlWhere [] =  " configField='".$opt['configField']."'";
    if($opt['configKey'] != '')$sqlWhere [] =  " configKey='".$opt['configKey']."'";
    if(sizeof($sqlWhere) > 0)$where = ' WHERE '.implode('AND',$sqlWhere);

	$config = $this->dbMulti("SELECT * FROM k_businessconfig ".$where." ".$opt['sqlOrder']);

    return $config;
}



/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function businessConfigSet($configField, $configKey, $def){

    if($configField != NULL && $configKey != NULL){
        $q = $this->dbUpdate($def)." WHERE configField='".$configField."' AND configKey='".$configKey."'";
    }else{
        return false;
    }

    @$this->dbQuery($q);
    if($this->db_error != NULL) return false;


    return true;
}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function businessStatusGet(){

	    $data   = array('OK', 'WAIT', 'CANCEL');
		$custom = $this->hookAction('businessStatusGet', $data);

		return is_array($custom) ? $custom : $data;
	}


//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function businessModePaymentGet(){

	    $data   = array('CB', 'CHEQUE', 'VIREMENT', 'ESPECES', 'PAYPAL');
		$custom = $this->hookAction('businessModePaymentGet', $data);

		return is_array($custom) ? $custom : $data;
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function businessCartDeliveryStatus(){
	    $data   = array('WAIT', 'INPROGRESS', 'SENT');
		$custom = $this->hookAction('businessCartDeliveryStatus', $data);

		return is_array($custom) ? $custom : $data;
	}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function businessCarriageGet($opt=array()){

	if($opt['id_carriage'] > 0){
		$dbMode = 'dbOne';
		$cond[] = "id_carriage=".$opt['id_carriage'];
	}else{
		$dbMode = 'dbMulti';
	}

	# Former les conditions
	#
	if(sizeof($cond) > 0) $where = "WHERE ".implode(" AND ", $cond);

	# Carraige
	#
	$carriage = $this->$dbMode("SELECT * FROM  k_businesscarriage ".$where);
	if($opt['debug']) $this->pre($opt, $this->db_query, $this->db_error, $carriage);

	return $carriage;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function businessCarriageSet($id_carriage, $def){

	if($id_carriage  > 0){
		$q = $this->dbUpdate($def)." WHERE id_carriage=".$id_carriage;
	}else{
		$q = $this->dbInsert($def);
	}

	@$this->dbQuery($q);
	if($this->db_error != NULL) return false;

	$this->id_carriage = ($id_carriage > 0) ? $id_carriage : $this->db_insert_id;

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function businessAccountGet($opt=array()){

    if($opt['id_account'] > 0){
        $dbMode = 'dbOne';
        $cond[] = "id_account=".$opt['id_account'];
    }else{
        $dbMode = 'dbMulti';
    }
    if($opt['accountNumber'] > 0) $cond[] = "accountNumber=".$opt['accountNumber'];

    # Former les conditions
    #
    if(sizeof($cond) > 0) $where = "WHERE ".implode(" AND ", $cond);

    # Account
    #
    $account = $this->$dbMode("SELECT * FROM  k_businessaccount ".$where);
    if($opt['debug']) $this->pre($opt, $this->db_query, $this->db_error, $account);

    return $account;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function businessAccountSet($id_account, $def){

    if($id_account  > 0){
        $q = $this->dbUpdate($def)." WHERE id_account=".$id_account;
    }else{
        $q = $this->dbInsert($def);
    }

    @$this->dbQuery($q);
    if($this->db_error != NULL) return false;

    $this->id_account = ($id_account > 0) ? $id_account : $this->db_insert_id;

    return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function businessAccountSelector($opt){
    $account = $this->businessAccountGet();
    $opt['multi'] = ($opt['one']) ? false : true;
    if($opt['multi']){
        $value = is_array($opt['value']) ? $opt['value'] : array();

        $form .= "<select name=\"".$opt['name']."\" id=\"".$opt['id']."\" size=\"".$opt['size']."\" multiple style=\"".$opt['style']."\" ".$opt['events'].">";
        foreach($account as $e){
            $selected = in_array($e['accountNumber'], $value) ? ' selected' : NULL;
            $form .= "<option value=\"".$e['accountNumber']."\"".$selected.">".$e['accountNumber']."&nbsp;(".$e['accountName'].")</option>";
        }
        $form .= "</select>";
    }else
    if($opt['one']){
        $value = is_array($opt['value']) ? $opt['value'][0] : $opt['value'];

        $form .= "<select name=\"".$opt['name']."\" id=\"".$opt['id']."\" style=\"".$opt['style']."\" ".$opt['events'].">";
        if($opt['empty']) $form .= "<option value=\"\"></option>";
        foreach($account as $e){
            $selected = ($e['accountNumber'] == $value) ? ' selected' : NULL;
            $form .= "<option value=\"".$e['accountNumber']."\"".$selected.">".$e['accountNumber']."&nbsp;(".$e['accountName'].")</option>";
        }
        $form .= "</select>";
    }
    
    return $form;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function businessTaxGet($opt=array()){

    if($opt['id_tax'] > 0){
        $dbMode = 'dbOne';
        $cond[] = "id_tax=".$opt['id_tax'];
    }else{
        $dbMode = 'dbMulti';
    }

    # Former les conditions
    #
    if(sizeof($cond) > 0) $where = "WHERE ".implode(" AND ", $cond);

    # Tax
    #
    $tax = $this->$dbMode("SELECT * FROM  k_businesstax ".$where." ORDER BY tax");
    if($opt['debug']) $this->pre($opt, $this->db_query, $this->db_error, $tax);

    return $tax;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function businessTaxSet($id_tax, $def){

    if($id_tax  > 0){
        $q = $this->dbUpdate($def)." WHERE id_tax=".$id_tax;
    }else{
        $q = $this->dbInsert($def);
    }

    @$this->dbQuery($q);
    if($this->db_error != NULL) return false;

    $this->id_tax = ($id_tax > 0) ? $id_tax : $this->db_insert_id;

    return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function businessTaxSelector($opt){

    $tax = $this->businessTaxGet();

    if($opt['multi']){
        $value = is_array($opt['value']) ? $opt['value'] : array();

        $form .= "<select name=\"".$opt['name']."\" id=\"".$opt['id']."\" size=\"".$opt['size']."\" multiple style=\"".$opt['style']."\" ".$opt['events'].">";
        foreach($tax as $e){
            $selected = in_array($e['tax'], $value) ? ' selected' : NULL;
            $form .= "<option value=\"".$e['tax']."\"".$selected.">".$e['tax']." %</option>";
        }
        $form .= "</select>";
    }else
    if($opt['one']){
        $value = is_array($opt['value']) ? $opt['value'][0] : $opt['value'];

        $form .= "<select name=\"".$opt['name']."\" id=\"".$opt['id']."\" style=\"".$opt['style']."\" ".$opt['events'].">";
        if($opt['empty']) $form .= "<option value=\"\"></option>";
        foreach($tax as $e){
            $selected = ($e['tax'] == $value) ? ' selected' : NULL;
            $form .= "<option value=\"".$e['tax']."\"".$selected.">".$e['tax']." %</option>";
        }
        $form .= "</select>";
    }
    
    return $form;
}




}
