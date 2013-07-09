<?php

namespace Kodeine;

class userImport extends appModule{

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userImportCSV($file, $post=NULL){


		$headers 		= $post['headers'];
		$id_group		= $post['id_group'];
		$is_activate	= $post['activate'];
		$is_subscribed	= $post['userMailing'];
		$removeFirst	= $post['removeFirst'];
		$checkDoublon	= $post['checkDoublon'];
		$offset			= $post['offset'];
		$length			= $post['length'];
		$content 		= file_get_contents($file);


		# Trouver les lignes du fichiers
		$sepLigne	= "\n";
		$lignes 	= explode($sepLigne, $content);
		if(sizeof($lignes) < 2){
			$sepLigne 	= "\r";
			$lignes 	= explode($sepLigne, $content);
		}
		if(sizeof($lignes) < 2) return USER_IMPORT_ERRORLINE;



		# Trouver les colonnes du fichier
		$sepColonne = "\t";
		$colonnes 	= explode($sepColonne, $lignes[0]);
		$colonnes	= array_map('trim', $colonnes);

		if(sizeof($colonnes) == 1){
			$sepColonne = ';';
			$colonnes 	= explode($sepColonne,  $lignes[0]);
		}
		if(sizeof($colonnes) == 1){
			$sepColonne	= ',';
			$colonnes 	= explode($sepColonne,  $lignes[0]);
		}
	#	if(sizeof($colonnes) == 1) 		return USER_IMPORT_ERRORCOLUMN;
	#	if(in_array(NULL, $colonnes)) 	return USER_IMPORT_EMPTYCELL;


		# Trouver s'il existe un caractere de protection de donnee
		$same			= 0;
		$testColonnes	= explode($sepColonne, $lignes[1]);
		foreach($testColonnes as $testColonne){
			if(substr($testColonne, 0, 1) == substr($testColonne, -1)) $same++;
		}
		if($same == sizeof($testColonnes)){
			foreach($lignes as $iLigne => $ligne){
				$tmpLigne 		 = array();
				foreach(explode($sepColonne, $ligne) as $iColonne => $colonne){
					$tmpLigne[]  = substr($colonne, 1, -1);
				}
				$lignes[$iLigne] = implode($sepColonne, $tmpLigne);
			}
		}


		# Construir la réponse
		$build = array(
			'lignes' 		=> $lignes,
			'sepLigne' 		=> $sepLigne,
			'colonnes'		=> $colonnes,
			'sepColonne'	=> $sepColonne
		);


		# On s'occupe des headers du fichier
		if(sizeof($headers) == 0) return array('needHeaders', $build);
		foreach($headers as $index => $header){
			if($header == NULL){
			//	$labels[] = '';
			}else
			if(ereg("[0-9]{1,}", $header)){
				$labels[$index] = $header;
			}else{
				$system[$header] = $index;
			}
		}

	#	if(sizeof($labels) == 0 || sizeof($system) == 0) return USER_IMPORT_HEADERS;
		if(sizeof($system) == 0) return USER_IMPORT_HEADERS;

		#$this->pre($headers, $system, $labels);
		if($removeFirst) unset($lignes[0]);

		foreach($lignes as $nLigne => $ligne){
			$myUser = array();
			$myCol	= explode($sepColonne, $ligne);

			foreach($system as $field => $nColonne){
				$myUser[$field] = $myCol[$nColonne];
			}

			if(sizeof($labels) > 0){
				foreach($labels as $nColonne => $id_field){
					if($myCol[$nColonne] != NULL) $myUser['id_field'][$id_field] = $myCol[$nColonne];
				}
			}

			$users[] = $myUser;
		}

		if($id_group == NULL) return array('needID', $build);

		foreach($users as $index => $user){
			if(!isset($users[$index]['id_group']))		$users[$index]['id_group'] 		= $id_group;
			if(!isset($users[$index]['activate']))		$users[$index]['activate'] 		= $is_activate;
			if(!isset($users[$index]['userMailing']))	$users[$index]['userMailing']  	= $is_subscribed;
		}

		$usersInDb = array();
		foreach($this->mysql->multi("SELECT id_user, userMail FROM k_user WHERE id_group = ".$id_group) as $userInDb){
			$usersInDb[$userInDb['id_user']] = $userInDb['userMail'];
		}

		$doublon = array();
		$errors	 = array();
		$done	 = array();
		$todo	 = sizeof($users);

		if($offset >= 0 && $length > 0) $users = array_splice($users, $offset, $length);

		foreach($users as $user){
			$ajoutable = true;

			if($checkDoublon && in_array($user['user'], $usersInDb)) 						$ajoutable = false; // Tri sur l'email
			if($user['id_user'] != NULL && array_key_exists($user['id_user'], $usersInDb))	$ajoutable = false; // Tri sur l'ID

			if($ajoutable){
				unset($def);

				if($user['userMail'] != NULL) $def['k_user']['userMail']	= array('value' => $user['userMail']);
				if($user['id_group'] != NULL) $def['k_user']['id_group']	= array('value' => $user['id_group']);

				$success = $this->userSet(array(
					'debug'			=> false,
					'def'			=> $def,
					'field'			=> $user['id_field']
				));

				if(!$success){
					$errors[] = $user;
				}else{
					$done[]   = $user;
				}

			}else{
				$doublon[] = $user;
			}
		}

		return array('imported', array('todo' => $todo, 'done' => $done, 'error' => $errors, 'doublon' => $doublon));
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userImportAddressBookCSV($file, $post=NULL){

	    $headers        = $post['headers'];
	    $id_group       = $post['id_group'];
	    $is_activate    = $post['activate'];
	    $is_subscribed  = $post['userMailing'];
	    $removeFirst    = $post['removeFirst'];
	    $checkDoublon   = $post['checkDoublon'];
	    $sepColonne     = $post['sepColonne'];
	    $deliveryDefault    = $post['deliveryDefault'];
	    $billingDefault     = $post['billingDefault'];
	    $offset         = $post['offset'];
	    $length         = $post['length'];
	    $content        = file_get_contents($file);

	    # Trouver les lignes du fichiers
	    $sepLigne   = "\n";
	    $lignes     = explode($sepLigne, $content);
	    if(sizeof($lignes) < 2){
	        $sepLigne   = "\r";
	        $lignes     = explode($sepLigne, $content);
	    }
	    if(sizeof($lignes) < 2) return USER_IMPORT_ERRORLINE;

	    # Trouver les colonnes du fichier
	    if($sepColonne == '') {
		    $sepColonne = "\t";
		    $colonnes   = explode($sepColonne, $lignes[0]);
		    $colonnes   = array_map('trim', $colonnes);

		    if(sizeof($colonnes) == 1){
		        $sepColonne = ';';
		        $colonnes   = explode($sepColonne,  $lignes[0]);
		    }
		    if(sizeof($colonnes) == 1){
		        $sepColonne = ',';
		        $colonnes   = explode($sepColonne,  $lignes[0]);
		    }
	    }else {
	        $colonnes   = explode($sepColonne,  $lignes[0]);
	    }
	   //if(sizeof($colonnes) == 1)      return USER_IMPORT_ERRORCOLUMN;
	   //if(in_array(NULL, $colonnes))   return USER_IMPORT_EMPTYCELL;


	    # Trouver s'il existe un caractere de protection de donnee
	    $same           = 0;
	    $testColonnes   = explode($sepColonne, $lignes[1]);
	    foreach($testColonnes as $testColonne){
	        if(substr($testColonne, 0, 1) == substr($testColonne, -1)) $same++;
	    }
	    if($same == sizeof($testColonnes)){
	        foreach($lignes as $iLigne => $ligne){
	            $tmpLigne        = array();
	            foreach(explode($sepColonne, $ligne) as $iColonne => $colonne){
	                $tmpLigne[]  = substr($colonne, 1, -1);
	            }
	            $lignes[$iLigne] = implode($sepColonne, $tmpLigne);
	        }
	    }


	    # Construir la réponse
	    $build = array(
	        'lignes'        => $lignes,
	        'sepLigne'      => $sepLigne,
	        'colonnes'      => $colonnes,
	        'sepColonne'    => $sepColonne
	    );

	    # On s'occupe des headers du fichier
	    if(sizeof($headers) == 0) return array('needHeaders', $build);
	    foreach($headers as $index => $header){
	        //echo substr($header,0,6);
	        if($header == NULL){
	        //  $labels[] = '';
	        }else
	        if(substr($header,0,6) == 'label-'){
	            $labels[$index] = substr($header,6,strlen($header));
	        }else{
	            $system[$header] = $index;
	        }
	    }

	    //die($this->pre($headers, $system, $labels));


	#   if(sizeof($labels) == 0 || sizeof($system) == 0) return USER_IMPORT_HEADERS;
	    //if(sizeof($system) == 0) return USER_IMPORT_HEADERS;

	    #$this->pre($headers, $system, $labels);
	    if($removeFirst) unset($lignes[0]);

	    foreach($lignes as $nLigne => $ligne){
	        $myUser = array();
	        $myCol  = explode($sepColonne, $ligne);

	        if(sizeof($system) > 0)foreach($system as $field => $nColonne){
	            $myUser[$field] = $myCol[$nColonne];
	        }

	        if(sizeof($labels) > 0){
	            foreach($labels as $nColonne => $id_field){
	                if($myCol[$nColonne] != NULL) $myUser['id_field'][$id_field] = $myCol[$nColonne];
	            }
	        }

	        $users[] = $myUser;
	    }

	    //die($this->pre($users));
	    if($id_group == NULL) return array('needID', $build);

	    foreach($users as $index => $user){
	        if(!isset($users[$index]['id_group']))      $users[$index]['id_group']      = $id_group;
	        if(!isset($users[$index]['activate']))      $users[$index]['activate']      = $is_activate;
	        if(!isset($users[$index]['userMailing']))   $users[$index]['userMailing']   = $is_subscribed;
	    }

	    $usersInDb = array();
	    foreach($this->mysql->multi("SELECT id_user, userMail FROM k_user WHERE is_deleted=0 AND id_group = ".$id_group) as $userInDb){
	        $usersInDb[$userInDb['id_user']] = $userInDb['userMail'];
	    }

	    $doublon = array();
	    $errors  = array();
	    $done    = array();
	    $todo    = sizeof($users);

	    if($offset >= 0 && $length > 0) $users = array_splice($users, $offset, $length);

	    foreach($users as $user){
	        $create_user = false;
			$id_user = $user['id_user'];
	        if($user['id_user'] == '' && $user['userMail'] == '')  $create_user = true;
	        if($user['id_user'] == '' && $user['userMail'] != ''){
	            if(in_array($user['userMail'], $usersInDb)){
	                $recup_id_user = array_keys($usersInDb, $user['userMail']);
	                $id_user = $recup_id_user[0];
	            }else
	                $create_user = true;
	        }

	        if($create_user){
	            unset($def);
	            $def['k_user']['userMail']  = array('value' => $user['userMail']);
	            $def['k_user']['id_group']  = array('value' => $user['id_group']);
	            $def['k_user']['is_active']  = array('value' => $is_activate);
	            //$q = $this->dbInsert($def);
	            //@$this->mysql->query($q);
				$this->userSet(array(
	                'debug'         => false,
	                'def'           => $def
	            ));

	            //$id_user = $this->db_insert_id;
	            $id_user = $this->id_user;
	        }


	        if($id_user > 0){

	            unset($def);
	            $fields = array();

	            $fields['id_user'] = array('value' => $id_user, 'query' => 1);
	            foreach($user['id_field'] as $k=>$v){
	                $fields[$k] = array('value' => $v, 'query' => 1);
	            }

	            $def['k_useraddressbook'] = $fields;
				if($deliveryDefault == 1 || $deliveryDefault == 1) {
					$this->mysql->query("UPDATE k_useraddressbook SET addressbookIsMain=0,addressbookIsProtected=0 WHERE id_user='".$id_user."'");
					$def['k_useraddressbook']['addressbookIsMain'] 		= array('value' => '1', 'query' => 1);
					$def['k_useraddressbook']['addressbookIsProtected'] 	= array('value' => '1', 'query' => 1);
				}
				if($deliveryDefault == 1) {
					$def['k_useraddressbook']['addressbookIsDelivery'] 	= array('value' => '1', 'query' => 1);
					$this->mysql->query("UPDATE k_useraddressbook SET addressbookIsDelivery=0 WHERE id_user='".$id_user."'");
				}
				if($billingDefault == 1) {
					$def['k_useraddressbook']['addressbookIsBilling'] = array('value' => '1', 'query' => 1);
					$this->mysql->query("UPDATE k_useraddressbook SET addressbookIsBilling=0 WHERE id_user='".$id_user."'");
				}

	            unset($defuser);
	            if($user['userMail'] != NULL) $defuser['k_user']['userMail']    = array('value' => $user['userMail']);
	            if($user['id_group'] != NULL) $defuser['k_user']['id_group']    = array('value' => $user['id_group']);

	            $success = $this->userSet(array(
	                'debug'         => false,
	                'id_user'       => $id_user,
	                'def'           => $defuser,
	                'addressbook'   => $def
	            ));
	            if(!$success){
	                $errors[] = $user;
	            }else{
	                $done[]   = $user;
	            }

	        }else{
	            $doublon[] = $user;
	        }
	    }

	    return array('imported', array('todo' => $todo, 'done' => $done, 'error' => $errors, 'doublon' => $doublon));
	}

}