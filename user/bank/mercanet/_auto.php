<?php

	// Recuperation de la variable cryptee DATA
	$message = "message=".$_POST['DATA'];

	// Initialisation du chemin du fichier pathfile (a modifier)
	// $pathfile="pathfile=/home/repertoire/pathfile"
	$pathfile = "pathfile=".dirname(__FILE__)."/pathfile.parmcom.mercanet";

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

	// Initialisation du chemin du fichier de log (? modifier)
	// $logfile="/home/repertoire/log/logfile.txt";
	$logfile 	= dirname(__FILE__)."/logfile.txt";
	$fp			= fopen($logfile, "a");


	if(($code == "" ) && ($error == "")){
		$apiSuccess = false;
		$apiOutput	= "executable response non trouve : ".$path_bin;

 	 	fwrite($fp, "erreur appel response\n");
	}else
	//	Erreur, sauvegarde le message d'erreur
	if ($code != 0 ){
		$apiSuccess = false;
		$apiOutput	= "error call";

		fwrite($fp, " API call error.\nError message : $error\n");

	}else{
	// OK, Sauvegarde des champs de la reponse
		fwrite($fp, "code : $code\n");
		fwrite($fp, "merchant_id : $merchant_id\n");
		fwrite($fp, "merchant_country : $merchant_country\n");
		fwrite($fp, "amount : $amount\n");
		fwrite($fp, "transaction_id : $transaction_id\n");
		fwrite($fp, "transmission_date: $transmission_date\n");
		fwrite($fp, "payment_means: $payment_means\n");
		fwrite($fp, "payment_time : $payment_time\n");
		fwrite($fp, "payment_date : $payment_date\n");
		fwrite($fp, "response_code : $response_code\n");
		fwrite($fp, "payment_certificate : $payment_certificate\n");
		fwrite($fp, "authorisation_id : $authorisation_id\n");
		fwrite($fp, "currency_code : $currency_code\n");
		fwrite($fp, "card_number : $card_number\n");
		fwrite($fp, "cvv_flag: $cvv_flag\n");
		fwrite($fp, "cvv_response_code: $cvv_response_code\n");
		fwrite($fp, "bank_response_code: $bank_response_code\n");
		fwrite($fp, "complementary_code: $complementary_code\n");
		fwrite($fp, "complementary_info: $complementary_info\n");
		fwrite($fp, "return_context: $return_context\n");
		fwrite($fp, "caddie : $caddie\n");
		fwrite($fp, "receipt_complement: $receipt_complement\n");
		fwrite($fp, "merchant_language: $merchant_language\n");
		fwrite($fp, "language: $language\n");
		fwrite($fp, "customer_id: $customer_id\n");
		fwrite($fp, "order_id: $order_id\n");
		fwrite($fp, "customer_email: $customer_email\n");
		fwrite($fp, "customer_ip_address: $customer_ip_address\n");
		fwrite($fp, "capture_day: $capture_day\n");
		fwrite($fp, "capture_mode: $capture_mode\n");
		fwrite($fp, "data: $data\n");
		fwrite($fp, "-------------------------------------------\n");
		
	
		// Commande OK
		if($response_code == '00'){
			$apiSuccess 		= true;
			$apiOutput			= "OK";
			$apiIdTransaction 	= $transaction_id;
			$apiIdCertificate	= $payment_certificate;
		}else{
		// Commande NOT OK
			$apiSuccess 		= false;
			$apiOutput 			= "refus de la banque";
		}
	}

	fclose($fp);
?>