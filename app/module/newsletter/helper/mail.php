<?php
	$api = $app->apiLoad('newsletter');

	$_GET['value'] = urldecode($_GET['value']);

	if($_GET['force_link']){

		// Le mail qui doit etre ajoute a la LISTE	
		$toCopy = $app->dbOne("SELECT * FROM k_newslettermail WHERE mail='".$_GET['value']."' AND id_newslettermail != ".$_GET['id_newslettermail']);
		if($toCopy['id_newslettermail'] != NULL){
			$app->dbQuery("INSERT IGNORE INTO k_newsletterlistmail (id_newsletterlist, id_newslettermail) VALUES (".$_GET['id_newsletterlist'].", ".$toCopy['id_newslettermail'].")");
			#$app->pre($app->db_query, $app->db_error);
			
			// Supprimer le mail en erreur de la LISTE
			$toKill = $app->dbOne("SELECT * FROM k_newslettermail WHERE mail='".$_GET['error']."' AND id_newslettermail = ".$_GET['id_newslettermail']);
			if($toKill['id_newslettermail'] != NULL){
				$app->dbQuery("DELETE FROM k_newsletterlistmail WHERE id_newsletterlist = ".$_GET['id_newsletterlist']." AND id_newslettermail=".$toKill['id_newslettermail']);
			#	$app->pre($app->db_query, $app->db_error);
			}
		}

		$ret = array('flag' => 'doublon-cleaned');
		echo json_encode($ret);
		
		exit();
	}

	# Verifier si le mail qu'on demande n'est pas deja enregistre et que ce n'est pas celui courant
	$exists = $app->dbOne("SELECT 1 FROM k_newslettermail WHERE mail='".$_GET['value']."' AND id_newslettermail != ".$_GET['id_newslettermail']);
	
	if($exists[1]){
		$ret = array(
			'flag'	=> 'doublon',
			'mail'	=> $_GET['value'],
			'id'	=> $_GET['id_newslettermail']
		);
	}else{
		$flag = (!filter_var($_GET['value'], FILTER_VALIDATE_EMAIL)) ? 'ERROR' : 'VALID';
	
		@$app->dbQuery("UPDATE k_newslettermail SET mail='".$_GET['value']."', flag='".$flag."' WHERE id_newslettermail=".$_GET['id_newslettermail']);
		
		$ret = ($app->db_error == NULL)
			? array('success' => true)
			: array('success' => false);

		$ret['flag'] = $flag;
		$ret['mail'] = $_GET['value'];
		$ret['id']	 = $_GET['id_newslettermail'];
	}

	echo json_encode($ret);
          
?>