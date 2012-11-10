<?php

	# Implémentation PHP du RFC2104 hmac sha1 ---
	@require_once("CMCIC_HMAC.inc.php");
	if(!function_exists('CMCIC_hmac'))  die('cant require hmac function.');

	// ----------------------------------------------------------------------------
	// function CMCIC_getMyTpe
	//
	// IN: Code société, Code langue
	//
	// OUT: Paramètres du Tpe
	//
	// Description: Rechercher le numéro de TPE, la 2nde partie cryptée de clef
	//              et autres infos de configuration Marchand
	// ----------------------------------------------------------------------------
	function CMCIC_getMyTpe($soc="mysoc",$lang=""){
	     @require("MyTpeCMCIC.inc.php");
	     if(!is_array($MyTpe)) die('cant require Tpe config.');
	     return $MyTpe;
	}

	// ----------------------------------------------------------------------------
	// function TesterHmac
	//
	// IN: Paramètres du Tpe, hamps du formulaire
	//
	// OUT: Résultat vérification
	//
	// description: Vérifier le MAC et préparer la Reponse
	// ----------------------------------------------------------------------------
	function TesterHmac($CMCIC_Tpe, $CMCIC_bruteVars ){

	   @$php2_fields = sprintf(CMCIC_PHP2_FIELDS, $CMCIC_bruteVars['retourPLUS'], 
	                                              $CMCIC_Tpe["tpe"], 
	                                              $CMCIC_bruteVars["date"],
	                                              $CMCIC_bruteVars['montant'],
	                                              $CMCIC_bruteVars['reference'],
	                                              $CMCIC_bruteVars['texte-libre'],
	                                               CMCIC_VERSION,
	                                              $CMCIC_bruteVars['code-retour']);

	
	    if ( strtolower($CMCIC_bruteVars['MAC'] ) == CMCIC_hmac($CMCIC_Tpe, $php2_fields) ):
	        $result  = $CMCIC_bruteVars['code-retour'].$CMCIC_bruteVars['retourPLUS'];
	        $receipt = CMCIC_PHP2_MACOK;
	    else: 
	        $result  = 'None';
	        $receipt = CMCIC_PHP2_MACNOTOK.$php2_fields;
	    endif;
	
	    $mnt_lth = strlen($CMCIC_bruteVars['montant'] ) - 3;
	    if ($mnt_lth > 0):
	        $currency = substr($CMCIC_bruteVars['montant'], $mnt_lth, 3 );
	        $amount   = substr($CMCIC_bruteVars['montant'], 0, $mnt_lth );
	    else:
	        $currency = "";
	        $amount   = $CMCIC_bruteVars['montant'];
	    endif;
	
	    return array( "resultatVerifie" => $result ,
	                  "accuseReception" => $receipt ,
	                  "tpe"             => $CMCIC_bruteVars['TPE'],
	                  "reference"       => $CMCIC_bruteVars['reference'],
	                  "texteLibre"      => $CMCIC_bruteVars['texte-libre'],
	                  "devise"          => $currency,
	                  "montant"         => $amount);
	}


	# Begin Main : Recevoir les variables postées par le serveur bancaire

	$CMCIC_reqMethod  = $HTTP_SERVER_VARS["REQUEST_METHOD"];
	if (($CMCIC_reqMethod == "GET") or ($CMCIC_reqMethod == "POST")) {
	    $wCMCIC_bruteVars = "HTTP_".$CMCIC_reqMethod."_VARS";
	    $CMCIC_bruteVars  = ${$wCMCIC_bruteVars};
	}else{
    	die ('Invalid REQUEST_METHOD (not GET, not POST).');
    }

	@$isVariableEmpty  = $CMCIC_bruteVars['TPE'];

	// empty variables ?
	if (!($isVariableEmpty > " ")){

	    // Il est recommandé de ne pas écrire de scripts qui exige de paramétrer
	    // register_globals à on. Utiliser les variables du formulaire comme
	    // globales peut amener des problèmes de sécurité si votre script n'est
	    // pas très bien conçu.
	
	    // var_dump($CMCIC_bruteVars);
	    echo "\r\nTrying PHP<=3 old style ! "."\r\n";
	
	    settype($CMCIC_bruteVars , "array"); 
	
	    @$CMCIC_bruteVars['MAC']         = $MAC;
	    @$CMCIC_bruteVars['TPE']         = $TPE;
	    @$CMCIC_bruteVars['date']        = $date;
	    @$CMCIC_bruteVars['montant']     = $montant;
	    @$CMCIC_bruteVars['reference']   = $reference;
	    $URL_texte_libre                 = "texte-libre";
	    @$CMCIC_bruteVars['texte-libre'] = $$URL_texte_libre;
	    $URL_code_retour                 = "code-retour";
	    @$CMCIC_bruteVars['code-retour'] = $$URL_code_retour;
	    @$CMCIC_bruteVars['retourPLUS']   = $retourPLUS;
	
	    // var_dump($CMCIC_bruteVars);
	    echo "\r\n Is it Better ? "."\r\n";
	}

	// variables initiales TPE
	@$CMCIC_Tpe = CMCIC_getMyTpe();
	
	// Test d'authentification
	@$CMCIC_authVars   = TesterHmac($CMCIC_Tpe, $CMCIC_bruteVars );
	
	@$Verified_Result  = $CMCIC_authVars['resultatVerifie'];
	
	// <<<--- code <<<--- 
	// (Cas / Case : "None" , "Annulation" , "Payetest", "Paiement")
	//-----------------------------------------------------------------------------
	// Vider ces variables peut vous aider à voir ce qui est à coder
	//-----------------------------------------------------------------------------
	// var_dump($Verified_Result_Array);
	// var_dump($CMCIC_bruteVars);
	// var_dump($CMCIC_authVars);
	
	//-----------------------------------------------------------------------------
	// Send receipt to CMCIC server
	// Envoyer un A/R au serveur bancaire
	//-----------------------------------------------------------------------------
	@printf (CMCIC_PHP2_RECEIPT, $CMCIC_authVars['accuseReception']);
?>