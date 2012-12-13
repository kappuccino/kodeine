<?php

	# REMOVE FIELD
	#
	if(sizeof($_POST['killme']) > 0){
		foreach($_POST['killme'] as $e){
			$app->apiLoad('field')->fieldRemove($e);
		}
	}else

	# INSERT / UPDATE
	#
	if($_POST['action']){
		$do = true;
		$def['k_field'] = array(
			'is_search'			=> array('value' => $_POST['is_search'],			'zero'  => true),
			'is_needed'			=> array('value' => $_POST['is_needed'],			'zero'  => true),
			'is_editor'			=> array('value' => $_POST['is_editor'],			'zero'  => true),
			'fieldName'			=> array('value' => $_POST['fieldName'], 			'check' => '.'),
			'fieldType' 		=> array('value' => $_POST['fieldType']),
			'fieldKey' 			=> array('value' => $_POST['fieldKey'], 			'check' => '.'),
			'fieldInstruction'	=> array('value' => $_POST['fieldInstruction']),
			'fieldContentType'	=> array('value' => $_POST['fieldContentType'],		'zero'  => true),
			'fieldUserField'	=> array('value' => $_POST['fieldUserField'],		'zero'  => true),
			'fieldParam'		=> array('value' => utf8_encode(json_encode($_POST['param']))),
			'fieldStyle'		=> array('value' => $_POST['fieldStyle']),
			'fieldShowForm'		=> array('value' => $_POST['fieldShowForm'],		'zero'	=> true)
		);

		if(!$app->formValidation($def)) $do = false;

		if($do){
			$result = $app->apiLoad('field')->fieldSet($_POST['id_field'], $def);
			$message = ($result)
				? 'OK: Enregistrement dans la base'
				: 'KO: Une erreur est survenue, APP:<br />'.$app->db_error;

			if($result){
				$app->apiLoad('field')->fieldChoiceSet($app->apiLoad('field')->id_field, $_POST['choice']);
				$app->apiLoad('field')->fieldCacheBuild();
			}
		}else{
			$message = 'KO: Validation failed';
		}

	}

	# GET DATA
	#
	if($_REQUEST['id_field'] != NULL){
		$data = $app->apiLoad('field')->fieldGet(array(
			'id_field'		=> $_REQUEST['id_field'],
			'debug'			=> false
		));

		$data['fieldParam']		= json_decode($data['fieldParam'], true);
	}else
	if(sizeof($_POST['fieldShowForm']) == 0){
		$data['fieldShowForm'] = 1;
	}

	$field		= $app->apiLoad('field')->fieldGet(array('debug' => false));
	$fieldType	= $app->formValue($data['fieldType'], $_POST['fieldType']);
	$types		= $app->apiLoad('field')->fieldTypeGet();
	$fieldView	= $types[$fieldType]['view'];

	$val		= $app->formValue($data['fieldParam']['type'], $_POST['param']['type']);

?><!DOCTYPE html>
<html lang="fr">
<head>
	<title>Kodeine</title>
	<?php
		echo $app->less('/admin/field/ui/css/field.less');
		include(COREINC.'/head.php');
	?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(dirname(__DIR__).'/content/ui/menu.php')
?></header>

<div id="app"><div class="wrapper">

	<?php if(sizeof($_POST['remove']) > 0 ){ ?>
		<div class="message messageWarning alert alert-error">
			<form action="/admin/field/index" method="post" class="nomargin">
				<b>ATTENTION</b> vous êtes sur le point de supprimer un ou plusieurs champs, cette action est irrémédiable
			
				<?php foreach($_POST['remove'] as $e){ ?>
				<input type="hidden" name="killme[]" value="<?php echo $e ?>" />
				<?php } ?>
				
				<button type="submit" class="btn btn-mini">Confirmer la suppression</button>
			</form>
		</div>
		<br style="clear: both;" />
	<?php } ?>
