<?php

	if($_POST['action'] == 'sql'){
		$do = true;

		$def['k_media'] = array(
			'mediaUrl'		=> array('value' => $_POST['url']),
			'mediaTitle'	=> array('value' => $_POST['mediaTitle']),
			'mediaCaption'	=> array('value' => $_POST['mediaCaption'])
		);

		if(!$app->formValidation($def)) $do = false;

		if($do){
			$result = $app->mediaDataSet($_POST['url'], $def);
		}
	}

?><!DOCTYPE html>
<html lang="fr">
<head>
	<style>
		body{
			color:#000;
			padding:2px;
			margin: 0px;
			font-family: Arial;
			font-size: 12px;
		}
		a{
			color:#000;
			text-decoration: underline;
		}
		textarea{
			width:99%;
			font-family:Arial;
			font-size:12px;
			padding:3px;
		}
	</style>
</head>
<body>
<?php

	if(isset($_GET['off'])){
		echo "&nbsp;";
	}else{
	
		$me = $_REQUEST['url'];
		
		if(!file_exists(KROOT.$me)){
			echo "Cet &eacute;lement n'est plus disponible";
		}else{

			$data  = $app->mediaDataGet($_REQUEST['url']);
			$infos = $app->mediaInfos($me);

	?>

	<form method="post" action="metadata-fromcontent" id="meta" name="meta">

		<input type="hidden" name="action" value="sql" />
		<input type="hidden" name="list" value="<?php echo $_REQUEST['list'] ?>" />
		<input type="hidden" name="url" value="<?php echo $me ?>" />

		<div class="clearfix">
			<div style="float:left; width:49%; margin-right:5px;">	
				<b>Titre</b><br />
				<textarea name="mediaTitle" rows="2"><?php echo $data['mediaTitle'] ?></textarea>
			</div>
	
			<div style="float:left; width:49%;">
				<b>Description</b><br />
				<textarea name="mediaCaption" rows="2"><?php echo $data['mediaCaption'] ?></textarea>
			</div>
		</div>

		<a class="btn btn-mini" onclick="javascript:$('#meta').submit();">Enregistrer</a>
		ou
		<a class="btn btn-mini" onclick="parent.mediaCloseMeta('<?php echo $_REQUEST['list'] ?>');">Annuler</a>

		&nbsp; &nbsp; Fichier: <i><?php echo $_REQUEST['url']; ?></i>

	</form>

<?php } } ?>

<?php include(COREINC.'/end.php'); ?>
</body></html>