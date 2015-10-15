<?php

	if(!defined('COREINC')) die('Direct access not allowed');
	$language 	= ($_REQUEST['language'] != NULL) ? $_REQUEST['language'] :  'fr';
	$languages	= $app->countryGet(array('is_used' => 1));

	if($_POST['action']){
		$do = true;

		if(empty($_POST['contentDateStartDo'])) $_POST['contentDateStart'] = NULL;
		if(empty($_POST['contentDateEndDo'])) 	$_POST['contentDateEnd'] = NULL;

		$_POST['contentDateCreation']	= implode(' ', $_POST['contentDateCreation']);
		$_POST['contentDateUpdate']		= implode(' ', $_POST['contentDateUpdate']);
		$_POST['contentMedia'] 			= addslashes($_POST['contentMedia']);

		$def['k_content'] = array(
			'is_version'			=> array('value' => $_POST['is_version'], 					'zero' => true),
			'contentSee'			=> array('value' => $_POST['contentSee'], 					'zero' => true),
			'contentTemplate'		=> array('value' => $_POST['contentTemplate']),
			'contentTemplateEnv'	=> array('value' => serialize($_POST['templateEnv'])),
			'contentComment'		=> array('value' => $_POST['contentComment']),
			'contentRate'			=> array('value' => $_POST['contentRate']),
			'contentDateCreation'	=> array('value' => $_POST['contentDateCreation']),
			'contentDateUpdate'		=> array('value' => $_POST['contentDateUpdate']),
			'contentDateStart'		=> array('value' => $_POST['contentDateStart'],				'null' => true),
			'contentDateEnd'		=> array('value' => $_POST['contentDateEnd'],				'null' => true),
			'contentMedia'			=> array('value' => $_POST['contentMedia'])
		);

		if(!$app->formValidation($def)) $do = false;

		$dat['k_contentdata'] = array(
			'contentUrl'				=> array('value' => $_POST['contentUrl'], 				'check' => '.'),
			'contentName' 				=> array('value' => $_POST['contentName'], 				'check' => '.'),
			'contentHeadTitle' 			=> array('value' => $_POST['contentHeadTitle'], 		'null' => true),
			'contentMetaKeywords' 		=> array('value' => $_POST['contentMetaKeywords'], 		'null' => true),
			'contentMetaDescription'	=> array('value' => $_POST['contentMetaDescription'],	'null' => true),
			'contentUrlAuto'	        => array('value' => $_POST['contentUrlAuto'],	        'zero' => true)
		);
		if(!$app->formValidation($dat)) $do = false;

		if($_POST['useBusiness']){
			$def['k_content']['id_carriage'] 		= array('value' => $_POST['id_carriage']);
			$def['k_content']['contentStock'] 		= array('value' => $_POST['contentStock'], 		'check' => '[0-9]{0,}');
			$def['k_content']['contentStockNeg']	= array('value' => $_POST['contentStockNeg'],	'zero'  => true);
			$def['k_content']['contentRef']			= array('value' => $_POST['contentRef'],		'check' => '.');
			$def['k_content']['contentWeight']		= array('value' => $_POST['contentWeight'],		'zero'  => true);

			if(!$app->formValidation($def)) $do = false;
		}

		if($_POST['useAd']){
			$ad = array(
				'language'				=> array('value' => $_POST['language']),
				'id_adzone'				=> array('value' => $_POST['id_adzone']),
				'contentAdUrl'			=> array('value' => $_POST['contentAdUrl'], 		'check' => '.'),
				'contentAdCode'			=> array('value' => $_POST['contentAdCode']),
				'contentAdPriority'		=> array('value' => $_POST['contentAdPriority']),
				'contentAdStockView'	=> array('value' => $_POST['contentAdStockView']),
				'contentAdStockClick'	=> array('value' => $_POST['contentAdStockClick'])
			);
			if(!$app->formValidation(array('k_contentad' => $ad))) $do = false;
		}

		if(!$app->apiLoad('field')->fieldValidation($_POST['field'])) $do = false;

		if($do){
			$options = array(
				'id_type'			=> $_POST['id_type'],
				'language'			=> $_POST['language'],
				'id_content'		=> $_POST['id_content'],
				'def'				=> $def,
				'data'				=> $dat,
				'field'				=> $_POST['field'],
				'id_chapter'		=> $_POST['id_chapter'],
				'id_category'		=> $_POST['id_category'],
				'id_search'			=> $_POST['id_search'],
				'id_shop'			=> $_POST['id_shop'],
				'id_socialforum'	=> $_POST['id_socialforum'],
				'debug'				=> false
			);

			if($_POST['useBusiness']){
				$options['group']		= $_POST['group'];
			}else{
				$options['id_group']	= $_POST['id_group'];
			}

			if($_POST['useAd']){
				$options['ad']			= $ad;
			}

			$result = $app->apiLoad('content')->contentSet($options);

			$message = ($result) ? 'OK: Enregistrement' : 'KO: Erreur APP:<br />'.$app->apiLoad('content')->db_error;

			if($result && $_POST['is_version']){
				$app->apiLoad('content')->contentVersionSet(array(
					'id_content'	=> $app->apiLoad('content')->id_content,
					'language'		=> $_POST['language']
				));
			}

			if($result && $_POST['resetRating']){
				$app->dbQuery("DELETE FROM k_contentrate WHERE id_content = ".$app->apiLoad('content')->id_content);
			}

			if($result) $app->go("data?id_content=".$app->apiLoad('content')->id_content.'&language='.$_POST['language']);

		}else{
			$message = 'WA: Validation failed';
		}
	}

	if($_GET['remove'] == 1 && $_GET['id_content'] > 0) {
		$data = $app->apiLoad('content')->contentGet(array(
			'id_content' 	=> $_GET['id_content'],
			'debug'	 		=> false,
			'raw'			=> true
		));
		if($data['id_content'] > 0) {
			$app->apiLoad('content')->contentRemove($data['id_type'], $data['id_content'], '');
			header('Location: index?id_type='.$data['id_type']);
		}
	}

	if($_REQUEST['id_content'] != NULL){

		if($_REQUEST['reloadFromVersion'] != NULL){
			$data = $app->apiLoad('content')->contentVersionGet(array(
				'id_version' => $_REQUEST['reloadFromVersion']
			));
		}else{
			$data = $app->apiLoad('content')->contentGet(array(
				'id_content' 	=> $_REQUEST['id_content'],
				'language'		=> $language,
				'debug'	 		=> false,
				'raw'			=> true
			));
		}
		if($data['id_content'] == $_REQUEST['id_content']){
			$type	= $app->apiLoad('type')->typeGet(array('id_type' => $data['id_type'], 'debug' => false));
			$title	= $data['contentName'];
			$tpl	= ($data['contentTemplate'] != NULL) ? $data['contentTemplate'] : $type['typeTemplate'];
			$opt	= $app->apiLoad('template')->templateInfoGet($tpl);
		}else{
			$nFound	= true;
			$title	= "Document inconnu";
		}

	}else{
		$type		= $app->apiLoad('type')->typeGet(array('id_type' => $_REQUEST['id_type']));
		$title 		= "Nouveau ".$type['typeName'];
	}

	if($type['id_type'] == NULL) $nFound = true;

	if($type['id_type']){
		$fields = $app->apiLoad('field')->fieldGet(array(
			'id_type'		=> $type['id_type'],
			'fieldShowForm'	=> true
		));
	}

	$unAffected = array(
		'contentName', 'contentSee', 'contentHeadMeta', 'contentTemplate', 'contentMediaBox',
		'contentAssociation', 'contentCategory', 'contentComment', 'contentDate'
	);

	if(sizeof($fields) > 0){
		foreach($fields as $e){
			$fieldId['field'.$e['id_field']] = $e;
			$unAffected[] = 'field'.$e['id_field'];
		}
	}

	if($type['is_business'] == '1'){
		$unAffected = array_merge($unAffected, array('is_pack', 'contentStock', 'contentRef', 'contentWeight', 'contentCarriage', 'contentGroup'));
	}else
	if($type['is_ad'] == '1'){
		$unAffected = array_merge($unAffected, array('contentAd', 'contentAdCode'));
	}

	foreach($unAffected as $idxOFF => $f){
		if(sizeof($type['typeFormLayout']['tab']) > 0){
			foreach($type['typeFormLayout']['tab'] as $e){
				foreach($e['field'] as $fu){
					if($fu['field'] == $f) unset($unAffected[$idxOFF]);
				}
			}
		}
	}

	foreach($unAffected as $e){
		$type['typeFormLayout']['tab']['view0']['field'][] = array('field' => $e);
	}

	$useCount 	= 0;
	$usePercent = 100;
	foreach(array('use_group', 'use_search', 'use_chapter', 'use_category', 'use_socialforum') as $use){
		if($type[$use] == '1') $useCount++;
	}
	if($useCount > 0) $usePercent = round(100 / $useCount);

