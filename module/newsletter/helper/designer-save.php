<?php    
    if(!$app->userIsAdmin) header("Location: ./");

    $html			= $_REQUEST['finalhtml'];
    $designerhtml	= $_REQUEST['designerhtml'];

	function formatHtml($html) {
		$html = preg_replace('#<!--TEMPLATE-->(.*)<!--/TEMPLATE-->#i', '', $html);
		$html = preg_replace('#<link([^>]*)>#i', '', $html);
		$html = preg_replace('#<script([^>]*)>(.*)</script>#i', '', $html);
		$html = preg_replace('#<item([^>]*)class="active">#i', '<repeater$1>', $html);
		$html = preg_replace("/<img([^>]+)\>/i", "<img $1 />", $html);
		//$html = preg_replace("/<br([^>]+)\>/i", "<br $1 />", $html);
        $html = str_replace('<br>', '<br />', $html);
        $html = str_replace('<br /><br />', '<br />', $html);
        $html = str_replace('<hr>', '<hr />', $html);
        $html = str_replace('<hr /><hr />', '<hr />', $html);
        $html = str_replace("'", "\\'", $html);
        $html = str_replace("&nbsp;", " ", $html);
		return $html;
	}

	$html_final = $app->apiLoad('newsletter')->newsletterDesignerCompil($html);
	
	$def['k_newsletter'] = array(
		'newsletterHtmlDesigner' 	=> array('value' => addslashes($designerhtml)),
		'newsletterHtml' 			=> array('value' => addslashes($html_final))
	);
	$result	 = $app->apiLoad('newsletter')->newsletterSet($_REQUEST['id_newsletter'], $def);

    if($result) echo '1';
    else echo '0';  
	
	
?>