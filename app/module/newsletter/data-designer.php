<?php
	$api	= $app->apiLoad('newsletter');
	$pref	= $app->configGet('newsletter');

	if($_POST['action']){
		$do = true;
		
		
		$def['k_newsletter'] = array(
			'newsletterName' 			=> array('value' => $_POST['newsletterName'], 		'check' => '.'),
			'newsletterTitle' 			=> array('value' => $_POST['newsletterTitle'], 		'check' => '.'),
            'is_designer'               => array('value' => 1),
			'newsletterTemplateUrl' 	=> array('value' => $_POST['newsletterTemplateUrl'])
		);
		
		// Changement de template
		if($_POST['do'] == 'template') {
			$template_info = @file_get_contents($_POST['newsletterTemplateUrl'].'/info.xml');
			preg_match("#<file>(.*)</file>?#", $template_info, $file);
			$template_source = @file_get_contents($_POST['newsletterTemplateUrl'].'/'.$file[1]);
			$def['k_newsletter']['newsletterTemplateSource'] 	= array('value' => $template_source);
			$def['k_newsletter']['newsletterHtmlDesigner'] 		= array('value' => '');
		}
		

		if(!$app->formValidation($def)) $do = $false;

		if($do){
			$result	 = $app->apiLoad('newsletter')->newsletterSet($_POST['id_newsletter'], $def);
			$message = ($result) ? 'OK: Enregistrement' : 'KO: Probleme, APP : <br />'.$app->db_error;

			if($result && $_POST['do'] == 'test'){
                
                // Envoi de mail de test
                $data = $app->apiLoad('newsletter')->newsletterGet(array(
                    'id_newsletter'     => $_REQUEST['id_newsletter']
                ));
                require_once(PLUGIN.'/phpmailer/class.phpmailer.php');
                $mail = new PHPMailer();
                $mail->SetFrom('noreply@'.$_SERVER['HTTP_HOST'], $_SERVER['HTTP_HOST']);
        
                $mail->AddAddress($pref['test']);
        
                $mail->Subject  = $data['newsletterTitle'];
                $mail->AltBody  = utf8_decode(strip_tags($data['newsletterHtml']));
                $mail->MsgHTML(utf8_decode($data['newsletterHtml']));
                
                if(!$mail->Send()) $message = "Erreur d'envoi".$mail->ErrorInfo;   
                else $message = 'OK: Newsletter enregistrée et envoyée en mode [TEST] ('.$pref['test'].')';
				
			}else
			if($result && $_POST['do'] == 'list'){
				if($data['newsletterSendDate'] == NULL){
					header("Location: ./data-list?id_newsletter=".$app->apiLoad('newsletter')->id_newsletter);
					exit();
				}else{
					$message = 'KO: Cette newsletter est en cours d\'envois ou bien elle a déjà été envoyé.';
				}
			}
	
			header("Location: data-designer?id_newsletter=".$app->apiLoad('newsletter')->id_newsletter.'&message='.urlencode($message));

		}else{
			$message = 'KO: Merci de remplir les champs correctement';
		}
	}

	if($_REQUEST['id_newsletter'] != NULL){
		$data = $app->apiLoad('newsletter')->newsletterGet(array(
			'id_newsletter' 	=> $_REQUEST['id_newsletter']
		));

		$title = $data['newsletterName'];
	}else{
		$title = 'Nouvelle newsletter';
	}
	
	
	$tps = $app->fsFolder(KROOT.'/media/newsletter');
	$templates = array();
    if(is_array($tps)) {
        foreach($tps as $t) {
            $info = @file_get_contents($t.'/info.xml' );
            if($info) {
                preg_match("#<name>(.*)</name>?#", $info, $name);
                $templates[$t] = $name[1];
            }
        }
    }
	//$app->pre($templates);

?><!DOCTYPE html>
<head>
	<title>Kodeine</title>
	<?php include(COREINC.'/head.php'); ?>
	
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/newsletter.css" /> 
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/designer.css" />
</head>