<div class="row-fluid">
<form action="./" method="post" id="listing" class="span6">
		<table border="0" cellpadding="0" cellspacing="0" class="listing">
			<thead>
				<tr>
					<th width="20" class="icone"></th>
					<th width="50%">Nom</th>
					<th class="filter">
						<span>Code</span>
						<input type="text" id="filter" class="input-small" placeholder="Filtrer..." />
					</th>
				</tr>
			</thead>
			<tbody>
			<?php if(sizeof($field) > 0){ foreach($field as $count => $e){ ?>
				<tr class="<?php if($e['id_field'] == $_REQUEST['id_field']) echo "selected" ?>">
					<td><input type="checkbox" name="remove[]" value="<?php echo $e['id_field'] ?>" class="cb" /></td>
					<td><a href="./?id_field=<?php echo $e['id_field'] ?>"><?php echo $e['fieldName'] ?></a></td>
					<td><a href="./?id_field=<?php echo $e['id_field'] ?>"><?php echo $e['fieldKey'] ?></a></td>
				</tr>
			<?php } }else{ ?>
				<tr>
					<td colspan="5" class="noData">Aucun champ</td>
				</tr>
			<?php } ?>
			</tbody>
			<?php if(sizeof($field) > 0){ ?>
			<tfoot>
				<tr>
					<td width="30" height="25" class="check-red"><input type="checkbox" class="chk" id="chk_remove_all" onchange="cbchange($(this));" /></td>
					<td><a onClick="remove();" class="btn btn-mini">Supprimer</a></td>
					<td align="right"><a href="asso?id_type=category" class="btn btn-mini">Relier les champs aux types</a></td>
				</tr>
			</tfoot>
			<?php } ?>
		</table>
	</form>

	<form action="./" method="post" id="data" class="span6">
	
	<input type="hidden" name="action" value="1" />
	<input type="hidden" name="id_field" value="<?php echo $data['id_field'] ?>" />

	<?php
		if($message != NULL){
			list($class, $message) = $app->helperMessage($message);
			echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
		}
	?>
	
	<table cellpadding="0" cellspacing="0" border="0" class="form">
		<tr>
			<td width="75">Recherche</td>
			<td><input type="checkbox" name="is_search" value="1" <?php if($app->formValue($data['is_search'], $_POST['is_search'])) echo " checked" ?> /></td>
		</tr>
		<tr>
			<td>Obligatoire</td>
			<td><input type="checkbox" name="is_needed" value="1" <?php if($app->formValue($data['is_needed'], $_POST['is_needed'])) echo " checked" ?> /></td>
		</tr>
		<tr>
			<td>Visible</td>
			<td><input type="checkbox" name="fieldShowForm" value="1" <?php if($app->formValue($data['fieldShowForm'], $_POST['fieldShowForm'])) echo " checked" ?> /></td>
		</tr>
		<tr>
			<td>Nom</td>
			<td><input type="text" name="fieldName" value="<?php echo $app->formValue($data['fieldName'], $_POST['fieldName']); ?>" /></td>
		</tr>
		<tr>
			<td>Code</td>
			<td><input type="text" name="fieldKey" value="<?php echo $app->formValue($data['fieldKey'], $_POST['fieldKey']); ?>" /></td>
		</tr>
		<tr>
			<td></td>
			<td><i>Utilisé pour nommer un champ dans le code, n'utiliser que des lettres. 
			Changer le contenu de ce champ pourrait provoquer des problèmes sur le site</i></td>
		</tr>
		<tr valign="top">
			<td>Instruction</td>
			<td><textarea name="fieldInstruction" rows="5" cols="40"><?php echo $app->formValue($data['fieldInstruction'], $_POST['fieldInstruction']); ?></textarea></td>
		</tr>
		<tr valign="top">
			<td>Style</td>
			<td>
				<textarea name="fieldStyle" rows="4" cols="40"><?php echo $app->formValue($data['fieldStyle'], $_POST['fieldStyle']) ?></textarea><br />
				<i>styles appliqués au champ (format css en ligne)</i><br /><br />
				<input type="checkbox" class="chk" id="chk_tinymce" name="is_editor" value="1" <?php if($app->formValue($data['is_editor'], $_POST['is_editor'])) echo " checked"; ?> />
				Utiliser un editeur de texte enrichi
				<br /><br /><br /><br />
			</td>
		</tr>
		<tr valign="top">
			<td>Type</td>
			<td><select name="fieldType" id="fieldType" onChange="changeType()"><?php
				foreach($types as $k => $e){	
					$sel = ($fieldType == $k) ? ' selected' : NULL;
					echo "<option value=\"".$k."\"".$sel.">".$e['name']."</option>\n";
				}
			?></select></td>
		</tr>

		<tr class="line-type line-texte-line <?php echo ($fieldView == 'texte-line') ? '' : 'line-off' ?>">
			<td></td>
			<td>Ce type de champs n'a pas d'option</td>
		</tr>
		<tr class="line-type line-integer <?php echo ($fieldView == 'integer') ? '' : 'line-off' ?>">
			<td></td>
			<td>Ce type de champs n'a pas d'option</td>
		</tr>
		<tr class="line-type line-media <?php echo ($fieldView == 'media') ? '' : 'line-off' ?>">
			<td></td>
			<td>Ce type de champs n'a pas d'option</td>
		</tr>
		<tr class="line-type line-date <?php echo ($fieldView == 'date') ? '' : 'line-off' ?>">
			<td></td>
			<td>Ce type de champs n'a pas d'option</td>
		</tr>
	
		<tr class="line-type line-content-type <?php echo ($fieldView == 'contentType') ? '' : 'line-off' ?>">
			<td></td>
			<td>Ce type de champs n'a pas d'option</td>
		</tr>
	
		<tr class="line-type line-category <?php echo ($fieldView == 'category') ? '' : 'line-off' ?>">
			<td></td>
			<td>Type
				<select name="param[type]"><?php
					if($val == '') $val = 'solor';
	
					$tps = array(
						'solo'		=> 'Un seul choix possible',
						'multi'		=> 'Plusieurs choix possible'
					);
					
					foreach($tps as $k => $v){
						echo "<option value=\"".$k."\"".(($k == $val) ? ' selected' : '').">".$v."</option>";
					}
				?></select>
				<i>Le changement de type (solo / multi) influ sur le type de champ de la base de donn&eacute;es et peu entrainer des perte de donn&eacute;es</i>
	
			</td>
		</tr>
	
		<tr class="line-type line-social-forum <?php echo ($fieldView == 'socialForum') ? '' : 'line-off' ?>">
			<td></td>
			<td>Type
				<select name="param[type]"><?php
					if($val == '') $val = 'multi';
	
					$tps = array(
						'multi'		=> 'Plusieurs choix possible',
						'solo'		=> 'Un seul choix possible',
					#	'select'	=> 'Un seul choix possible (menu deroulant)'
					);
					
					foreach($tps as $k => $v){
						echo "<option value=\"".$k."\"".(($k == $val) ? ' selected' : '').">".$v."</option>";
					}
				?></select>
			</td>
		</tr>
	
		<tr class="line-type line-multichoice line-onechoice <?php echo ($fieldView == 'choice') ? '' : 'line-off' ?>">
			<td></td>
			<td><?php $choices = $app->apiLoad('field')->fieldChoiceGet(array('id_field' => $data['id_field'])); ?>
				<a href="javascript:addChoice()" class="button button-green">Ajouter un élement</a><br style="clear:both" />
				<ul id="choices">
					<?php foreach($choices as $e){ ?>
					<li id="<?php echo $e['id_fieldchoice'] ?>">
						<span class="move"></span>
						<input type="checkbox" name="choice[remove][]" value="<?php echo $e['id_fieldchoice'] ?>" />
						<textarea name="choice[<?php echo $e['id_fieldchoice'] ?>]" cols="40" rows="2"><?php echo $e['choiceValue'] ?></textarea>
					</li>
					<?php } ?>
				</ul>
			</td>
		</tr>
		<tr class="line-type line-content <?php echo ($fieldView == 'content') ? '' : 'line-off' ?>">
			<td></td>
			<td>Type
				<select name="fieldContentType"><?php
					foreach($app->apiLoad('content')->contentType() as $e){
						$sel = ($app->formValue($data['fieldContentType'], $_POST['fieldContentType']) == $e['id_type']) ? ' selected' : NULL;
						echo "<option value=\"".$e['id_type']."\"".$sel.">".$e['typeName']."</option>";
					}
				?></select>
				<select name="param[type]"><?php
					if($val == '') $val = 'multi';
	
					$tps = array(
						'multi'		=> 'Plusieurs choix possible',
						'solo'		=> 'Un seul choix possible',
						'select'	=> 'Un seul choix possible (menu deroulant)'
					);
					
					foreach($tps as $k => $v){
						echo "<option value=\"".$k."\"".(($k == $val) ? ' selected' : '').">".$v."</option>";
					}
				?></select>
			</td>
		</tr>
		<tr class="line-type line-user <?php echo ($fieldView == 'user') ? '' : 'line-off' ?>">
			<td></td>
			<td><?php $fields = $app->apiLoad('field')->fieldGet(array('user' => true)); ?>
				<table border="0" class="5">
					<tr>
						<td width="20"></td>
						<td>
							<select name="param[type]"><?php
								if($val == '') $val = 'multi';
	
								$tps = array(
									'multi'		=> 'Plusieurs choix possible',
									'solo'		=> 'Un seul choix possible',
									'select'	=> 'Un seul choix possible (menu deroulant)'
								);
	
								foreach($tps as $k => $v){
									echo "<option value=\"".$k."\"".(($k == $val) ? ' selected' : '').">".$v."</option>";
								}
							?></select>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>Champs</td>
					</tr>
					<?php
						$selected = $app->formValue($data['fieldParam'], $_POST['param']);
						foreach($fields as $e){
							$sel = @in_array($e['id_field'], $selected['id_field']);
					?>
					<tr>
						<td><input type="checkbox" name="param[id_field][]" value="<?php echo $e['id_field'] ?>" <? if($sel) echo 'checked'; ?> /></td>
						<td><?php echo $e['fieldName'] ?></td>
					</tr>
					<?php } ?>
				</table>
	
			</td>
		</tr>
		<tr class="line-type line-dbtable <?php echo ($fieldView == 'dbtable') ? '' : 'line-off' ?>">
			<td></td>
			<td>
				<table border="0" class="5">
					<tr>
						<td width="100">Table</td>
						<td><input type="text" name="param[table]" value="<?php echo $app->formValue($data['fieldParam']['table'], $_POST['param']['table']) ?>" /></td>
					</tr>
					<tr>
						<td>ID Maitre</td>
						<td><input type="text" name="param[id]" value="<?php echo $app->formValue($data['fieldParam']['id'], $_POST['param']['id']) ?>" /></td>
					</tr>
					<tr>
						<td>Champ visible</td>
						<td><input type="text" name="param[field]" value="<?php echo $app->formValue($data['fieldParam']['field'], $_POST['param']['field']) ?>" /></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><select name="param[type]"><?php
							if($val == '') $val = 'multi';
	
							$tps = array(
								'multi'		=> 'Plusieurs choix possible',
								'solo'		=> 'Un seul choix possible',
								'select'	=> 'Un seul choix possible (menu deroulant)'
							);
							
							foreach($tps as $k => $v){
								echo "<option value=\"".$k."\"".(($k == $val) ? ' selected' : '').">".$v."</option>";
							}
						?></select>
						</td>
					</tr>
					<tr>
						<td colspan="2">Condition WHERE</td>
					</tr>
					<tr>
						<td colspan="2">
							<textarea name="param[where]" style="width:300px; height:100px;"><?php
								echo $app->formValue($data['fieldParam']['where'], $_POST['param']['where']);
							?></textarea>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td height="30"></td>
			<td>
				<a href="javascript:$('#data').submit();" class="btn btn-mini">Enregistrer</a>
				<a href="/admin/field/index" class="btn btn-mini">Nouveau</a>
			</td>
		</tr>
	</table>
	</form>

</div></div></div>
	
<?php include(COREINC.'/end.php'); ?>
<script src="/app/module/core/ui/_datatables/jquery.dataTables.js"></script>
<script src="ui/js/field.js"></script>

</body></html>