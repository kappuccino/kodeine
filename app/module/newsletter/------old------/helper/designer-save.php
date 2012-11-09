<?php    
    if(!$app->userIsAdmin) header("Location: ./");
    
	//$app->pre($_REQUEST);
	
	$html = $_REQUEST['html'];
	$html = preg_replace('#<div([^>]*)(class\\s*=\\s*["\']repeaterBTEdit["\'])([^>]*)>(.*?)</div>#i', '', $html);
	$html = preg_replace('#<link(.*)>#i', '', $html);
	$html = preg_replace('#<script(.*)>(.*)</script>#i', '', $html);
	$html = preg_replace('#<repeater(.*)class="active">#i', '<repeater$1>', $html);
	
	$def['k_newsletter'] = array(
		'newsletterHtmlDesigner' 	=> array('value' => utf8_decode($html))
	);
	$result	 = $app->apiLoad('newsletter')->newsletterSet($_REQUEST['id_newsletter'], $def);
	
    if($result) echo '1';
    else echo '0';  
	
	
?>