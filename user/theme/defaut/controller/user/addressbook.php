<?php

	if(intval($this->user['id_user']) == 0){
		header("Location: login");
		exit();
	}

	# Mettre a jour les valeur de livraion et de facturation
	#
	if($_POST['todo'] == 'updateBillingAndDelivery'){
		$books 		= $this->apiLoad('user')->userAddressBookGet(array(
			'id_user' 	=> $this->user['id_user']
		));

		$delivery 	= $_POST['addressbookIsDelivery'];
		if($delivery == NULL) $delivery = $books[0]['id_addressbook'];

		$billing  	= $_POST['addressbookIsBilling'];
		if($billing == NULL) $billing = $books[0]['id_addressbook'];

		$this->dbQuery("UPDATE k_useraddressbook SET addressbookIsDelivery=0, addressbookIsBilling=0 WHERE id_user='".$this->user['id_user']."'");
		$this->dbQuery("UPDATE k_useraddressbook SET addressbookIsDelivery=1  WHERE id_addressbook='".$delivery."'");
		$this->dbQuery("UPDATE k_useraddressbook SET addressbookIsBilling=1   WHERE id_addressbook='".$billing."'");
	}else

	# Suppression d'un carnet d'adresse
	#
	if($_GET['remove'] != NULL){

		$me = $this->apiLoad('user')->userAddressBookGet(array(
			'id_addressbook'	=> $_GET['remove'],
			'id_user'			=> $this->user['id_user'],
			'debug'				=> false
		));

		if($me['addressbookIsProtected'] == 0){
			$this->dbQuery("DELETE FROM k_useraddressbook WHERE id_addressbook='".$_GET['remove']."' AND id_user=".$this->user['id_user']);
		}
	}


	# Recuperer les carnets d'addresse
	$myAddressBook = $this->apiLoad('user')->userAddressBookGet(array(
		'id_user'	=> $this->user['id_user'],
		'debug'		=> false
	));

?>