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

	$me = urldecode($_REQUEST['url']);

	if(file_exists(KROOT.$me)){
		$data = $app->mediaDataGet($me);
	}else{
		die("Element introuvable");
	}

?><!DOCTYPE html>
<html lang="fr">
<head>
	<title></title>
	<style>
		body{
			color:#444;
			padding: 0px;
			margin: 0px;
			font-family: Arial;
			font-size: 12px;
		}
		.btn {
			display: inline-block;
			padding: 4px 10px 4px;
			margin-bottom: 0;
			font-size: 13px;
			line-height: 18px;
			color: #333;
			text-align: center;
			text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75);
			vertical-align: middle;
			cursor: pointer;
			background-color: whiteSmoke;
			background-image: -moz-linear-gradient(top, white, #E6E6E6);
			background-image: -ms-linear-gradient(top, white, #E6E6E6);
			background-image: -webkit-gradient(linear, 0 0, 0 100%, from(white), to(#E6E6E6));
			background-image: -webkit-linear-gradient(top, white, #E6E6E6);
			background-image: -o-linear-gradient(top, white, #E6E6E6);
			background-image: linear-gradient(top, white, #E6E6E6);
			background-repeat: repeat-x;
			filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#e6e6e6', GradientType=0);
			border-color: #E6E6E6 #E6E6E6 #BFBFBF;
			border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
			filter: progid:DXImageTransform.Microsoft.gradient(enabled = false);
			border: 1px solid #CCC;
			border-bottom-color: #B3B3B3;
			-webkit-border-radius: 4px;
			-moz-border-radius: 4px;
			border-radius: 4px;
			border-radius: 4px;
			-moz-border-radius: 4px;
			-webkit-border-radius: 4px;
			-khtml-border-radius: 4px;
			border-radius: 4px;
			-webkit-box-shadow: inset 0 1px 0 rgba(255, 255, 255, .2), 0 1px 2px rgba(0, 0, 0, .05);
			-moz-box-shadow: inset 0 1px 0 rgba(255,255,255,.2), 0 1px 2px rgba(0,0,0,.05);
			box-shadow: inset 0 1px 0 rgba(255, 255, 255, .2), 0 1px 2px rgba(0, 0, 0, .05);
			border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
		} 
		a {
			text-decoration: none;
		}
		.btn-mini {
			padding: 2px 6px;
			font-size: 11px;
			line-height: 14px;
		}
		textarea{
			width:99%;
			font-family:Arial;
			font-size:12px;
			padding:3px;
		}
	</style>
	<script src="../../core/vendor/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
</head>
<body>
<form method="post" action="metadata" id="meta" name="meta">

<input type="hidden" name="action" value="sql" />
<input type="hidden" name="url" value="<?php echo $me ?>" />

<table border="0" cellpadding="5" cellspacing="5" width="100%">
	<tr valign="top">
		<td width="50%"><b>Titre</b><br />		<textarea name="mediaTitle" 	rows="7"><?php echo $data['mediaTitle'] ?></textarea></td>
		<td width="50%"><b>Description</b><br /><textarea name="mediaCaption"	rows="7"><?php echo $data['mediaCaption'] ?></textarea></td>
	</tr>
	<tr valign="top">
		<td><i><?php echo basename($_REQUEST['url']); if($me != $_REQUEST['url']) echo " <i>(".$me.")</i>"; ?></i></td>
		<td align="right">
			<a href="javascript:$('#meta').submit();" class="btn btn-mini">Enregistrer</a> ou
			<a href="javascript:parent.modalHideUpload();" class="btn btn-mini">Fermer</a>
		</td>
	</tr>
</table>
</form>

</body></html>