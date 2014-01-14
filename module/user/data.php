<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	if($_POST['action']){
	#	die($app->pre($_POST));

		$app->dbQuery("UPDATE k_group SET groupFormLayout='".$_POST['groupFormLayout']."' WHERE id_group=".$_REQUEST['id_group']);
		#$app->pre($app->db_query, $app->db_error);
		$do = true;

		$def['k_user'] = array(
			'id_profile'		=> array('value' => $_POST['id_profile'],		'zero'	=> true),
			'id_group'			=> array('value' => $_POST['id_group'],			'zero'	=> true),
			'is_admin'			=> array('value' => $_POST['is_admin'],			'zero'	=> true),
			'is_active'			=> array('value' => $_POST['is_active'],		'zero'	=> true),
			'userMail' 			=> array('value' => $_POST['userMail'],			'check' => '.'),
			'userDateCreate'	=> array('value' => $_POST['userDateCreate'],	'check' => '.'),
			'userDateExpire'	=> array('value' => $_POST['userDateExpire'],	'null'  => true),
			'userDateUpdate'	=> array('value' => $_POST['userDateUpdate'],	'check' => '.'),
			'userNewsletter'	=> array('value' => $_POST['userNewsletter'],	'zero'	=> true),
			'userMedia'			=> array('value' => $_POST['userMedia']),
		);

		if($_POST['userPasswd'] != NULL){
			if($_POST['userPasswd'] != $_POST['confPasswd']){
				$do = false;
			}else
			if($_POST['userPasswd'] == $_POST['confPasswd']){
				$def['k_user']['userPasswd'] = array('function' => 'MD5(\''.$_POST['userPasswd'].'\'	)');
			}
		}else{
			if($_POST['id_user'] == NULL) $do = false;
		}

		if(!$app->formValidation($def)) $do = false;

		if(!$app->apiLoad('field')->fieldValidation($_POST['field'])) $do = false;

		if($do){
			$result = $app->apiLoad('user')->userSet(array(
				'id_user'		=> $_POST['id_user'],
				'def'			=> $def,
				'field'			=> $_POST['field']
			));

			$message = ($result) ? 'OK: '._('Saved') : 'KO: Error : <br />'.$app->apiLoad('user')->db_query.' '.$app->apiLoad('user')->db_error;
			if($result) $app->go("data?id_user=".$app->apiLoad('user')->id_user);
		}else{
			$message = 'WA: '._('Please, fill the form correctly');
		}
	}

	if($_REQUEST['id_user'] != NULL){
		$data = $app->apiLoad('user')->userGet(array(
			'id_user' 	=> $_REQUEST['id_user'],
			'useField'	=> false
		));
		
		$group	= $app->apiLoad('user')->userGroupGet(array('id_group' => $data['id_group']));
	}else{
		$group	= $app->apiLoad('user')->userGroupGet();
		$group	= $group[0];
		$group['groupFormLayout'] = json_decode($group['groupFormLayout'], true);
	}

	if($group['groupFormLayout'] == ''){
		$group['groupFormLayout'] = array(
			'tab' => array(
				'view0' => array(
					'label' => 'Defaut',
					'field' => array()
				)
			),
			'bottom' => array(
				
			)
		);
	}
	
	if($group['id_group'] != ''){
		$fields = $app->apiLoad('field')->fieldGet(array(
			'id_group'	=> $group['id_group']
		));
	}else{
		$fields = array();
	}

	foreach($fields as $e){
		$fieldId['field'.$e['id_field']] = $e;
		$unAffected[] = 'field'.$e['id_field'];
	}
		$unAffected[] = 'id_group';
		$unAffected[] = 'id_profile';
		$unAffected[] = 'userMail';
		$unAffected[] = 'userPasswd';
		$unAffected[] = 'userDate';
		$unAffected[] = 'is_active';
		$unAffected[] = 'userNewsletter';
		$unAffected[] = 'userMediaBox';

		foreach($unAffected as $idxOFF => $f){
			foreach($group['groupFormLayout']['tab'] as $e){
				foreach($e['field'] as $fu){
					if($fu['field'] == $f) unset($unAffected[$idxOFF]);
				}
			}
		}

		foreach($unAffected as $e){
			$group['groupFormLayout']['tab']['view0']['field'][] = array('field' => $e);
		}


?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
	<link rel="stylesheet" type="text/css" href="../content/ui/css/data.css" />
	<link rel="stylesheet" type="text/css" href="../content/ui/css/dropdowns.css" />
	<link rel="stylesheet" type="text/css" href="../core/vendor/datepicker/css/datepicker.css" />
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div class="inject-subnav-right hide">
	<li><a href="data" class="btn btn-small"><?php echo _('New user') ?></a></li>
    <li><a href="../user/" class="btn btn-small"><i class="icon-list"></i> <?php echo _('Users') ?></a></li>
	<li><a href="#" onclick="$('#data').submit();" class="btn btn-small btn-success"><?php echo _('Save') ?></a></li>
