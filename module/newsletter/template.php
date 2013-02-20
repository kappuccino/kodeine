<?php
	if(sizeof($_POST['remove']) > 0){
		foreach($_POST['remove'] as $e){
			$app->apiLoad('newsletter')->newsletterTemplateRemove($e);
		}
		header("Location: template");
	}else
	if($_POST['action']){
		$do = true;
	
		if($_SESSION['upFile'] == NULL) $_SESSION['upFile'] = uniqid();
	
		$up = USER.'/'.$_SESSION['upFile'];
		if(file_exists($up)) unlink($up);
		umask(0);
	
		# Si le fichier est bien deplace dans le bon dossier
		if(move_uploaded_file($_FILES['upFile']['tmp_name'], $up)){
			$_POST['templateData'] = file_get_contents($up);
			unlink($up);
		}
		
		$templateStyle = json_encode($_POST['style']);
		
		$def['k_newslettertemplate'] = array(
			'templateName' 	=> array('value' => $_POST['templateName'], 'check' => '.'),
			'templateData' 	=> array('value' => $_POST['templateData'], 'check' => '.'),
			'templateStyle'	=> array('value' => $templateStyle)
		);
		if(!$app->formValidation($def)) $do = false;

		if($do){
			$result = $app->apiLoad('newsletter')->newsletterTemplateSet($_POST['id_newslettertemplate'], $def);
			$message = ($result)
				? 'OK: Enregistrement dans la base'
				: 'KO: Une erreur est survenue, APP:<br />'.$app->db_error;
		}else{
			$message = 'KO: Validation failed';
		}
	}

	if($_REQUEST['id_newslettertemplate'] != NULL){
		$data = $app->apiLoad('newsletter')->newsletterTemplateGet(array(
			'id_newslettertemplate'	=> $_REQUEST['id_newslettertemplate'],
			'debug'					=> false
		));
	}

	$type = $app->apiLoad('newsletter')->newsletterTemplateGet(array('debug' => false));
?><!DOCTYPE html>
<head>
	<?php include(COREINC.'/head.php'); ?>
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/newsletter.css" /> 
</head>
<body>
	
<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div id="app"><div class="wrapper">

<div>

	<form action="template" method="post" id="listing">
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing">
		<thead>
			<tr>
				<th width="30" class="icone"><i class="icon-remove icon-white"></i></th>
				<th>Gabarit</th>
				<th width="100">
					<input type="text" class="field roundTextInput roundSearchInput" onkeyup="recherche($(this))" onkeydown="recherche($(this))" size="10" style="float:right;" />
				</th>
			</tr>
		</thead>
		<tbody><?php
		if(sizeof($type) > 0){
			foreach($type as $e){ ?>
			<tr class="<?php if($e['id_newslettertemplate'] == $_REQUEST['id_newslettertemplate']) echo "selected" ?>">
				<td><input type="checkbox" name="remove[]" value="<?php echo $e['id_newslettertemplate'] ?>" class="cb" /></td>
				<td colspan="2"><a href="template?id_newslettertemplate=<?php echo $e['id_newslettertemplate'] ?>" class="sniff"><?php echo $e['templateName'] ?></a></td>
			</tr>
			<?php }
		}else{ ?>
			<tr>
				<td colspan="3" style="text-align:center; padding:30px 0px 30px 0px; font-weight:bold;">Aucun gabarit existant.</td>
			</tr>
		<?php } ?>
		</tbody>
		<tfoot>
			<?php if(sizeof($type) > 0){ ?>
			<tr>
				<td width="30" height="25"><input type="checkbox" onchange="$$('.cb').set('checked', this.checked);" /></td>
				<td colspan="3"><a href="#" onClick="applyRemove();" class="btn btn-mini">Supprimer la selection</a></td>
			</tr>
			<?php }else{ ?>
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>
			<?php } ?>
		</tfoot>
	</table>
	</form>
</div>
	
