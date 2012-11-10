<?php

	if(intval($this->user['id_user']) == 0){
		header("Location: login");
		exit();
	}

	# Mise a jour du carnet d'adresse
	if($_POST['action']){
		$do = true;

		$def['k_useraddressbook'] = array(
			'id_user'						=> array('value' => $this->user['id_user'],					'query' => 1),
			'addressbookTitle'				=> array('value' => $_POST['addressbookTitle'],				'query' => 1, 	'check' => '.'),
			'addressbookLastName'			=> array('value' => $_POST['addressbookLastName'],			'query' => 1, 	'check' => '.'),
			'addressbookFirstName'			=> array('value' => $_POST['addressbookFirstName'],			'query' => 1, 	'check' => '.'),
			'addressbookEmail'				=> array('value' => $_POST['addressbookEmail'],				'query' => 1),
			'addressbookCompanyName'		=> array('value' => $_POST['addressbookCompanyName'],		'query' => 1),
			'addressbookCompanyFonction'	=> array('value' => $_POST['addressbookCompanyFunction'],	'query' => 1),
			'addressbookAddresse1'			=> array('value' => $_POST['addressbookAddresse1'],			'query' => 1, 	'check' => '.'),
			'addressbookAddresse2'			=> array('value' => $_POST['addressbookAddresse2'],			'query' => 1),
			'addressbookAddresse3'			=> array('value' => $_POST['addressbookAddresse3'],			'query' => 1),
			'addressbookCityCode'			=> array('value' => $_POST['addressbookCityCode'],			'query' => 1, 	'check' => '.'),
			'addressbookCityName'			=> array('value' => $_POST['addressbookCityName'],			'query' => 1, 	'check' => '.'),
			'addressbookCountryCode'		=> array('value' => $_POST['addressbookCountryCode'],		'query' => 1),
			'addressbookStateName'			=> array('value' => $_POST['addressbookStateName'],			'query' => 1),
			'addressbookPhone1'				=> array('value' => $_POST['addressbookPhone1'],			'query' => 1),
			'addressbookPhone2'				=> array('value' => $_POST['addressbookPhone2'],			'query' => 1)
		);

		if(!$this->formValidation($def)) $do = false;

		if($do){
			$result = $this->apiLoad('user')->userAddressBookSet(array(
				'id_user' 			=> $this->user['id_user'],
				'id_addressbook'	=> $_POST['id_addressbook'],
				'def'				=> $def
			));

			if($result){
				$ADDRESSBOOK_UPDATED = true;
			}else{
				$ADDRESSBOOK_ERROR = true;
			}
		}else{
			$ADDRESSBOOK_FILLED = true;
		}
	}


	# Recuperer le carnet d'adresse
	if($_REQUEST['id_addressbook'] != NULL){
		$myAddressBook = $this->apiLoad('user')->userAddressBookGet(array(
			'id_addressbook' 	=> $_REQUEST['id_addressbook'],
			'id_user'			=> $this->user['id_user']
		));	
	}
?>