<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div class="inject-subnav-right hide">
    <?php if($data['id_newsletter'] > 0) { ?>
        <?php if($data['newsletterSendDate'] != NULL){ ?>
            <li><a href="analytic?id_newsletter=<?php echo $_REQUEST['id_newsletter'] ?>" class="btn btn-mini">Consulter les statistiques</a></li>
            <li><a href="data-designer" class="btn btn-mini">Nouveau</a></li>
        <?php } ?>
        <?php if($_REQUEST['id_newsletter'] > 0){ ?>
            <li><a href="preview?id_newsletter=<?php echo $_REQUEST['id_newsletter'] ?>" class="btn btn-mini" target="_blank">Prévisualiser</a></li>
            <?php } ?>
        <?php if($data['newsletterSendDate'] == NULL){ ?>
            <li><a href="javascript:$('#do').val('test');save();" class="btn btn-mini btn-success">Enregistrer et envoyer un mail de test</a></li>
            <li><a href="javascript:$('#do').val('list');save();" class="btn btn-mini btn-success">Enregistrer et sélectionner les abonnés</a></li>
        <?php } ?>
    <?php } ?>
    <li><a href="javascript:save();" class="btn btn-mini btn-success">Enregistrer</a></li>
</div>

<div id="app">

<?php	if($message == NULL && $_GET['message'] != NULL) $message = urldecode($_GET['message']);
	if($message != NULL){
		list($class, $message) = $app->helperMessage($message);
		echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
	}
?>

<form action="data-designer" method="post" id="data" enctype="multipart/form-data">

	<input type="hidden" name="action" value="1" />
	<input type="hidden" name="id_newsletter" value="<?php echo $data['id_newsletter'] ?>" />
	<input type="hidden" name="do" id="do" value="" />

	<table cellpadding="5" width="100%">
        <!--<tr>
            <td height="30" colspan="2">
                <a href="javascript:save();" class="btn btn-mini">Enregistrer</a>
                <?php if($data['id_newsletter'] > 0) { ?>
                <?php if($data['newsletterSendDate'] == NULL){ ?>
                    <a href="javascript:$('#do').val('test');save();" class="btn btn-mini">Enregistrer et envoyer un mail de test</a>
                    <a href="javascript:$('#do').val('list');save();" class="btn btn-mini">Enregistrer et sélectionner les abonnés</a>
                    <?php } if($_REQUEST['id_newsletter'] > 0){ ?>
                    <a href="preview?id_newsletter=<?php echo $_REQUEST['id_newsletter'] ?>" class="btn btn-mini" target="_blank">Prévisualiser</a>
                    <?php } if($data['newsletterSendDate'] != NULL){ ?>
                    <a href="analytic?id_newsletter=<?php echo $_REQUEST['id_newsletter'] ?>" class="btn btn-mini">Consulter les statistiques</a>
                    <a href="data" class="btn btn-mini">Nouveau</a>
                    <?php } ?>
                <?php } ?>
            </td>
        </tr>-->
		<tr>
			<td width="100">Nom</td>
			<td><input type="text" name="newsletterName" value="<?php echo $app->formValue($data['newsletterName'], $_POST['newsletterName']); ?>" style="width:96%" /></td>	
		</tr>
		<tr>
			<td>Titre du mail</td>
			<td><input type="text" name="newsletterTitle" value="<?php echo $app->formValue($data['newsletterTitle'], $_POST['newsletterTitle']); ?>" style="width:96%" /></td>	
		</tr>
    <?php if($data['id_newsletter'] > 0) { ?>
		<tr>
			<td>Choix du template</td>
			<td>
				<select name="newsletterTemplateUrl" id="newsletterTemplateUrl" onChange="templateChange();">
					<option value=""></option>
					<?php foreach($templates as $url=>$name) { ?>
					<option value="<?php echo $url; ?>" <?php if($url == $_POST['newsletterTemplateUrl'] || $url == $data['newsletterTemplateUrl']) echo ' selected="selected" '; ?>><?php echo $name; ?></option>
					<?php }?>
				</select>
				
				
				<a href="#" onClick="return templateChange();" class="btn btn-mini">Recharger le template</a>
				
			</td>	
		</tr>
    <?php } ?>
	</table>

	
    <?php if($data['id_newsletter'] > 0) { ?>

		<div id="previewContainer">    
		    <iframe width="100%" scrolling="yes" frameborder="0" name="preview" id="preview"></iframe>
		</div>
		
		<div style="float: left;">
			<div id="add">
				<select id="stRepeater"><option value="0">Insérer un élément</option></select>
			</div>
			<br clear="both">
			<div id="edit">
			</div>
		</div>
		
		
		<!--<div id="templatediv" style="display: none;">
		    <?php //echo $data['newsletterTemplateSource']; ?>
		</div>
		<textarea id="templatetext" style="display: none;">
		    <?php //echo $data['newsletterTemplateSource']; ?>
		</textarea>
		<iframe id="template" style="display: none;"></iframe>
		<textarea id="newsletterHtml" style="display: none;">
		    <?php //echo $data['newsletterHtmlDesigner']; ?>
		</textarea>-->
		

    <?php } ?>


