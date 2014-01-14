<?php
$api	= $app->apiLoad('newsletter');
$pref	= $app->configGet('newsletter');
$mailchimp = $app->apiLoad('newsletterMailChimp');

if (!empty($_POST['testmail'])) {
	$lists = $mailchimp->listGet();

	## Si on a au moins une liste de renseignée
	if (isset($lists['data'][0])) {

		$nl = $app->apiLoad('newsletter')->newsletterGet(array(
			'id_newsletter' => $_REQUEST['id_newsletter']
		));

		$campainOptions = array(
			'list_id' => $lists['data'][0]['id'],
			'subject' => '[email test] '.$nl['newsletterTitle'],
			'from_email' => $lists['data'][0]['default_from_email'],
			'from_name' => $lists['data'][0]['default_from_name'],
			'to_name' => "*|FNAME|*"
		);

		$campainContents = array(
			'html' => $nl['newsletterHtml'],
			'text' => strip_tags($nl['newsletterHtml']),
			'url' => 'http://'.$_SERVER['HTTP_HOST'].'/?preview-newsletter='.base64_encode($_REQUEST['id_newsletter'])
		);

		$campainParams = array(
			'type' => 'regular',
			'options' => $campainOptions,
			'content' => $campainContents
		);

		## Renvoie le campaign id
		$cid = $mailchimp->campaignCreate($campainParams);

		$test = $mailchimp->campaignSendTest(array(
			'cid' => $cid,
			'test_emails' => array(
				$_POST['testmail']
			)
		));

		$delete = $mailchimp->campaignDelete(array(
			'cid' => $cid
		));

		## 4138d1bdb8
		#$app->pre($cid, $test, $delete);
	}

}

if($_POST['action']){
    $do = true;


    $def['k_newsletter'] = array(
        'is_archive'				=> array('value' => $_POST['is_archive'],			'zero'  => true),
        'newsletterName' 			=> array('value' => $_POST['newsletterName'], 		'check' => '.'),
        'newsletterTitle' 			=> array('value' => $_POST['newsletterTitle'], 		'check' => '.'),
        'newsletterTemplateUrl' 	=> array('value' => $_POST['newsletterTemplateUrl'])
    );
    if($_REQUEST['designer'] == 1) {
        $def['k_newsletter']['is_designer'] = array('value' => 1);
    }

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
                    $message = 'KO: Cette newsletter est en cours d\'envoi ou bien elle a déjà été envoyée.';
                }
            }

        header("Location: data-options?id_newsletter=".$app->apiLoad('newsletter')->id_newsletter.'&message='.urlencode($message));

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
    if($data['newsletterSendDate'] != NULL) {
        header("Location: analytic?id_newsletter=".$_REQUEST['id_newsletter']);
    }

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

?><!DOCTYPE html>
<head>
    <?php include(COREINC.'/head.php'); ?>
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/bootstrap3/css/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/flatui/css/flat-ui.css" />
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/dsnr-ui.css" />
</head>

<body>

<header><?php
    include(COREINC.'/top.php');
    include(__DIR__.'/ui/menu.php');
    include(__DIR__.'/ui/steps.php');
?></header>

<div class="inject-subnav-right hide">
  <a href="javascript:save();" class="btn btn-mini btn-success">Enregistrer</a>
</div>

<!-- <div class="inject-subnav-right hide">
    <?php if($data['id_newsletter'] > 0) { ?>
    <?php if($_REQUEST['id_newsletter'] > 0){ ?>
        <li><a href="preview?id_newsletter=<?php echo $_REQUEST['id_newsletter'] ?>" class="btn btn-mini" target="_blank">Prévisualiser</a></li>
        <?php } ?>
    <?php if($data['newsletterSendDate'] == NULL){ ?>
        <li><a href="javascript:$('#do').val('test');save();" class="btn btn-mini">Envoyer un mail de test</a></li>
        <?php } ?>
    <?php } ?>
    <?php if($data['newsletterSendDate'] == NULL){ ?>
    <li><a href="javascript:save();" class="btn btn-mini btn-success">Enregistrer</a></li>
    <?php } ?>
</div> -->

<div id="app">

    <?php	if($message == NULL && $_GET['message'] != NULL) $message = urldecode($_GET['message']);
    if($message != NULL){
        list($class, $message) = $app->helperMessage($message);
        echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
    }
    ?>
    <div class="wrapper clearfix">

	    <?php if (empty($data['newsletterHtml'])) { ?>
	    <div class="alert alert-info">
		    <h4>Vous n'avez pas encore <a href="data-editor?id_newsletter=<?php echo $data['id_newsletter'] ?>">ajouté de gabarit</a> à votre newsletter.</h4>
		    <em>Les gabarits sont des <a href="template">assemblages de blocs</a>. <br/>Avant de pouvoir en
			    <a href="data-editor?id_newsletter=<?php echo $data['id_newsletter'] ?>">lier à votre newsletter</a>, vous devez un générer un a partir de <a href="blocs">vos blocs de contenu</a>.</em>
	    </div>
        <?php } ?>

	    <?php if (!empty($data['newsletterHtml'])) { ?>
		    <div class="alert alert-success">
			    <h4>Bravo ! cette newsletter <a href="data-list?id_newsletter=<?php echo $data['id_newsletter'] ?>">est prête à être envoyée !</a></h4>
			    <em>Vous n'êtes pas sûr ? Envoyez un mail de test rapidement&nbsp;
				    <form action="" method="post" style="display:inline">
					    <input type="text" placeholder="adresse@email.com" name="testmail"/><button type="submit" id="testbtn" class="btn btn-block btn-lg btn-info" style="display: inline;font-size: 14px;color: white; padding: 2px 10px; border-radius: 0; vertical-align: 0px;width:auto;">Tester avec mailchimp</button></em>
				    </form>
		    </div>
	    <?php } ?>

	    <form action="data-options" method="post" id="data" enctype="multipart/form-data">

	        <input type="hidden" name="action" value="1" />
	        <input type="hidden" name="id_newsletter" value="<?php echo $data['id_newsletter'] ?>" />
	        <input type="hidden" name="do" id="do" value="" />
	        <input type="hidden" name="designer" value="<?php echo $_REQUEST['designer']; ?>" />

	        <table cellpadding="5" width="100%">
	            <tr>
	                <td width="100">Nom</td>
	                <td><input type="text" name="newsletterName" value="<?php echo $app->formValue($data['newsletterName'], $_POST['newsletterName']); ?>" style="width:300px" /></td>
	            </tr>
	            <tr>
	                <td>Titre du mail</td>
	                <td><input type="text" name="newsletterTitle" value="<?php echo $app->formValue($data['newsletterTitle'], $_POST['newsletterTitle']); ?>" style="width:500px" /></td>
	            </tr>
	            <tr>
	                <td>Archivage</td>
	                <td>
	                    <input type="checkbox" id="is_archive" name="is_archive" value="1" <?php if($app->formValue($data['is_archive'], $_POST['is_archive'])) echo "checked" ?> />
	                    <label for="is_archive">Si cette option est activée, la newsletter sera lisible par tout le monde depuis le site internet</label>
	                </td>
	            </tr>
	        </table>
	    </form>

    </div>


</div>

<?php include(COREINC.'/end.php'); ?>

<script>
    function save() {
        $("#data").submit();
    }

    $(document).ready(function() {

    });

</script>
</body></html>