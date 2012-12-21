<?php    
    if(!$app->userIsAdmin) header("Location: ./");
    
	
	
	$html			= $_REQUEST['html'];
	$templatehtml	= $_REQUEST['templatehtml'];
	//$app->pre($html);
	function formatHtml($html) {		
		$html = preg_replace('#<div([^>]*)(class\\s*=\\s*["\']repeaterBTEdit["\'])([^>]*)>(.*?)</div>#i', '', $html);
		$html = preg_replace('#<link([^>]*)>#i', '', $html);
		$html = preg_replace('#<script([^>]*)>(.*)</script>#i', '', $html);
		$html = preg_replace('#<repeater([^>]*)class="active">#i', '<repeater$1>', $html);
		$html = preg_replace("/<img([^>]+)\>/i", "<img $1 />", $html);
		//$html = preg_replace("/<br([^>]+)\>/i", "<br $1 />", $html);
        $html = str_replace('<br>', '<br />', $html);
        $html = str_replace('<br /><br />', '<br />', $html);
        $html = str_replace("'", "\\'", $html);
        $html = str_replace("&nbsp;", " ", $html);
		return $html;
	}
	$html			= formatHtml($html);
	$templatehtml	= formatHtml($templatehtml);
	
	$html_final = $app->apiLoad('newsletter')->newsletterDesignerCompil($html);
	
	//$app->pre($html_final);
	
	$def['k_newsletter'] = array(
		'newsletterHtmlDesigner' 	=> array('value' => utf8_decode($html)),
		'newsletterTemplateSource' 	=> array('value' => utf8_decode($templatehtml)),
		'newsletterHtml' 			=> array('value' => utf8_decode($html_final))
	);
	$result	 = $app->apiLoad('newsletter')->newsletterSet($_REQUEST['id_newsletter'], $def);

    if($result) echo '1';
    else echo '0';  
	
	
?>