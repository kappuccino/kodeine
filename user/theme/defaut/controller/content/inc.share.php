<?php


	# On fixe les adresses BILLLING et DELIVERY
	#
	if($this->user['id_user'] > 0){
		$billing 	= $this->apiLoad('user')->userAddressBookGet(array('id_user' => $this->user['id_user'], 'billing'  => true));
		$delivery 	= $this->apiLoad('user')->userAddressBookGet(array('id_user' => $this->user['id_user'], 'delivery' => true));
	
		$adBilling	= $this->apiLoad('user')->userAddressBookFormat($billing);
		$adDelivery	= $this->apiLoad('user')->userAddressBookFormat($delivery);

		$def['k_businesscart'] = array(
			'id_delivery'			=> array('value' => $delivery['id_addressbook']),
			'cartDeliveryName'		=> array('value' => $delivery['addressbookFirstName'].' '.$delivery['addressbookLastName']),
			'cartDeliveryAddress'	=> array('value' => $adDelivery),

			'id_billing'			=> array('value' => $billing['id_addressbook']),
			'cartBillingName'		=> array('value' => $billing['addressbookFirstName'].' '.$billing['addressbookLastName']),
			'cartBillingAddress'	=> array('value' => $adBilling)
		);
		$this->dbQuery($this->dbUpdate($def)." WHERE id_cart=".$_SESSION['id_cart']);
	}


	# On fixe les prix (par necessite);
	#
	$this->apiLoad('business')->businessCartPrice($_SESSION['id_cart']);

?>