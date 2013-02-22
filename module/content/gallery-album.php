<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	if($_POST['action']){
		$do = true;

		if(!$_POST['contentDateStartDo'])	$_POST['contentDateStart'] == NULL;
		if(!$_POST['contentDateEndDo']) 	$_POST['contentDateEnd'] == NULL;

		$_POST['contentDateCreation']	= implode(' ', $_POST['contentDateCreation']);
		$_POST['contentDateUpdate']		= implode(' ', $_POST['contentDateUpdate']);

		// Core
		$def['k_content'] = array(
			'is_album'				=> array('value' => 1),
			'contentSee'			=> array('value' => $_POST['contentSee'], 				'zero' => true),
			'contentTemplate'		=> array('value' => $_POST['contentTemplate']),
			'contentDateCreation'	=> array('value' => $_POST['contentDateCreation']),
			'contentDateUpdate'		=> array('value' => $_POST['contentDateUpdate']),
			'contentDateStart'		=> array('value' => $_POST['contentDateStart'],			'null' => true),
			'contentDateEnd'		=> array('value' => $_POST['contentDateEnd'],			'null' => true),
		);
		if(!$app->formValidation($def)) $do = false;
		
		// Album
		// Ligne suivante = soucis en cas de update non ?
		// $last = $app->dbOne("SELECT MAX(contentAlbumPos) AS h FROM k_contentalbum WHERE id_album=".$_POST['id_album']);
		$album['k_contentalbum'] = array(
			'id_album'				=> array('value' => $_POST['id_album']),
			//'contentAlbumPos'		=> array('value' => ($last['h'] + 1))
		);

		// Data
		$dat['k_contentdata'] = array(
			'contentUrl'			=> array('value' => $_POST['contentUrl'], 				'check' => '.'),
			'contentName' 			=> array('value' => $_POST['contentName'], 				'check' => '.')
		);
		if(!$app->formValidation($dat)) $do = false;

		// Field
		if(!$app->apiLoad('field')->fieldValidation($_POST['field'])) $do = false;

		if($do){
			$result = $app->apiLoad('content')->contentSet(array(
				'id_type'		=> $_POST['id_type'],
				'language'		=> $_POST['language'],
				'id_content'	=> $_POST['id_content'],
				'debug'			=> false,

				// Les donnees
				'def'			=> $def,
				'data'			=> $dat,
				'album'			=> $album,
				'field'			=> $_POST['field'],

				// Association
				'id_group'		=> $_POST['id_group'],
				'id_chapter'	=> $_POST['id_chapter'],
				'id_category'	=> $_POST['id_category'],
				'id_search'		=> $_POST['id_search']
			));

			$message = ($result) ? 'OK: Enregistrement' : 'KO: Erreur APP:<br />'.$app->apiLoad('content')->db_error;

			if($result){
				$id_content = $app->apiLoad('content')->id_content;

				if(isset($_POST['removeSync']) && $_POST['removeSync'] == '0'){
					$app->dbQuery("UPDATE k_contentalbum SET contentAlbumSyncFolder='' WHERE id_content=".$id_content);
				}

				header("Location: gallery-album?id_content=".$id_content.'&message='.$message);
			}

		}else{
			$message = 'WA: Validation failed';
		}
	}

	if($_REQUEST['id_content'] != NULL){
	
		$data = $app->apiLoad('content')->contentGet(array(
			'id_content' 	=> $_REQUEST['id_content'],
			'language'		=> 'fr',
			'raw'			=> true,
			'debug'	 		=> false
		));
		
		$type		= $app->apiLoad('type')->typeGet(array('id_type' => $data['id_type']));
		$title		= $data['contentName'];

		$tpl		= ($data['contentTemplate'] != NULL) ? $data['contentTemplate'] : $type['typeTemplate'];
		$opt		= $app->apiLoad('template')->templateInfoGet($tpl);
		$id_album	= $data['id_album'];
	}else{
		$type		= $app->apiLoad('type')->typeGet(array('id_type' => $_REQUEST['id_type']));
		$title 		= "Nouvel album";
		$id_album	= ($_REQUEST['id_album']) ? $_REQUEST['id_album'] : 0;
	}

	$albums = $app->apiLoad('content')->contentGet(array(
		'id_type'		=> $type['id_type'],
		'id_album'		=> $id_album,
		'is_album'		=> true,
		'raw'			=> true,
		'debug'			=> false,
		'noLimit'		=> true
	));
	
	for($i=0; $i<sizeof($albums); $i++){
		if($albums[$i]['id_content'] == $data['id_content']){
			$previous = $albums[$i-1];
			if($previous['id_content'] == NULL) unset($previous);

			$next = $albums[$i+1];
			if($next['id_content'] == NULL) unset($next);
		}
	}

	if(is_array($previous)){
		$leftLink = "gallery-album?id_content=".$previous['id_content'];
	}

	if(is_array($next)){
		$rightLink = "gallery-album?id_content=".$next['id_content'];
	}


	$fields = $app->apiLoad('field')->fieldGet(array(
		'id_type'		=> $type['id_type'],
		'albumField'	=> true,
		'fieldShowForm'	=> true,
		'debug'			=> false
	));

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
	<link rel="stylesheet" type="text/css" href="ui/css/gallery.css">
	<link rel="stylesheet" type="text/css" href="../media/ui/css/media.css">
    <link rel="stylesheet" type="text/css" href="../content/ui/css/data.css" />
	<link rel="stylesheet" type="text/css" href="../core/vendor/datepicker/css/datepicker.css" />
	<link rel="stylesheet" type="text/css" href="../core/vendor/codemirror/lib/codemirror.css" />
	<link rel="stylesheet" type="text/css" href="../core/vendor/codemirror/theme/monokai.css" />