</div>

<div id="app" class="data">

<?php
	if($message != NULL){
		list($class, $message) = $app->helperMessage($message);
		echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
	}
?>

<form action="data" method="post" id="data">
<input type="hidden" name="action" value="1" />
<input type="hidden" name="id_user" value="<?php echo $data['id_user'] ?>" />
<input type="hidden" name="groupFormLayout" id="groupFormLayout" />

<div class="tabset">

	<div class="wrapper"/>
		<ul class="tab clearfix">
			<?php foreach($group['groupFormLayout']['tab'] as $e){ ?>
			<li class="is-tab do-view"><span class="text"><?php echo $e['label'] ?></span><span class="edit"></span><span class="remove"></span><span class="handle"></span></li>
			<?php } ?>
		
			<li class="light do-wiew view-all"><span class="text"><?php echo _('See all'); ?></span></li>
			<li class="light" id="action-add-tab" class="hide"><a href="#" onclick="addTab($('.tabset')[0])"><?php echo _('Add a tab'); ?></a></li>
			<li id="action-move-on"><a href="#" onclick="enableMove()"><?php echo _('Edit tabs'); ?></a></li>
			<li id="action-move-off" class="hide"><a href="#" onclick="disableMove()"><?php echo _('Save tabs'); ?></a></li>
		</ul>
	</div>

	<?php foreach($group['groupFormLayout']['tab'] as $id => $tab){ ?>
	<div class="view view-tab" id="<?php echo $id ?>" style="display:none_;">
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
	<?php } ?>

	<div class="view">
		<div class="view-label">
			<span><?php echo _('Always visible'); ?></span>
		</div>
		<ul class="is-sortable field-list field-list-bottom"><?php
			foreach($group['groupFormLayout']['bottom'] as $f){

				$name	= $f['field'];
				$e 		= $fieldId[$name];

				if(is_array($e)){
					fieldTrace($app, $data, $e, $f);
				}else{
					echo "<div id=\"replace-".$name."\">".$name."</div>";
					$replace[] = '#'.$name;
				}
			}
		?></ul>
	</div>
</div>

