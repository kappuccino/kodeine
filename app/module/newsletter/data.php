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
			'newsletterName' 			=> array('value' => $_POST['newsletterName'], 		'check' => '.'),
			'newsletterTitle' 			=> array('value' => $_POST['newsletterTitle'], 		'check' => '.'),
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
	
			header("Location: data?id_newsletter=".$app->apiLoad('newsletter')->id_newsletter.'&message='.urlencode($message));

		}else{
			$message = 'KO: Merci de remplir les champs correctement';
		}
	}

	if($_REQUEST['id_newsletter'] != NULL){
		$data = $app->apiLoad('newsletter')->newsletterGet(array(
			'id_newsletter' 	=> $_REQUEST['id_newsletter']
		));
		
		if($data['newsletterHtmlDesigner'] != '') header("Location: ./data-designer?id_newsletter=".$_REQUEST['id_newsletter']);
	}else{
		$title = 'Nouvelle newsletter';
	}

?><!DOCTYPE html>
<head>
	<title>Kodeine</title>
	<?php include(COREINC.'/head.php'); ?>
	
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/newsletter.css" /> 
</head>

<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div class="inject-subnav-right hide">
				
				
	<?php if($data['newsletterSendDate'] != NULL){ ?>
	<li><a href="analytic?id_newsletter=<?php echo $_REQUEST['id_newsletter'] ?>" class="btn btn-small">Consulter les statistiques</a></li>
	<li><a href="data" class="btn btn-mini">Nouveau</a></li>
	<?php } ?>
	<?php if($_REQUEST['id_newsletter'] > 0){ ?>
	<li><a href="preview?id_newsletter=<?php echo $_REQUEST['id_newsletter'] ?>" class="btn btn-small" target="_blank">Prévisualiser</a></li>
	<?php } ?>
	<?php if($data['newsletterSendDate'] == NULL){ ?>
	<li><a href="javascript:$('#do').val('test');$('#data').submit();" class="btn btn-small">Tester la newsletter</a></li>
	<li><a href="javascript:$('#do').val('list');$('#data').submit();" class="btn btn-small btn-success">Enregistrer et sélectionner les abonnés</a></li>
	<?php } ?>
	<li><a href="javascript:$('#data').submit();" class="btn btn-small btn-success">Enregistrer</a></li>
</div>

<div id="app">

<?php	if($message == NULL && $_GET['message'] != NULL) $message = urldecode($_GET['message']);
	if($message != NULL){
		list($class, $message) = $app->helperMessage($message);
		echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
	}
?>

