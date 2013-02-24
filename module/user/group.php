<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	# Suppression d'un groupe
	#
	if(sizeof($_POST['remove']) > 0){
		foreach($_POST['remove'] as $e){
			$app->apiLoad('user')->userGroupRemove($e);
		}
	}else
	
	# Associer les champs au groupe
	#
	if($_GET['id_group'] != '' && isset($_GET['apply'])){	
		foreach(explode(',', $_GET['apply']) as $id_field){
			if(intval($id_field) > 0) $app->apiLoad('field')->fieldAffectPush('usergroup', $id_field, $_GET['id_group']);
		}

		foreach(explode(',', $_GET['move']) as $id_field){
			if(intval($id_field) > 0) $app->apiLoad('field')->fieldAffectPop('usergroup', $id_field, $_GET['id_group']);
		}
	}else
	
	# ACTION
	#
	if($_POST['action']){

		$def['k_group'] = array(
			'mid_group' 	=> array('value' => $_POST['mid_group']),
			'groupName' 	=> array('value' => $_POST['groupName'], 	'check' => '.')
		);

		if($app->formValidation($def)){
			$result  = $app->apiLoad('user')->userGroupSet($_POST['id_group'], $def);
			$message = ($result) ? 'OK: Enregistrement' : 'KO: Erreur APP:<br />'.$app->db_error;
		}else{
			$message = 'KO: Validation failed';
		}
	}
	
	$group = $app->apiLoad('user')->userGroupGet(array(
		'thread' => true
	));

	if($_REQUEST['id_group'] != NULL){
		$data = $app->apiLoad('user')->userGroupGet(array(
			'id_group' => $_REQUEST['id_group']
		));
	}

	foreach($app->dbMulti("SELECT id_group, COUNT(id_user) as how FROM k_user GROUP BY id_group") as $e){
		$how[$e['id_group']] = $e['how'];
	}

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
    <link rel="stylesheet" type="text/css" href="ui/css/group.css" />
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div class="inject-subnav-right hide">
<?php if(isset($_GET['sort'])){ ?>
	<li><a href="group?id_group=<?php echo $_REQUEST['id_group'] ?>" class="btn btn-small"><?php echo _('Cancel'); ?></a></li>
	<li><a onclick="sauver();" class="btn btn-mini btn-success"><?php echo _('Save'); ?></a></li>
<?php }else{ ?>
	<li><a href="group" class="btn btn-small"><?php echo _('New'); ?></a></li>
	<li><a onclick="$('#data').submit();" class="btn btn-mini btn-success"><?php echo _('Save'); ?></a></li>
<?php } ?>
</div>

