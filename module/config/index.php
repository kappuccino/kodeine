<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	$airFile = KROOT.'/user/config/.onair';

	# Save
	#
	if($_POST['action']){

		$keys = array(
			'configMailTo', 'configMailCc', 'configMailBcc',
			'defaultIdTheme', 'defaultIdChapter', 'defaultLanguage',
			'dateFormat', 'timeFormat',
			'defautAnalytic'
		);

		foreach($keys as $k){
			$exi = $app->dbOne("SELECT 1 FROM k_config WHERE configModule='boot' AND configName='".$k."'");
			$q	 = ($exi[1])
				? "UPDATE k_config SET configValue='".addslashes($_POST[$k])."' WHERE configModule='boot' AND configName='".$k."'"
				: "INSERT INTO k_config (configModule, configName, configValue) VALUES ('boot', '".$k."', '".addslashes($_POST[$k])."')";

			$app->dbQuery($q);
		}

		if(sizeof($_POST['domain']) > 0){
			foreach($_POST['domain'] as $tag => $dom){
				if($tag == 'new' && trim($dom['domain']) != ''){
					$exi = $app->dbOne("SELECT 1 FROM k_config WHERE configModule='boot' AND configName='domain:".$dom['domain']."'");
					if(!$exi[1]) $app->dbQuery("INSERT INTO k_config (configModule, configName) VALUES ('boot', 'domain:".$dom['domain']."')");
				}else
				if(trim($dom['domain']) == ''){
					$app->dbQuery("DELETE FROM k_config WHERE configModule='boot' AND configName='domain:".$tag."'");
				}

				if(trim($dom['domain']) != ''){
					$app->dbQuery("UPDATE k_config SET configValue='".addslashes(json_encode($dom))."' WHERE configModule='boot' AND configName='domain:".trim($dom['domain'])."'");
				}
			}
		}

        if(sizeof($_POST['field']) > 0){
            $i = 0;
            foreach($_POST['field'] as $ext_id => $ext_value){
                $val = $app->apiLoad('field')->fieldSaveValue($ext_id, $ext_value);
                $app->dbQuery("UPDATE k_config SET configValue='".addslashes($val)."' WHERE configModule='bootExt' AND configName='".$i.":id_field:".$ext_id."'");
                #	$app->pre($app->db_query, $app->db_error);
                $i++;
            }
        }

		if(isset($_POST['onAir'])){
			if(!file_exists($airFile)) touch($airFile);
		}else{
			if(file_exists($airFile)) unlink($airFile);
		}

		$app->go("./?saved");
	}

	# Data
	#
	$db  = $app->dbMulti("SELECT * FROM k_config WHERE configModule='boot'");
	$ext = $app->dbMulti("SELECT * FROM k_config WHERE configModule='bootExt'");
	$dom = array();
	foreach($db as $e){
		if(preg_match("#^domain:#", $e['configName'])){
			$dom[] = json_decode(stripslashes($e['configValue']), true);
		}else{
			$data[$e['configName']] = $e['configValue'];
		}
	}

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
    <link rel="stylesheet" type="text/css" href="../content/ui/css/data.css" />
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div class="inject-subnav-right hide">
	<li><a href="./" class="btn btn-small"><?php echo _('Cancel') ?></a></li>
	<li><a onclick="$('#data').submit()" class="btn btn-small btn-success"><?php echo _('Save'); ?></a></li>
</div>

