<?php 
	
	if (is_array($_POST) && !empty($_POST)) {
		
		if (!empty($_POST['text'])) {
			$time= time();
			
			$opt = array('id_type' 		=> 2,
						 'id_group'		=> array(-1),
						 'id_chapter' 	=> array(1),
						 'contentSee'	=> 1,
							 'data'	=> array('k_contentdata' => array(
							 	'contentName' => array('value' => $time),
							 	'contentUrl'  => array('value' => $time)
							 )),
							 'field' => array('1' => $_POST['text'])
						 );
			
			$job = $this->apiLoad('content')->contentSet($opt);
			
			if ($job && $_POST['media']) {
				$id = $this->apiLoad('content')->id_content;
			}
					
			return json_encode($job);	
		}		
	} 

?>