<!-- ## ELEMENT DEPLACE AU BON ENDROIT A LA VOLEE ## -->
<ul style="display:none">
	<li id="userMail" class="clearfix form-item <?php echo $app->formError('userMail', 'needToBeFilled') ?>">
		<div class="hand">&nbsp;</div>
		<div class="toggle">&nbsp;</div>
		<label><?php echo _('Login/eMail'); ?></label>
		<div class="form"><input type="text" name="userMail" value="<?php echo $app->formValue($data['userMail'], $_POST['userMail']); ?>" /></div>
	</li>
	<li id="userPasswd" class="clearfix form-item <?php echo $app->formError('userPasswd', 'needToBeFilled') ?>">
		<div class="hand">&nbsp;</div>
		<div class="toggle">&nbsp;</div>
		<label><?php echo _('Password'); ?></label>
		<div class="form">
			<input type="text" name="userPasswd" value="<?php echo $app->formValue('', $_POST['userPasswd']); ?>" /> <?php echo _('confirm'); ?>
			<input type="text" name="confPasswd" value="<?php echo $app->formValue('', $_POST['confPasswd']); ?>" /> <?php echo _('Let both fields empty to keep the current password'); ?>
		</div>
	</li>
	<li id="userDate" class="clearfix form-item">
		<div class="hand">&nbsp;</div>
		<div class="toggle">&nbsp;</div>
		<label><?php echo _('Dates'); ?></label>
		<div class="form">
			<table border="0">
				<tr>
					<td width="60"><?php echo _('Created'); ?></td>
					<td width="120"><input type="text" name="userDateCreate" id="userDateCreate" value="<?php echo $app->formValue($data['userDateCreate'], $_POST['userDateCreate']); ?>" size="10" /><i class="icon-remove-sign clear"></i></td>
					<td width="70"><?php echo _('Updated'); ?></td>
					<td width="120"><input type="text" name="userDateUpdate" id="userDateUpdate" value="<?php echo $app->formValue($data['userDateUpdate'], $_POST['userDateUpdate']); ?>" size="10" /><i class="icon-remove-sign clear"></i></td>
					<td width="70"><?php echo _('Expired'); ?></td>
					<td width="120"><input type="text" name="userDateExpire" id="userDateExpire" value="<?php echo $app->formValue($data['userDateExpire'], $_POST['userDateExpire']); ?>" size="10" /><i class="icon-remove-sign clear"></i></td>
				</tr>
			</table>
		</div>
	</li>

	<li id="id_profile" class="clearfix form-item">
		<div class="hand">&nbsp;</div>
		<div class="toggle">&nbsp;</div>
		<label><?php echo _('Profile'); ?></label>
		<div class="form">
			<select name="id_profile"><?php
				echo "<option value=\"0\">Aucun profile</option>";
				foreach($app->apiLoad('user')->userProfileGet() as $p){
					$sel = ($p['id_profile'] == $app->formValue($data['id_profile'], $_POST['id_profile'])) ? ' selected' : NULL;
					echo "<option value=\"".$p['id_profile']."\"".$sel.">".$p['profileName']."</option>";
				}
			?></select>
		
			<?php echo _('Back office access'); ?>
			<input type="checkbox" name="is_admin" value="1" <?php if($app->formValue($data['is_admin'], $_POST['is_admin'])) echo " checked"; ?> />
		</div>
	</li>

	<li id="is_active" class="clearfix form-item">
		<div class="hand">&nbsp;</div>
		<div class="toggle">&nbsp;</div>
		<label><?php echo _('Active'); ?></label>
		<div class="form">
			<input type="checkbox" name="is_active" value="1" <?php if($app->formValue($data['is_active'], $_POST['is_active'])) echo " checked"; ?> />
			<?php echo _('Allow this user to log in'); ?>
		</div>
	</li>

	<li id="userNewsletter" class="clearfix form-item">
		<div class="hand">&nbsp;</div>
		<div class="toggle">&nbsp;</div>
		<label><?php echo _('Newsletter'); ?></label>
		<div class="form">
			<input type="checkbox" name="userNewsletter" value="1" <?php if($app->formValue($data['userNewsletter'], $_POST['userNewsletter'])) echo " checked"; ?> />
			<?php echo _('Accept to receive newsletter'); ?>
		</div>
	</li>

	<li id="id_group" class="clearfix form-item">
		<div class="hand">&nbsp;</div>
		<div class="toggle">&nbsp;</div>
		<label><?php echo _('Group'); ?></label>
		<div class="form">
			<select name="id_group" id="group-select"><?php
				echo "<option></option>";
				foreach($app->apiLoad('user')->userGroupGet(array('threadFlat' => true)) as $e){
					$sel = ($e['id_group'] == $app->formValue($data['id_group'], $_POST['id_group'])) ? ' selected' : NULL;
					echo "<option value=\"".$e['id_group']."\"".$sel.">".$e['groupName']."</option>";
				}
			?></select>
		</div>
	</li>

	<li id="userMediaBox" class="clearfix form-item">
		<div class="hand">&nbsp;</div>
		<div class="toggle">&nbsp;</div>
		<label><?php echo _('Media'); ?></label>
		<div class="form"><?php echo
			$app->apiLoad('field')->fieldForm(
				NULL,
				$app->formValue($data['userMedia'], $_POST['userMedia']),
				array(
					'name' 	=> 'userMedia',
					'id' 	=> 'userMedia',
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

</div>

<?php include(COREINC.'/end.php'); ?>
<!--<script src="../core/vendor/tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
<script src="../core/vendor/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>-->
<script src="../core/vendor/ckeditor/ckeditor.js"></script>
<script src="../core/vendor/ckeditor/adapters/jquery.js"></script>

<script src="../content/ui/js/content.js"></script>
<script type="text/javascript" src="../core/vendor/datepicker/js/bootstrap-datepicker.js" charset="UTF-8"></script>
<script type="text/javascript">

	doMove  		= false;
	useEditor		= true;
	replace 		= <?php echo json_encode($replace); ?>;
//	var mediaList	= [<?php echo @implode(',', $GLOBALS['mediaList']) ?>];

	textarea		= "<?php echo @implode(',', $GLOBALS['textarea']) ?>";
	datePick		= [<?php echo @implode(',', $GLOBALS['datePick']) ?>];
	MceStyleFormats = [<?php echo @file_get_contents(USER.'/config/tinymceStyleFormats.php') ?>];

	$(function() {
		boot();
		openView(0,0);
		
		$('#userDateCreate').datepicker({
			format: 'yyyy-mm-dd'
		});
		$('#userDateUpdate').datepicker({
			format: 'yyyy-mm-dd'
		});
        $('#userDateExpire').datepicker({
            format: 'yyyy-mm-dd'
        });
        $('.datePicker').datepicker({
            format: 'yyyy-mm-dd'
        });

		$('#userDateCreate').siblings('.clear').on('click', function() {$('#userDateCreate').val('');});
		$('#userDateUpdate').siblings('.clear').on('click', function() {$('#userDateUpdate').val('');});
        $('#userDateExpire').siblings('.clear').on('click', function() {$('#userDateExpire').val('');});
        $('.datePicker').siblings('.clear').on('click', function() {$(this).val('');});

		
	});
		
	var d = new Date();
	var day = (d.getUTCDate() < 10) ? '0'+d.getUTCDate() : d.getUTCDate();
	if ($('#userDateCreate').val() == '') $('#userDateCreate').val(d.getFullYear()+'-'+(d.getUTCMonth()+1)+'-'+day);
    if ($('#userDateUpdate').val() == '') $('#userDateUpdate').val(d.getFullYear()+'-'+(d.getUTCMonth()+1)+'-'+day);


</script>

</body></html>