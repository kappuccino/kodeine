<?php
    foreach($app->apiLoad('field')->fieldGet(array('user' => true)) as $e){
        $fields[$e['id_field']] = $e;
    }

    foreach($fields as $index => $label){
        if($index < 0) unset($labels[$index]);
    }

    
    $file = USER.'/temp/user.tmp';
    list($label, $r) = $app->apiLoad('user')->userImportAddressBookCSV($file, $_GET);

    $done = array();
    if(sizeof($r['done']) > 0){
        foreach($r['done'] as $_done){
            $done[] = array('id_user' => $_done['id_user'], 'user' => trim($_done['userMail']));
        }
    }

    $error = array();
    if(sizeof($r['error']) > 0){
        foreach($r['error'] as $_error){
            $error[] = array('id_user' => $_error['id_user'], 'user' => trim($_error['userMail']));
        }
    }

    $doublon = array();
    if(sizeof($r['doublon']) > 0){
        foreach($r['doublon'] as $_doublon){
            $doublon[] = array('user' => trim($_doublon['userMail']));
        }
    }

    $jsonObj = array(
        'todo'      => $r['todo'],
        'done'      => $done,
        'error'     => $error,
        'doublon'   => $doublon
    );

    echo json_encode($jsonObj);
?>