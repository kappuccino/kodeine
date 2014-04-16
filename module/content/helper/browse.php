<?php
	// Content
	$m = $app->apiLoad('category')->categoryGet(array(
		'language'		=> 'fr',
		'debug'	 		=> false,
		'mid_category'	=> $_GET['id_category'],
		'debug'			=> false
	));
	
	foreach($m as $idx => $e){

		$subs = $app->apiLoad('category')->categoryGet(array(
			'language'		=> 'fr',
			'mid_category'	=> $e['id_category']
		));

		$con = $app->apiLoad('content')->contentGet(array(
			'language'			=> 'fr',
			'useGroup'			=> false,
			'useChapter'		=> false,
			'debug'				=> false,
			'human'				=> false,
			'categoryThrough'	=> true,
			'limit'				=> 1,
			'id_type'			=> $_GET['id_type'],
			'id_category'		=> $e['id_category']
		));

		$e['categoryName']	= htmlentities($e['categoryName']);
		$e['hasSub']		= (sizeof($subs) > 0) ? true : false;
		$e['hasContent']	= (sizeof($con) > 0)  ? true : false;
		$out['category'][]	= $e;
	}

	// Contenu
	if($_GET['id_category'] > 0){
		$cts = $app->apiLoad('content')->contentGet(array(
			'language'		=> 'fr',
			'raw'			=> true,
			'useGroup'		=> false,
			'useChapter'	=> false,
			'debug'			=> false,
			'human'			=> false,
			'id_type'		=> $_GET['id_type'],
			'id_category'	=> $_GET['id_category']
		));

		if(sizeof($cts) > 0){
			foreach($cts as $c){
			
				$c['contentName'] = htmlentities($c['contentName']);

				$com 		= $app->dbOne("SELECT COUNT(*) AS h FROM k_contentcomment WHERE id_content=".$c['id_content']);;
				$c['com'] 	= ($com['h'] > 0) ? $com['h'] : 0;

				foreach($app->dbMulti("SELECT language FROM k_contentdata WHERE id_content=".$c['id_content']) as $l){
					$lan = $app->countryGet(array('iso' => $l['language'], 'debug' => false));
					$c['lan'][] = $l['language'];
				}

				$c['contentDateCreation']	= $app->helperDate($c['contentDateCreation'],	'%d.%m.%Y');
				$c['contentDateUpdate']		= $app->helperDate($c['contentDateUpdate'],		'%d.%m.%Y');

				$out['content'][] = $c;
			}
		}
	}

	if(!is_array($out['category'])) $out['category'] = array();
	if(!is_array($out['content']))	$out['content']	 = array();

	if(isset($_GET['pre'])){
		print_r($out);
	}else{
		echo json_encode($out);
	}

?>