<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	function cache($app){
		$thread	= array();

		foreach($app->countryGet(array('is_used' => true)) as $e){
			$thread[$e['iso']] = $app->apiLoad('chapter')->chapterGet(array(
				'language'	=> $e['iso'],
				'order'		=> 'pos_chapter',
				'direction'	=> 'ASC',
				'thread'	=> true
			));
		}

		$app->configSet('chapter', 'jsonCacheChapter', json_encode($thread));
	}

	if($_POST['serialized'] != NULL){
		$serialized = json_decode(stripslashes($_POST['serialized']), true);
		if(sizeof($serialized) > 0){
			$app->apiLoad('chapter')->chapterUpdatePos($serialized);
		}
		$app->apiLoad('chapter')->chapterFamily();
		$reload = true;
	}
	
	if(sizeof($_POST['del']) > 0){
		foreach($_POST['del'] as $e){
			$app->apiLoad('chapter')->chapterRemove($e);
		}
		$reload = true;
	}

	if($_POST['action']){
		$do = true;

		$def['k_chapter'] = array(
			'id_theme'		=> array('value' => $_POST['id_theme'],		'null'  => true),
			'mid_chapter'	=> array('value' => $_POST['mid_chapter'],	'zero'  => true)
		);

		foreach($_POST['lan'] as $iso => $lan_){
			$dat['k_chapterdata'] = array(
				'chapterUrl'			=> array('value' => $lan_['chapterUrl'], 	'check' => '.'),
				'chapterName'			=> array('value' => $lan_['chapterName'], 	'check' => '.'),
				'chapterModule'			=> array('value' => $lan_['chapterModule']),
				'chapterMedia'			=> array('value' => $lan_['chapterMedia']),
				'chapterDescription'	=> array('value' => $lan_['chapterDescription'])
			);

			if(!$lan_['copy'] && !$app->formValidation($dat, array('suffix' => '-'.$iso)))	$do = false;
			if(!$lan_['copy'] && !$app->apiLoad('field')->fieldValidation($lan_['field']))  $do = false;

			$lan[$iso] = array(
				'copy'	=> (($lan_['copy']) ? $_POST['ref'] : NULL),
				'sql'	=> $dat,
				'field'	=> $lan_['field']
			);
		}

		if($_POST['errorUrl'] != ''){
			$app->formError['chapterUrl-'.$iso] = true;
			$do = false;
		}

		if($do){
			$result  = $app->apiLoad('chapter')->chapterSet($_POST['id_chapter'], $def, $lan);
			$message = ($result) ? 'OK: Enregistrement effectué' : 'KO: Erreur APP:<br />'.$app->db_error;
			
			if($result) cache($app);
		}else{
			$message = 'KO: Attention, les champs ne sont pas remplis correctement.';
		}
	}

	$chapter = $app->apiLoad('chapter')->chapterGet(array(
		'language'		=> 'fr',
		'thread' 		=> true,
		'mid_chapter'	=> 0
	));

	$fields = $app->apiLoad('field')->fieldGet(array(
		'chapter'	=> true,
		'debug'		=> false	
	));

	$languages = $app->countryGet(array('is_used' => 1));
	foreach($languages as $e){
		$isoJS[] = "'".$e['iso']."'";
	}

	if($_REQUEST['id_chapter'] != NULL){
		$data = $app->apiLoad('chapter')->chapterGet(array(
			'language'		=> 'fr',
			'id_chapter' 	=> $_REQUEST['id_chapter']
		));

		foreach($languages as $e){
			$tmp = $app->apiLoad('chapter')->chapterGet(array(
				'language' 		=> $e['iso'],
				'id_chapter' 	=> $data['id_chapter']
			));

			$data['lan'][$e['iso']] = (sizeof($tmp) > 0) ? $tmp : array('is_copy' => true);
		}

		$title = $data['chapterName'];
	}else{
		$title = 'Nouveau chapitre';
	}

	if($reload){
		cache($app);
		$app->go('./');
	}

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
    <link rel="stylesheet" type="text/css" href="ui/css/chapter.css" />
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(dirname(__DIR__).'/content/ui/menu.php')
?></header>

