<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	if($_POST['action']){
		$do = true;

	#	if(!$_POST['contentDateStartDo'])	$_POST['contentDateStart'] == NULL;
	#	if(!$_POST['contentDateEndDo']) 	$_POST['contentDateEnd'] == NULL;

	#	$_POST['contentDateCreation']	= implode(' ', $_POST['contentDateCreation']);
	#	$_POST['contentDateUpdate']		= implode(' ', $_POST['contentDateUpdate']);

		// CORE ////////////////////////////////////////////////////////////////////////////////////////////////////////
		$def['k_content'] = array(
			'is_album'				=> array('value' => 1),
			'contentSee'			=> array('value' => $_POST['contentSee'], 				'zero' => true),
			'contentTemplate'		=> array('value' => $_POST['contentTemplate']),
		#	'contentDateCreation'	=> array('value' => $_POST['contentDateCreation']),
		#	'contentDateUpdate'		=> array('value' => $_POST['contentDateUpdate']),
		#	'contentDateStart'		=> array('value' => $_POST['contentDateStart'],			'null' => true),
		#	'contentDateEnd'		=> array('value' => $_POST['contentDateEnd'],			'null' => true),
		);
		if(!$app->formValidation($def)) $do = false;
		
		// ALBUM ///////////////////////////////////////////////////////////////////////////////////////////////////////
		$album['k_contentalbum'] = array(
			'id_album' => array('value' => $_POST['id_album']),
		);

		if(empty($_POST['id_content'])){
			$l = $app->dbOne("SELECT MAX(contentAlbumPos) AS h FROM k_contentalbum WHERE id_album=".$_POST['id_album']);
			$album['k_contentalbum']['contentAlbumPos'] = array('value' => ($l['h'] + 1));
		}

		// DATA ////////////////////////////////////////////////////////////////////////////////////////////////////////
		$dat['k_contentdata'] = array(
			'contentUrl'                => array('value' => $_POST['contentUrl'],               'check' => '.'),
			'contentName'               => array('value' => $_POST['contentName'],              'check' => '.'),
			'contentHeadTitle' 			=> array('value' => $_POST['contentHeadTitle'], 		'null' => true),
			'contentMetaKeywords' 		=> array('value' => $_POST['contentMetaKeywords'], 		'null' => true),
			'contentMetaDescription'	=> array('value' => $_POST['contentMetaDescription'],	'null' => true),
		);
		if(!$app->formValidation($dat)) $do = false;

		// FIELD ///////////////////////////////////////////////////////////////////////////////////////////////////////
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

				$app->go('gallery-album?id_content='.$id_content.'&message='.$message);
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
		$id_album	= $data['id_album'];

	#	$tpl		= ($data['contentTemplate'] != NULL) ? $data['contentTemplate'] : $type['typeTemplate'];
	#	$opt		= $app->apiLoad('template')->templateInfoGet($tpl);
		$language   = $data['language'];
	}else{
		$type		= $app->apiLoad('type')->typeGet(array('id_type' => $_REQUEST['id_type']));
		$title 		= "Nouvel album";
		$id_album	= $_REQUEST['id_album'] ?: 0;
		$language   = 'fr';
	}

	if($id_album > 0){
		$parent = $app->apiLoad('content')->contentGet(array(
			'debug'		 => false,
			'id_type'	 => $type['id_type'],
			'id_content' => $id_album,
			'is_album'	 => true,
			'raw'		 => true,
		));
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

	if(is_array($previous)) $leftLink  = "gallery-album?id_content=".$previous['id_content'];
	if(is_array($next))     $rightLink = "gallery-album?id_content=".$next['id_content'];

	$fields = $app->apiLoad('field')->fieldGet(array(
		'id_type'		=> $type['id_type'],
		'albumField'	=> true,
		'fieldShowForm'	=> true,
		'debug'			=> false
	));

	$useCount 	= 0;
	$usePercent = 100;
	foreach(array('use_group', 'use_search', 'use_chapter', 'use_category', 'use_socialforum') as $use){
		if($type[$use] == '1') $useCount++;
	}

	if($useCount > 0) $usePercent = round(100 / $useCount);

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
	<link rel="stylesheet" type="text/css" href="ui/css/gallery.css">
	<link rel="stylesheet" type="text/css" href="../media/ui/css/media.css">
    <link rel="stylesheet" type="text/css" href="../content/ui/css/data.css">
</head>
<body>
	
<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php');
?></header>

<div class="inject-subnav-right hide">
    <li><a onclick="$('#data').submit()" class="btn btn-small btn-success"><?php echo _('Save'); ?></a></li>
	<?php if($data['id_content'] > 0){ ?>
	<li><a href="gallery?id_type=<?php echo $data['id_type'] ?>#album/<?php echo $data['id_content'] ?>" class="btn btn-small"><?php echo _('Thumbnails view'); ?></a></li>
	<li><a href="gallery-import?id_album=<?php echo $data['id_content'].'&id_type='.$data['id_type'] ?>" class="btn btn-small"><?php echo _('Import'); ?></a></li>
	<?php } ?>
</div>

<div id="app">
	<div class="wrapper">

		<?php
			if(isset($_GET['message'])) $message = urldecode($_GET['message']);
			if($message != NULL){
				list($class, $message) = $app->helperMessage($message);
				echo "<div class=\"message message".ucfirst($class)."\" style=\"margin-top:20px;\">".$message."</div>";
			}
		?>

		<table width="100%" border="0" cellpadding="0" cellspacing="2" id="gCarrousel">
			<thead>
				<tr>
					<td class="previous"><a href="<?php echo ($leftLink  != '') ? $leftLink  : '#'; ?>" id="goToLeft">← <?php echo _('Previous album'); ?></a></td>
					<td class="current"	>↑ <?php
						echo ($id_album == 0)
							? '<a id="goToAlbum" href="gallery?id_type='.$type['id_type'].'">'._('Root').'</a>'
							: '<a id="goToAlbum" href="gallery?id_type='.$type['id_type'].'#album/'.$parent['id_content'].'">Album '.$parent['contentName'].'</a>';
					?></td>
					<td width="25%" class="next"><a href="<?php echo ($rightLink != '') ? $rightLink : '#'; ?>" id="goToRight"><?php echo _('Next album'); ?> →</a></td>
				</tr>
			</thead>
			<tr>
				<td class="previous"><?php
					echo ($leftLink != '')
						? "<a href=\"".$leftLink."\">".$previous['contentName']."</a>"
						: _('No previous album');
				?></td>
				<td class="current" align="center"><?php
					if(isset($data)) printf(_('Current album: %s'), $data['contentName']);
				?></td>
				<td class="next" align="right"><?php
					echo ($rightLink != '')
						? "<a href=\"".$rightLink."\">".$next['contentName']."</a>"
						: _('No more album');
				?></td>
			</tr>
		</table>
    </div>

	<form action="gallery-album" method="post" id="data">
	
		<input type="hidden" name="action"      value="1" />
		<input type="hidden" name="id_type"     value="<?php echo $type['id_type'] ?>" />
		<input type="hidden" name="id_content"  value="<?php echo $data['id_content'] ?>" id="id_content" />
		<input type="hidden" name="language"    value="<?php echo $language; ?>" id="language" />
		<input type="hidden" name="id_album"    value="<?php echo $id_album; ?>" />
	
		<div class="tabset">
			<div class="view view-tab">
				<ul class="is-sortable field-list">

					<li class="clearfix form-item">
						<div class="hand">&nbsp;</div>
						<div class="toggle toggle-hidden">&nbsp;</div>
						
						<span class="<?php echo $app->formError('contentName', 'needToBeFilled') ?> clearfix">
							<label><?php echo _('Name'); ?></label>
							<div class="form">
								<input type="text" class="field" name="contentName" id="contentNameField" value="<?php echo $app->formValue($data['contentName'], $_POST['contentName']); ?>" autocomplete="off" size="100" style="width:99%;" />
							</div>
						</span>

						<div class="spacer"></div>
	
						<span class="<?php echo $app->formError('contentUrl', 'needToBeFilled') ?>">
							<label><?php echo _('Album URL'); ?></label>

							<div class="form clearfix">
								<input type="text" name="contentUrl" id="urlField" class="field" value="<?php echo $app->formValue($data['contentUrl'], $_POST['contentUrl']); ?>" size="100" style="width:75%; float:left;" />
								<div style="float:left; margin-top:2px;">
									<input type="checkbox" id="autogen" value="1" name="contentUrlAuto" onclick="if(this.checked)urlCheck();"  <?php if($app->formValue($data['contentUrlAuto'], $_POST['contentUrlAuto']) || (!isset($data['contentUrlAuto']) && !isset($_POST['contentUrlAuto']))) echo "checked" ?> />
									<?php echo _('Auto generate'); ?>
								</div>
							</div>
						</span>
					</li>

					<li id="contentHeadMeta" class="clearfix form-item">
						<div class="hand"></div>
						<div class="toggle"></div>

						<span class="">
							<label><?php echo _('Title (seo)'); ?></label>
							<div class="form"><input type="text" class="field" name="contentHeadTitle" value="<?php echo $app->formValue($data['contentHeadTitle'], $_POST['contentHeadTitle']); ?>" size="100" style="width:99%;" /></div>
						</span>

						<br style="clear:both" />
						<div class="spacer"></div>

						<span>
							<label class="off"><?php echo _('Key words (seo)'); ?></label>
							<div class="form"><input type="text" name="contentMetaKeywords" class="field" value="<?php echo $app->formValue($data['contentMetaKeywords'], $_POST['contentMetaKeywords']); ?>" size="100" style="width:99%;" /></div>
						</span>

						<br style="clear:both" />
						<div class="spacer"></div>

						<span>
							<label class="off"><?php echo _('Description (seo)'); ?></label>
							<div class="form"><input type="text" name="contentMetaDescription" class="field" value="<?php echo $app->formValue($data['contentMetaDescription'], $_POST['contentMetaDescription']); ?>" size="100" style="width:99%;" /></div>
						</span>
					</li>

					<li class="clearfix form-item">
						<div class="hand">&nbsp;</div>
						<div class="toggle toggle-hidden">&nbsp;</div>
						<label><?php echo _('Visibility'); ?></label>
						<div class="form">
							<input type="checkbox" name="contentSee" id="contentSee" value="1" <?php if($app->formValue($data['contentSee'], $_POST['contentSee'])) echo "checked"; ?> />
							<?php echo _('Front office view'); ?>
						</div>
					</li>
	
					<?php if($data['contentAlbumSyncFolder'] != ''){ ?>
					<li class="clearfix form-item">
						<div class="hand">&nbsp;</div>
						<div class="toggle toggle-hidden">&nbsp;</div>
						<label><?php echo _('Sync'); ?></label>
						<div class="form">
							<input type="hidden" 	name="removeSync" value="0" />
							<input type="checkbox"	name="removeSync" value="1" checked="checked" />
							<?php echo _('This album is synced with folder').' <b>'.$data['contentAlbumSyncFolder'].'</b>' ?>
						</div>
					</li>
					<? } ?>
	
					<li class="clearfix form-item">
						<div class="hand">&nbsp;</div>
						<div class="toggle toggle-hidden">&nbsp;</div>
						<label><?php echo _('Template'); ?></label>
						<div class="form">
							<select name="contentTemplate">
								<option value=""><?php echo _('Use default template'); ?></option><?php
								foreach($app->fsFolder(TEMPLATE, '', 'FLAT') as $e){
									$e	 = basename($e);
									$sel = ($app->formValue($data['contentTemplate'], $_POST['contentTemplate']) == $e) ? ' selected' : NULL;
									echo "<option value=\"".$e."\"".$sel.">".$e."</option>\n";
								}
							?></select>
						</div>
					</li>

					<?php if($useCount > 0){ ?>
					<li class="clearfix form-item">
						<div class="hand">&nbsp;</div>
						<div class="toggle toggle-hidden">&nbsp;</div>
						<label><?php echo _('Relationships'); ?></label>
						<div class="form">

							<?php if($type['use_chapter']){ ?>
								<div style="width:<?php echo $usePercent ?>%;" class="panelItem">
									<span class="panelLabel clearfix">
										<span class="name"><?php echo _('Chapters'); ?></span>
										<span class="action">
											<a onclick="sizer('#id_chapter', 100, 100)"><i class="icon-plus"></i></a>
											<a onclick="sizer('#id_chapter', 100,-100)"><i class="icon-minus"></i></a>
										</span>
									</span>
									<div class="panelBody"><?php
										echo $app->apiLoad('chapter')->chapterSelector(array(
											'name'		=> 'id_chapter[]',
											'id'		=> 'id_chapter',
											'multi' 	=> true,
											'style' 	=> 'width:100%; height:200px',
											'profile'	=> true,
											'value'		=> $app->formValue($data['id_chapter'], $_POST['id_chapter'])
										));
									?></div>
								</div>
							<?php } if($type['use_category']){ ?>
								<div style="width:<?php echo $usePercent ?>%;" class="panelItem">
									<span class="panelLabel clearfix">
										<span class="name"><?php echo _('Category'); ?></span>
										<span class="action">
											<a onclick="sizer('#id_category', 100, 100)"><i class="icon-plus"></i></a>
											<a onclick="sizer('#id_category', 100,-100)"><i class="icon-minus"></i></a>
										</span>
									</span>
									<div class="panelBody"><?php
										echo $app->apiLoad('category')->categorySelector(array(
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
							<?php } if($type['use_group'] && !$type['is_business']){ ?>
								<div style="width:<?php echo $usePercent ?>%;" class="panelItem">
									<span class="panelLabel clearfix">
										<span class="name"><?php echo _('Groups'); ?></span>
										<span class="action">
											<a onclick="sizer('#id_group', 100, 100)"><i class="icon-plus"></i></a>
											<a onclick="sizer('#id_group', 100,-100)"><i class="icon-minus"></i></a>
										</span>
									</span>
									<div class="panelBody"><?php
										echo $app->apiLoad('user')->userGroupSelector(array(
											'name'		=> 'id_group[]',
											'id'		=> 'id_group',
											'multi' 	=> true,
											'style' 	=> 'width:100%; height:200px',
											'profile'	=> true,
											'value'		=> $app->formValue($data['id_group'], $_POST['id_group'])
										));
									?></div>
								</div>
							<?php } if($type['use_search']){ ?>
								<div style="width:<?php echo $usePercent ?>%;" class="panelItem">
									<span class="panelLabel clearfix">
										<span class="name"><?php echo _('Smart groups'); ?></span>
										<span class="action">
											<a onclick="sizer('#id_search', 100, 100)"><i class="icon-plus"></i></a>
											<a onclick="sizer('#id_search', 100,-100)"><i class="icon-minus"></i></a>
										</span>
									</span>
									<div class="panelBody"><?php
										echo $app->apiLoad('coreSearch')->searchSelector(array(
											'name'		=> 'id_search[]',
											'id'		=> 'id_search',
											'searchType'=> 'user',
											'multi' 	=> true,
											'style' 	=> 'width:100%; height:200px',
											'value'		=> $app->formValue($data['id_search'], $_POST['id_search'])
										));
										?></div>
								</div>
							<?php } if($type['use_socialforum']){ ?>
							<div style="width:<?php echo $usePercent ?>%;" class="panelItem">
								<span class="panelLabel clearfix">
									<span class="name"><?php echo _('Forum (Social)'); ?></span>
									<span class="action">
										<a onclick="sizer('#id_socialforum', 100, 100)"><i class="icon-plus"></i></a>
										<a onclick="sizer('#id_socialforum', 100,-100)"><i class="icon-minus"></i></a>
									</span>
								</span>
								<div class="panelBody"><?php
									echo $app->apiLoad('socialForum')->socialForumSelector(array(
										'name'		=> 'id_socialforum[]',
										'id'		=> 'id_socialforum',
										'multi' 	=> true,
										'style' 	=> 'width:100%; height:200px',
										'value'		=> $app->formValue($data['id_socialforum'], $_POST['id_socialforum'])
									));
								?></div>
							<?php } ?>
						</div>

					</li>
					<?php } ?>

					<!--
					<li id="contentDate" class="clearfix form-item">
						<div class="hand">&nbsp;</div>
						<div class="toggle toggle-hidden">&nbsp;</div>
						<label><?php echo _('Dates'); ?></label>
						<div class="form">
							<table>
								<tr>
									<td width="80 "><?php echo _('Created'); ?></td>
									<td width="200"><?php
											$v = $app->formValue($data['contentDateCreation'], $_POST['contentDateCreation']);
											if(!is_array($v)) $v = explode(' ', $v);
										?>
										<input type="text" name="contentDateCreation[0]" id="contentDateCreation" value="<?php echo $v[0] ?>" size="12" style="text-align:center;" />
										<input type="text" name="contentDateCreation[1]" 						  value="<?php echo $v[1] ?>" size="7"  style="text-align:center;" />
									</td>
									<td width="50"><?php echo _('Starts'); ?></td>
									<td width="200">
										<?php $v = $app->formValue($data['contentDateStart'], $_POST['contentDateStart']); ?>
										<input type="checkbox" name="contentDateStartDo" id="contentDateStartDo" value="1" <?php if($v != '') echo "checked" ?> />
										<input type="text" name="contentDateStart" id="contentDateStart" value="<?php echo $v ?>" size="12" style="text-align:center;" />
									</td>
								</tr>
								<tr>
									<td><?php echo _('Updated'); ?></td>
									<td><?php
											$v = $app->formValue($data['contentDateUpdate'], $_POST['contentDateUpdate']);
											if(!is_array($v)) $v = explode(' ', $v);
										?>
										<input type="text" name="contentDateUpdate[0]" id="contentDateUpdate" value="<?php echo $v[0] ?>" size="12" style="text-align:center;" />
										<input type="text" name="contentDateUpdate[1]" 						  value="<?php echo $v[1] ?>" size="7"  style="text-align:center;" />
									<td><?php echo _('Ends'); ?></td>
									<td><?php $v = $app->formValue($data['contentDateEnd'], $_POST['contentDateEnd']); ?>
										<input type="checkbox" name="contentDateEndDo" id="contentDateEndDo" value="1" <?php if($v != '') echo "checked" ?> />
										<input type="text" name="contentDateEnd" id="contentDateEnd" value="<?php echo $v ?>" size="12" style="text-align:center;" />
									</td>
								</tr>
							</table>
						</div>
					</li>
					-->
	
					<?php
						foreach($fields as $e){
							$app->apiLoad('field')->fieldTrace($data, $e, $f);
						}
					?>
	
				</ul>
			</div>
		</div>
	
	</form>
	
</div>


<?php include(COREINC.'/end.php'); ?>

<script src="../core/vendor/ckeditor/ckeditor.js"></script>
<script src="../core/vendor/ckeditor/adapters/jquery.js"></script>

<script src="ui/js/content.js"></script>
<script src="ui/js/gallery.nav.js"></script>

<script>

	actionNav		= true;
	language		= '<?php echo $data['language'] ?>';
	doMove  		= false;
	useEditor		= true;
	replace			= [];
	textarea		= "<?php echo @implode(',', $GLOBALS['textarea']) ?>";
//	MceStyleFormats = [<?php echo @file_get_contents(USER.'/config/tinymceStyleFormats.php') ?>];

	$(function(){
		boot();
	});

</script>

</body>
</html>