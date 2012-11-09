<?php

	$actions = array(
		
		'1' => array('url' => '/admin/content/index', 'title' => 'Liste'),
		'2' => array('url' => '/admin/content/browse', 'title' => 'Naviguer'),
		'3' => array('url' => '/admin/category/index', 'title' => 'Cat&eacute;gories'),
		'4' => array('url' => '/admin/chapter/index', 'title' => 'Arborescence'),
		'5' => array('url' => '/admin/content/type', 'title' => 'Types'),
		'6' => array('url' => '/admin/field/index', 'title' => 'Champs'),
		'7' => array('url' => '/admin/comment/index', 'title' => 'Commentaires'),
		'8' => array('url' => '/admin/content/search', 'title' => 'Recherche'),
		'9' => array('url' => '/admin/localisation/index', 'title' => 'Traduction')
	); 
	
	echo json_encode($actions);

?>