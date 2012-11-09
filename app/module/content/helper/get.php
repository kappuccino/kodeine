<?php
	header('Content-type: application/json');

	require(dirname(dirname(dirname(dirname(__FILE__)))).'/api/core.admin.php');
	$app = new coreAdmin();

	// Content
	$content = $app->apiLoad('content')->contentGet(array(
		'id_type' 		=> $_GET['id_type'],
		'language'		=> 'fr',
		'debug'	 		=> false,
		'useChapter'	=> false,
		'useGroup'		=> false,
		'limit'			=> $filter['limit'],
		'offset'		=> $filter['offset'],
		'search'		=> $filter['q'],
		'id_search'		=> $filter['id_search'],
	));
	
	

	$total = $app->apiLoad('content')->total + 100;

	$field = $app->apiLoad('field')->fieldGet(array(
		'id_type'	=> $_GET['id_type'],
		'is_column'	=> true
	));	
	
	/*for($i=0; $i<100; $i++){
		$data[] = array($i+1);
	}
	$total	= 10000;*/
	
	foreach($content as $i => $e){

		$tmp = array(
			"<input type=\"checkbox\" name=\"remove[]\" value=\"".$e['id_content']."\" class=\"cb\" />",
			"<a href=\"content.data.php?id_content=".$e['id_content']."\">".$e['contentName']."</a>"
		);

		foreach($field as $f){
			$tmp[] = $e['field'][$f['fieldKey']];
		}
		
		$data[] = $tmp;
	}
	

	$ret = array("total"=>$total, "data"=>$data);

	echo json_encode($ret);

          
?>