?><!DOCTYPE html>
<head>
	<?php include(COREINC.'/head.php'); ?>
	<link rel="stylesheet" type="text/css" href="ui/css/data.css" />
	<link rel="stylesheet" type="text/css" href="../core/vendor/datepicker/css/datepicker.css" />
	<link rel="stylesheet" type="text/css" href="../core/vendor/codemirror/lib/codemirror.css" />
	<link rel="stylesheet" type="text/css" href="../core/vendor/codemirror/theme/monokai.css" />
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(dirname(__DIR__).'/content/ui/menu.php')
?></header>

<div class="inject-subnav-right hide">
	<li>
		<div class="btn-group">
			<a class="btn btn-mini dropdown-toggle" data-toggle="dropdown" href="#"><?php echo _('Actions'); ?> <span class="caret"></span></a>
			<ul class="dropdown-menu">
				<li class="clearfix"><a href="data?id_type=<?php echo $type['id_type'] ?>" class="left"><?php echo _('New document'); ?></a>
				<?php if($data['id_content'] > 0){ ?>
				<li class="clearfix"><a href="data?id_content=<?php echo $data['id_content'] ?>" class="left"><?php echo _('Reload'); ?></a></li>
				<li class="clearfix"><a href="data-language?id_content=<?php echo $data['id_content'] ?>&language=<?php echo $data['language'] ?>" class="left"><?php echo _('Translation'); ?></a></li>
				<li class="clearfix"><a href="./?id_content=<?php echo $data['id_content'] ?>" class="left"><?php echo _('Comments'); ?></a></li>
				<li class="clearfix"><a href="parent?id_content=<?php echo $data['id_content'] ?>" class="left"><?php echo _('Sub-content'); ?></a></li>
				<?php } ?>
			</ul>
		</div>
	</li>
	<li><a href="./?id_type=<?php echo $type['id_type'] ?>" class="btn btn-small"><i class="icon-list"></i> <?php echo $type['typeName']; ?></a></li>
	<li><a onclick="removeThis(<?php echo $_REQUEST['id_content'] ?>)" class="btn btn-small btn-danger"><?php echo _('Remove'); ?></a></li>
	<li><a onclick="$('#data').submit()" class="btn btn-small btn-success"><?php echo _('Save'); ?></a></li>
</div>

<div id="app" class="data"><?php

if($message != NULL){
	list($class, $message) = $app->helperMessage($message);
	echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
}