<form action="data" method="post" id="data" enctype="multipart/form-data">

	<input type="hidden" name="action" value="1" />
	<input type="hidden" name="id_newsletter" value="<?php echo $data['id_newsletter'] ?>" />
	<input type="hidden" name="do" id="do" value="" />

	<table cellpadding="5" width="100%">
		<tr>
			<td width="100">Nom</td>
			<td><input type="text" name="newsletterName" value="<?php echo $app->formValue($data['newsletterName'], $_POST['newsletterName']); ?>" style="width:96%" /></td>	
		</tr>
		<tr>
			<td>Titre du mail</td>
			<td><input type="text" name="newsletterTitle" value="<?php echo $app->formValue($data['newsletterTitle'], $_POST['newsletterTitle']); ?>" style="width:96%" /></td>	
		</tr>
		<tr>
			<td colspan="2">
				<table width="100%" cellpadding="0" cellspacing="0">
					<tr valign="top">
						<td width="30%">
							<span class="heading">Style</span>
							<table class="dest desttab" width="98%">
								<tr>
									<td width="25%">Couleur de fond</td>
									<td><input type="text" name="style[backgroundColor]" id="backgroundColor" value="<?php echo $app->formValue($data['newsletterStyle']['backgroundColor'], $_POST['style']['backgroundColor']); ?>" style="width:75%" /> <i>exa ou rgb()</i></td>	
								</tr>
								<tr>
									<td>Image de fond</td>
									<td>
										<input type="text" name="style[backgroundImage]" id="backgroundImage" value="<?php echo $app->formValue($data['newsletterStyle']['backgroundImage'], $_POST['style']['backgroundImage']); ?>" style="width:75%" />
										<a href="#" onclick="mediaOpen('line', 'backgroundImage')">choisir</a>
									</td>	
								</tr>
							</table>
						</td>
						<td width="30%">
							<span class="heading">Contenu</span>
							<table class="dest desttab" width="100%">
								<tr>
									<td>Importer un fichier HTML depuis votre ordinateur<br /><input type="file" name="upFile" /></td>
								</tr>
								<?php $template = $app->apiLoad('newsletter')->newsletterTemplateGet(); if(sizeof($template) > 0){ ?>
								<tr>
									<td>Utiliser un gabarit :
										<select onChange="loadTemplate($(this))"><?php
											echo "<option></option>";
											foreach($template as $e){
												echo "<option value=\"".$e['id_newslettertemplate']."\">".$e['templateName']."</option>";
											}
										?></select>
									</td>
								</tr>
								<?php } ?>
							</table>
						</td>
						<td width="30%">
							<div style="float:right; width:98%;">
							<span class="heading">Champs personnalisés</span>
							<table class="dest desttab" width="98%" style="margin:0px 0px 20px 0px;">
								<tr>
									<td>Insérer ces variables dans le corps du mail : <br /><?php
									
										$field = $app->apiLoad('field')->fieldGet(array(
											'user'	=> true	
										));
			
										$tmp[] = '&lt;webversion&gt;...&lt;/webversion&gt;, &lt;unsubscribe&gt;...&lt;/unsubscribe&gt;, {id_user}, {userMail}, {userToken}';
										/*if(sizeof($field) > 0){
											foreach($field as $e){
												$tmp[] = '{'.$e['fieldKey'].'}';
											}
										}*/
			
										echo implode(', ', $tmp);

									?></td>
								</tr>
							</table>
							</div>
						</td>
					</tr>
				</table>


			</td>
		</tr>
		<tr>
			<td colspan="2">
				<a href="javascript:toggleEditor('newsletterHtml');" class="btn btn-mini">Activer/Désactiver l'éditeur de texte</a>

				<textarea name="newsletterHtml" id="newsletterHtml" style="height:900px; width:100%;"><?php
					$html = $app->formValue($data['newsletterHtml'], $_POST['newsletterHtml']);
					if(preg_match("#<head>#", $html) OR trim($html) == '') $plugins = 'fullpage,';
					echo $html;
				?></textarea>

			</td>
		</tr>
	</table>

</form>


</div>

<?php include(COREINC.'/end.php'); ?>
<script src="/admin/core/ui/_tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
<script src="/admin/core/ui/_tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script>
//    tinymce.PluginManager.load('name', 'url');

	tinyMCE.init({
		mode								: 'exact',
		elements							: 'newsletterHtml',
		theme								: 'advanced',
		plugins								: '<?php echo $plugins ?>safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras',
        remove_script_host					: true,
        convert_urls 						: false,
		theme_advanced_buttons1 			: 'mybutton,code,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect',
		theme_advanced_buttons2 			: 'cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,|,insertdate,inserttime,preview,|,forecolor,backcolor',
		theme_advanced_buttons3 			: 'tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak',
		theme_advanced_toolbar_location		: 'top',
		theme_advanced_toolbar_align		: 'center',
		theme_advanced_statusbar_location	: 'bottom',
		theme_advanced_resizing				: false,

		apply_source_formatting				: false,
        verify_html 						: false,
        convert_fonts_to_spans				: true,
        forced_root_block 					: 'div',
		extended_valid_elements				: 'webversion,unsubscribe,currentday,currentdayname,currentmonth,currentmonthname,currentyear',
		setup 								: function(ed) {
		    ed.addButton('mybutton', {
		        title : 'Insérer des images',
		        image : '/admin/core/ui/img/_img/myb.gif',
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

	function loadTemplate(menu){
		id = menu[0].options[menu.selectedIndex].value;

		if(id != null && confirm("Voulez vous remplacer le contenu et le style existant par le gabarit selectionné ?")){
			var ed = tinyMCE.get('newsletterHtml');
			ed.setProgressState(1);
			
			$.ajax({
				url : 'helper/template?id_newslettertemplate='+id,
				dataType : 'json'
			}).done(function(d) {
				ed.setProgressState(0);
				ed.setContent(d.templateData);
				
				if(typeof d.templateStyle == 'object'){
					$('#backgroundColor').val() = d.templateStyle.backgroundColor;
					$('#backgroundImage').val() = d.templateStyle.backgroundImage;
				}
			});
		}

		menu.selectedIndex = 0;
	}
	
</script>
</body></html>