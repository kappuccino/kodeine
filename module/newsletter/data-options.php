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
            else $message = 'OK: Newsletter enregistr�e et envoy�e en mode [TEST] ('.$pref['test'].')';

        }else
            if($result && $_POST['do'] == 'list'){
                if($data['newsletterSendDate'] == NULL){
                    header("Location: ./data-list?id_newsletter=".$app->apiLoad('newsletter')->id_newsletter);
                    exit();
                }else{
                    $message = 'KO: Cette newsletter est en cours d\'envois ou bien elle a d�j� �t� envoy�.';
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
    <title>Kodeine</title>
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
        <li><a href="javascript:$('#do').val('test');save();" class="btn btn-mini btn-success">Enregistrer et envoyer un mail de test</a></li>
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
    <div class="wrapper clearfix">

    <form action="data-designer" method="post" id="data" enctype="multipart/form-data">

        <input type="hidden" name="action" value="1" />
        <input type="hidden" name="id_newsletter" value="<?php echo $data['id_newsletter'] ?>" />
        <input type="hidden" name="do" id="do" value="" />

        <table cellpadding="5" width="100%">
            <tr>
                <td width="100">Nom</td>
                <td><input type="text" name="newsletterName" value="<?php echo $app->formValue($data['newsletterName'], $_POST['newsletterName']); ?>" style="width:300px" /></td>
            </tr>
            <tr>
                <td>Titre du mail</td>
                <td><input type="text" name="newsletterTitle" value="<?php echo $app->formValue($data['newsletterTitle'], $_POST['newsletterTitle']); ?>" style="width:500px" /></td>
            </tr>
        </table>
    </form>

        </div>


</div>

<?php include(COREINC.'/end.php'); ?>


<script>

    <?php
    /*
    <script src="/admin/core/ui/_tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
    <script src="/admin/core/ui/_tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
    script.src      = 'http://<?php echo $_SERVER["HTTP_HOST"]; ?>/app/module/core/ui/_jquery/jquery-1.7.2.min.js';
    script2.src     = 'http://<?php echo $_SERVER["HTTP_HOST"]; ?>/app/module/core/ui/_jqueryui/jqui.sortable.min.js';
    script3.src     = 'http://<?php echo $_SERVER["HTTP_HOST"]; ?>/app/module/newsletter/ui/js/designer-iframeload.js';
    */
    ?>
    function save() {
        $("#data").submit();
    }
    $(document).ready(function() {
    });

</script>
</body></html>