if($nFound){ ?>

	<div class="message messageNotFound"><?php

		$more = $app->dbMulti("SELECT * FROM k_contentdata WHERE id_content='".$_REQUEST['id_content']."'");

		if(sizeof($more)){
			echo "<p>"._('No document found in this language, other available languages: ').": ";
			foreach($more as $e){
				$iso = $app->countryGet(array('iso' => $e['language'], 'debug' => false));

				echo "<a href=\"data?id_content=".$e['id_content']."&language=".$e['language']."\">".$iso['countryLanguage']."</a>";
			}
			echo "</p>";
		}else{
			echo "<p>"._('No document found')."</p>";
		}
		?></div>

<?php }else{ ?>

<form action="data" method="post" id="data">

<input type="hidden" name="action" value="1" />
<input type="hidden" name="id_type" id="id_type" value="<?php echo $type['id_type'] ?>" />
<input type="hidden" name="id_content" id="id_content" value="<?php echo $data['id_content'] ?>" />
<input type="hidden" name="typeFormLayout" id="typeFormLayout" />
<input type="hidden" name="is_social" value="<?php $data['is_social'] ?>" />

<div class="tabset">

	<div class="wrapper"><ul class="tab fix clearfix">

			<ul class="do-viewer">
				<?php foreach($type['typeFormLayout']['tab'] as $e){ ?>
					<li class="is-tab do-view">
						<span class="text"><?php echo $e['label'] ?></span>
						<span class="edit"></span>
						<span class="remove"></span>
						<span class="handle"></span>
					</li>
				<?php } ?>
			</ul>

			<li class="do-wiew view-all"><a class="text"><?php echo _('See all'); ?></a></li>
			<li class="hide" id="action-add-tab"><a onclick="addTab($('.tabset')[0])"><?php echo _('Add a tab'); ?></a></li>
			<li class="" id="action-move-on"><a onclick="enableMove()"><?php echo _('Edit tabs'); ?></a></li>
			<li class="hide" id="action-move-off"><a onclick="disableMove()"><?php echo _('Save tabs'); ?></a></li>

			<li class="right right-select">
				<?php echo _('Versioning'); ?>
				<input type="checkbox" name="is_version" value="1" <?php if($app->formValue($data['is_version'], $_POST['is_version'])) echo "checked" ?> />

				<select onChange="version(this)" class="select-small nomargin"><?php
					if($data['id_content'] != NULL){
						echo "<option value=\"\">".sprintf(_('%s version avalaible'), count($versions))."</option>";
						$versions = $app->apiLoad('content')->contentVersionGet(array(
							'id_content'	=> $data['id_content'],
							'language'		=> $data['language'],
							'debug'			=> false
						));
						if(sizeof($versions) > 0){
							foreach($versions as $vrs){
								$sel = ($_REQUEST['reloadFromVersion'] == $vrs['id_version']) ? ' selected' : NULL;
								echo "<option value=\"".$vrs['id_version']."\"".$sel.">Afficher la version du : ".$app->helperDate($vrs['versionDate'], '%e %b %Y à %Hh %Mm %S')."</option>";
							}
						}
					}else{
						echo "<option value=\"\">"._('No data')."</option>";
					}
					?></select>
			</li>
		</ul></div>

	<?php if($_REQUEST['id_content'] == NULL && sizeof($languages) > 1){ ?>
		<div class="view-label view-label-colored">
		<?php echo _('Language of this document'); ?>
		<select name="language" id="language" onchange="urlCheck();"><?php
			foreach($languages as $l){
				$sel = ($language == $l['iso']) ? ' selected' : NULL;
				echo "<option value=\"".$l['iso']."\"".$sel.">".$l['countryLanguage']."</option>\n";
			}
			?></select>
		</div><?php }else{ echo "<input type=\"hidden\" name=\"language\" id=\"language\" value=\"".$language."\" />"; } ?>

	<?php foreach($type['typeFormLayout']['tab'] as $id => $tab){ ?>
		<div class="view view-tab" id="<?php echo $id ?>">
			<div class="view-label view-label-toggle">
				<span><?php echo $tab['label'] ?></span>
			</div>
			<ul class="is-sortable field-list"><?php
				foreach($tab['field'] as $f){

					$name	= $f['field'];
					$e 		= $fieldId[$name];

					if(is_array($e)){
						$app->apiLoad('field')->fieldTrace($data, $e, $f);
					}else{
						echo "<div id=\"replace-".$name."\" class=\"replace".(($f['close']) ? ' closed' : '')."\"></div>";
						$replace[] = '#'.$name;
					}
				}

				?></ul>
		</div>
	<?php } ?>

	<div class="view">
		<div class="view-label">
			<span><?php echo _('Always visible'); ?></span>
		</div>
		<ul class="is-sortable field-list field-list-bottom"><?php
			foreach($type['typeFormLayout']['bottom'] as $f){

				$name	= $f['field'];
				$e 		= $fieldId[$name];

				if(is_array($e)){
					$app->apiLoad('field')->fieldTrace($data, $e, $f);
				}else{
					echo "<div id=\"replace-".$name."\" class=\"replace".(($f['close']) ? ' closed' : '')."\"></div>";
					$replace[] = '#'.$name;
				}

			}
			?></ul>
	</div>


</div>



<!-- ## ELEMENT DEPLACE AU BON ENDROIT A LA VOLEE ## -->
<ul style="display:none">

<li id="contentName" class="clearfix form-item">
	<div class="hand"></div>
	<div class="toggle"></div>

		<span class="<?php echo $app->formError('contentName', 'needToBeFilled'); ?>">
			<label><?php echo _('Name'); ?></label>
			<div class="form"><input type="text" class="field" name="contentName" id="contentNameField" value="<?php echo $app->formValue($data['contentName'], $_POST['contentName']); ?>" autocomplete="off" style="width:99%;" /></div>
		</span>

	<br style="clear:both" />
	<div class="spacer"></div>

		<span class="<?php echo $app->formError('contentUrl', 'needToBeFilled') ?>">
			<label class="off"><?php echo _('Url'); ?></label>
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

<li id="contentSee" class="clearfix form-item">
	<div class="hand"></div>
	<div class="toggle"></div>
	<label for="contentSeeBx"><?php echo _('Visibility'); ?></label>
	<div class="form" style="padding-top:3px;">
		<input type="checkbox" name="contentSee" id="contentSeeBx" value="1" <?php if($app->formValue($data['contentSee'], $_POST['contentSee'])) echo "checked"; ?> />
		<?php echo _('Front office view'); ?>
	</div>
</li>

<li id="contentRef" class="clearfix form-item <?php echo $app->formError('contentRef', 'needToBeFilled') ?>">
	<div class="hand"></div>
	<div class="toggle"></div>
	<label><?php echo _('Reference'); ?></label>
	<div class="form"><input type="text" name="contentRef" class="input-thin" value="<?php echo $app->formValue($data['contentRef'], $_POST['contentRef']); ?>" size="100" style="width:99%;" /></div>
</li>

<li id="contentWeight" class="clearfix form-item">
	<div class="hand"></div>
	<div class="toggle"></div>
	<label><?php echo _('Weight'); ?></label>
	<div class="form">
		<input type="text" name="contentWeight" class="input-thin" value="<?php echo $app->formValue($data['contentWeight'], $_POST['contentWeight']); ?>" size="8" />
		<?php echo _('grams') ?>
	</div>
</li>

<li id="contentCarriage" class="clearfix form-item">
	<div class="hand"></div>
	<div class="toggle"></div>
	<label><?php echo _('Carriage'); ?></label>
	<div class="form"><select name="id_carriage"><?php
			$carriage = $app->apiLoad('business')->businessCarriageGet();
			foreach($carriage as $e){
				$sel = ($e['id_carriage'] == $app->formValue($data['id_carriage'], $_POST['id_carriage'])) ? ' selected' : NULL;
				echo "<option value=\"".$e['id_carriage']."\"".$sel.">".$e['carriageName']."</option>";
			}
			?></select></div>
</li>

<li id="contentStock" class="clearfix form-item">
	<div class="hand"></div>
	<div class="toggle"></div>
	<label>Stock</label>
	<div class="form">
		<input type="text" name="contentStock" class="input-thin" value="<?php echo $app->formValue($data['contentStock'], $_POST['contentStock']); ?>" size="8" />
		<input type="checkbox" name="contentStockNeg" value="1" <?php if($app->formValue($data['contentStockNeg'], $_POST['contentStockNeg'])) echo "checked" ?> />
		<?php echo _('Allow negative stock') ?>
	</div>
</li>

<li id="contentTemplate" class="clearfix form-item">
	<div class="hand"></div>
	<div class="toggle"></div>
	<label><?php echo _('Template'); ?></label>
	<div class="form"><?php

		echo $app->apiLoad('template')->templateSelector(array(
			'name'		=> 'contentTemplate',
			'value'		=> $app->formValue($data['contentTemplate'], $_POST['contentTemplate']),
			'empty'		=> true,
			'emptyText'	=> _('Use default template')
		));

		if(sizeof($opt['options']) > 0){

			echo "<table style=\"width:100%; margin:5px 0 0 0\">";
			foreach($opt['options'] as $opt_){
				echo "<tr><td width=\"150\">".$opt_['name']."</td>";
				echo "<td>".$app->apiLoad('field')->fieldForm(
							$opt_['opt-'.$opt_['key']],
							$app->formValue($data['contentTemplateEnv'][$opt_['key']], $_POST['templateEnv'][$opt_['key']]),
							array(
								'class'	=> 'field',
								'style' => $opt_['style'],
								'name'	=> 'templateEnv['.$opt_['key'].']',
								'field' => array(
									'fieldType'		=> $opt_['type'],
									'fieldChoices'	=> $opt_['choice']
								)
							)
						)."</td></tr>";
			}
			echo "</table>";
		} ?>

	</div>
</li>

<li id="contentComment" class="clearfix form-item">
	<div class="hand"></div>
	<div class="toggle"></div>
	<label><?php echo _('Comment'); ?></label>
	<div class="form">
		<select name="contentComment"><?php
			foreach(array(''=>'', 'ALL'=>_('Every body'), 'USER'=>_('Members only')) as $k => $e){
				echo "<option value=\"".$k."\"".(($app->formValue($data['contentComment'], $_POST['contentComment']) == $k) ? ' selected' : NULL).">".$e."</option>\n";
			}
			?></select>

		et note
		<select name="contentRate"><?php
			foreach(array(''=>'', 'ALL'=>_('Every body'), 'USER'=>_('Members only')) as $k => $e){
				echo "<option value=\"".$k."\"".(($app->formValue($data['contentRate'], $_POST['contentRate']) == $k) ? ' selected' : NULL).">".$e."</option>\n";
			}
			?></select>
		<?php if($data['id_content'] > 0) echo '('.     _("Current note: ".$data['contentRateAvg'])  .")"; ?>
		<input type="checkbox" name="resetRating" value="1" /> <?php echo _('Reset notes'); ?>.
	</div>
</li>

<li id="contentDate" class="clearfix form-item">
	<div class="hand"></div>
	<div class="toggle"></div>
	<label><?php echo _('Dates'); ?></label>
	<div class="form">
		<table>
			<tr>
				<td width="80 "><?php echo _('Created'); ?></td>
				<td width="200"><?php
					$v = $app->formValue($data['contentDateCreation'], $_POST['contentDateCreation']);
					if(!is_array($v)) $v = explode(' ', $v);
					?>
					<input type="text" class="input-small input-thin" name="contentDateCreation[0]" id="contentDateCreation" value="<?php echo $v[0] ?>" size="12" style="text-align:center;" />
					<input type="text" class="input-small input-thin" name="contentDateCreation[1]" 						 value="<?php echo $v[1] ?>" size="7"  style="text-align:center;" />
				</td>
				<td width="50"><?php echo _('Starts'); ?></td>
				<td width="200">
					<?php $v = $app->formValue($data['contentDateStart'], $_POST['contentDateStart']); ?>
					<input type="checkbox" class="input-small" name="contentDateStartDo" id="contentDateStartDo" value="1" <?php if($v != '') echo "checked" ?> />
					<input type="text" class="input-small input-thin" name="contentDateStart" id="contentDateStart" value="<?php echo $v ?>" size="12" style="text-align:center;" />
				</td>
			</tr>
			<tr>
				<td><?php echo _('Updated'); ?></td>
				<td><?php
					$v = $app->formValue($data['contentDateUpdate'], $_POST['contentDateUpdate']);
					if(!is_array($v)) $v = explode(' ', $v);
					?>
					<input type="text" class="input-small input-thin" name="contentDateUpdate[0]" id="contentDateUpdate" value="<?php echo $v[0] ?>" size="12" style="text-align:center;" />
					<input type="text" class="input-small input-thin" name="contentDateUpdate[1]" 						  value="<?php echo $v[1] ?>" size="7"  style="text-align:center;" />
				<td><?php echo _('Ends'); ?></td>
				<td>
					<?php $v = $app->formValue($data['contentDateEnd'], $_POST['contentDateEnd']); ?>
					<input type="checkbox" name="contentDateEndDo" id="contentDateEndDo" value="1" <?php if($v != '') echo "checked" ?> />
					<input type="text" class="input-small input-thin" name="contentDateEnd" id="contentDateEnd" value="<?php echo $v ?>" size="12" style="text-align:center;" />
				</td>
			</tr>
		</table>
	</div>
</li>

<?php if($useCount > 0){ ?>
	<li id="contentAssociation" class="clearfix form-item">
		<div class="hand"></div>
		<div class="toggle"></div>
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
					<div class="panelBody"><?php echo
						$app->apiLoad('chapter')->chapterSelector(array(
							'name'	  => 'id_chapter[]',
							'id'	  => 'id_chapter',
							'multi'   => true,
							'style'   => 'width:100%; height:200px',
							'profile' => true,
							'value'	  => $app->formValue($data['id_chapter'], $_POST['id_chapter'])
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
					<div class="panelBody"><?php echo
						$app->apiLoad('category')->categorySelector(array(
							'name'	   => 'id_category[]',
							'id'	   => 'id_category',
							'multi'    => true,
							'style'    => 'width:100%; height:200px',
							'profile'  => true,
							'language' => 'fr',
							'value'	   => $app->formValue($data['id_category'], $_POST['id_category'])
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
					<div class="panelBody"><?php echo
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
			<?php } if($type['use_search']){ ?>
				<div style="width:<?php echo $usePercent ?>%;" class="panelItem">
				<span class="panelLabel clearfix">
					<span class="name"><?php echo _('Smart groups'); ?></span>
					<span class="action">
						<a onclick="sizer('#id_search', 100, 100)"><i class="icon-plus"></i></a>
						<a onclick="sizer('#id_search', 100,-100)"><i class="icon-minus"></i></a>
					</span>
				</span>
					<div class="panelBody"><?php echo
						$app->apiLoad('coreSearch')->searchSelector(array(
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
				<div class="panelBody"><?php echo
					$app->apiLoad('socialForum')->socialForumSelector(array(
						'name'	=> 'id_socialforum[]',
						'id'	=> 'id_socialforum',
						'multi' => true,
						'style' => 'width:100%; height:200px',
						'value'	=> $app->formValue($data['id_socialforum'], $_POST['id_socialforum'])
					));
					?></div>
				<?php } ?>

			</div>
	</li>
<?php } ?>



<?php if($type['is_ad']){ ?>
	<input type="hidden" name="useAd" value="1" />
	<li id="contentAd" class="clearfix form-item">
		<div class="hand"></div>
		<div class="toggle"></div>

		<span class="<?php echo $app->formError('contentAdUrl', 'needToBeFilled'); ?> clearfix">
			<label><?php echo _('Ad URL'); ?></label>
			<div class="form">
				<input type="text" name="contentAdUrl" value="<?php echo $app->formValue($data['contentAdUrl'], $_POST['contentAdUrl']); ?>" size="100" autocomplete="off" style="width:99%;" />
			</div>
		</span>

		<div class="spacer"></div>

		<span class="clearfix">
			<label class="off"><?php echo _('View limit'); ?></label>
			<div class="form clearfix">
				<input type="text" name="contentAdStockView" value="<?php echo $app->formValue($data['contentAdStockView'], $_POST['contentAdStockView']); ?>" size="10" autocomplete="off" />
				<?php if($data['contentAdCacheView'] >= 0) echo '<i>Actuellement '.$data['contentAdCacheView'].'</i>'; ?>
			</div>
		</span>

		<div class="spacer"></div>

		<span class="clearfix">
			<label class="off"><?php echo _('Click Limit'); ?></label>
			<div class="form clearfix">
				<input type="text" name="contentAdStockClick" value="<?php echo $app->formValue($data['contentAdStockClick'], $_POST['contentAdStockClick']); ?>" size="10" autocomplete="off" />
				<?php if($data['contentAdCacheClick'] >= 0) echo '<i>Actuellement '.$data['contentAdCacheClick'].'</i>'; ?>
			</div>
		</span>

		<div class="spacer"></div>

		<span class="clearfix">
			<label class="off"><?php echo _('Weighting'); ?></label>
			<div class="form clearfix">
				<input type="text" name="contentAdPriority" value="<?php echo $app->formValue($data['contentAdPriority'], $_POST['contentAdPriority']); ?>" size="10" autocomplete="off" />
				<i><?php echo _('A percentage to give more weight to this banner'); ?></i>
			</div>
		</span>

		<div class="spacer"></div>

		<span>
			<label class="off"><?php echo _('Location'); ?></label>
			<div class="form clearfix">
				<select name="id_adzone"><?php
					foreach($app->apiLoad('ad')->adZoneGet() as $e){
						$sel = ($app->formValue($data['id_adzone'], $_POST['id_adzone']) == $e['id_adzone']) ? ' selected' : NULL;
						$sel = ($sel == NULL && $app->formValue($data['id_adzone'], $_POST['id_adzone']) == '' && $_GET['id_adzone'] == $e['id_adzone']) ? ' selected' : $sel;
						echo "<option value=\"".$e['id_adzone']."\"".$sel.">".$e['zoneName']."</option>";
					}
					?></select>
			</div>
		</span>
	</li>

	<li id="contentAdCode" class="clearfix form-item">
		<div class="hand"></div>
		<div class="toggle"></div>

		<label><?php echo _('Ad code'); ?></label>
		<div class="form">
			<textarea name="contentAdCode" style="width:99%; height:100px;"><?php
				echo $app->formValue($data['contentAdCode'], $_POST['contentAdCode']);
				?></textarea>
		</div>
	</li>
<?php } ?>



<?php if($type['is_business']){ ?>
	<input type="hidden" name="useBusiness" value="1" />
	<li id="contentGroup" class="clearfix form-item">
		<div class="hand"></div>
		<div class="toggle"></div>
		<label><?php echo _('Buyers'); ?></label>
		<div class="form">

			<?php
			$groups = $app->apiLoad('content')->contentGroupGet($data['id_content'], $type['id_type']);

			if($type['is_businessloc'] == '0'){ ?>
			<table border="0" cellpadding="0" cellspacing="0" width="100%" class="listing">
				<thead>
					<tr>
						<th width="200">Nom</th>
						<th width="75" style="text-align:center;"><?php echo _('Visible'); ?></th>
						<th width="75" style="text-align:center;"><?php echo _('Buyable'); ?></th>
						<th width="75" style="text-align:right;"><?php echo _('Price'); ?></th>
						<th width="75" style="text-align:right;"><?php echo _('Price with taxes'); ?></th>
						<th width="75" style="text-align:right;"><?php echo _('Normal price'); ?></th>
						<th style="padding-left:50px;"><?php echo _('Comment'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach($groups as $id_group => $e){
                        $prompt = 'group['.$id_group.']';
						$disabled = ($e['is_view']) ? NULL : "disabled=\"disabled\""; ?>
						<tr id="line-<?php echo $id_group ?>">
							<td>
								<span style="padding-left:<?php echo ($e['level']+1) * 10 ?>px;"><?php echo $e['groupName'] ?></span>
								<input type="hidden" name="group[<?php echo $id_group ?>][1]" value="1" />
							</td>
							<td style="text-align:center"><input type="checkbox"	name="<?php echo $prompt ?>[is_view]"				value="1" <?php if($e['is_view']) echo  " checked"; ?> class="cb-view" onClick="toggleLine(<?php echo $id_group ?>,$(this))" accept="<?php echo $id_group ?>" /></td>
							<td style="text-align:center"><input type="checkbox"	name="<?php echo $prompt ?>[is_buy]"				value="1" <?php if($e['is_buy'])  echo  " checked"; ?> class="cb-buy is-toggle" <?php echo $disabled ?> /></td>
							<td style="text-align:right;"><input type="text" 		name="<?php echo $prompt ?>[contentPrice]"			value="<?php echo $app->formValue($e['contentPrice'], 			$_POST['group'][$id_group]['contentPrice']) ?>" size="6" class="fl-ht is-toggle input-thin" <?php echo $disabled ?> /></td>
							<td style="text-align:right;"><input type="text"		name="<?php echo $prompt ?>[contentPriceTax]"		value="<?php echo $app->formValue($e['contentPriceTax'], 		$_POST['group'][$id_group]['contentPriceTax']) ?>" size="6" class="fl-tt is-toggle input-thin" <?php echo $disabled ?> /></td>
							<td style="text-align:right;"><input type="text"		name="<?php echo $prompt ?>[contentPriceNormal]"	value="<?php echo $app->formValue($e['contentPriceNormal'], 	$_POST['group'][$id_group]['contentPriceNormal']) ?>" size="6" class="fl-no is-toggle input-thin" <?php echo $disabled ?> /></td>
							<td style="padding-left:50px;"><input type="text"		name="<?php echo $prompt ?>[contentPriceComment]"	value="<?php echo $app->formValue($e['contentPriceComment'], 	$_POST['group'][$id_group]['contentPriceComment']) ?>" class="fl-co is-toggle input-thin" <?php echo $disabled ?> /></td>
						</tr>
					<?php } ?>
				</tbody>
				<tfoot>
					<tr>
						<td></td>
						<td style="text-align:center">
							<a href="javascript:chk('view',false)"><img src="ui/img/boxcheck.png" /></a>
							<a href="javascript:chk('view',true)"><img src="ui/img/boxchecked.png" /></a>
							<a href="javascript:permu('view')"><img src="ui/img/boxcheckreverse.png" /></a>
						</td>
						<td style="text-align:center">
							<a href="javascript:chk('buy',false)"><img src="ui/img/boxcheck.png" /></a>
							<a href="javascript:chk('buy',true)"><img src="ui/img/boxchecked.png" /></a>
							<a href="javascript:permu('buy')"><img src="ui/img/boxcheckreverse.png" /></a>
						</td>
						<td style="text-align:right"><a href="javascript:dupli('ht')"><img src="ui/img/bigt.png" /></a></td>
						<td style="text-align:right"><a href="javascript:dupli('tt')"><img src="ui/img/bigt.png" /></a></td>
						<td style="text-align:right"><a href="javascript:dupli('no')"><img src="ui/img/bigt.png" /></a></td>
						<td style="padding-left:50px"><a href="javascript:dupli('co')"><img src="ui/img/bigt.png" /></a></td>
					</tr>
				</tfoot>
			</table>
			<?php }else if($type['is_businessloc'] == '1'){
				if(count($languages) > 1){
					echo _('You need to save this content before adding prices.');
				}else{ ?>
				<table border="0" cellpadding="0" cellspacing="0" width="100%" class="listing">
					<thead>
						<tr>
							<th width="200">Nom</th>
							<th width="75" style="text-align:center;"><?php echo _('Visible'); ?></th>
							<th width="75" style="text-align:center;"><?php echo _('Buyable'); ?></th>
							<th width="75" style="text-align:right;"><?php echo _('Price'); ?></th>
							<th width="75" style="text-align:right;"><?php echo _('Price with taxes'); ?></th>
							<th width="75" style="text-align:right;"><?php echo _('Normal price'); ?></th>
							<th style="padding-left:50px;"><?php echo _('Comment'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$prompt = 'group[@]['.$id_group.']';
						foreach($groups as $id_group => $e){
							$disabled = ($e['is_view']) ? NULL : "disabled=\"disabled\""; ?>
							<tr id="line-<?php echo $id_group ?>">
								<td>
									<span style="padding-left:<?php echo ($e['level']+1) * 10 ?>px;"><?php echo $e['groupName'] ?></span>
									<input type="hidden" name="group[<?php echo $id_group ?>][1]" value="1" />
								</td>
								<td style="text-align:center"><input type="checkbox"	name="<?php echo $prompt ?>[is_view]"				value="1" <?php if($e['is_view']) echo  " checked"; ?> class="cb-view" onClick="toggleLine(<?php echo $id_group ?>,$(this))" accept="<?php echo $id_group ?>" /></td>
								<td style="text-align:center"><input type="checkbox"	name="<?php echo $prompt ?>[is_buy]"				value="1" <?php if($e['is_buy'])  echo  " checked"; ?> class="cb-buy is-toggle" <?php echo $disabled ?> /></td>
								<td style="text-align:right;"><input type="text" 		name="<?php echo $prompt ?>[contentPrice]"			value="<?php echo $app->formValue($e['contentPrice'], 			$_POST['group'][$id_group]['contentPrice']) ?>" size="6" class="fl-ht is-toggle input-thin" <?php echo $disabled ?> /></td>
								<td style="text-align:right;"><input type="text"		name="<?php echo $prompt ?>[contentPriceTax]"		value="<?php echo $app->formValue($e['contentPriceTax'], 		$_POST['group'][$id_group]['contentPriceTax']) ?>" size="6" class="fl-tt is-toggle input-thin" <?php echo $disabled ?> /></td>
								<td style="text-align:right;"><input type="text"		name="<?php echo $prompt ?>[contentPriceNormal]"	value="<?php echo $app->formValue($e['contentPriceNormal'], 	$_POST['group'][$id_group]['contentPriceNormal']) ?>" size="6" class="fl-no is-toggle input-thin" <?php echo $disabled ?> /></td>
								<td style="padding-left:50px;"><input type="text"		name="<?php echo $prompt ?>[contentPriceComment]"	value="<?php echo $app->formValue($e['contentPriceComment'], 	$_POST['group'][$id_group]['contentPriceComment']) ?>" class="fl-co is-toggle input-thin" <?php echo $disabled ?> /></td>
							</tr>
						<?php } ?>
					</tbody>
					<tfoot>
						<tr>
							<td></td>
							<td style="text-align:center">
								<a href="javascript:chk('view',false)"><img src="ui/img/boxcheck.png" /></a>
								<a href="javascript:chk('view',true)"><img src="ui/img/boxchecked.png" /></a>
								<a href="javascript:permu('view')"><img src="ui/img/boxcheckreverse.png" /></a>
							</td>
							<td style="text-align:center">
								<a href="javascript:chk('buy',false)"><img src="ui/img/boxcheck.png" /></a>
								<a href="javascript:chk('buy',true)"><img src="ui/img/boxchecked.png" /></a>
								<a href="javascript:permu('buy')"><img src="ui/img/boxcheckreverse.png" /></a>
							</td>
							<td style="text-align:right"><a href="javascript:dupli('ht')"><img src="ui/img/bigt.png" /></a></td>
							<td style="text-align:right"><a href="javascript:dupli('tt')"><img src="ui/img/bigt.png" /></a></td>
							<td style="text-align:right"><a href="javascript:dupli('no')"><img src="ui/img/bigt.png" /></a></td>
							<td style="padding-left:50px"><a href="javascript:dupli('co')"><img src="ui/img/bigt.png" /></a></td>
						</tr>
					</tfoot>
				</table>
			<?php }} ?>

			<span class="panelLabel clearfix">
				<span class="name"><?php echo _('Shop'); ?></span>
				<span class="action">
					<a onclick="sizer('#id_shop', 100, 100)"><i class="icon-plus"></i></a>
					<a onclick="sizer('#id_shop', 100,-100)"><i class="icon-minus"></i></a>
				</span>
			</span>
			<div class="panelBody"><?php echo
				$app->apiLoad('shop')->shopSelector(array(
					'name'		=> 'id_shop[]',
					'id'		=> 'id_shop',
					'multi' 	=> true,
					'style' 	=> 'width:100%; height:200px',
					'profile'	=> true,
					'value'		=> $app->formValue($data['id_shop'], $_POST['id_shop'])
				));
			?></div>
		</div>
	</li>
<?php } ?>

<li id="contentMediaBox" class="clearfix form-item">
	<div class="hand"></div>
	<div class="toggle"></div>
	<label><?php echo _('Media'); ?></label>
	<div class="form"><?php echo

		$app->apiLoad('field')->fieldForm(
			NULL,
			$app->formValue($data['contentMedia'], $_POST['contentMedia']),
			array(
				'name' 	=> 'contentMedia',
				'id' 	=> 'contentMedia',
				'style' => 'width:100%',
				'field' => array(
					'fieldType' => 'media'
				),
			)
		);

	?></div>
</li>

</ul>

</form>
</div></div>

<?php include(COREINC.'/end.php'); ?>

<script src="../core/vendor/datatables/jquery.dataTables.js"></script>
<script src="../core/vendor/bootstrap/js/bootstrap-dropdown.js"></script>
<script src="../core/vendor/ckeditor/ckeditor.js"></script>
<script src="../core/vendor/ckeditor/adapters/jquery.js"></script>
<script src="../core/vendor/datepicker/js/bootstrap-datepicker.js" charset="UTF-8"></script>
<script src="../core/vendor/codemirror/lib/codemirror.js"></script>
<script src="../core/vendor/codemirror/mode/javascript/javascript.js"></script>
<script src="../media/ui/_uploadifive/jquery.uploadifive-v1.0.js"></script>

<script src="ui/js/content.js"></script>

<script>

	doMove  		= false;
	useEditor		= true;
	replace 		= <?php echo json_encode($replace); ?>;
	textarea		= "<?php echo @implode(',', $GLOBALS['textarea']) ?>";
/*	MceStyleFormats = [<?php echo @file_get_contents(USER.'/config/tinymceStyleFormats.php') ?>];*/
	isDrag          = false;

	function sizer(e, min, add){
		if($(e).length > 0){
			hauteur = $(e).height();
			if(hauteur + add >= min) $(e).css('height', hauteur+add);
		}
	}

	function removeThis(id_content){
		if(confirm('SUPPRIMER CE CONTENU ?')){
			document.location = 'data?id_content='+id_content+'&remove=1';
		}
	}

	function toggleLine(id,trigger){
		list = $('#line-'+id).find('.is-toggle');
		v = (trigger[0].checked) ? false : true;
		list.each(function(i, e){
			if($(e).hasClass('is-toggle')){
				$(e).prop('disabled', v);
			}
		})
	}

	function dupli(id){
		lst = $('.fl-'+id);
		lst.each(function(me, i){
			if($(this).val() == '') $(this).val(lst[0].value);
		});
	}

	function chk(id, state, doFnc){
		$('.cb-'+id).each(function(i, me){
			$(me).prop('checked', ((state) ? 'checked' : ''));
			toggleLine(me.accept, $(me));
		});
	}

	function empty(id){
		$('.'+id).each(function(i, me){
			me.value = '';
		});
	}

	function permu(id){
		$('.cb-'+id).each(function(i, me){
			me.checked = (me.checked) ? false : true;
		});
	}

	function version(sel){
		var next = '';
		var id   = sel.options[sel.selectedIndex].value;

		if(id > 0) next = '&reloadFromVersion='+id;

		if(next != ''){
			if(confirm("LOADER L'ARCHIVE ?")){
				document.location = 'data?id_content=<?php echo $_REQUEST['id_content'] ?>'+next;
			}
		}else{
			if(confirm("LOADER LA DERNIERE VERSION ?")){
				document.location = 'data?id_content=<?php echo $_REQUEST['id_content'] ?>';
			}
		}
	}

	$(function(){

		$('textarea.codemirror').each(function(i, e){
			console.log(e);

			var editor = CodeMirror.fromTextArea(e, {
				lineNumbers: true,
				theme: 'monokai',
				mode: "javascript",
				json: true
			});
		})

		boot();
		openView(0,0);

		/* -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --*/
		/* DRAG & DROP UPLOAD - -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --*/
		$('#fade-wall').on('click', function() {
			modalHideUpload();
		});

		$(document).keyup(function (e) {
			if (e.keyCode == 27 && $('#modal-upload').css('display') == 'block') {
				modalHideUpload();
			}
		});

		document.addEventListener('dragleave', function(e) {
			// Stop FireFox from opening the dropped file(s)

			if (e.pageX === 0) {
				modalHideUpload();
				isDrag = false;
			}

			e.preventDefault();
			e.stopPropagation();
		}, false);

		document.addEventListener('dragenter', function(e) {
			// Stop FireFox from opening the dropped file(s)
			if (!$(e.srcElement).is('img')) {
				console.log(e);
				console.log($(e.srcElement));
				if (isDrag) return;
				isDrag = true;

				modalShowUpload();
				e.preventDefault();
				e.stopPropagation();
			}
		}, false);

		document.addEventListener('dragover', function(e) {
			// Stop FireFox from opening the dropped file(s)
			e.preventDefault();
			e.stopPropagation();
		}, false);

		/* -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --*/
		/* -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --*/

		//	DATE PICKERS
		$('#contentDateCreation').datepicker({
			format: 'yyyy-mm-dd'
		});

		$('#contentDateUpdate').datepicker({
			format: 'yyyy-mm-dd'
		});

		$('#contentDateStart').datepicker({
			format: 'yyyy-mm-dd'
		});

		$('#contentDateEnd').datepicker({
			format: 'yyyy-mm-dd'
		});

		$('.datePicker').datepicker({
			format: 'yyyy-mm-dd'
		});

		var d = new Date();
		var day = (d.getUTCDate() < 10) ? '0'+d.getUTCDate() : d.getUTCDate();
		if ($('#contentDateCreation').val() == '') $('#contentDateCreation').val(d.getFullYear()+'-'+(d.getUTCMonth()+1)+'-'+day);
		if ($('#contentDateUpdate').val() == '') $('#contentDateUpdate').val(d.getFullYear()+'-'+(d.getUTCMonth()+1)+'-'+day);
	});

</script>

<?php } ?>

</body></html>