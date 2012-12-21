<?php
		$apiConnector	= $app->apiLoad('newsletterMailChimp');
		
		$data = $app->apiLoad('newsletter')->newsletterGet(array(
			'id_newsletter' 	=> $_REQUEST['id_newsletter']
		));
		
	    $options = array();
	    $options['list_id'] = $_REQUEST['id_newsletterList'];
		
		$list	= $apiConnector->listGet(array('filters' => array('list_id' => $_REQUEST['id_newsletterList'])));
		//$app->pre($list['data'][0]['default_from_name']);
		//die();
	    $options['subject']     = $data['newsletterTitle'];
	    $options['from_email']  = $list['data'][0]['default_from_email'];
	    $options['from_name']   = $list['data'][0]['default_from_name'];
	    $options['to_name']	    = "*|FNAME|*";
	    
	    $conditions = array();
	    if(sizeof($_REQUEST['listInterestGroupings']) > 0) {
	    	foreach($_REQUEST['listInterestGroupings'] as $r) {				
				$pos = strpos($r, '-');
				if($pos != false) {
					$id		= substr($r, 0, $pos);
				    $name	= substr($r, $pos+1);
		    		$conditions[] = array('field'=>'interests-'.$id, 'op'=>'one', 'value'=>$name);	
				}    		
	    	}
	    	
		    if(sizeof($conditions) > 0) $segment_opts = array('match'=>'any', 'conditions'=>$conditions);	    	
	    }
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
		if(is_array($result)) die('Erreur : '.$app->pre($result));
		else {			
			$app->dbQuery("UPDATE k_newsletter SET newsletterSendDate=NOW(), newsletterConnector='mailchimp', newsletterConnectorId='".$result."' WHERE id_newsletter=".$_REQUEST['id_newsletter']);
			die(1);
		}
?>