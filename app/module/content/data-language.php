<?php

	if($_POST['copy']){
		$app->apiLoad('content')->contentDuplicateLanguage($_POST['id_content'], $_POST['from'], $_POST['copy']);
		header("Location: data-language?id_content=".$_POST['id_content'].'&language='.$_POST['copy']);
	}else
	if($_GET['remove']){

		$from = $app->apiLoad('content')->contentGet(array(
			'id_content' 	=> $_GET['id_content'],
			'language'		=> $_GET['remove'],
			'debug'	 		=> false,
			'raw'			=> true
		));

		$app->dbQuery("DELETE FROM k_contentdata 	WHERE id_content=".$_GET['id_content']." AND language='".$_GET['remove']."'");
		$app->dbQuery("DELETE FROM k_contentversion WHERE id_content=".$_GET['id_content']." AND language='".$_GET['remove']."'");

		if($from['id_type'] != NULL){
			$app->dbQuery("DELETE FROM k_content".$from['id_type']." WHERE id_content=".$_GET['id_content']." AND language='".$_GET['remove']."'");
		}

		$how = $app->dbMulti("SELECT * FROM k_contentdata WHERE id_content=".$_GET['id_content']);

		if(sizeof($how) == 0){
			$app->apiLoad('content')->contentRemove($from['id_type'], $from['id_content'], $from['language']);
			header('Location: index?id_type='.$from['id_type']);
		}else{
			header('Location: data-language?id_content='.$_GET['id_content'].'&language='.$how[0]['language']);
		}

	}else
	if($_POST['action']){
		$do = true;

		$def['k_content'] = array(
			'is_version'		=> array('value' => $_POST['is_version'], 		'zero' => true),
		);
		if(!$app->formValidation($def)) $do = false;

		$dat['k_contentdata'] = array(
			'contentUrl'				=> array('value' => $_POST['contentUrl'], 				'check' => '.'),
			'contentName' 				=> array('value' => $_POST['contentName'], 				'check' => '.'),
			'contentHeadTitle' 			=> array('value' => $_POST['contentHeadTitle'], 		'null' => true),
			'contentMetaKeywords' 		=> array('value' => $_POST['contentMetaKeywords'], 		'null' => true),
			'contentMetaDescription'	=> array('value' => $_POST['contentMetaDescription'],	'null' => true)

		);
		if(!$app->formValidation($dat)) $do = false;

		if(!$app->apiLoad('field')->fieldValidation($_POST['field'])) $do = false;

		if($do){
			$result = $app->apiLoad('content')->contentSet(array(
				'id_type'		=> $_POST['id_type'],
				'language'		=> $_POST['language'],
				'id_content'	=> $_POST['id_content'],
				'def'			=> $def,
				'data'			=> $dat,
				'field'			=> $_POST['field'],
				'debug'			=> false
			));

			$message = ($result) ? 'OK: Enregistrement' : 'KO: Erreur APP:<br />'.$app->apiLoad('content')->db_error;
			
			if($result && $_POST['is_version']){
				$app->apiLoad('content')->contentVersionSet(array(
					'id_content'	=> $app->apiLoad('content')->id_content,
					'language'		=> $_POST['language']
				));
			}
			
			if($result) header("Location: data-language?id_content=".$app->apiLoad('content')->id_content.'&language='.$_REQUEST['language']);

		}else{
			$message = 'WA: Validation failed';
		}
	}

	$ext = $app->dbMulti("SELECT * FROM k_contentdata WHERE id_content='".$_REQUEST['id_content']."'");
	$lan = $ext[0]['language'];
	
	if($lan == '') $nFound = true;

	$from = $app->apiLoad('content')->contentGet(array(
		'id_content' 	=> $_REQUEST['id_content'],
		'language'		=> $lan,
		'debug'	 		=> false,
		'raw'			=> true
	));

	if($from['id_type'] != NULL){
		$type	= $app->apiLoad('content')->contentType(array('id_type' => $from['id_type'], 'debug' => false)); 
		$title	= $from['contentName'];
	}else{
		$title	= 'Document inconnu';
	}

	if($_REQUEST['reloadFromVersion'] != NULL){
		$data = $app->apiLoad('content')->contentVersionGet(array(
			'id_version' => $_REQUEST['reloadFromVersion']
		));
	}else{
		$data = $app->apiLoad('content')->contentGet(array(
			'id_content' 	=> $_REQUEST['id_content'],
			'language'		=> $_REQUEST['language'],
			'debug'	 		=> false,
			'raw'			=> true
		));
	}

	if($type['id_type'] != NULL){
		$fields = $app->apiLoad('field')->fieldGet(array(
			'id_type'	=> $type['id_type']
		));
	}

	foreach($app->countryGet(array('is_used' => 1)) as $e){
		$exists	= $app->dbOne("SELECT 1 FROM k_contentdata WHERE id_content='".$from['id_content']."' AND language='".$e['iso']."'");
		if(!$exists[1] && $_REQUEST['language'] != $e['iso']) $unset[] = $e;
	}

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php
		echo $app->less('/admin/content/ui/css/data.less');
		include(COREINC.'/head.php');
	?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(dirname(__DIR__).'/content/ui/menu.php')
