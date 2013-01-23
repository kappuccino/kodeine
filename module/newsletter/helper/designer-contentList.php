<?php
if(!$app->userIsAdmin) header("Location: ./");
//$app->pre($_REQUEST);
$data = $app->apiLoad('content')->contentGet(array(
    'id_type'       => $_REQUEST['id_type'],
    'language'      => ($_REQUEST['language'] != '') ? $_REQUEST['language'] : 'fr',
    'useChapter'    => false,
    'useGroup'      => false,
    'contentSee'    => 'ALL',
    'order'			=> $_REQUEST['order'],
    'direction'		=> $_REQUEST['direction'],
    'limit'		    => $_REQUEST['limit'],
    'debug'         => false
));

//$app->pre($data['field']);
if(sizeof($data) > 0) die(json_encode($data));
?>