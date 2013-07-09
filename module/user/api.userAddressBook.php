<?php

namespace Kodeine;

class userAddressBook extends appModule{

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userAddressBookCheck($id_user){

		$books = $this->userAddressBookGet(array('id_user' => $id_user), false);

		if(sizeof($books) == 0){
			$this->mysql->query("INSERT INTO k_useraddressbook (id_user, addressbookIsMain, addressbookIsDelivery, addressbookIsBilling, addressbookIsProtected) VALUES (".$id_user.", 1, 1, 1, 1)");
		}else{
			foreach($books as $book){
				if($book['addressbookIsMain']) 		$main 		= $book['id_addressbook'];
				if($book['addressbookIsDelivery']) 	$delivery 	= $book['id_addressbook'];
				if($book['addressbookIsBilling']) 	$billing 	= $book['id_addressbook'];
			}

			if($main == '') 			$sql[] = "UPDATE k_useraddressbook SET addressbookIsMain=1 		WHERE id_addressbook=".$books[0]['id_addressbook'];
			if($delivery == '') 		$sql[] = "UPDATE k_useraddressbook SET addressbookIsDelivery=1 	WHERE id_addressbook=".$books[0]['id_addressbook'];
			if($billing == '') 			$sql[] = "UPDATE k_useraddressbook SET addressbookIsBilling=1	WHERE id_addressbook=".$books[0]['id_addressbook'];
			if(sizeof($books) == '1')	$sql[] = "UPDATE k_useraddressbook SET addressbookIsProtected=1	WHERE id_addressbook=".$books[0]['id_addressbook'];

			if(sizeof($sql) > 0){
				foreach($sql as $q){
					$this->mysql->query($q);
				}
			}
		}
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userAddressBookGet($opt=array(), $check=true){

		if($opt['id_user'] == NULL) return array();
		if($check) $this->userAddressBookCheck($opt['id_user']);

		$cond[] = "id_user=".$opt['id_user'];

		if($opt['delivery'] != ''){
			$dbMode = 'dbOne';
			$cond[] = 'addressbookIsDelivery=1';
		}else
		if($opt['billing'] != ''){
			$dbMode = 'dbOne';
			$cond[] = 'addressbookIsBilling=1';
		}else
		if($opt['id_addressbook'] != ''){
			$dbMode = 'dbOne';
			$cond[] = 'id_addressbook='.$opt['id_addressbook'];
		}else{
			$dbMode = 'dbMulti';
		}

		if(sizeof($cond) > 0) $where = "WHERE ".implode(" AND ", $cond);

		$ab = $this->$dbMode("SELECT * FROM k_useraddressbook ".$where);
		if($opt['debug']) $this->pre($this->db_query, $this->db_error, $ab);

		return $ab;
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userAddressBookSet($opt=array()){

	    $id_user		= $opt['id_user'];
	    $id_addressbook	= $opt['id_addressbook'];
	    $def			= $opt['def'];

	    if(!$this->formValidation($def)) return false;

	    if($id_addressbook != NULL){
	        $q = $this->dbUpdate($def)." WHERE id_addressbook=".$id_addressbook;
	    }else{
	        $q = $this->dbInsert($def);
	    }

	    @$this->mysql->query($q);
	    if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	    if($this->db_error != NULL) return false;

	    $this->id_addressbook = ($id_addressbook > 0) ? $id_addressbook : $this->db_insert_id;

	    if($opt['is_delivery']) $this->userAddressBookDeliverySet($this->id_addressbook, $id_user);
	    if($opt['is_billing']) $this->userAddressBookBillingSet($this->id_addressbook, $id_user);

	    $this->hookAction('userAddressBookSet', $this->id_addressbook);

	    return true;
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userAddressBookDeliverySet($id_addressbook, $id_user){

	    if(intval($id_addressbook) == 0 || intval($id_user) == 0) return false;

	    $this->mysql->query("UPDATE k_useraddressbook SET addressbookIsProtected=0 WHERE addressbookIsDelivery = '1' AND id_user='".$id_user."'");
	    $this->mysql->query("UPDATE k_useraddressbook SET addressbookIsDelivery=0,addressbookIsMain=0 WHERE id_user='".$id_user."'");
	    $this->mysql->query("UPDATE k_useraddressbook SET addressbookIsDelivery=1,addressbookIsProtected=1,addressbookIsMain=1 WHERE id_addressbook='".$id_addressbook."'");

	    return true;
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userAddressBookBillingSet($id_addressbook, $id_user){

	    if(intval($id_addressbook) == 0 || intval($id_user) == 0) return false;

	    $this->mysql->query("UPDATE k_useraddressbook SET addressbookIsProtected=0 WHERE addressbookIsBilling = '1' AND id_user='".$id_user."'");
	    $this->mysql->query("UPDATE k_useraddressbook SET addressbookIsBilling=0 WHERE id_user='".$id_user."'");
	    $this->mysql->query("UPDATE k_useraddressbook SET addressbookIsBilling=1,addressbookIsProtected=1 WHERE id_addressbook='".$id_addressbook."'");

	    return true;
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userAddressBookDefine($id_user, $id_addressbook, $field, $value, $erase=true){

		if($erase){
			$this->mysql->query("UPDATE ".$this->tableUserAddressBook." SET ".$field."=0 WHERE id_user=".$id_user);
		}

		$this->mysql->query("UPDATE ".$this->tableUserAddressBook." SET ".$field."=".$value." WHERE id_user=".$id_user." AND id_addressbook=".$id_addressbook);
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public function userAddressBookFormat($data, $opt=array()){

	    $out = '';
		if($opt['name']){
			$out = $data['addressbookCivility'].' '.$data['addressbookFirstName'].' '.$data['addressbookLastName']."\n";
		}

		if($data['addressbookCompanyName']) 	$out.= $data['addressbookCompanyName']."\n";
		if($data['addressbookAddresse1']) 		$out.= $data['addressbookAddresse1']."\n";
		if($data['addressbookAddresse2']) 		$out.= $data['addressbookAddresse2']."\n";
		if($data['addressbookAddresse3']) 		$out.= $data['addressbookAddresse3']."\n";
		if($data['addressbookCityCode']) 		$out.= $data['addressbookCityCode'].' '.$data['addressbookCityName']."\n";
		if($data['addressbookStateName']) 		$out.= $data['addressbookStateName'].' ';
		if($data['addressbookCountryCode']) 	$out.= strtoupper($data['addressbookCountryCode']);

		if($opt['html']) $out = nl2br($out);

	    $ret = array('out'  => $out, 'data' => $data, 'opt' => $opt);
	    $ret  = $this->hookFilter('userAddressBookFormat', $ret);

		return $ret['out'];
	}


}