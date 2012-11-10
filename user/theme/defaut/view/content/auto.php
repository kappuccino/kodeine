<?php

	include(USER.'/bank/'.APIBANK.'/_auto.php');

	if($apiSuccess){

		$this->apiLoad('business')->businessCmdNew(array(
			'id_cart'	=> $apiIdCart,
			'update'	=> array(
				'id_shop'			=> 1,
				'cartStatus'		=> 'OK',
				'cartPayment'		=> 'CB',
				'cartTransaction'	=> $apiIdTransaction,
				'cartCertificate'	=> $apiIdCertificate
			)
		));

		$myCmd = $this->apiLoad('business')->businessCartGet(array(
			'id_cart'	=> $apiIdCart,
			'is_cmd'	=> true
		));

		if($myCmd['id_cart'] > 0){
			$this->apiLoad('business')->businessCmdMail(array(
				'id_cart'		=> $myCmd['id_cart']
			));
		}

	}else{
		mail("bm@kappuccino.org", '[kodeine] : no succes : apiIdCart='.$apiIdCart.' '.time(), $apiOutput);
	}

	exit();

?>