<?php
	if($_GET['id_category'] != ''){
		$more = "AND id_category != ".$_GET['id_category'];
	}

	$content = $app->dbMulti("SELECT categoryUrl FROM k_categorydata WHERE language='".$_GET['language']."' AND categoryUrl='".$_GET['url']."' ".$more);
	#$app->pre($app->db_query, $app->db_error);

	if(sizeof($content) > 0){
		
		$i = 1;
		while(!$found){
			$check  = $app->dbOne("SELECT 1 FROM k_categorydata WHERE language='".$_GET['language']."' AND categoryUrl='".$_GET['url']."-".$i."'");
		#	$app->pre($app->db_query, $app->db_error);
			$exists = ($check['1'] == '1') ? true : false;

		#	$app->pre($check);
			
			if(!$exists){
				echo json_encode(array('url' => $_GET['url'].'-'.$i));
				$found = true;
			}

			$i++;
		}
	
	
	}else{
		echo json_encode(array('url' => $_GET['url']));
	}
	

?>