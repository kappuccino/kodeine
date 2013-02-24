<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	if(sizeof($_POST['remove']) > 0){
		foreach($_POST['remove'] as $e){
			$app->dbQuery("UPDATE k_user SET id_profile=0 WHERE id_profile=".$e);
			$app->dbQuery("DELETE FROM k_userprofile WHERE id_profile=".$e);
		}
	}else
	if($_POST['action']){		
		$do = true;

		# Check : ODD values
		$_POST['profileRule']['id_chapter']		= $app->apiLoad('user')->userProfileCheckChapter($_POST['profileRule']['id_chapter']);
		$_POST['profileRule']['id_category']	= $app->apiLoad('user')->userProfileCheckCategory($_POST['profileRule']['id_category']);
		$_POST['profileRule']['id_group']		= $app->apiLoad('user')->userProfileCheckGroup($_POST['profileRule']['id_group']);

		$def['k_userprofile'] = array(
			'profileName' 	=> array('value' => $_POST['profileName'], 'check' => '.'),
			'profileRule'	=> array('value' => serialize($_POST['profileRule']))
		);

		if(!$app->formValidation($def)) $do = false;

		if($do){
			$result  = $app->apiLoad('user')->userProfileSet($_POST['id_profile'], $def);
			$message = ($result) ? 'OK: Enregistrement' : 'KO: Erreur APP:'.$app->db_error;
			if($result) $_REQUEST['id_profile'] = $app->apiLoad('user')->id_profile;
		}else{
			$message = 'KO: Validation failed';
		}
	}

	if($_REQUEST['id_profile'] != NULL){
		$data = $app->apiLoad('user')->userProfileGet(array(
			'id_profile' 	=> $_REQUEST['id_profile'],
		));
	}

	$profile = $app->apiLoad('user')->userProfileGet(array(
		'debug' => false
	));

	$def = $app->moduleList(array(
		'all' => true
	));

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div class="inject-subnav-right hide">
	<li><a href="profile" class="btn btn-mini"><?php echo _('New'); ?></a></li>
	<li><a onclick="$('#data').submit();" class="btn btn-mini btn-success"><?php echo _('Validate'); ?></a></li>
</div>