<div id="app"><div class="wrapper">

	<?php if(isset($_GET['saved'])) echo '<div class="message messageValid">'._('Configuration updated').'</div>'; ?>

	<form action="./" method="post" id="data">
	
		<input type="hidden" name="action" value="1" />
	
		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing">
			<thead>
				<tr>
					<th width="25%"><?php echo _('Parameter') ?></th>
					<th width="25%"><?php echo _('Value') ?></th>
					<th width="50%"><?php echo _('Explanation') ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php echo _('Mail recipient') ?></td>
					<td><input type="text" name="configMailTo" value="<?php echo $app->formValue($data['configMailTo'], $_POST['configMailTo']) ?>" style="width:80%;" /></td>
					<td><?php echo _('Website\'s email recipient') ?></td>
				</tr>
				<tr>
					<td><?php echo _('Copy') ?></td>
					<td><input type="text" name="configMailCc" value="<?php echo $app->formValue($data['configMailCc'], $_POST['configMailCc']) ?>" style="width:80%;" /></td>
					<td><?php echo _('Website\'s carbon copy email recipient') ?></td>
				</tr>
				<tr>
					<td><?php echo _('Blind copy') ?></td>
					<td><input type="text" name="configMailBcc" value="<?php echo $app->formValue($data['configMailBcc'], $_POST['configMailBcc']) ?>" style="width:80%;" /></td>
					<td><?php echo _('Website\'s blind copy email recipient') ?></td>
				</tr>
				<tr>
					<td><?php echo _('Theme') ?></td>
					<td><select name="defaultIdTheme"><?php
						foreach($app->dbMulti("SELECT * FROM k_theme") as $e){
							$sel = ($e['id_theme'] == $app->formValue($data['defaultIdTheme'], $_POST['defaultIdTheme'])) ? ' selected' : NULL;
							echo "<option value=\"".$e['id_theme']."\"".$sel.">".$e['themeName']."</option>";
						}
					?></select></td>
					<td><?php echo _('Default theme — Could be changed by chapters and/or modules') ?></td>
				</tr>
				<tr>
					<td><?php echo _('Chapter') ?></td>
					<td><?php echo
						$app->apiLoad('chapter')->chapterSelector(array(
							'value'		=> $app->formValue($data['defaultIdChapter'], $_POST['defaultIdChapter']),
							'name'		=> 'defaultIdChapter',
							'language'	=> 'fr',
							'one'		=> true
						))
					?></td>
					<td><?php echo _('Default chapter for homepage') ?></td>
				</tr>
				<tr>
					<td><?php echo _('Language') ?></td>
					<td><select name="defaultLanguage"><?php
						$language = $app->countryGet();
						foreach($language as $e){
							$sel = ($e['iso'] == $app->formValue($data['defaultLanguage'], $_POST['defaultLanguage'])) ? ' selected' : NULL;
							echo "<option value=\"".$e['iso']."\"".$sel.">".$e['countryLanguage']."</option>";
						}
					?></select></td>
					<td><?php echo _('Default language if not specified in the URL') ?></td>
				</tr>
				<tr>
					<td><?php echo _('Date format') ?></td>
					<td><select name="dateFormat"><?php
						$dates = array(
							'%A %e %B %Y',		// jeudi 13 fevrier 2008
							'%a. %d %b. %Y',	// jeu. 9 fev. 2008
							'%e %B %Y',			// 13 fevrier 2008
							'%d %B %Y',			// 9 Fevrier 2008
							'%d %b %Y',			// 9 fev 2008
							'%e/%m/%y',			// 9/2/06
							'%e/%m/%Y',			// 09/02/2006
							'%d/%m', 			// 9/2
							'%d-%b-%Y', 		// 9-FEV-2006
							'%d-%b-%y', 		// 9-FEV-06
							'%b %y', 			// FEV.-06
							'%e %b %Y'  		// 9 Fev 2006
						);

						foreach($dates as $e){
							$sel = ($e == $app->formValue($data['dateFormat'], $_POST['dateFormat'])) ? ' selected' : NULL;
							echo "<option value=\"".$e."\"".$sel.">".strftime($e)."</option>";
						}
					?></select></td>
					<td><?php echo _('Date format') ?></td>
				</tr>
				<tr>
					<td><?php echo _('Time format') ?></td>
					<td><select name="timeFormat"><?php
						$times = array(
							'%R',				// 14:10
							'%R:%S',			// 14:20:30
							'%Hh%M'				// 14h10
						);
						foreach($times as $e){
							$sel = ($e == $app->formValue($data['timeFormat'], $_POST['timeFormat'])) ? ' selected' : NULL;
							echo "<option value=\"".$e."\"".$sel.">".strftime($e)."</option>";
						}
					?></select></td>
					<td><?php echo _('Change time format') ?></td>
				</tr>
				<tr>
					<td><?php echo _('Google Analytics') ?></td>
					<td><input type="text" name="defautAnalytic" value="<?php echo $app->formValue($data['defautAnalytic'], $_POST['defautAnalytic']) ?>" style="width:80%;" /></td>
					<td><?php echo _('Google Analytics ID (UA-YYYYYYY-XX)') ?></td>
				</tr>
				<tr>
					<td><?php echo _('In production') ?></td>
					<td><input type="checkbox" name="onAir" value="YES" <?php if(file_exists($airFile)) echo ' checked' ?> /></td>
					<td><?php echo _('Toggle development mode ON or OFF') ?></td>
				</tr>
			</tbody>
		</table>
	
		<div class="mar-top-20"><?php
			echo _('You can change default values for a specific domain name.');
			echo ' &nbsp; &nbsp; ';
			echo _('Note: you can use regular expression to define a domaine name. (www\.)?kappuccino.org to match the main domain and www sub domaine');
		?></div>

		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing mar-top-10">
			<thead>
				<tr>
					<th width="20%"><?php echo _('Domain name') ?></th>
					<th width="20%"><?php echo _('Chapter') ?></th>
					<th width="20%"><?php echo _('Theme') ?></th>
					<th width="20%"><?php echo _('Language') ?></th>
					<th width="20%"><?php echo _('Google Analytics') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($dom as $e){ $k = $e['domain'] ?>
				<tr>
					<td><input type="text" name="domain[<?php echo $k ?>][domain]" value="<?php echo $app->formValue($e['domain'], $_POST['domain'][$k]['domain']) ?>" size="35" /></td>
					<td><?php echo
						$app->apiLoad('chapter')->chapterSelector(array(
							'value'		=> $app->formValue($e['id_chapter'], $_POST['domain'][$k]['id_chapter']),
							'name'		=> 'domain['.$k.'][id_chapter]',
							'language'	=> 'fr',
							'one'		=> true,
							'empty'		=> true
						));
					?></td>
					<td><select name="domain[<?php echo $k ?>][id_theme]"><option></option><?php
						foreach($app->dbMulti("SELECT * FROM k_theme") as $t){
							$sel = ($t['id_theme'] == $app->formValue($e['id_theme'], $_POST['domain'][$k]['id_theme'])) ? ' selected' : NULL;
							echo "<option value=\"".$t['id_theme']."\"".$sel.">".$t['themeName']."</option>";
						}
					?></select></td>
					<td><select name="domain[<?php echo $k ?>][language]"><option></option><?php
						foreach($app->countryGet() as $l){
							$sel = ($l['iso'] == $app->formValue($l['language'], $_POST['domain'][$k]['language'])) ? ' selected' : NULL;
							echo "<option value=\"".$l['iso']."\"".$sel.">".$l['countryLanguage']."</option>";
						}
					?></select></td>
					<td><input type="text" name="domain[<?php echo $k ?>][analytic]" value="<?php echo $app->formValue($e['analytic'], $_POST['domain'][$k]['analytic']) ?>" size="20" /></td>
				</tr>
				<?php } ?>
				<tr>
					<td><input type="text" name="domain[new][domain]" value="<?php echo $app->formValue('', $_POST['domain']['new']['domain']) ?>" size="35" /></td>
					<td><?php echo
						$app->apiLoad('chapter')->chapterSelector(array(
							'value'		=> $app->formValue('', $_POST['domain']['new']['id_chapter']),
							'name'		=> 'domain[new][id_chapter]',
							'language'	=> 'fr',
							'one'		=> true,
							'empty'		=> true
						))
					?></td>
					<td><select name="domain[new][id_theme]"><option></option><?php
						foreach($app->dbMulti("SELECT * FROM k_theme") as $e){
							echo "<option value=\"".$e['id_theme']."\">".$e['themeName']."</option>";
						}
					?></select></td>
					<td><select name="domain[new][language]"><option></option><?php
						foreach($app->countryGet() as $e){
							echo "<option value=\"".$e['iso']."\">".$e['countryLanguage']."</option>";
						}
					?></select></td>
					<td><input type="text" name="domain[new][analytic]" value="<?php echo $app->formValue('', $_POST['domain']['new']['analytic']) ?>" size="20" /></td>
				</tr>
			</tbody>
		</table>
		
		<table cellpadding="0" cellspacing="0" border="0" class="listing form mar-top-20">
			<thead>
				<tr>
					<th colspan="2"><?php echo _('More parameters') ?></th>
				</tr>
			</thead>
			<tbody><?php

				function fieldTrace($app, $data, $e){

					$field = $app->apiLoad('field')->fieldForm(
						$e['id_field'],
						$data,
						array(
							'style' => 'width:100%; '.$e['fieldStyle']
						)
					);

					if(preg_match("#richtext#", 	$field)) $GLOBALS['textarea'][]	 = 'form-field-'.$e['id_field'];
					if(preg_match("#media\-list#", 	$field)) $GLOBALS['mediaList'][] = "'form-field-".$e['id_field']."'";

					echo '<tr>';
						echo '<td width="25%">'.$e['fieldName'];
						if($e['is_needed']) echo ' *';
						echo '</td>';
	
						echo '<td>';
							if(preg_match("#richtext#", $field)){
								echo "<br /><a href=\"javascript:toggleEditor('form-field-".$e['id_field']."');\">Activer/Désactiver l'éditeur</a>";
							}

							echo $field;
						echo '</td>';
					echo '</tr>';
				}

				foreach($ext as $e){
					list($pos, $n, $id_field) = explode(':', $e['configName']);

					$field = $app->apiLoad('field')->fieldGet(array(
						'debug'		=> false,
						'id_field'	=> $id_field
					));
		
					fieldTrace($app, $app->formValue($e['configValue'], $_POST['ext'][$e['id_field']]), $field); 
				}

			?></tbody>
		</table>
	</form>

</div></div>

<?php include(COREINC.'/end.php'); ?>
</body></html>