<div style="float:right; width:70%;">
	<?php
		if($message != NULL){
			list($class, $message) = $app->helperMessage($message);
			echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
		}
	?>

	<a href="javascript:$('#data').submit();" class="btn btn-mini">Enregistrer</a>
	<a href="template" class="btn btn-mini">Nouveau</a>


	<form action="template" method="post" id="data" enctype="multipart/form-data">
	<input type="hidden" name="action" value="1" />
	<input type="hidden" name="id_newslettertemplate" value="<?php echo $data['id_newslettertemplate'] ?>" />
	<table cellpadding="3" border="0" width="100%">
		<tr>
			<td width="75">Nom</td>
			<td><input type="text" name="templateName" value="<?php echo $app->formValue($data['templateName'], $_POST['templateName']); ?>"  style="width:90%;" /></td>
		</tr>
		<tr>
			<td>Importer</td>
			<td><input type="file" name="upFile" /></td>
		</tr>
		<tr valign="top">
			<td colspan="2">
				<table class="dest" width="99%">
					<tr>
						<td colspan="2"><b>Styles</b></td>
					</tr>
					<tr>
						<td width="100">Couleur de fond</td>
						<td><input type="text" name="style[backgroundColor]" value="<?php echo $app->formValue($data['templateStyle']['backgroundColor'], $_POST['style']['backgroundColor']); ?>" style="width:100px" /> Exemple blanc : #FFFFF ou rgb(255,255,255)</td>	
					</tr>
					<tr>
						<td>Image de fond</td>
						<td>
							<input type="text" name="style[backgroundImage]" id="backgroundImage" value="<?php echo $app->formValue($data['templateStyle']['backgroundImage'], $_POST['style']['backgroundImage']); ?>" style="width:300px" />
							<a href="#" onclick="mediaOpen('line', 'backgroundImage')" class="btn btn-mini">Choisir une image</a>
						</td>	
					</tr>
				</table>
			</td>
		</tr>
		<tr valign="middle">
			<td colspan="2">
				<table class="dest" width="99%">
					<tr>
						<td><b>Champs personnalisés</b></td>
					</tr>
					<tr>
						<td>Insérer ces variables dans le gabarit : <?php
						
							$field = $app->apiLoad('field')->fieldGet(array(
								'user'	=> true	
							));

							$tmp[] = ' {read}, {unsubscribe}, {id_user}, {userMail}, {userToken}';

							if(sizeof($field) > 0){
								foreach($field as $e){
									$tmp[] = '{'.$e['fieldKey'].'}';
								}
							}

							echo implode(', ', $tmp);
						
						?></td>
					</tr>
				</table>
			</td>
		</tr>

		<tr>
			<td colspan="2" style="padding-top:20px;">
				<a href="javascript:toggleEditor('templateData');" class="btn btn-mini">Activer/Désactiver l'éditeur de texte</a>
				<textarea name="templateData" id="templateData" style="width:99%; height:900px;"><?php
					$data = $app->formValue($data['templateData'], $_POST['templateData']);
					if(preg_match("#<head>#", $data) OR trim($data) == '') $plugins = 'fullpage,';
					echo $data;
				?></textarea>
			</td>
		</tr>
	</table>

</div>


<?php include(COREINC.'/end.php'); ?>
<script src="../core/vendor/tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
<script src="../core/vendor/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script>
	tinyMCE.init({
		mode								: 'exact',
		elements							: 'templateData',
		theme								: 'advanced',
		plugins								: '<?php echo $plugins ?>safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras',
        remove_script_host					: true,
        convert_urls 						: false,
		theme_advanced_buttons1 			: 'mybutton,code,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect',
		theme_advanced_buttons2 			: 'cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,|,insertdate,inserttime,preview,|,forecolor,backcolor',
		theme_advanced_buttons3 			: 'tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen',
		theme_advanced_buttons4 			: 'styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak',
		theme_advanced_toolbar_location		: 'top',
		theme_advanced_toolbar_align		: 'center',
		theme_advanced_statusbar_location	: 'bottom',
		theme_advanced_resizing				: false,

		apply_source_formatting				: false,
        convert_fonts_to_spans				: true,
        forced_root_block 					: 'div',
		style_formats 						: [<?php echo @file_get_contents(USER.'/config/tinymceStyleFormats.php') ?>],
		setup 								: function(ed) {
		    ed.addButton('mybutton', {
		        title : 'Insérer des images',
		        image : 'ressource/img/myb.gif',
		        onclick : function() {
					mediaPicker(ed.id, 'mce');
		        }
		    });
		}
	
	});

	function toggleEditor(id) {
		if (!tinyMCE.getInstanceById(id)){
			tinyMCE.execCommand('mceAddControl', false, id);
		}else{
			tinyMCE.execCommand('mceRemoveControl', false, id);
		}
	}

	function applyRemove(){
		if(confirm("SUPPRIMER ?")){
			$('#listing').submit();
		}
	}

</script>

</div></body></html>