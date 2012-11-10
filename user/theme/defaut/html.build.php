<?php

	$SHOW_404			= false;
	$controller			= '/{T}/controller/{m}/{F}';
	$view				= '/{T}/view/{m}/{F}';
	$me					= array();

	include(dirname(__FILE__).'/helper/build.php');

	/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */

	// Recuperer le USER courant
	if($this->user['id_user'] > 0){
		$me = $this->apiLoad('user')->userGet(array(
			'id_user' => $this->user['id_user']
		));
	}

	// Rewriter les URL si besoin
	if($this->kodeine['get']['urlFile'] != ''){
		if(!file_exists($this->kTalk('{R}'.$controller)) && !file_exists($this->kTalk('{R}'.$view))){
			include(dirname(__FILE__).'/helper/url.php');
		}
	}

	// configGet (bootExt) - decommenter si vous avez besoin des bootExt
	#$this->kodeine['bootExt'] = $this->configGet('bootExt');

	// Je demande le controller du theme (CONTROLLER)
	if(file_exists($this->kTalk('{R}'.$controller))){
		include($this->kTalk('{R}'.$controller));

		// Je demande la vue si elle existe
		if(file_exists($this->kTalk('{R}'.$view))) include($this->kTalk('{R}'.$view));
	}else

	// Je demande la vue si elle existe
	if(file_exists($this->kTalk('{R}'.$view))){
		include($this->kTalk('{R}'.$view));
	}

	// Afficher une 404 si non
	else{
		$SHOW_404 = true;
	}

	// Si un fichier inclus veut un 404 
	if($SHOW_404) include(dirname(__FILE__).'/helper/404.php');

?>