<?php
    $_POST = json_decode($_POST['data']);
    $_POST = get_object_vars( $_POST);
    
    foreach($_POST as $k=>$v){
        $tab = explode('-',$k);
        if(sizeof($tab) == 3){
            $_POST[$tab[0]][$tab[1]][$tab[2]] = $v;
        }
    }
    
    # Data
    # On recupere le panier
    #
    $myCmd = $app->apiLoad('business')->businessCartGet(array(
        'is_cart'   => true,
        'id_cart'   => $_POST['id_cart'],
        'debug'     => false
    ));
    //die($app->pre($_POST));
    # On enregistre les changements
    if(sizeof($_POST) > 0){
        
        
        //$app->apiLoad('business')->businessCartUserSet($_REQUEST['id_cart'], $myCmd['id_user']);
        
        if($_POST['id_cart'] > 0){
            
            
            $coeff_tax = 1;
            if($_POST['cartCarriageTax'] > 0) $coeff_tax = 1+($_POST['cartCarriageTax']/100);

            $def['k_businesscart'] = array(
                'is_admin'                          => array('value' => 1),
                'id_shop'                           => array('value' => $_POST['id_shop']),
                'cartName'                          => array('value' => $_POST['cartName']),
                'cartStatus'                        => array('value' => $_POST['cartStatus']),
                'cartPayment'                       => array('value' => $_POST['cartPayment']),
                'cartDeliveryStatus'                => array('value' => $_POST['cartDeliveryStatus']),
                'cartCarriage'                      => array('value' => ($_POST['cartCarriage'] > 0) ? number_format($_POST['cartCarriage'], 2, '.', ' ') : 0),
                'cartCarriageTax'                   => array('value' => ($_POST['cartCarriageTax'] > 0) ? number_format($_POST['cartCarriageTax'], 2, '.', ' ') : 0),
                'cartCarriageTotalTax'              => array('value' => ($coeff_tax * $_POST['cartCarriage'] > 0) ? number_format($coeff_tax * $_POST['cartCarriage'], 2, '.', ' ') : 0),
                'cartCarriageAccountNumber'         => array('value' => $_POST['cartCarriageAccountNumber'])
             );
             $q = $app->dbUpdate($def)." WHERE id_cart=".$_POST['id_cart'];
             $app->dbQuery($q);
        }            
        
        foreach($myCmd['line'] as $l){
            if(sizeof($_POST['line'][$l['id_cartline']]) > 0){
                $opt = array_merge(array('id_cart' => $myCmd['id_cart'],'id_cartline' => $l['id_cartline']),$_POST['line'][$l['id_cartline']]);
                
                $msg = $app->apiLoad('business')->businessCartLineSet($opt);
            }
        }

    }
    
    $app->apiLoad('business')->businessCartTaxJSON($_POST['id_cart']);
	
    # Data
    # On recupere le panier
    #
    $myCmd = $app->apiLoad('business')->businessCartGet(array(
        'is_cart'   => true,
        /*'create'    => true,*/
        'id_cart'   => $_POST['id_cart'],
        'debug'     => false
    ));
	
	
    /*
	 * # Bases TVA - Totaux TVA
    $tva = array();
    
    foreach($myCmd['line'] as $l){
        if($l['contentTax'] > 0){
            $tva[$l['contentTax']]['total'] += $l['contentPriceQuantity'];
            $tva[$l['contentTax']]['base'] += $l['contentPriceTaxQuantity'] - $l['contentPriceQuantity'];
        }
    }
    # Tri par ordre décroissant TVA
    ksort($tva);
    
	*/
	 
    /*
    $myCmd = $app->apiLoad('business')->businessCartGet(array(
        'is_cmd'    => true,
        'id_cart'   => $_GET['id_cart'],
        'debug'     => false
    ));
    */
    
    //$flag = $app->dbMulti("SELECT * FROM k_businesscartflag WHERE id_cart=".$myCmd['id_cart']);
    //$app->pre($myCmd);
    /*$return = array(
        "cartTotalFinal" => $myCmd['cartTotalFinal'],
        "cartTotalFinal" => $myCmd['cartTotalFinal']
    );*/
    
    foreach($myCmd['line'] as $k=>$l){
        foreach($l as $k=>$v){
            $myCmd['line-'.$l['id_cartline'].'-'.$k] = $v;
        }
        $myCmd['line-'.$l['id_cartline'].'-stock'] = "-";
        if($l['id_content'] > 0) {
            $qstock = $app->dbOne("SELECT contentStock,contentStockNeg FROM k_content WHERE id_content='".$l['id_content']."'");
            if($qstock['contentStockNeg'] >= 0) $myCmd['line-'.$l['id_cartline'].'-stock'] = $qstock['contentStock'];           
        }
    }
    $myCmd['cartDeliveryName'] = str_replace("\n", '###', $myCmd['cartDeliveryName']);
    $myCmd['totalTVA'] = number_format(($myCmd['cartTotalTax'] - $myCmd['cartTotal']), 2, '.', ' ');
    $myCmd['cartCarriageTVA'] = number_format($myCmd['cartCarriageTotalTax']-$myCmd['cartCarriage'], 2, '.', ' ');
    //$myCmd['tva'] = $tva;
    $myCmd['msg'] = $msg;    
    //$myCmd['cartDelivery'] = str_replace("\n", '###', $myCmd['cartDeliveryName']);//addslashes("<b>".str_replace("\n", '<br />', $myCmd['cartDeliveryName'])."</b><br />".str_replace("\n", '<br />', $myCmd['cartDeliveryAddress']));
    //$myCmd['cartBilling'] = addslashes("<b>".str_replace("\n", '<br />', $myCmd['cartBillingName'])."</b><br />".str_replace("\n", '<br />', $myCmd['cartBillingAddress']));
    //die(str_replace("\n", '###', $myCmd['cartDeliveryName']));
    echo json_encode($myCmd);
?>