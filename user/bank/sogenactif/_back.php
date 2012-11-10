<?php

	// Recuperation de la variable cryptee DATA
	$message = "message=".$_POST['DATA'];

	// Initialisation du chemin du fichier pathfile (a modifier)
	// $pathfile="pathfile=/home/repertoire/pathfile"
	$pathfile = "pathfile=".dirname(__FILE__)."/pathfile";

	//Initialisation du chemin de l'executable response (? modifier)
	// $path_bin = "/home/repertoire/bin/response"
	$path_bin = dirname(__FILE__)."/response";

	// Appel du binaire response
	$result = exec("$path_bin $pathfile $message");

	//	Sortie de la fonction : !code!error!v1!v2!v3!...!v29
	// - code=0		: la fonction retourne les donnŽes de la transaction dans les variables v1, v2, ...
	// - code=-1 	: La fonction retourne un message d'erreur dans la variable error
	
	$tableau 				= explode ("!", $result);
	$code 					= $tableau[1];
	$error 					= $tableau[2];
	$merchant_id 			= $tableau[3];
	$merchant_country 		= $tableau[4];
	$amount 				= $tableau[5];
	$transaction_id 		= $tableau[6];
	$payment_means 			= $tableau[7];
	$transmission_date		= $tableau[8];
	$payment_time 			= $tableau[9];
	$payment_date 			= $tableau[10];
	$response_code 			= $tableau[11];
	$payment_certificate 	= $tableau[12];
	$authorisation_id 		= $tableau[13];
	$currency_code 			= $tableau[14];
	$card_number 			= $tableau[15];
	$cvv_flag 				= $tableau[16];
	$cvv_response_code		= $tableau[17];
	$bank_response_code 	= $tableau[18];
	$complementary_code	 	= $tableau[19];
	$complementary_info		= $tableau[20];
	$return_context 		= $tableau[21];
	$caddie 				= $tableau[22];
	$receipt_complement 	= $tableau[23];
	$merchant_language 		= $tableau[24];
	$language 				= $tableau[25];
	$customer_id 			= $tableau[26];
	$order_id 				= $tableau[27];
	$customer_email 		= $tableau[28];
	$customer_ip_address	= $tableau[29];
	$capture_day 			= $tableau[30];
	$capture_mode 			= $tableau[31];
	$data 					= $tableau[32];

	$apiRaw		= $tableau;
	$apiIdCart	= $caddie;
	$apiIdUser	= $customer_id;

	# Analyse du code retour
	if(($code == "") && ($error == "")){
		$apiSuccess = false;
		$apiOutput 	= "erreur appel request, executable request non trouve : ".$path_bin;
 	}

	# Erreur, affiche le message d'erreur
	else
	if($code != 0){
		$apiSuccess = false;
		$apiOutput 	= $error;
	# OK, affiche le formulaire HTML (affichage du mode DEBUG si activé)
	}else{
		$apiSuccess = true;
		$apiOutput 	= $message;
	}

?>