</head>
<body>
	
<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php');
?></header>

<div class="inject-subnav-right hide">
    <li><a onclick="$('#data').submit()" class="btn btn-small btn-success">Enregistrer</a></li>
	<?php if($data['id_content'] > 0){ ?>
	<li><a href="gallery?id_type=<?php echo $data['id_type'] ?>#<?php echo $data['id_content'] ?>" class="button button-blue">Afficher l'album en image</a></li>
	<?php } ?>
</div>

<div id="app">
	<div class="wrapper">

		<?php
			if(isset($_GET['message'])) $message = urldecode($_GET['message']);
			if($message != NULL){
				list($class, $message) = $app->helperMessage($message);
				echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
			}
		?>

		<table width="100%" border="0" cellpadding="0" cellspacing="2" id="gCarrousel">
			<tr>
				<td class="previous"><a href="<?php echo ($leftLink  != '') ? $leftLink  : '#'; ?>">&#8592; Album précédente</a></td>
				<td class="current"	>&#8593; <?php

					echo ($data['id_album'] == 0)
						? "<a href=\"gallery?id_type=".$type['id_type']."\">Racine</a>"
						: "<a href=\"gallery?id_type=".$type['id_type']."#".$album['id_content']."\">Album ".$album['contentName']."</a>";

				?></td>
				<td width="25%" class="next"><a href="<?php echo ($rightLink != '') ? $rightLink : '#'; ?>">Album suivant &#8594;</a></td>
			</tr>
			<tr>
				<td class="previous"><?php

					echo ($leftLink != '')
						? "<a href=\"".$leftLink."\">".$previous['contentName']."</a>"
						: "<span id=\"leftDeadEnd\" style=\"padding:5px;\">Vous êtes au debut de l'album</span>";

				?>&nbsp;</td>
				<td class="current" align="center";>Album courant: <?php echo $data['contentName']  ?></td>
				<td class="next" align="right">&nbsp;<?php

					echo ($rightLink != '')
						? "<a href=\"".$rightLink."\">".$next['contentName']."</a>"
						: "<span id=\"rightDeadEnd\" style=\"padding:5px;\">Vous êtes a la fin de l'album</span>";

				?></td>
			</tr>
		</table>
    </div>

	<form action="gallery-album" method="post" id="data">
	
		<input type="hidden" name="action" value="1" />
		<input type="hidden" name="id_type" value="<?php echo $type['id_type'] ?>" />
		<input type="hidden" name="id_content" id="id_content" value="<?php echo $data['id_content'] ?>" />
		<input type="hidden" name="language" value="fr" />
		<input type="hidden" name="id_album" value="<?php echo $id_album; ?>" />
	
		<div class="tabset">
			<div class="view view-tab">
				<ul class="is-sortable field-list">
					<li class="clearfix form-item">
						<div class="hand">&nbsp;</div>
						<div class="toggle toggle-hidden">&nbsp;</div>
						
						<span class="<?php echo $app->formError('contentName', 'needToBeFilled') ?> clearfix">
							<label>Nom</label>
							<div class="form">
								<input type="text" name="contentName" id="contentNameField" value="<?php echo $app->formValue($data['contentName'], $_POST['contentName']); ?>" size="100" style="width:99%;" />
							</div>
						</span>
	
						<div class="spacer">&nbsp;</div>
	
						<span class="<?php echo $app->formError('contentUrl', 'needToBeFilled') ?>">
							<label>Url de l'album</label>
							<div class="form">
								<input type="text" name="contentUrl" id="urlField" value="<?php echo $app->formValue($data['contentUrl'], $_POST['contentUrl']); ?>" size="100" style="width:99%;" />
							</div>
						</span>
					</li>
	
					<li class="clearfix form-item">
						<div class="hand">&nbsp;</div>
						<div class="toggle toggle-hidden">&nbsp;</div>
						<label>Visibilité</label>
						<div class="form">
							<input type="checkbox" name="contentSee" id="contentSee" value="1" <?php if($app->formValue($data['contentSee'], $_POST['contentSee'])) echo "checked"; ?> />
							Indique que cet album est visible sur le site
						</div>
					</li>
	
					<?php if($data['contentAlbumSyncFolder'] != ''){ ?>
					<li class="clearfix form-item">
						<div class="hand">&nbsp;</div>
						<div class="toggle toggle-hidden">&nbsp;</div>
						<label>Synchro</label>
						<div class="form">
							<input type="hidden" 	name="removeSync" value="0" />
							<input type="checkbox"	name="removeSync" value="1" checked="checked" />
							Cet album est synchronisé avec le dossier <b><?php echo $data['contentAlbumSyncFolder'] ?></b>
						</div>
					</li>
					<? } ?>
	
					<li class="clearfix form-item">
						<div class="hand">&nbsp;</div>
						<div class="toggle toggle-hidden">&nbsp;</div>
						<label>Template</label>
						<div class="form">
							<select name="contentTemplate">
								<option value="">Utiliser la template par defaut</option><?php
								foreach($app->fsFolder(TEMPLATE, '', FLAT) as $e){
									$e	 = basename($e);
									$sel = ($app->formValue($data['contentTemplate'], $_POST['contentTemplate']) == $e) ? ' selected' : NULL;
									echo "<option value=\"".$e."\"".$sel.">".$e."</option>\n";
								}
							?></select>
						</div>
					</li>
	
					<li class="clearfix form-item">
						<div class="hand">&nbsp;</div>
						<div class="toggle toggle-hidden">&nbsp;</div>
						<label>Liaisons</label>
						<div class="form">
							<div style="width:25%;" class="panelItem">
								<span class="panelLabel">
									Arborescence
								</span>
								<div class="panelBody" style="width:95%;"><?php echo 
									$app->apiLoad('chapter')->chapterSelector(array(
										'name'		=> 'id_chapter[]',
										'id'		=> 'id_chapter',
										'multi' 	=> true,
										'style' 	=> 'width:100%; height:200px',
										'profile'	=> true,
										'value'		=> $app->formValue($data['id_chapter'], $_POST['id_chapter'])
									));
								?></div>
							</div>
							<div style="width:25%;" class="panelItem">
								<span class="panelLabel">
									Cat&eacute;gories
								</span>
								<div class="panelBody" style="width:95%;"><?php echo 
									$app->apiLoad('category')->categorySelector(array(
										'name'		=> 'id_category[]',
										'id'		=> 'id_category',
										'multi' 	=> true,
										'style' 	=> 'width:100%; height:200px',
										'profile'	=> true,
										'language'	=> 'fr',
										'value'		=> $app->formValue($data['id_category'], $_POST['id_category'])
									));
								?></div>
							</div>
							<?php if(!$type['is_business']){ ?>
							<div style="width:25%;" class="panelItem">
								<span class="panelLabel">
									Groupes
								</span>
								<div class="panelBody" style="width:95%;"><?php echo 
									$app->apiLoad('user')->userGroupSelector(array(
										'name'		=> 'id_group[]',
										'id'		=> 'id_group',
										'multi' 	=> true,
										'style' 	=> 'width:100%; height:200px',
										'profile'	=> true,
										'value'		=> $app->formValue($data['id_group'], $_POST['id_group'])
									));
								?></div>
							</div>
							<?php } ?>
							<div style="width:25%;" class="panelItem">
								<span class="panelLabel">
									Groupes intelligents
								</span>
								<div class="panelBody" style="width:95%;"><?php echo 
									$app->searchSelector(array(
										'name'		=> 'id_search[]',
										'id'		=> 'id_search',
										'searchType'=> 'user',
										'multi' 	=> true,
										'style' 	=> 'width:100%; height:200px',
										'value'		=> $app->formValue($data['id_search'], $_POST['id_search'])
									));
								?></div>
							</div>
						</div>
					</li>
	
					<li id="contentDate" class="clearfix form-item">
						<div class="hand">&nbsp;</div>
						<div class="toggle toggle-hidden">&nbsp;</div>
						<label>Dates</label>
						<div class="form">
							<table>
								<tr>
									<td width="80 ">Creation</td>
									<td width="200">
										<?php
											$v = $app->formValue($data['contentDateCreation'], $_POST['contentDateCreation']);
											if(!is_array($v)) $v = explode(' ', $v);
										?>
										<input type="text" name="contentDateCreation[0]" id="contentDateCreation" value="<?php echo $v[0] ?>" size="12" style="text-align:center;" />
										<input type="text" name="contentDateCreation[1]" 						  value="<?php echo $v[1] ?>" size="7"  style="text-align:center;" />
									</td>
									<td width="50">Debut</td>
									<td width="200">
										<?php $v = $app->formValue($data['contentDateStart'], $_POST['contentDateStart']); ?>
										<input type="checkbox" name="contentDateStartDo" id="contentDateStartDo" value="1" <?php if($v != '') echo "checked" ?> />
										<input type="text" name="contentDateStart" id="contentDateStart" value="<?php echo $v ?>" size="12" style="text-align:center;" />
									</td>
								</tr>
								<tr>
									<td>Mise a jour</td>
									<td>
										<?php
											$v = $app->formValue($data['contentDateUpdate'], $_POST['contentDateUpdate']);
											if(!is_array($v)) $v = explode(' ', $v);
										?>
										<input type="text" name="contentDateUpdate[0]" id="contentDateUpdate" value="<?php echo $v[0] ?>" size="12" style="text-align:center;" />
										<input type="text" name="contentDateUpdate[1]" 						  value="<?php echo $v[1] ?>" size="7"  style="text-align:center;" />
									<td>Fin</td>
									<td>
										<?php $v = $app->formValue($data['contentDateEnd'], $_POST['contentDateEnd']); ?>
										<input type="checkbox" name="contentDateEndDo" id="contentDateEndDo" value="1" <?php if($v != '') echo "checked" ?> />
										<input type="text" name="contentDateEnd" id="contentDateEnd" value="<?php echo $v ?>" size="12" style="text-align:center;" />
									</td>
								</tr>
							</table>
						</div>
					</li>
	
					<?php
						foreach($fields as $e){
							$app->apiLoad('field')->fieldTrace($data, $e, $f);
						//	fieldTrace($app, $data, $e, $f);
						}
					?>
	
				</ul>
			</div>
		</div>
	
	</form>
	