<div id="app"><div class="wrapper"><div class="row-fluid">

	<div class="span6" id="list">
	
		<table border="0" cellpadding="0" cellspacing="0" class="listing">
			<thead>
				<tr>
					<th width="30" class="icone"><i class="icon-remove icon-white"></i></th>
					<th width="15">&nbsp;</th>
					<th align="left"><?php echo _('Name'); ?></th>
				</tr>
			</thead>
		</table>
	
		<form action="group" method="post" id="listing">
		<?php function trace($app, $mid, $group, $how, $level){
	
			echo "<ul id=\"mid-".$mid."\">";
			foreach($group as $e){
				echo "<li id=\"".$e['id_group']."\">";
					echo "<div style=\"border:1px solid #e1e1e1; margin:2px 0px 2px 0px; clear:both;\" class=\"holder\">";
						echo "<div class=\"check\"><input type=\"checkbox\" name=\"remove[]\" value=\"".$e['id_group']."\" ".(($e['id_group'] < 0) ? "disabled" : "")." /></div>";
						echo "<div class=\"handle\"><i class=\"icon-move\"></i></div>";
						echo "<div class=\"data\" style=\"padding-left:".($level * 20 + 10)."px;float: left;\"><a href=\"group?id_group=".$e['id_group']."\">".$e['groupName']."</a></div>";
						echo "<div class=\"action\">";
							echo "<a href=\"group-view?id_group=".$e['id_group']."\" class=\"lang\">".(($how[$e['id_group']] != NULL) ? $how[$e['id_group']] : 0)."</a> &nbsp;";
							echo "<a href=\"group?sort&id_group=".$e['id_group']."\" class=\"btn btn-mini\">Champs</a>";
						echo "</div>";
						echo "<br style=\"clear:both;\" />";
					echo "</div>";
	
				if(sizeof($e['sub']) > 0) trace($app, $e['id_group'], $e['sub'], $how, ($level+1));
				echo "</li>";
			}
			echo "</ul>";
		}
	
		trace($app, 0, $group, $how, 0); ?>
	
		<br style="clear:both;" />
	
		<a onclick="$('#listing').submit()" class="btn btn-mini"><?php echo _('Save changes'); ?></a>
		<a href="group" class="btn btn-mini"><?php echo _('Cancel'); ?></a>
	
		<input type="hidden" id="serialized" name="serialized" />
	
		</form>
	</div>

	<div class="span6">
	<?php
	
		if($message != NULL){
			list($class, $message) = $app->helperMessage($message);
			echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
		}
	
		if(!isset($_REQUEST['sort'])){
	?>
		<form action="group" method="post" id="data">
		<input type="hidden" name="action" value="1" />
	
		<table cellpadding="5">
			<tr>
				<td><?php echo _('Name'); ?></td>
				<td><input type="text" name="groupName" value="<?php echo $app->formValue($data['groupName'], $_POST['groupName']); ?>" /></td>	
			</tr>
			<tr>
				<td><?php echo _('Parent group'); ?></td>
				<td><?php
					$group = $app->apiLoad('user')->userGroupGet(array(
						'mid_group'			=> 0,
						'threadFlat'		=> true,
						'noid_group' 		=> $data['id_group'],
						'debug'				=> true
					));
					
	
					echo "<select name=\"mid_group\"><option value=\"0\"></option>";
					foreach($group as $e){
						$sel = ($e['id_group'] == $app->formValue($data['mid_group'], $_POST['mid_group'])) ? ' selected' : NULL;
						echo "<option value=\"".$e['id_group']."\"".$sel.">".str_repeat('&nbsp; &nbsp;', $e['level']).' '.$e['groupName']."</option>";
					}
					echo "</select>";
	
				?></td>
			</tr>
		</table>
		</form>
			
		<?php }else{
		
			$field = $app->apiLoad('field')->fieldGet(array('id_group' => $_REQUEST['id_group']));
			foreach($field as $e){
				$used[] = $e['id_field'];
			}
		
			$used = is_array($used) ? $used : array();
		
			$user = $app->apiLoad('field')->fieldGet(array('user' => true));
			foreach($user as $e){
				if(!in_array($e['id_field'], $used)){
					$not[] = $e;
				}
			}
			
			$not = is_array($not) ? $not : array();
		?>
		
			<form action="group" method="post" id="data">
			<input type="hidden" name="action" value="1" />
			<input type="hidden" name="id_group" value="<?php echo $data['id_group'] ?>" />
			<input type="hidden" name="sort" value="" />
		
		
			<p><b><?php echo _('Used fields'); ?></b></p>
			<ul id="la" class="myList mylistleft clearfix">
				<?php foreach($field as $e){ ?>
				<li id="<?php echo $e['id_field'] ?>" class="in-place"><?php echo $e['fieldName'] ?></li>
				<?php } ?>
			</ul>
		
			<p><b><?php echo _('Other fields'); ?></b></p>
			<ul id="lb" class="myList mylistleft clearfix">
				<?php foreach($not as $e){ ?>
				<li  id="<?php echo $e['id_field'] ?>"><?php echo $e['fieldName'] ?></li>
				<?php } ?>
			</ul>
		
			<input type="hidden" id="move" size="80" />
		
			<script>
			

			</script>	
		
			</form>
		<?php } ?>
	</div>

</div></div></div>

<?php include(COREINC.'/end.php'); ?>
<script src="ui/js/group.js" type="text/javascript"></script>

<script>
	id_group = <?php echo $_REQUEST['id_group'] ?>;
</script>

</body></html>