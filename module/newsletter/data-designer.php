<?php
	$api	= $app->apiLoad('newsletter');
	$pref	= $app->configGet('newsletter');

    if($_POST['do'] == 'test'){
        //die('ok');
        $result = $app->apiLoad('newsletter')->newsletterPreview($_REQUEST['id_newsletter']);
        $message = ($result) ? 'OK: Newsletter envoyée en mode [TEST] ('.$pref['test'].')' : 'KO: Erreur Test';
    }

	if($_POST['action']){
        $do = true;

        $def['k_newsletter'] = array(
            'newsletterTemplateUrl' 	=> array('value' => $_POST['newsletterTemplateUrl'])
        );

        // Changement de template
        if($_POST['do'] == 'template') {
            $template_info = @file_get_contents($_POST['newsletterTemplateUrl'].'/info.xml');
            preg_match("#<file>(.*)</file>?#", $template_info, $file);
            /*$template_source = @file_get_contents($_POST['newsletterTemplateUrl'].'/'.$file[1]);
            $def['k_newsletter']['newsletterTemplateSource'] 	= array('value' => $template_source);*/
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


    // Changement de template
    if($_POST['newsletterTemplateUrl'] != '') {
        $def = array();
        $template_info = @file_get_contents($_POST['newsletterTemplateUrl'].'/info.xml');
        preg_match("#<file>(.*)</file>?#", $template_info, $file);
        $template_source = @file_get_contents($_POST['newsletterTemplateUrl'].'/'.$file[1]);

        $def['k_newsletter']['newsletterTemplateUrl'] 	    = array('value' => $_POST['newsletterTemplateUrl']);
        $def['k_newsletter']['newsletterTemplateSource'] 	= array('value' => addslashes($template_source));
        $def['k_newsletter']['newsletterHtmlDesigner'] 		= array('value' => '');
        $result	 = $app->apiLoad('newsletter')->newsletterSet($_REQUEST['id_newsletter'], $def);
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
	<?php include(COREINC.'/head.php'); ?>
    <link rel="stylesheet" type="text/css" media="all" href="ui/css/newsletter.css" />
</head>

<body>

<header><?php
	include(COREINC.'/top.php');
    include(__DIR__.'/ui/menu.php');
    include(__DIR__.'/ui/steps.php');
?></header>

<div class="inject-subnav-right hide">
    <?php if($data['id_newsletter'] > 0) { ?>
        <?php if($_REQUEST['id_newsletter'] > 0){ ?>
            <li><a href="preview?id_newsletter=<?php echo $_REQUEST['id_newsletter'] ?>" class="btn btn-mini" target="_blank">Prévisualiser</a></li>
            <?php } ?>
        <?php if($data['newsletterSendDate'] == NULL){ ?>
            <li><a href="javascript:$('#do').val('test');$('#data').submit();" class="btn btn-mini">Envoyer un mail de test</a></li>
        <?php } ?>
    <?php } ?>
    <?php if($data['newsletterSendDate'] == NULL){ ?>
    <li><a href="javascript:save();" class="btn btn-mini btn-success">Enregistrer</a></li>
    <?php } ?>
</div>

<div id="app">

<?php	if($message == NULL && $_GET['message'] != NULL) $message = urldecode($_GET['message']);
	if($message != NULL){
		list($class, $message) = $app->helperMessage($message);
		echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
	}
?>

    <?php if($data['id_newsletter'] > 0 && $data['newsletterSendDate'] == NULL) { ?>

    <form action="data-designer?id_newsletter=<?php echo $data['id_newsletter'] ?>" method="post" id="data" enctype="multipart/form-data">
        <input type="hidden" name="id_newsletter" value="<?php echo $data['id_newsletter'] ?>" />
        <input type="hidden" name="do" id="do" value="" />
    </form>


    <div class="wrapper clearfix">

        <form method="post" action="" id="formTemplateChange">
            Choix du template : <select name="newsletterTemplateUrl" id="newsletterTemplateUrl" onChange="templateChange();">
                <option value=""></option>
                <?php foreach($templates as $url=>$name) { ?>
                <option value="<?php echo $url; ?>" <?php if($url == $_POST['newsletterTemplateUrl'] || $url == $data['newsletterTemplateUrl']) echo ' selected="selected" '; ?>><?php echo $name; ?></option>
                <?php }?>
            </select> <a href="#" onClick="return templateChange();" class="btn btn-mini">Recharger le template</a>
        </form>
        <br />&nbsp;
    </div>


    <?php } ?>

	
    <?php
        if($data['id_newsletter'] > 0 && $data['newsletterSendDate'] == NULL) {
            $iframeUrl = 'designer?id_newsletter='.$data['id_newsletter'].'';
    ?>

        <iframe src="<?php echo $iframeUrl; ?>" width="100%" style="height:1200px;border-top: 1px solid #999;" border="0" id="designer-iframe"></iframe>

    <?php } ?>




</div>

<?php include(COREINC.'/end.php'); ?>


<script>

function save() {
    document.getElementById('designer-iframe').contentWindow.save();
}
$(document).ready(function() {
    $(window).scroll( function(event){
        document.getElementById('designer-iframe').contentWindow.topBar();
    });

});

function templateChange() {
    if(confirm('Etes-vous certain de changer de template et de perdre les données actuelles ?')) {
        $('#formTemplateChange').submit();
    }else {
        return false;
    }
}
</script>
</body></html>