<?php
    $limit = 30;

    $offset = ($_GET['offset'] > 0) ? $_GET['offset'] : 0;
    $out    = array();

    $apiConnector	= $app->apiLoad('newsletterMailChimp');
    
    $data = $app->apiLoad('newsletter')->newsletterGet(array(
        'id_newsletter' 	=> $_REQUEST['id_newsletter']
    ));

    /*----------------------------- SEGMENT -------------------------*/

    $mails	= $app->apiLoad('newsletter')->newsletterPoolPopulation($data['id_newsletter']);
    $out['total'] = sizeof($mails);


    if(is_array($mails) && sizeof($mails) > 0 && $_REQUEST['segment'] != '') {

        $out['pourcent'] = ceil( ((($offset + 1) * $limit) / sizeof($mails)) * 100);
        if($out['pourcent'] > 100) $out['pourcent'] = 100;

        // On decoupe le travail en lots
        $mails  = array_slice($mails, ($offset*$limit), $limit);

        if(($offset * $limit) <= $out['total']) {

            if($data['newsletterConnectorValue'] != '') $id_segment = $data['newsletterConnectorValue'];
            else {
                $id_segment = $apiConnector->listStaticSegmentAdd(array('id' => $_REQUEST['id_newsletterList'], 'name' => $_REQUEST['segment']));

                // Save id_segment en BDD
                $def['k_newsletter'] = array(
                    'newsletterConnectorValue' => array('value' => $id_segment)
                );
                $app->apiLoad('newsletter')->newsletterSet($data['id_newsletter'], $def);
            }

            // Inscription des mails au segment
            $tosubscribe = array();
            $tosegment   = array();
            foreach($mails as $mail) {
                $tosubscribe[]  = array('EMAIL'=>$mail['userMail'], 'EMAIL_TYPE '=>'html');
                $tosegment[]    = $mail['userMail'];
            }

            $apiConnector->listBatchSubscribe(
                array(
                    'id' => $_REQUEST['id_newsletterList'],
                    'batch' => $tosubscribe,
                    'double_optin' => false, // pas de mail de confirmation
                    'update_existing' => true
                )
            );
            $apiConnector->listStaticSegmentMembersAdd (array('id' => $_REQUEST['id_newsletterList'], 'seg_id' => $id_segment, 'batch' => $tosegment));

            die(json_encode($out));
        }

    }


    // Envoi de la campagne sur Mailchimp

    $options = array();
    $options['list_id'] = $_REQUEST['id_newsletterList'];

    $list	= $apiConnector->listGet(array('filters' => array('list_id' => $_REQUEST['id_newsletterList'])));

    $options['subject']     = $data['newsletterTitle'];
    $options['from_email']  = $list['data'][0]['default_from_email'];
    $options['from_name']   = $list['data'][0]['default_from_name'];
    $options['to_name']	    = "*|FNAME|*";
    
    $conditions = array();
    if(is_array($_REQUEST['listInterestGroupings'])) {
        foreach($_REQUEST['listInterestGroupings'] as $r) {				
            $pos = strpos($r, '-');
            if($pos != false) {
                $id		= substr($r, 0, $pos);
                $name	= substr($r, $pos+1);
                $conditions[] = array('field'=>'interests-'.$id, 'op'=>'one', 'value'=>$name);	
            }    		
        }

    }
    if($data['newsletterConnectorValue'] > 0) $conditions[] = array('field'=>'static_segment', 'op'=>'eq', 'value'=>$data['newsletterConnectorValue']);

    if(sizeof($conditions) > 0) $segment_opts = array('match'=>'any', 'conditions'=>$conditions);
    //$app->pre($segment_opts);
    $content = array();
    $content['html'] = $data['newsletterHtml'];
    $content['text'] = strip_tags($data['newsletterHtml']);
    $content['url'] = 'http://'.$_SERVER['HTTP_HOST'].'/?preview-newsletter='.base64_encode($_REQUEST['id_newsletter']);
    $content['archive'] = "archive";
    
    
    $data = array();
    $data['type'] = 'regular';
    $data['options'] = $options;
    $data['content'] = $content;
    if($segment_opts) $data['segment_opts'] = $segment_opts;
    
    $result = $apiConnector->campaignCreate($data);

    if(is_array($result)) {
        $out['done'] = 0;
        $out['error'] = $result;
        die(json_encode($out));
    }
    else {			
        $app->dbQuery("UPDATE k_newsletter SET newsletterSendDate=NOW(), newsletterConnector='mailchimp', newsletterConnectorId='".$result."' WHERE id_newsletter=".$_REQUEST['id_newsletter']);
        $out['done'] = 1;
        die(json_encode($out));
    }
?>