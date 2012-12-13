<?php
	if($_POST['action']){
		$do = true;

		// Core
		$def['k_content'] = array(
			'is_item'				=> array('value' => 1),
			'contentSee'			=> array('value' => $_POST['contentSee'], 			'zero' => true)
		);
		if(!$app->formValidation($def)) $do = false;

		// Data
		$dat['k_contentdata'] = array(
			'contentName'			=> array('value' => $_POST['contentName'], 			'check' => '.'),
		);
		if(!$app->formValidation($dat)) $do = false;


		// Item
		$itm['k_contentitem'] = array(
			'contentItemUrl'		=> array('value' => $_POST['contentItemUrl'], 		'check' => '.')
		);
		if(file_exists(KROOT.$_POST['contentItemUrl'])){

			list($type, $mime) = explode('/', $app->mediaMimeType(KROOT.$_POST['contentItemUrl']));

			$itm['k_contentitem']['contentItemType']		= array('value' => $type);
			$itm['k_contentitem']['contentItemMime']		= array('value' => $mime);
			$itm['k_contentitem']['contentItemWeight']		= array('value' => filesize(KROOT.$_POST['contentItemUrl']));

			if($type == 'image'){
				$size = getimagesize(KROOT.$_POST['contentItemUrl']);
				$itm['k_contentitem']['contentItemHeight']	= array('value' => $size[1]);
				$itm['k_contentitem']['contentItemWidth']	= array('value' => $size[0]);
			}
		}
		if(!$app->formValidation($itm)) $do = false;

		// Field
		if(!$app->apiLoad('field')->fieldValidation($_POST['field'])) $do = false;

		if($do){
			$result = $app->apiLoad('content')->contentSet(array(
				'id_type'		=> $_REQUEST['id_type'],
				'language'		=> $_POST['language'],
				'id_content'	=> $_POST['id_content'],
				'debug'			=> false,

				// Les donnees
				'def'			=> $def,
				'data'			=> $dat,
				'field'			=> $_POST['field'],
				'item'			=> $itm,

				// Association
				'id_group'		=> $_POST['id_group'],
				'id_chapter'	=> $_POST['id_chapter'],
				'id_category'	=> $_POST['id_category'],
				'id_search'		=> $_POST['id_search']
			));

			$message = ($result) ? 'OK: Enregistrement' : 'KO: Erreur APP:<br />'.$app->apiLoad('content')->db_error;

			if($result) header("Location: content.gallery.item.php?id_content=".$app->apiLoad('content')->id_content.'&id_type='.$_REQUEST['id_type']);
		}else{
			$message = 'WA: Validation failed';
		}
	}

	$data = $app->apiLoad('content')->contentGet(array(
		'id_content' 	=> $_REQUEST['id_content'],
		'id_type'		=> $_REQUEST['id_type'],
		'is_item'		=> true,
		'language'		=> 'fr',
		'debug'	 		=> false,
		'raw'			=> true
	));
	#$app->pre($data);

	$type	= $app->apiLoad('content')->contentType(array(
		'id_type' => $_REQUEST['id_type']
	)); 

	if($data['id_album'] == 0){
		$items = $app->apiLoad('content')->contentGet(array(
			'id_type'		=> $_REQUEST['id_type'],
			'id_album'		=> 0,
			'is_item'		=> true,
			'language'		=> 'fr',
			'raw'			=> true,
			'debug'			=> false
		));
		
		#$items = $app->dbMulti("SELECT * FROM k_contentitem WHERE k_contentitem.id_album=0 ORDER BY contentItemPos ASC");
		#$app->pre($app->db_query, $app->db_error, $items);

	}else{
		$album = $app->apiLoad('content')->contentGet(array(
			'id_content'	=> $data['id_album'],
			'id_type'		=> $_REQUEST['id_type'],
			'is_album'		=> true,
			'language'		=> 'fr',
			'raw'			=> true,
			'debug'			=> false
		));

		$items = $app->apiLoad('content')->contentGet(array(
			'id_type'		=> $_REQUEST['id_type'],
			'id_album'		=> $data['id_album'],
			'is_item'		=> true,
			'language'		=> 'fr',
			'raw'			=> true,
			'debug'			=> false,
			'noLimit'		=> true
		));
	}

	for($i=0; $i<sizeof($items); $i++){
		if($items[$i]['id_content'] == $data['id_content']){
			$previous = $items[$i-1];
			if($previous['id_content'] == NULL) unset($previous);

			$next = $items[$i+1];
			if($next['id_content'] == NULL) unset($next);
		}
	}
	
	$fields = $app->apiLoad('field')->fieldGet(array(
		'id_type'		=> $_REQUEST['id_type'],
		'fieldShowForm'	=> true,
		'debug'			=> false
	));

	# Path Way
	$id_album	= $data['id_album'];

	if($id_album > 0){
		$touched = ($id_album == '0');
		$loop	 = 0;

		while(!$touched && $loop < 50){
			$me = $app->apiLoad('content')->contentGet(array(
				'raw'			=> true,
				'id_type'		=> $type['id_type'],
				'id_content'	=> $id_album,
				'is_album'		=> true,
				'debug'			=> false
			));

			$pathway[]	= "<a href=\"content.gallery.album.php?id_content=".$me['id_content']."\">".$me['contentName']."</a>";
			$id_album	= $me['id_album'];			

			if($me['id_album'] == '0'){
				$touched	= true;
				$loop		= 60;
			}
			
			$loop++;
		}

	}

	$pathway[] 	= "/<a href=\"content.gallery.index.php?id_type=".$type['id_type']."\">Racine</a>";
	$title		= implode('/', array_reverse($pathway)).$title;

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

	function fieldTrace($app, $data, $e, $f){

		$field = $app->apiLoad('field')->fieldForm(
			$e['id_field'],
			$app->formValue($data['field'.$e['id_field']], $_POST['field'][$e['id_field']]),
			array(
				'style' => 'width:99%; '.$e['fieldStyle']
			)
		);

		if(preg_match("#richtext#", $field)) 	$GLOBALS['textarea'][]	= 'wform-field-'.$e['id_field'];
		if(preg_match("#media\-list#", $field)) $GLOBALS['mediaList'][]	= "'form-field-".$e['id_field']."'";
		if(preg_match("#datePicker#", $field))  $GLOBALS['datePick'][]	= "'form-field-".$e['id_field']."'";

		echo "<li class=\"clearfix ".$app->formError('field'.$e['id_field'], 'needToBeFilled')." form-item\" id=\"field".$e['id_field']."\">";
		echo "<div class=\"hand\">&nbsp;</div>";
		echo "<div class=\"toggle toggle-hidden\">&nbsp;</div>";

			echo "<label>".$e['fieldName'];
				if($e['is_needed']) echo ' *';
				if(preg_match("#richtext#", $field)){
					echo "<br /><a href=\"javascript:toggleEditor('form-field-".$e['id_field']."');\">Activer/Désactiver l'éditeur</a>";
				}
			echo "</label>";

			echo "<div class=\"form\">".$field."</div>";

			if($e['fieldInstruction']){
				echo "<div class=\"instruction off\">".$e['fieldInstruction']."</div>";
			}

		echo "</li>";
	}
	
	function previewMe($item, $value, $link=NULL){
		global $app;

		$url = '/v/'.$item['id_content'].'/'.$item['contentName'];

		if($item['contentItemType'] == 'image'){
		
			$img = $app->mediaUrlData(array(
				'url'	=> $item['contentItemUrl'],
				'mode'	=> 'width',
				'value'	=> $value,
				'cache'	=> true
			));

			$img = "<a href=\"".$url."\" target=\"_blank\"><img src=\"".$img['img']."\" height=\"".$img['height']."\" width=\"".$img['width']."\" class=\"isimg\" /></a><br />";
		}else
		if($item['contentItemType'] == 'video'){
			$img = "<a href=\"".$url."\" target=\"_blank\"><img src=\"ressource/img/media-file_quicktime.png\" height=\"128\" width=\"128\" /></a><br />";
		}else
		if($item['contentItemType'] == 'audio'){
			$img = "<a href=\"".$url."\" target=\"_blank\"><img src=\"ressource/img/media-file_audio.png\" height=\"128\" width=\"128\" /></a><br />";
		}else
		if($item['contentItemType'] == 'application' AND $item['contentItemMime'] == 'pdf'){
			$img = "<a href=\"".$url."\" target=\"_blank\"><img src=\"ressource/img/media-file_pdf.png\" height=\"128\" width=\"128\" /></a><br />";
		}else{
			$img = NULL;
		}

		if(isset($link)){
			echo "<a href=\"".$link."\">" . $img . $item['contentName'] . "</a>";
		}else{
			echo $img . $item['contentName'];
		}
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

?><!DOCTYPE html>
<html>
<head>
	<?php include(COREINC.'/head.php'); ?>
</head>

<body>

<div class="pbg">
	
	<!-- BANDEAU TOP - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - --> 
	
	<div class="top">
		<div class="logo">Logo</div>
		<div class="pathway clearfix">
			<h1><a href="index">Contenu</a> &raquo; 
				<a href="gallery-index?id_type=<?php echo $_REQUEST['id_type'] ?>"><?php echo $type['typeName'] ?></a> &raquo;
				<?php echo $title.' &raquo; '.$data['contentName'] ?></h1>
		</div>
	</div>
	<?php include(COREINC.'/sidebar.php'); ?>
</div>	

<div class="bocontainer">
	<div class="row-fluid">
		
	<div class="app">
	<?php
		if($message != NULL){
			list($class, $message) = $app->helperMessage($message);
			echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
		}
	?>
	
	<form action="content.gallery.item.php" method="post" id="data">
	
		<input type="hidden" name="action" value="1" />
		<input type="hidden" name="id_type" value="<?php echo $_REQUEST['id_type'] ?>" />
		<input type="hidden" name="id_content" id="id_content" value="<?php echo $data['id_content'] ?>" />
		<input type="hidden" name="language" value="fr" />
	
		<div>
			<a href="javascript:$('data').submit()" class="button button-blue">Enregistrer</a>
		</div>
	
	
		<div class="form-horizontal">
			<fieldset>
		
				<div class="control-group noclear">
					<label class="control-label" for="contentNameInput">Nom</label>
					<div class="controls">
						<input type="text" class="span12" id="contentNameInput" name="contentName" value="<?php echo $app->formValue($data['contentName'], $_POST['contentName']); ?>">
					</div>
				</div>

				<div class="control-group noclear">
					<label class="control-label" for="contentItemUrlInput"><a href="#" onclick="mediaOpen('line', 'urlField')">Changer l'URL</a></label>
					<div class="controls">
						<input type="text" class="span12" id="contentItemUrlInput" name="contentItemUrl" value="<?php echo $app->formValue($data['contentItemUrl'], $_POST['contentItemUrl']); ?>">
					</div>
				</div>
				
				<div class="control-group ">
					<label class="control-label" for="contentSeeChkbox"><a href="#" >Visibilité</a></label>
					<div class="controls">
						<input type="checkbox" name="contentSee" id="contentSeeChkbox" value="1" <?php if($app->formValue($data['contentSee'], $_POST['contentSee'])) echo "checked"; ?> />
						Indique que ce document est visible sur le site
					</div>
				</div>
		
				
				<?php foreach($fields as $e){
					fieldTrace($app, $data, $e, $f);
				} ?>
		
			</fieldset>
		</div>
	
		<?php
	
			if(is_array($previous)){
				$leftLink = "content.gallery.item.php?id_content=".$previous['id_content']."&id_type=".$_REQUEST['id_type'];
			}
	
			if(is_array($next)){
				$rightLink = "content.gallery.item.php?id_content=".$next['id_content']."&id_type=".$_REQUEST['id_type'];
			}
	
		?>
		<table width="100%" border="0" cellpadding="0" cellspacing="5" class="gCarrousel">
			<tr>
				<th>&nbsp;</th>
				<th class="current">&#8593;<?php
					echo ($data['id_album'] == 0)
						? "<a href=\"content.gallery.index.php?id_type=".$type['id_type']."\">Racine</a>"
						: "<a href=\"content.gallery.index.php?id_type=".$type['id_type']."#".$album['id_content']."\">Album ".$album['contentName']."</a>";
				?></th>
				<th>&nbsp;</th>
			</tr>
			<tr>
				<th width="25%" 	class="previous"><a href="<?php echo ($leftLink  != '') ? $leftLink  : '#'; ?>">&#8592; Element précédent</a></th>
				<th align="center"	class="current"	>Element courant</th>
				<th width="25%" 	class="next"	><a href="<?php echo ($rightLink != '') ? $rightLink : '#'; ?>">Element suivant &#8594;</a></th>
			</tr>
			<tr valign="top" align="left">
				<td class="previous"><?php
					if($leftLink != ''){
						previewMe($previous, 200, $leftLink);
					}else{
						echo "<br /><br /><span id=\"leftDeadEnd\" style=\"padding:5px;\">Vous êtes au debut de l'album</span>";
					}
				?>&nbsp;</td>
				<td class="current"><?php previewMe($data, 600); ?></td>
				<td class="next" align="right">&nbsp;<?php
					if($rightLink != ''){
						previewMe($next, 200, $rightLink);
					}else{
						echo "<br /><br /><span id=\"rightDeadEnd\" style=\"padding:5px;\">Vous êtes a la fin de l'album</span>";	
					}
				?></td>
			</tr>
		</table>
	
	</form>
	
	</div>

</div></div>

</body>

	<script>
	
		actionNav		= true;
		language		= '<?php echo $data['language'] ?>';
		doMove  		= false;
		useEditor		= true;
		replace			= []
		textarea		= "<?php echo @implode(',', $GLOBALS['textarea']) ?>";
		MceStyleFormats = [<?php echo @file_get_contents(USER.'/config/tinymceStyleFormats.php') ?>];
	
		window.addEvents({
			'domready' : function(){
				boot();
				checkNeedToBeFilled();
				
				$$('#data input').addEvents({
					'focus' : function(){
						actionNav = false;
					},
					'blur' : function(){
						actionNav = true;
					}
				});
			},
			
			'keydown' : function(e){
				if(actionNav){
					if(e.key == 'left'){
						link = '<?php echo $leftLink ?>';
						if(link != ''){
							document.location=link;
						}else{
							$('leftDeadEnd').highlight('#fffc00');
						}
					}else
					if(e.key == 'right'){
						link = '<?php echo $rightLink ?>';
						if(link != ''){
							document.location=link;
						}else{
							$('rightDeadEnd').highlight('#fffc00');
						}
					}else{
					if(e.key == 'up')
						document.location='content.gallery.index.php?id_type=<?php echo $type['id_type'] ?>#<?php echo $album['id_content'] ?>';
					}
				}
			}
		});
	
	</script>
	
</html>