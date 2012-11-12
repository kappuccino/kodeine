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

		# Search
		if(sizeof($_POST['id_newsletterSearch']) > 0){
			foreach($_POST['id_newsletterSearch'] as $e){
				$newslettersearch[] = $e;
			}
			$newsletterSearch = '@@'.implode('@@', $newslettersearch).'@@';
		}

		# Groupe
		if(sizeof($_POST['id_newsletterGroup']) > 0){
			foreach($_POST['id_newsletterGroup'] as $e){
				$newsletterGroup[] = $e;
			}
			$newsletterGroup = '@@'.implode('@@', $newsletterGroup).'@@';
		}

		# Liste
		if(sizeof($_POST['id_newsletterList']) > 0){
			foreach($_POST['id_newsletterList'] as $e){
				$newsletterList[] = $e;
			}
			$newsletterList = '@@'.implode('@@', $newsletterList).'@@';
		}
		
		$newsletterStyle = json_encode($_POST['style']);
	
		$def['k_newsletter'] = array(
			'is_archive'				=> array('value' => $_POST['is_archive'],			'zero'  => true),
			'newsletterAllUser'			=> array('value' => $_POST['newsletterAllUser'],	'zero'  => true),
			'newsletterName' 			=> array('value' => $_POST['newsletterName'], 		'check' => '.'),
			'newsletterTitle' 			=> array('value' => $_POST['newsletterTitle'], 		'check' => '.'),
			'newsletterHtml' 			=> array('value' => $_POST['newsletterHtml']),
			'newsletterSearch'			=> array('value' => $newsletterSearch),
			'newsletterGroup'			=> array('value' => $newsletterGroup),
			'newsletterList'			=> array('value' => $newsletterList),
			'newsletterListRaw'			=> array('value' => $_POST['newsletterListRaw']),
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
			if($result && $_POST['do'] == 'send'){
				if($data['newsletterSendDate'] == NULL){
					header("Location: helper/push?id_newsletter=".$app->apiLoad('newsletter')->id_newsletter);
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

		$title = $data['newsletterName'];
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
			<td height="30" colspan="2">
				<a href="javascript:$('#data').submit();" class="btn btn-mini">Enregistrer</a>
				<?php if($data['newsletterSendDate'] == NULL){ ?>
				<a href="javascript:$('#do').val()='test';$('#data').submit();" class="btn btn-mini">Enregistrer en envoyer un mail de test</a>
				<a href="javascript:$('#do').val()='send';$('#data').submit();" class="btn btn-mini">Enregistrer en envoyer aux abonnés</a>
				<?php } if($_REQUEST['id_newsletter'] > 0){ ?>
				<a href="preview?id_newsletter=<?php echo $_REQUEST['id_newsletter'] ?>" class="btn btn-mini" target="_blank">Prévisualiser</a>
				<?php } if($data['newsletterSendDate'] != NULL){ ?>
				<a href="analytic?id_newsletter=<?php echo $_REQUEST['id_newsletter'] ?>" class="btn btn-mini">Consulter les statistiques</a>
				<a href="data" class="btn btn-mini">Nouveau</a>
				<?php } ?>
			</td>
		</tr>
		<tr>
			<td width="100">Nom</td>
			<td><input type="text" name="newsletterName" value="<?php echo $app->formValue($data['newsletterName'], $_POST['newsletterName']); ?>" style="width:96%" /></td>	
		</tr>
		<tr>
			<td>Titre du mail</td>
			<td><input type="text" name="newsletterTitle" value="<?php echo $app->formValue($data['newsletterTitle'], $_POST['newsletterTitle']); ?>" style="width:96%" /></td>	
		</tr>
		<tr>
			<td>Archivage</td>
			<td>
				<input type="checkbox" id="is_archive" name="is_archive" value="1" <?php if($app->formValue($data['is_archive'], $_POST['is_archive'])) echo "checked" ?> />
				<label for="is_archive">Si cette option est activée, la newsletter sera lisible par tout le monde depuis le site internet</label>
			</td>
		</tr>
		<tr valign="top">
			<td colspan="2">
				<span class="heading">Destinataires <i id="totalView"></i></span>

				<span style="float:right;">
					<i><label for="newsletterAllUser">Si cette option est activée, cette newsletter sera envoyée à tous les utilisateurs, même ceux qui n'acceptent pas de recevoir de newsletter.</label></i>
					<input type="checkbox" name="newsletterAllUser" id="newsletterAllUser" value="1" <?php if($app->formValue($data['newsletterAllUser'], $_POST['newsletterAllUser'])) echo "checked" ?> />
				</span>

				<table border="0" width="100%" class="dest desttab">
					<tr>
						<!--
						<td width="20%" height="18"><i>Abonnés aux newsletters	<span id="totalType"></span></i></td>
						-->
						<td width="25%"><i>Groupe d'utilisateurs				(<span id="totalGroup"></span>)</i></td>
						<td width="25%"><i>Utilisateur par critères				(<span id="totalSearch"></span>)</i></td>
						<td width="25%"><i>Liste enregistrées					(<span id="totalList"></span>)</i></td>
						<td width="25%"><i>Liste d'emails - un par ligne		(<span id="totalRaw"></span>)</i></td>
					</tr>
					<tr valign="top">
						<td><?php
							$val	= $app->formValue($data['newsletterGroup'], $_POST['id_newsletterGroup']);
							$val	= is_array($val) ? $val : array();

							echo $app->apiLoad('user')->userGroupSelector(array(
								'name'		=> 'id_newsletterGroup[]',
								'id'		=> 'id_newsletterGroup',
								'multi' 	=> true,
								'style' 	=> 'width:100%; height:130px;',
								'profile'	=> true,
								'events'	=> "onchange=\"getTotal()\"",
								'value'		=> $val
							));
						?></td>
						<td><select name="id_newsletterSearch[]" id="id_newsletterSearch" multiple="multiple" style="width:100%; height:130px;" onchange="getTotal()"><?php
							$val	= $app->formValue($data['newsletterSearch'], $_POST['id_newsletterSearch']);
							$val	= is_array($val) ? $val : array();
							$search	= $app->searchGet(array('type' => 'user'));
			
							foreach($search as $e){
								$chk = in_array($e['id_search'], $val) ? ' selected' : NULL;
								echo "<option value=\"".$e['id_search']."\" ".$chk.">".$e['searchName']."</option>";
							}
						?></select></td>
						<td><select name="id_newsletterList[]" id="id_newsletterList" multiple="multiple" style="width:100%; height:130px;" onchange="getTotal()"><?php
							$val	= $app->formValue($data['newsletterList'], $_POST['id_newsletterlist']);
							$val	= is_array($val) ? $val : array();
							$list	= $app->apiLoad('newsletter')->newsletterListGet();
			
							foreach($list as $e){
								$chk = in_array($e['id_newsletterlist'], $val) ? ' selected' : NULL;
								echo "<option value=\"".$e['id_newsletterlist']."\" ".$chk.">".$e['listName']."</option>";
							}
						?></select></td>
						<td>
							<textarea name="newsletterListRaw" id="newsletterListRaw" style="width:97%; height:124px;" onfocus="getTotal()" onblur="getTotal()"><?php
								echo $app->formValue($data['newsletterListRaw'], $_POST['newsletterListRaw'])
							?></textarea>
						</td>
					</tr>
				</table>
			</td>
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
	
	function getTotal(){
		$('#totalType, #totalGroup, #totalSearch, #totalList, #totalRaw').html('...');
		
		url = '?';
		
		group = getSelected($('#id_newsletterGroup'));
		if(group.length > 0) url += '&group=' + group.join(',');

		search = getSelected($('#id_newsletterSearch'));
		if(search.length > 0) url += '&search=' + search.join(',');

		list = getSelected($('#id_newsletterList'));
		if(list.length > 0) url += '&list=' + list.join(',');
			
			var get = $.ajax({
				url : 'helper/total'+url,
				dataType : 'json'
			}).done(function(d) {
				$('#totalGroup').html(d.group);
				$('#totalSearch').html(d.search);
				$('#totalList').html(d.list);
				
				rawLength = $('#newsletterListRaw').val().split(/\n/g).length;
				if(rawLength == 1){
					var str = $.trim($('#newsletterListRaw').val());
					if(str == '') rawLength = 0;
				}

				if($('#newsletterListRaw').val().length == 0){
					$('#totalRaw').html('0');
				}else{
					$('#totalRaw').html(rawLength); // +' (validité des mails non vérifiée)');
				}

				$('totalView').html('('+ (d.total + rawLength) +')');
			});
			
			var getget = $.ajax({
				url : 'helper/total'+url,
				dataType : 'json'
			}).done(function(d) {
				$('#totalGroup').html(d.group);
				$('#totalSearch').html(d.search);
				$('#totalList').html(d.list);
			});
	}
	
	$(function() {
		setTimeout(function() {
			getTotal();
		}, 250);
	});

	function getSelected(opt) {
		var selected = new Array();
		var index = 0;
		for (var intLoop = 0; intLoop < opt.length; intLoop++) {
			if ((opt[intLoop].selected) || (opt[intLoop].checked)) {
				selected.push(opt[intLoop].value);
			}
		}
		return selected;
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