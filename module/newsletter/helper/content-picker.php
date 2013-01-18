<?php
	//$app->pre($_REQUEST['q']);
	if($_REQUEST['q'] == '' || $_REQUEST['id_type'] == '') die();
	$data = $app->apiLoad('content')->contentGet(array(
		'id_type'		=> $_REQUEST['id_type'],
		'limit'			=> 30,
		'useGroup'		=> false,
		'useChapter'	=> false,
		'useCategory'	=> false,
		'contentSee'	=> 'ALL',
		'search'		=> $_REQUEST['q'],
        'sqlWhere'      => " OR k_content.id_content LIKE '%".$_REQUEST['q']."%' ",
        'debug'			=> false
	));
	if(sizeof($data) == 0) die();
	echo '<div class="results-inner">';
	foreach($data as $d) {
		echo '<a href="#" data-id_content="'.$d['id_content'].'" onclick="return false;">';
		echo $d['id_content'].' - '.$d['contentName'];
		echo '</a>';
	}
	echo '</div>';
	die();
?>