<div id="app"><div class="wrapper"><div class="row-fluid">

	<div class="span6" id="list">
		<form action="./" method="post" id="order">
			<table width="100%" border="0" cellspacing="0" class="listing">
				<thead>
					<tr>
						<th width="20" class="icone"><i class="icon-remove icon-white"></i></th>
						<th width="15">&nbsp;</th>
						<th class="filter">
							<span><?php echo _('Name'); ?></span>
							<input type="text" id="filter" class="input-small" onkeyup="recherche($(this))" placeholder="<?php echo _('Filter'); ?>" />
						</th>
					</tr>
				</thead>
			</table>
		
			<div id="chapter"><?php
				function trace($app, $mid, $chapter, $level){
			
					echo "<ul id=\"mid-".$mid."\" class=\"ul-items\">";
					foreach($chapter as $e){
						echo "<li id=\"".$e['id_chapter']."\" class=\"sniff\">";
							echo "<div class=\"holder clearfix\">";
								echo "<div class=\"check\"><input type=\"checkbox\" name=\"del[]\" value=\"".$e['id_chapter']."\" id=\"chk-".$e['id_chapter']."\"/></div>";
								echo "<div class=\"handle\"></div>";
								echo "<div class=\"data\" style=\"padding-left:".(($level * 10) + 15)."px;\"><a href=\"./?id_chapter=".$e['id_chapter']."\">".$e['chapterName']."</a></div>";
							echo "</div>";
			
						if(sizeof($e['sub']) > 0) trace($app, $e['id_chapter'], $e['sub'], ($level+1));
						echo "</li>";
					}
					echo "</ul>";
				}
			
				trace($app, 0, $chapter, 0);
			?></div>
		
			<div class="clearfix">	
				<div class="left">
					<a href="javascript:serialMe(0)" class="btn btn-mini"><?php echo _('Save'); ?></a>
					<a href="./" class="btn btn-mini"><?php echo _('Cancel'); ?></a>
				</div> 
				<div class="right">
					<a href="../field/asso?id_type=chapter" class="btn btn-mini"><?php echo _('Manage fields'); ?></a>
				</div>
			</div>
		
			<input type="hidden" id="serialized" name="serialized" />
		</form>
	</div>
	
	<form action="./" method="post" id="data" class="span6">
	<?php
	
		if($message != NULL){
			list($class, $message) = $app->helperMessage($message);
			echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
		}
	
		?>
		<input type="hidden" name="action" value="1" />
		<input type="hidden" name="id_chapter" id="id_chapter" value="<?php echo $data['id_chapter'] ?>" />
		<input type="hidden" name="language" value="fr" />
		<input type="hidden" name="errorUrl" id="errorUrl" value="" />
	
		<table cellpadding="0" cellspacing="0" border="0" class="form">
			<tr>
				<td width="120"><?php echo _('Parent chapter'); ?></td>
				<td><?php
					$chapter = $app->apiLoad('chapter')->chapterGet(array(
						'language'		=> 'fr',
						'mid_chapter'	=> 0,
						'threadFlat'	=> true,
						'noid_chapter' 	=> $data['id_chapter']
					));
	
					echo "<select name=\"mid_chapter\"><option value=\"0\"></option>";
					foreach($chapter as $e){
						$sel = ($e['id_chapter'] == $app->formValue($data['mid_chapter'], $_POST['mid_chapter'])) ? ' selected' : NULL;
						echo "<option value=\"".$e['id_chapter']."\"".$sel.">".str_repeat('&nbsp; &nbsp;', $e['level']).' '.$e['chapterName']."</option>";
					}
					echo "</select>";
	
				?></td>
			</tr>
			<tr>
				<td><?php echo _('Force a theme'); ?></td>
				<td>
					<select name="id_theme"><option></option><?php
					foreach($app->dbMulti("SELECT * FROM k_theme") as $e){
						$sel = ($e['id_theme'] == $app->formValue($data['id_theme'], $_POST['id_theme'])) ? ' selected' : NULL;
						echo "<option value=\"".$e['id_theme']."\"".$sel.">".$e['themeName']."</option>";
					}
				?></select>
			</td>
			</tr>
		</table>
	
		<div class="tabset">
			<ul class="tab clearfix"><?php
				foreach($languages as $e){
					echo "<li class=\"is-tab ".$class."\" id=\"tab-".$e['iso']."\"><a href=\"javascript:tab('".$e['iso']."');\">".$e['countryLanguage']."</a></li>";
				}
			?></ul>
			
			<?php foreach($languages as $i => $e){ $iso = $e['iso'] ?>
			<div class="view" style="" id="view-<?php echo $iso ?>">
				<input type="hidden" name="errorUrl-<?php echo $iso ?>" id="errorUrl-<?php echo $iso ?>" value="" />
	
				<table border="0" cellpadding="3" cellspacing="0" width="100%">
					<tr>
						<td class="view-split" colspan="2"><?php
			
							if($i == 0){
								echo "<input type=\"hidden\" name=\"ref\" value=\"".$iso."\" /> ";
								echo "<div class=\"alert\">";
								echo _('This is reference language');
								echo "</div>";
							}else{
								$copy = ($data['lan'][$iso]['is_copy'] && $i > 0) ? true : false;
								$chk  = ($copy) ? ' checked' : NULL;
			
								echo "<input type=\"hidden\"   name=\"lan[".$iso."][copy]\" value=\"0\" />";
								echo "<div class=\"alert\"><input type=\"checkbox\" name=\"lan[".$iso."][copy]\" value=\"1\" ".$chk." id=\"copy-".$iso."\" onChange=\"toggleCopy('".$iso."')\" />";
								echo _('Same as reference');
							}
												
						?></td>
					</tr>
					<tr>
						<td width="100" class="<?php echo $app->formError('chapterName-'.$iso, 'alertNeedToCheck') ?>"><?php echo _('Name'); ?></td>
						<td>
							<input type="text" name="lan[<?php echo $iso ?>][chapterName]" id="chapterName-<?php echo $iso ?>" value="<?php echo $app->formValue($data['lan'][$iso]['chapterName'], $_POST['lan'][$iso]['chapterName']); ?>" <?php if($copy) echo "disabled=\"disabled\""; ?> class="item-<?php echo $iso ?>" style="width:90%" />
							<input type="checkbox" id="transform-<?php echo $iso ?>" />
						</td>
					</tr>
					<tr>
						<td class="<?php echo $app->formError('chapterUrl-'.$iso, 'alertNeedToCheck') ?>"><?php echo _('Url'); ?></td>
						<td><input type="text" name="lan[<?php echo $iso ?>][chapterUrl]" id="chapterUrl-<?php echo $iso ?>" value="<?php echo $app->formValue($data['lan'][$iso]['chapterUrl'], $_POST['lan'][$iso]['chapterUrl']); ?>" <?php if($copy) echo "disabled=\"disabled\""; ?> class="item-<?php echo $iso ?>" style="width:90%;" /></td>	
					</tr>
					<tr>
						<td></td>
						<td><span id="alert-<?php echo $iso ?>" style="display:none;"><?php echo _('Duplicate - change name'); ?></span>&nbsp;</td>
					</tr>
					<tr>
						<td><?php echo _('Module'); ?></td>
						<td><input type="text" name="lan[<?php echo $iso ?>][chapterModule]" id="chapterModule-<?php echo $iso ?>" value="<?php echo $app->formValue($data['lan'][$iso]['chapterModule'], $_POST['lan'][$iso]['chapterModule']); ?>" <?php if($copy) echo "disabled=\"disabled\""; ?> class="item-<?php echo $iso ?>" style="width:75%" /></td>
					</tr>
					<tr>
						<td><?php echo _('Media'); ?></td>
						<td>
							<input type="text" name="lan[<?php echo $iso ?>][chapterMedia]" id="chapterMedia-<?php echo $iso ?>" value="<?php echo $app->formValue($data['lan'][$iso]['chapterMedia'], $_POST['lan'][$iso]['chapterMedia']); ?>" <?php if($copy) echo "disabled=\"disabled\""; ?> class="item-<?php echo $iso ?>" style="width:75%" />
							<a href="#" onclick="mediaOpen('line', 'chapterMedia-<?php echo $iso ?>')">choisir</a>
						</td>
					</tr>
					<tr valign="top">
						<td><?php echo _('Description'); ?></td>
						<td><textarea name="lan[<?php echo $iso ?>][chapterDescription]" id="chapterDescription-<?php echo $iso ?>" <?php if($copy) echo "disabled=\"disabled\""; ?> class="item-<?php echo $iso ?>" style="width:99%; height:70px;"><?php echo $app->formValue($data['lan'][$iso]['chapterDescription'], $_POST['lan'][$iso]['chapterDescription']); ?></textarea></td>
					</tr>
					<?php if(sizeof($fields) > 0){ ?>
					<tr>
						<td colspan="2" style="background:#A6B5BE; font-weight:bold; padding:4px; height:18px;">Paramètres supplémentaires</td>
					</tr>
					<?php foreach($fields as $f){ ?>
					<tr valign="top">
						<td><?php echo $f['fieldName'] ?></td>
						<td><?php

							$tmp = $app->formValue($data['lan'][$iso]['field'][$f['fieldKey']], $_POST['lan'][$iso]['field'][$f['id_field']]);
							$app->apiLoad('field')->fieldTrace($iso, $copy, $tmp, $f);

						?></td>
					</tr>
					<?php }} ?>
				</table>
			</div>
			<?php } ?>
		</div>

		<a onclick="$('#data').submit();" class="btn btn-mini"><?php echo _('Validate'); ?></a>
		<a href="./" class="btn btn-mini"><?php echo _('Cancel'); ?></a>
	
	</form>

</div></div></div>

<?php include(COREINC.'/end.php'); ?>
<script type="text/javascript" src="ui/js/chapter.js"></script> 

<script>
	languages = [<?php echo implode(',', $isoJS) ?>];
	function recherche(f){
		$('.sniff').each(function(me){
			// http://stackoverflow.com/questions/1789945/javascript-string-contains
			if ($(this).find('.data a').html().toLowerCase().indexOf(f.val().toLowerCase()) != -1) {
				$(this).css('display', '');
			} else {
				$(this).css('display', 'none');
			}
			
			if ( f.val() == "" ) $('.sniff').parent().css('display', '');
		});
	}
</script>

</body></html>