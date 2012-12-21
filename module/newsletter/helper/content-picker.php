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
		'debug'			=> false
	));
	if(sizeof($data) == 0) die();
	echo '<div class="results-inner">';
	echo '<ul>';
	foreach($data as $d) {
		echo '<li>';
		echo '<a href="#" onclick="$(\'#select_id_content\').val('.$d['id_content'].');$(\'#results\').hide();return false;">';
		echo $d['id_content'].' - '.$d['contentName'];
		echo '</a>';
		echo '</li>';
	}
	echo '</ul>';
	echo '</div>';
	die();
?>