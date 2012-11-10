<?php

	# On recupere le content d'apres l'URL
	#
	$content	= $this->apiLoad('content')->contentGet(array(
		'debug'			=> false,
		'cache'			=> true,
		'contentUrl'	=> $this->kodeine['get']['urlFile']
	));

	if($content['id_type'] > 0){
		$fields	= $this->apiLoad('content')->contentField;
		$type	= $this->apiLoad('content')->contentType(array('id_type' => $content['id_type']));
	}


	# Incremente le compteur de VIEW si la page n'a pas deja ete affichee.
	#
	if($content['id_content'] != NULL && !@in_array($content['id_content'], $_SESSION['contentViewed'])){
		#if(!is_array($_SESSION['contentViewed'])) $_SESSION['contentViewed'] = array();
		$this->apiLoad('content')->contentView($content['id_content']);
		$_SESSION['contentViewed'][] = $content['id_content'];
	}

?>