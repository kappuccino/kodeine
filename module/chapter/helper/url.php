<?php

	if($_GET['id_chapter'] != ''){
		$more = "AND id_chapter != ".$_GET['id_chapter'];
	}

	$content = $app->dbMulti("SELECT chapterUrl FROM k_chapterdata WHERE language='".$_GET['language']."' AND chapterUrl='".$_GET['url']."' ".$more);
	#$app->pre($app->db_query, $app->db_error);

	if(sizeof($content) > 0){
		
		$i = 0;
		while(!$found){
			$check = $app->dbOne("SELECT 1 FROM k_chapterdata WHERE language='".$_GET['language']."' AND chapterUrl='".$_GET['url']."-".$i."'");
			$i++;
			
			if(!$check[1]){
				echo json_encode(array('url' => $_GET['url'].'-'.$i));
				$found = true;
			}
		}
	
	
	}else{
		echo json_encode(array('url' => $_GET['url']));
	}
	

?>