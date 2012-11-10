<?php

	# Exemple :	numŽro de carte		code rŽponse
	# 4974934125497800		00	(paiement acceptŽ)	Crypto 600
	# 4972187615205			05	(paiement refusŽ)

	# Affectation des parametres obligatoires
	$parm	 = "merchant_id=014213245611111"; // DEMO
	$parm	.= " currency_code=978";
	$parm	.= " merchant_country=fr";
	$parm	.= " amount=".str_replace('.', NULL, $myCart['cartTotalFinal']);

	# Initialisation du chemin de l'executable request (ˆ modifier)
	# Unix    : $path_bin = "/home/repertoire/bin/request";
	$path_bin = dirname(__FILE__)."/request";

	# Configuration des URL de retour
	if(!isset($urlCancel)){
		$urlCancel	= "http://".$_SERVER['HTTP_HOST'].$this->kTalk('/{l}/content/cancel.html');
	}
	if(!isset($urlNormal)){
		$urlNormal	= "http://".$_SERVER['HTTP_HOST'].$this->kTalk('/{l}/content/back.html');
	}
	if(!isset($urlAuto)){
		$urlAuto	= "http://".$_SERVER['HTTP_HOST'].$this->kTalk('/{l}/content/auto.html');
	}
	
	/************************************************************************************/

	$parm	.= " pathfile=".dirname(__FILE__)."/pathfile";
	$parm	.= " normal_return_url=".$urlNormal;					// URL de retour si paiement OK
	$parm	.= " cancel_return_url=".$urlCancel;					// URL d'annulation CLIENT ou BANQUE
	$parm	.= " automatic_response_url=".$urlAuto;					// URL de rŽponse automatique en fin de transaction
	$parm	.= " language=fr";										// Code de la lanque
	$parm	.= " payment_means=MASTERCARD,2,CB,2,VISA,2";			// Ordre d'affichage des cartes
	$parm	.= " header_flag=no";
	$parm	.= " capture_day=0";									// Rend la paiment immŽdiat
	$parm	.= " capture_mode=";									// Rend la paiment immŽdiat
	$parm	.= " bgcolor=FFFFFF";
#	$parm	.= " block_align=";
#	$parm	.= " block_order=";
#	$parm	.= " textcolor=";
#	$parm	.= " receipt_complement=";								// Texte de personnalisation au dessus du ticket client (HTML)
	$parm	.= " caddie=".$myCart['id_cart'];						// Information sur l'utilisateur
	$parm	.= " customer_id=".$myCart['id_user'];					// Suite
#	$parm	.= " customer_email=";									// L'email de l'acheteur
	$parm	.= " customer_ip_address=".$_SERVER['REMOTE_ADDR'];		// L'IP de l'acheteur
#	$parm	.= " data=";
#	$parm	.= " return_context=";
	$parm	.= " target=_blank";									// Ouverture de la banque dans une nouvelle page
#	$parm	.= " order_id=";

	# Les valeurs suivantes ne sont utilisables qu'en prŽ-production
	# Elles nŽcessitent l'installation de vos fichiers sur le serveur de paiement
#	$parm	.= " normal_return_logo=";
#	$parm	.= " cancel_return_logo=";
#	$parm	.= " submit_logo=";
#	$parm	.= " logo_id=";
#	$parm	.= " logo_id2=";
#	$parm	.= " advert=";
#	$parm	.= " background_id=";
#	$parm	.= " templatefile=";

	# Appel du binaire request
	$result = exec($path_bin." ".$parm, $SHout, $SHerr);

	# Sortie de la fonction : $result=!code!error!buffer!
	# code  0 : la fonction gŽnre une page html contenue dans la variable buffer
	# code -1 : La fonction retourne un message d'erreur dans la variable error
	# On separe les differents champs et on les met dans une variable tableau
	$tableau	= explode ("!", $result);

	# RŽcupŽration des paramtres
	$code 		= $tableau[1];
	$error 		= $tableau[2];
	$message	= $tableau[3];
	
#	$this->pre("$path_bin $parm", "Sortie SHELL", $SHout, $SHerr);
#	$this->pre($error);
#	$this->pre($code);
#	$this->pre($message);

	# Analyse du code retour
	if(($code == "") && ($error == "")){
		$apiSuccess = false;
		$apiOutput 	= "erreur appel request<br />executable request non trouve ".$path_bin;
 	}else

	# Erreur, affiche le message d'erreur
	if($code != 0){
		$apiSuccess = false;
		$apiOutput 	= $error;
	}

	# OK, affiche le formulaire HTML (affichage du mode DEBUG si activŽ)
	else{
		$apiSuccess = true;
		$apiOutput 	= $message;
	}
?>