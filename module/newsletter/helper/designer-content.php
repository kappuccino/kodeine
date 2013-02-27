<?php    
    if(!$app->userIsAdmin) header("Location: ./");
    
    $data = $app->apiLoad('content')->contentGet(array(
        'id_content'    => $_REQUEST['id_content'],
        'language'      => ($_REQUEST['language'] != '') ? $_REQUEST['language'] : 'fr',
        'useChapter'    => false,
        'useGroup'      => false,
        'assoCategory'  => true,
        'contentSee'    => 'ALL',
        'debug'         => false
    ));
	
    //$app->pre($data['field']);
    if(sizeof($data) > 0) die(json_encode($data));
?>