<?php
$api	= $app->apiLoad('newsletter');
$pref	= $app->configGet('newsletter');

/*if($_REQUEST['id_newsletter']){
    $rest = new newsletterREST($pref['auth'], $pref['passw']);
    $stat = $rest->request('/controller.php', 'POST', array(
        'analytic' 		=> true,
        'id_newsletter'	=> $_REQUEST['id_newsletter']
    ));
    $stat = json_decode($stat, true);
}*/

if($_FILES['upFile']['type'] == 'text/html'){
    $file = file_get_contents($_FILES['upFile']['tmp_name']);
    unlink($_FILES['upFile']['tmp_name']);
    $_POST['newsletterHtml'] = addslashes($file);
}

if($_POST['action']){
    $do = true;

    $newsletterStyle = json_encode($_POST['style']);

    $def['k_newsletter'] = array(
        'newsletterHtml' 			=> array('value' => $_POST['newsletterHtml']),
        'newsletterStyle'			=> array('value' => $newsletterStyle, 				'null' => true),
    );

    if(!$app->formValidation($def)) $do = $false;

    if($do){
        $result	 = $app->apiLoad('newsletter')->newsletterSet($_POST['id_newsletter'], $def);
        $message = ($result) ? 'OK: Enregistrement' : 'KO: Probleme, APP : <br />'.$app->db_error;

        if($result && $_POST['do'] == 'test'){
            $app->apiLoad('newsletter')->newsletterPreview($_POST['id_newsletter']);
            $message = ($result) ? 'OK: Newsletter enregistré et envoyé en mode [TEST] ('.$pref['test'].')' : 'KO: Erreur Test';
        }else
            if($result && $_POST['do'] == 'list'){
                if($data['newsletterSendDate'] == NULL){
                    header("Location: ./data-list?id_newsletter=".$app->apiLoad('newsletter')->id_newsletter);
                    exit();
                }else{
                    $message = 'KO: Cette newsletter est en cours d\'envois ou bien elle a déjà été envoyé.';
                }
            }

        header("Location: data-editor?id_newsletter=".$app->apiLoad('newsletter')->id_newsletter.'&message='.urlencode($message));

    }else{
        $message = 'KO: Merci de remplir les champs correctement';
    }
}

if($_REQUEST['delete'] != NULL){
	$delete = $app->apiLoad('newsletter')->newsletterTemplateRemove($_REQUEST['delete']);
}

if($_REQUEST['id_newsletter'] != NULL){
    $data = $app->apiLoad('newsletter')->newsletterGet(array(
        'id_newsletter' 	=> $_REQUEST['id_newsletter']
    ));

    if($data['is_designer'] == 1) header("Location: ./data-infos?id_newsletter=".$_REQUEST['id_newsletter']);
}else{
    $title = 'Nouvelle newsletter';
}


$gabarits = $app->apiLoad('newsletter')->newsletterTemplateGet();
//$app->pre($gabarits);

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

<!--<div class="inject-subnav-right hide">
    <?php /*if($data['newsletterSendDate'] != NULL){ */?>
    <li><a href="analytic?id_newsletter=<?php /*echo $_REQUEST['id_newsletter'] */?>" class="btn btn-small">Consulter les statistiques</a></li>
    <li><a href="data" class="btn btn-mini">Nouveau</a></li>
    <?php /*} */?>
    <?php /*if($_REQUEST['id_newsletter'] > 0){ */?>
    <li><a href="preview?id_newsletter=<?php /*echo $_REQUEST['id_newsletter'] */?>" class="btn btn-small" target="_blank">Prévisualiser</a></li>
    <?php /*} */?>
    <?php /*if($data['newsletterSendDate'] == NULL){ */?>
    <li><a href="javascript:$('#do').val('test');$('#data').submit();" class="btn btn-small">Tester la newsletter</a></li>
    <li><a href="javascript:$('#do').val('list');$('#data').submit();" class="btn btn-small btn-success">Enregistrer et sélectionner les abonnés</a></li>
    <?php /*} */?>
    <li><a href="javascript:$('#data').submit();" class="btn btn-small btn-success">Enregistrer</a></li>
</div>-->

<div id="app">

	<div class="col-lg-8" style="margin: 0 auto;display:block;float:none;">
		<div class="alert alert-info"> Sélectionnez un gabarit à envoyer parmis ceux que vous avez généré sur <a target="_blank" href="../newsletter/template">la page d'assemblage de gabarit</a> </div>
		<div class="alert alert-success" id="success" style="display: none"></div>

		<ul class="list-group templateselect">
			<?php
			foreach($gabarits as $g) {
				echo '<li class="list-group-item clearfix">
							<span>'.$g['templateName'].'</span>
							<a class="btn btn-block btn-mini btn-lg btn-danger delete" href="?delete='.$g['id_newslettertemplate'].'">Supprimer</a>
							<a class="btn btn-block btn-lg btn-success select" data-id="'.$g['id_newslettertemplate'].'" href="#">Utiliser ce gabarit</a>
						</li>';
			}
			?>
		</ul>

	</div>

</div>

<?php include(COREINC.'/end.php'); ?>
<script>

	$('.templateselect li a[data-id]').on('click', function() {
		$.ajax({
			url: 'helper/ajax-dsnr-savenewsletter',
			type: 'POST',
			dataType: 'json',
			data: {
				id_newsletter: <?php echo $_REQUEST['id_newsletter']; ?>,
				id_template: $(this).attr('data-id')
			}
		}).done(function(data) {
			if (data.ok) {
				$('#success').html('Le gabarit a été associé a la newletter "'+ data.newsletter.newsletterTitle + '".&nbsp;&nbsp;<a target="_blank" href="preview?id_newsletter=<?php echo $_REQUEST['id_newsletter']; ?>">Previsualiser cette newsletter</a>').css('display', 'block');
			}
			console.log('DONE')
		});
	});

</script>

</body></html>