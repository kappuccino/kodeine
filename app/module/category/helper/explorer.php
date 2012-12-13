<?php
	$category = $app->apiLoad('category')->categoryGet(array(
		'mid_category'	=> $_GET['mid_category'],
		'language'		=> 'fr'
	));

	foreach($category as $e){
		$temp[] = array(
			'id_category' 	=> $e['id_category'],
			'parent'		=> $e['categoryParent'],
			'name'			=> $e['categoryName'],
			'hasChildren'	=> ($e['categoryHasChildren'] == '1')
		);
	}
	
	echo json_encode($temp);


?>