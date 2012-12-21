<?php
	if(is_numeric($_GET['id_content'])) $more = " AND id_content != '".$_GET['id_content']."'";

	$content = $app->dbMulti("SELECT contentUrl FROM k_contentdata WHERE contentUrl='".$_GET['url']."' AND language='".$_GET['language']."'".$more);
	$q 		 = $app->db_query; 
	$found	 = false;

	if(sizeof($content) > 0){
		
		$i = 1;
		while(!$found){
			$check = $app->dbOne("SELECT 1 FROM k_contentdata WHERE contentUrl='".$_GET['url']."-".$i."' AND language='".$_GET['language']."'");
		#	$app->pre($app->db_query, $app->db_error, $check);

			if(!$check[1]){
				echo json_encode(array('url' => $_GET['url'].'-'.$i, 'q' => $q));
				$found = true;
			}

			$i++;
		}
	
	
	}else{
		echo json_encode(array('url' => $_GET['url']));
	}
	

?>