<div id="app"><div class="wrapper"><div class="row-fluid">

	<div class="span3">
		<form action="profile" method="post" id="listing">
		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing">
			<thead>
				<tr>
					<th width="30"></th>
					<th><?php echo _('Name'); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($profile as $e){ ?>
				<tr class="<?php if($e['id_profile'] == $_REQUEST['id_profile']) echo "selected" ?>">
					<td><input type="checkbox" name="remove[]" value="<?php echo $e['id_profile'] ?>" /></td>
					<td class="sniff"><a href="profile?id_profile=<?php echo $e['id_profile'] ?>"><?php echo $e['profileName'] ?></a></td>
				</tr>
				<?php } ?>
			</tbody>
			<tfoot>
				<tr>
					<td height="30"></td>
					<td><a href="javascript:$('#listing').submit();" class="btn btn-mini"><?php echo _('Remove selected items'); ?></a></td>
				</tr>
			</tfoot>
		</table>
		</form>
	</div>

	<div class="span9">
		<?php
			if($message != NULL){
				list($class, $message) = $app->helperMessage($message);
				echo "<div class=\"alert ".ucfirst($class)."\">".$message."</div>";
			}
		?>

		<form action="profile?id_profile=<?php echo $data['id_profile'] ?>" method="post" id="data">

		<input type="hidden" name="action" value="1" />
		<input type="hidden" name="id_profile" value="<?php echo $data['id_profile'] ?>" />

		<div class="row-fluid" style="margin-bottom:10px;">
			<div class="span3"><?php echo _('Profile name'); ?></div>
			<div class="span9">
				<input type="text" name="profileName" value="<?php echo $app->formValue($data['profileName'], $_POST['profileName']); ?>" style="width:99%;" />
			</div>
		</div>
		
		<div class="row-fluid">
			<div class="span3">
				<b><?php echo _('Chapter'); ?></b><br /> <?php echo
				$app->apiLoad('chapter')->chapterSelector(array(
					'name'		=> 'profileRule[id_chapter][]',
					'multi' 	=> true,
					'size'		=> 4,
					'style' 	=> 'width:100%; height:200px',
					'value'		=> $app->formValue($data['profileRule']['id_chapter'], $_POST['profileRule']['id_chapter'])
				));
			?></div>
	
			<div class="span3">
				<b><?php echo _('Category'); ?></b><br /> <?php echo
				$app->apiLoad('category')->categorySelector(array(
					'name'		=> 'profileRule[id_category][]',
					'language'	=> 'fr',
					'multi' 	=> true,
					'size'		=> 4,
					'style' 	=> 'width:100%; height:200px',
					'value'		=> $app->formValue($data['profileRule']['id_category'], $_POST['profileRule']['id_category'])
				));
			?></div>
	
			<div class="span3">
				<b><?php echo _('Group'); ?></b><br /> <?php echo
				$app->apiLoad('user')->userGroupSelector(array(
					'name'		=> 'profileRule[id_group][]',
					'multi' 	=> true,
					'size'		=> 4,
					'style' 	=> 'width:100%; height:200px; margin-bottom:16px;',
					'value'		=> $app->formValue($data['profileRule']['id_group'], $_POST['profileRule']['id_group'])
				));
			?></div>

			<div class="span3">
				<b><?php echo _('Type'); ?></b><br />
				<select name="profileRule[id_type][]" id="profileRule[id_type][]" size="4" multiple style="width:100%; height:200px"><?php
	
					$type	= $app->apiLoad('type')->typeGet();
					$value	= $app->formValue($data['profileRule']['id_type'], $_POST['profileRule']['id_type']);
					$value	= is_array($value) ? $value : array();
	
					foreach($type as $e){
						$sel = in_array($e['id_type'], $value) ? ' selected' : NULL;
						echo "<option value=\"".$e['id_type']."\"".$sel.">".$e['typeName']."</option>";
					}
	
				?></select>
			</div>
		</div>
		
		<div style="margin-top:5px;" class="clearfix"><?php

			foreach($def as $m){
	
				if (is_array($m['profile'])) {

					echo "<div class=\"clearfix\" style=\"margin:0px 3px 0px 3px;\">";
					echo '<h3>'.$m['name'].' ';
					echo '<input type="checkbox" onclick="togBox(this);" />';
					echo '</h3>';

					foreach($m['profile'] as $e){
						$key 	= $m['key'];
						$code	= $e['code'];
						$v 		= $app->formValue($data['profileRule'][$key][$code], $_POST['profileRule'][$key][$code]);
						$chk	= ($v) ? ' checked' : NULL;
						
						echo "<div class=\"zone\" style=\"float:left; width:250px; height:25px;\">";

						if($e['type'] == 'ALLOW_DENY'){
							echo "<input type=\"checkbox\" name=\"profileRule[".$key."][".$code."]\" value=\"1\" ".$chk." /> ";
						}else
						if($e['type'] == 'FREE'){
							echo "<input type=\"text\" name=\"profileRule[".$key."][".$code."]\" value=\"".$v."\" /> ";
						}

						echo $e['name']."</div>";
					}

					echo "</div>";
				/*} else {
					echo 'Pas de configuration pour ce profile';*/
				}
				
			}
		?></div>
	
		</form>
	</div>


</div></div></div>

<?php include(COREINC.'/end.php'); ?>

<script>

	function togBox(me){
		var cbs = $(me).parent().parent().find('.zone input[type="checkbox"]');
		cbs.attr('checked', $(me).prop('checked'));
		console.log(cbs);
	}

</script>

</body></html>