<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	if($_POST['action']){
	#	die($app->pre($_POST));
		$do = true;

		$def['k_survey'] = array(
			'surveyName'		=> array('value' => $_POST['surveyName'],			'check' => '.'),
			'surveyDescription'	=> array('value' => $_POST['surveyDescription'])
		);

		if(!$app->formValidation($def)) $do = false;

		if($do){
			$result = $app->apiLoad('survey')->surveySet(array(
				'debug'		=> false,
				'id_survey'	=> $_POST['id_survey'],
				'def'		=> $def,
			));

			$message = ($result) ? 'OK: Enregistrement en base' : 'KO: Erreur, APP : <br />'.$app->apiLoad('survey')->db_query.' '.$app->apiLoad('survey')->db_error;
			if($result) header("Location: data?id_survey=".$app->apiLoad('survey')->id_survey);
		}else{
			$message = 'WA: Merci de compléter les champs correctement';
		}
	}

	if($_REQUEST['id_survey'] != NULL){
		$data = $app->apiLoad('survey')->surveyGet(array(
			'id_survey' => $_REQUEST['id_survey'],
		));
		
		$title	= "Modification ".$data['surveyName'];
	}else{
		$title 	= "Nouvelle enquête";
	}

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div id="app"><div class="wrapper">

	<?php
		if($message != NULL){
			list($class, $message) = $app->helperMessage($message);
			echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
		}
	?>
	
	<div style="padding:5px 0px 5px 5px">
		<a href="javascript:$('#data').submit()" class="btn btn-mini">Enregistrer</a>
		<a href="data" class="btn btn-mini">Nouveau</a>
		<?php if($data['id_survey'] > 0){ ?>
		<a href="data?id_survey=<?php echo $data['id_survey'] ?>"  class="btn btn-mini">Recharger la page</a>
		<a href="query?id_survey=<?php echo $data['id_survey'] ?>"  class="btn btn-mini">Gérer les question/réponses</a>
		<a href="stat?id_survey=<?php echo $data['id_survey'] ?>"  class="btn btn-mini">Afficher les stats</a>
		<?php } ?>
	</div>

	<form action="data" method="post" id="data">
	<input type="hidden" name="action" value="1" />
	<input type="hidden" name="id_survey" value="<?php echo $data['id_survey'] ?>" />
	
	<div class="tabset">
		<div class="view clearfix">
		
			<table cellpadding="3" width="100%" border="0">
				<tr>
					<td>Nom</td>
				</tr>
				<tr valign="top">
					<td height="30">
						<input type="text" name="surveyName" value="<?php echo $app->formValue($data['surveyName'], $_POST['surveyName']); ?>" size="40" style="width:99%;" />
					</td>
				</tr>
				<tr>
					<td>Description</td>
				</tr>
				<tr valign="top">
					<td>
						<textarea name="surveyDescription" id="surveyDescription" style="width:99%; height:450px;"><?php echo $app->formValue($data['surveyDescription'], $_POST['surveyDescription']); ?></textarea>
					</td>
				</tr>
	
			</table>
	
		</div>	
	</div>
	</form>

</div></div>	

<?php include(COREINC.'/end.php'); ?>
<script src="../core/vendor/tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
<script src="../core/vendor/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script src="../core/ui/js/common.js"></script>

<script>

	$(function() {
		useEditor = true;
		textarea = 'surveyDescription';
		MceStyleFormats = [<?php echo @file_get_contents(USER.'/config/tinymceStyleFormats.php') ?>];
		buildRichEditor();
	});

	</script>

</body></html>