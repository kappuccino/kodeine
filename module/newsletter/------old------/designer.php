<?php
    $html = file_get_contents(MEDIA.'/newsletter/template1.txt');
    //$html = file_get_contents('http://nx.kappuccino.org/newsletter/template');
    //die($html);
	//$fp = fopen (MEDIA.'/newsletter/template1.txt', "r"); 
    //$html = fgets ($fp, 255);
    //fclose ($fp); 
    //$app->pre($html);
	$data	= $app->apiLoad('newsletter')->newsletterGet(array('id_newsletter' => 70));
	//$app->pre($data);
?><!DOCTYPE html>
<head>
	<title>Kodeine</title>
	<?php include(COREINC.'/head.php'); ?>
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/designer.css" />
	
	<!-- TINY MCE -->
	<!--<script type="text/javascript" src="ui/plugin/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>-->
</head>
<body>
	
<header><?php 
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div id="app"><div class="wrapper">

<div id="btSave">ENREGISTRER</div>
<div id="btReset">RESET</div>

<div id="previewContainer">    
    <iframe width="100%" scrolling="yes" frameborder="0" id="preview"></iframe>
</div>
<div id="add">
	<select id="stRepeater"><option value="0">Insérer un élément</option></select>
</div>
<div id="edit">
</div>

<br clear="both">

<div id="template" style="display: none;">
    <?php echo $html; ?>
</div>
<textarea id="templatei" style="display: none;">
    <?php echo $html; ?>
</textarea>
<textarea id="newsletterHtml" style="display: none;">
    <?php echo $data['newsletterHtmlDesigner']; ?>
</textarea>

<?php include(COREINC.'/end.php'); ?>

    <script type="text/javascript" src="ui/js/designer.js"></script>

    <script src="/admin/core/ui/_tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
    <script src="/admin/core/ui/_tinymce/jscripts/tiny_mce/tiny_mce.js"></script>

<script>
var id_newsletter = <?php echo $data['id_newsletter']; ?>;
var id_repeater = 0;
var repeaterData = new Object();

var $template = $("#template");

var $preview = $("#preview").contents();

var css = '<link rel="stylesheet" type="text/css" media="all" href="http://<?php echo $_SERVER["HTTP_HOST"]; ?>/admin/newsletter/ui/css/designer.css" />';
var script		= document.createElement( 'script' );
var script2 	= document.createElement( 'script' );
var script3 	= document.createElement( 'script' );
script.type		= 'text/javascript';
script2.type	= 'text/javascript';
script3.type	= 'text/javascript';
script.src 		= 'http://nx.kappuccino.org/app/module/core/ui/_jquery/jquery-1.7.2.min.js';
script2.src 	= 'http://nx.kappuccino.org/app/module/core/ui/_jqueryui/jqui.sortable.min.js';
script3.src 	= 'http://nx.kappuccino.org/app/module/newsletter/ui/js/designer-iframe.js';


$(document).ready(function() {
    
    // Bouton enregistrer
    $('#btSave').click( function(e) {
        save();
    });
    // Bouton enregistrer
    $('#btReset').click( function(e) {
        alert('init');
        start(true);
    }); 

    start(<?php /*echo 'true';*/echo (trim($data['newsletterHtmlDesigner']) != '') ? 'false' : 'true'; ?>);	
});

</script>

</div></div></body></html>