</form>


</div>

<?php include(COREINC.'/end.php'); ?>

    <script type="text/javascript" src="ui/js/designer.js"></script>

	<script src="/admin/core/ui/_tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
	<script src="/admin/core/ui/_tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script>
    <?php if($data['id_newsletter'] > 0) { ?>

var id_newsletter = <?php echo $data['id_newsletter']; ?>;
var id_repeater = 0;
var repeaterData = new Object();

var $template       = $();
var templatehtml    = '';
var $designer       = $();
var designerhtml    = '';
var $preview = $("#preview").contents();

var css = '<link rel="stylesheet" type="text/css" media="all" href="http://<?php echo $_SERVER["HTTP_HOST"]; ?>/admin/newsletter/ui/css/designer.css" />';
var script      = document.createElement( 'script' );
var script2     = document.createElement( 'script' );
var script3     = document.createElement( 'script' );
script.type     = 'text/javascript';
script2.type    = 'text/javascript';
script3.type    = 'text/javascript';
script.src      = 'http://<?php echo $_SERVER["HTTP_HOST"]; ?>/app/module/core/ui/_jquery/jquery-1.7.2.min.js';
script2.src     = 'http://<?php echo $_SERVER["HTTP_HOST"]; ?>/app/module/core/ui/_jqueryui/jqui.sortable.min.js';
script3.src     = 'http://<?php echo $_SERVER["HTTP_HOST"]; ?>/app/module/newsletter/ui/js/designer-iframeload.js';

function templateChange() {
    if(confirm('Etes-vous certain de changer de template ?')) {
        templateLoad(true);
    }else {
        //$('#do').val('');
        return false;
    }
}

function templateLoad(is_start) {   
    var templateUrl = $('#newsletterTemplateUrl').val();
    //alert(templateUrl);
    var request = $.ajax({
        url: 'helper/designer-template',
        dataType: 'xml',
        data: {
            url: templateUrl
        }
    });
    request.done(function(data) {

        $template = $(data);
        if(is_start) {
        	start(true);
        }
   });
   request.fail(function(data) {
        alert('Format de la template incorrect');
   });
}

$(document).ready(function() {
     templateLoad();
     
    // Bouton enregistrer
    $('#btSave').click( function(e) {
        save();
    });
    // Bouton enregistrer
    $('#btReset').click( function(e) {
        start(true);
    }); 
    
    var request = $.ajax({
        url: 'helper/designer-get',
        dataType: 'xml',
        data: {
            id_newsletter: <?php echo $data['id_newsletter']; ?>
        }
    });
    request.done(function(data) {
        $designer = $(data);
        start(false);
   });
   request.fail(function(data) {
        alert('Format de la template incorrect get');
   });
       
    //start(<?php echo (trim($data['newsletterHtmlDesigner']) != '') ? 'false' : 'true'; ?>);   
    
    
});


// Enregistrement de la newsletter en cours
function save() {
    var html = $preview.contents()[0].outerHTML;
    $.post('helper/designer-save', { id_newsletter: id_newsletter, html: html, templatehtml: templatehtml}, function(data) {
        if(data != 0) {
            //alert('Enregistré');
            $('#data').submit()
        }
    }); 
}
    <?php } else { ?>
// Enregistrement de la newsletter en cours
function save() {
    $('#data').submit();
}
    <?php } ?>
</script>
</body></html>