</div>

</body>

<?php include(COREINC.'/end.php'); ?>
<script src="../core/vendor/tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
<script src="../core/vendor/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script src="ui/js/content.js"></script>
<script>

	actionNav		= true;
	language		= '<?php echo $data['language'] ?>';
	doMove  		= false;
	useEditor		= true;
	replace			= [];
	textarea		= "<?php echo @implode(',', $GLOBALS['textarea']) ?>";
	MceStyleFormats = [<?php echo @file_get_contents(USER.'/config/tinymceStyleFormats.php') ?>];

	$(document).ready(function(){
		boot();
		checkNeedToBeFilled();

		$('#data input').bind({
			'focus' : function(){
				actionNav = false;
			},
			'blur' : function(){
				actionNav = true;
			}
		});
	});

	$(window).bind({
		'keydown' : function(e){
			if(actionNav){
				if(e.keyCode == 37){ // left
					link = '<?php echo $leftLink ?>';
					if(link != '') document.location=link;
				}else
				if(e.keyCode == 39){ // right
					link = '<?php echo $rightLink ?>';
					if(link != '') document.location=link;
				}else
				if(e.keyCode == 38){ // up
					document.location='gallery?id_type=<?php echo $type['id_type'] ?>#<?php echo $album['id_content'] ?>';
				}
			}
		}
	});

</script>

</html>