?></header>	

<div id="app" class="data"><?php

	if($message != NULL){
		list($class, $message) = $app->helperMessage($message);
		echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
	}

	if($nFound){ ?>

		<div class="message messageNotFound"><?php 
		
			$more = $app->dbMulti("SELECT * FROM k_contentdata WHERE id_content='".$_REQUEST['id_content']."'");
	
			if(sizeof($more)){
				echo "<p>Aucun document ne correspond dans cette langue.</p>";
				echo "<p>Autre langue disponible : ";
				foreach($more as $e){
					$iso = $app->countryGet(array('iso' => $e['language'], 'debug' => false));
					echo "<a href=\"data?id_content=".$e['id_content']."&language=".$e['language']."\">".$iso['countryLanguage']."</a>";
				}
				echo "</p>";
			}else{
				echo "<p>Aucun document ne correspond</p>";
			}
		?></div>
	
	<?php }else{ 
	
		if(sizeof($unset) > 0){ ?>
		<div class="message messageWarning alert">
			<form action="data-language" method="post">
				<input type="hidden" name="id_content"	value="<?php echo $_REQUEST['id_content'] ?>" />
				<input type="hidden" name="from" 		value="<?php echo $_REQUEST['language'] ?>" />
		
				Recopier les données de cette langue vers
				<select name="copy" class="select-small nomargin"><?php
					foreach($unset as $e){
						echo "<option value=\"".$e['iso']."\">".$e['countryLanguage']."</option>";
					}
				?></select>
				<button type="submit" name="valider" class="button button-green" style="float: none;">Valider</button>
				(Pensez à enregistrer cette langue avant de la recopier)
			</form>
		</div>
	<?php } ?>


	<div class="inject-subnav-right hide">
        
		<li>
			<div class="btn-group">
				<a class="btn btn-mini dropdown-toggle" data-toggle="dropdown" href="#">Autres actions <span class="caret"></span></a>
				<ul class="dropdown-menu">
					<li class="clearfix"><a href="data?id_type=<?php echo $type['id_type'] ?>" class="left">Nouveau document (<?php echo $type['typeName'] ?>)</a></li>
					<li class="clearfix"><a href="data?id_content=<?php echo $data['id_content'] ?>" class="left">Recharger le formulaire</a></li>
					<li class="clearfix"><a href="data?id_content=<?php echo $_REQUEST['id_content'] ?>" class="left">Retour au formulaire complet</a></li>
				</ul>
			</div>
		</li>
		<li>
			<a onclick="removeThis('<?php echo $_REQUEST['language'] ?>', <?php echo $_REQUEST['id_content'] ?>)" class="btn btn-small btn-danger">Supprimer</a>
		</li>
		<li>
			<a onclick="$('#data').submit()" class="btn btn-small btn-success">Enregistrer</a>
		</li>
	</div>


	<form action="data-language" method="post" id="data">
	
	<input type="hidden" name="action" value="1" />
	<input type="hidden" name="id_type" id="id_type" value="<?php echo $from['id_type'] ?>" />
	<input type="hidden" name="id_content" id="id_content" value="<?php echo $from['id_content'] ?>" />
	<input type="hidden" name="language" id="language" value="<?php echo $_REQUEST['language'] ?>" />
	
	<div class="tabset">
	
		<div class="wrapper"><ul class="tab clearfix">
		<?php
			foreach($app->countryGet(array('is_used' => 1)) as $e){
				$exists	= $app->dbOne("SELECT 1 FROM k_contentdata WHERE id_content=".$from['id_content']." AND language='".$e['iso']."'");
				$lan	= ($exists[1]) ? '<b>'.$e['countryLanguage'].'</b>' : $e['countryLanguage'];
				$class	= ($e['iso'] == $_REQUEST['language']) ? ' is-selected' : NULL;
				$empty	= ($e['iso'] == $_REQUEST['language'] && !$exists[1]) ? true : false;

				echo "<li class=\"is-tab ".$class."\"><a href=\"data-language?id_content=".$_REQUEST['id_content']."&language=".$e['iso']."\">".$lan."</a></li>";
			}
		?>

			<li class="right right-select">
				Archiver
				<input type="checkbox" name="is_version" value="1" <?php if($app->formValue($data['is_version'], $_POST['is_version'])) echo "checked" ?> /><?php
		
				if($data['id_content'] != NULL){
					$versions = $app->apiLoad('content')->contentVersionGet(array(
						'id_content'	=> $data['id_content'],
						'language'		=> $data['language'],
						'debug'			=> false
					));
					if(sizeof($versions) > 0){
						echo "<select onChange=\"version(this)\"><option value=\"\">".sizeof($versions)." versions disponibles</option>";
						foreach($versions as $vrs){
							$sel = ($_REQUEST['reloadFromVersion'] == $vrs['id_version']) ? ' selected' : NULL;
							echo "<option value=\"".$vrs['id_version']."\"".$sel.">Afficher la version du : ".$vrs['versionDate']."</option>";
						}
						echo "</select>";
					}else{
						echo "Pas de version";
					}
				}
			?></li>
		</ul></div>
		
		<div class="view view-tab">
			<?php if($empty){ ?>
			<div class="view-label view-label-toggle">
				<span>Cette page n'est pas traduite dans cette langue</span>
			</div>
			<?php } ?>
		
			<ul class="is-sortable field-list">
				<li id="contentName" class="clearfix form-item">
					<div class="hand">&nbsp;</div>
					<div class="toggle">&nbsp;</div>
			
					<span class="<?php echo $app->formError('contentName', 'needToBeFilled') ?> clearfix">
						<label>Nom</label>
						<div class="form"><input type="text" name="contentName" id="contentNameField" value="<?php echo $app->formValue($data['contentName'], $_POST['contentName']); ?>" size="100" style="width:99%;" /></div>
					</span>
					
					<div class="spacer">&nbsp;</div>
			
					<span class="<?php echo $app->formError('contentUrl', 'needToBeFilled') ?>">
						<label class="off">Url</label>
						<div class="form"><input type="text" name="contentUrl" id="urlField" value="<?php echo $app->formValue($data['contentUrl'], $_POST['contentUrl']); ?>" size="100" style="width:99%;" /></div>
					</span>
					
				</li>
	
				<li id="contentHeadMeta" class="clearfix form-item">
					<div class="hand">&nbsp;</div>
					<div class="toggle">&nbsp;</div>
			
					<span class="clearfix">
						<label>Titre</label>
						<div class="form"><input type="text" name="contentHeadTitle" value="<?php echo $app->formValue($data['contentHeadTitle'], $_POST['contentHeadTitle']); ?>" size="100" style="width:99%;" /></div>
					</span>
					<div class="spacer">&nbsp;</div>
					<span>
						<label class="off">Mots-clés</label>
						<div class="form"><input type="text" name="contentMetaKeywords" value="<?php echo $app->formValue($data['contentMetaKeywords'], $_POST['contentMetaKeywords']); ?>" size="100" style="width:99%;" /></div>
					</span>
					<br style="clear:both" /> 
					<div class="spacer">&nbsp;</div>
					<span>
						<label class="off">Description</label>
						<div class="form"><input type="text" name="contentMetaDescription" value="<?php echo $app->formValue($data['contentMetaDescription'], $_POST['contentMetaDescription']); ?>" size="100" style="width:99%;" /></div>
					</span>
				</li>
	
				<?php
					foreach($fields as $f){
						$app->apiLoad('field')->fieldTrace($data, $f);
					}
				?>
	
			</ul>
		</div>
	</div>
	</form>
</div>

<?php include(COREINC.'/end.php'); ?>
<script src="/app/module/core/ui/_bootstrap/js/bootstrap-dropdown.js"></script>
<script src="/app/module/core/ui/_tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
<script src="/app/module/core/ui/_tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script src="ui/js/content.js"></script>
<script type="text/javascript">
	
/*
	mediaList 	= new Object;
	doMove  	= false;
	useEditor	= true;
	replace 	= [];
	mediaList	= [<?php echo @implode(',', $GLOBALS['mediaList']) ?>];
	textarea	= "<?php echo @implode(',', $GLOBALS['textarea']) ?>";
	datePick	= [<?php echo @implode(',', $GLOBALS['datePick']) ?>];
*/

	doMove  		= false;
	useEditor		= true;
	replace 		= [];
	mediaList		= ['contentMedia'<?php if(sizeof($GLOBALS['mediaList']) > 0) echo','.implode(',', $GLOBALS['mediaList']) ?>];
	textarea		= "<?php echo @implode(',', $GLOBALS['textarea']) ?>";
	datePick		= [<?php echo @implode(',', $GLOBALS['datePick']) ?>];
	MceStyleFormats = [<?php echo @file_get_contents(USER.'/config/tinymceStyleFormats.php') ?>];

	$(function(){
		boot();
		checkNeedToBeFilled();

		$('#contentNameField').bind('keyup', function(){
			url = liveUrlTitle($(this).val());
			$('#urlField').val(url);

			var get = $.ajax({
				url: 'helper/url?id_content='+$('#id_content').val()+'&url='+url+'&language='+language
			});

			get.done(function(obj) {
				if($('#urlField').val() != obj.url) $('#urlField').text(obj.url);
			});

		});

	});

	function removeThis(language, id_content){
		if(confirm('SUPPRIMER CETTE VERSION')){
			document.location = 'data-language?id_content='+id_content+'&remove='+language;
		}
	}

	function version(sel){
		var next = '';
		var id   = sel.options[sel.selectedIndex].value;

		if(id > 0) next = '&reloadFromVersion='+id;

		if(next != ''){
			if(confirm("LOADER L'ARCHIVE ?")){
				document.location = 'data-language?id_content=<?php echo $_REQUEST['id_content'] ?>&language=<?php echo $_REQUEST['language'] ?>'+next;
			}
		}else{
			if(confirm("LOADER LA DERNIERE VERSION ?")){
				document.location = 'data-language?id_content=<?php echo $_REQUEST['id_content'] ?>&language=<?php echo $_REQUEST['language'] ?>';
			}
		}
	}

</script>	
<?php } ?>


</body>
</html>