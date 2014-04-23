<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	// Remove
	if(sizeof($_POST['del']) > 0){
		foreach($_POST['del'] as $e){
			$app->apiLoad('user')->userRemove($e);
		}
	}

	// Filter
	if(isset($_GET['cf'])){
		$app->filterSet('user', $_GET);
		$filter = array_merge($app->filterGet('user'), $_GET);	
	}else
	if(isset($_POST['filter'])){
		$app->filterSet('user', $_POST['filter']);
		$filter = array_merge($app->filterGet('user'), $_POST['filter']);	
	}else{
		$filter = $app->filterGet('user');
	}

	$dir = ($filter['direction'] == 'ASC') ? 'DESC' : 'ASC';

	$users = $app->apiLoad('user')->userGet(array(
		'search'	=> $filter['q'],
		'useField' 	=> false,
		'debug'		=> false,
		'limit'		=> $filter['limit'],
		'offset'	=> $filter['offset'],
		'order'		=> $filter['order'],
		'direction'	=> $filter['direction']
	));

	$fields = $app->apiLoad('field')->fieldGet(array(
		'user' 		=> true,
		'debug'	 	=> false
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
	<li><a onclick="filterToggle('user');" class="btn btn-small"><?php echo _('Display settings'); ?></a></li>
	<li><a href="data" class="btn btn-small btn-success"><?php echo _('New user'); ?></a></li>
</div>

<div id="app">
	
	<div class="quickForm clearfix" style="<?php echo ($filter['open']) ? '' : 'display:none;' ?>">
		<form action="index" method="post" class="form-inline">
			<input type="hidden" name="filter[open]"	value="1" />
			<input type="hidden" name="filter[offset]"	value="0" />
	
			<?php echo _('Search'); ?>
			<input type="text" name="filter[q]" value="<?php echo $filter['q'] ?>" class="input-small" />

			<?php echo _('Limit'); ?>
			<input type="text" name="filter[limit]" value="<?php echo $filter['limit'] ?>" size="3" class="input-small" />

			<?php echo _('Column 1'); ?>
			<select name="filter[cola]"><?php
				echo "<option value=\"\"></option>";
				foreach($fields as $f){
					echo "<option value=\"".$f['id_field']."\"".(($f['id_field'] == $filter['cola']) ? ' selected' : '').">".$f['fieldName']."</option>";
				} ?>
			</select>

			<?php echo _('Column 2'); ?>
			<select name="filter[colb]"><?php
				echo "<option value=\"\"></option>";
				foreach($fields as $f){
					echo "<option value=\"".$f['id_field']."\"".(($f['id_field'] == $filter['colb']) ? ' selected' : '').">".$f['fieldName']."</option>";
				}?>
			</select>

			<button class="btn btn-mini" type="submit"><?php echo _('Filter'); ?></button>
		</form>
	</div>
	
	<form method="post" action="index" id="listing">
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing">
		<thead>
			<tr>
				<th width="20"  class="icone"><i class="icon-remove icon-white"></i></th>
				<th width="80"  class="order <?php if($filter['order'] == 'k_user.id_user')	echo 'order'.$dir; ?>" onClick="document.location='./?cf&order=k_user.id_user&direction=<?php echo $dir ?>'"><span>#</span></th>
				<th width="110" class="order <?php if($filter['order'] == 'k_user.userDateCreate')	echo 'order'.$dir; ?>" onClick="document.location='./?cf&order=k_user.userDateCreate&direction=<?php echo $dir ?>'"><span><?php echo _('Created'); ?></span></th>
				<th width="110" class="order <?php if($filter['order'] == 'k_user.userDateUpdate')	echo 'order'.$dir; ?>" onClick="document.location='./?cf&order=k_user.userDateUpdate&direction=<?php echo $dir ?>'"><span><?php echo _('Updated'); ?></span></th>
				<th			    class="order <?php if($filter['order'] == 'k_user.userMail')		echo 'order'.$dir; ?>" onClick="document.location='./?cf&order=k_user.userMail&direction=<?php echo $dir ?>'"><span><?php echo _('Name'); ?></span></th>
				<?php
					$colspan = 1;
	
					if($filter['cola'] != ''){
						$col = $app->apiLoad('field')->fieldGet(array('id_field' => $filter['cola']));
						echo "<th width=\"180\" class=\"order ".(($filter['order'] == 'field'.$filter['cola']) ? 'order'.$dir : '')."\" onClick=\"document.location='./?cf&order=field".$filter['cola']."&direction=".$dir."'\"><span>".$col['fieldName']."</span></th>";
						$colspan++;
					}
	
					if($filter['colb'] != ''){
						$col = $app->apiLoad('field')->fieldGet(array('id_field' => $filter['colb']));
						echo "<th width=\"180\" class=\"order ".(($filter['order'] == 'field'.$filter['colb']) ? 'order'.$dir : '')."\" onClick=\"document.location='./?cf&order=field".$filter['colb']."&direction=".$dir."'\"><span>".$col['fieldName']."</span></th>";
						$colspan++;
					}
				?>
			</tr>
		</thead>
		<tbody><?php
		if(sizeof($users) > 0){
			foreach($users as $e){
					$chkdel++;
					$disabled = ($e['id_user'] == $app->user['id_user']) ? "disabled=\"disabled\"" : NULL;
			?>
			<tr>
				<td class="check-red"><input type="checkbox" name="del[]" id="chkdel<?php echo $chkdel ?>" value="<?php echo $e['id_user'] ?>" class="cb chk" <?php echo $disabled ?> />
					</label>
				</td>
				<td><?php echo $e['id_user'] ?></td>
				<td class="dateTime">
					<span class="date"><?php echo $app->helperDate($e['userDateCreate'], '%d.%m.%Y')?></span>
					<span class="time"><?php echo $app->helperDate($e['userDateCreate'], '%Hh%M') 	 ?></span>
				</td>
				<td class="dateTime">
					<span class="date"><?php echo $app->helperDate($e['userDateUpdate'], '%d.%m.%Y')?></span>
					<span class="time"><?php echo $app->helperDate($e['userDateUpdate'], '%Hh%M') 	 ?></span>
				</td>
				<td><a href="data?id_user=<?php echo $e['id_user'] ?>"><?php echo $e['userMail'] ?></a></td>
				<?php
					if($filter['cola'] != '') echo "<td>".$e['field'.$filter['cola']]."</td>";
					if($filter['colb'] != '') echo "<td>".$e['field'.$filter['colb']]."</td>";
				?>
			</tr>
			<?php }
			}else{ ?>
			<tr>
				<td colspan="<?php echo (4 + $colspan) ?>" style="text-align:center; font-weight:bold; padding:30px 0px 30px 0px;">
					<?php echo _('No date'); ?>
				</td>
			</tr>
		<?php } ?>
		</tbody>
		<tfoot>
			<tr>
			<?php if(sizeof($users) > 0){ ?>
				<td><input id="delall" type="checkbox" class="chk" onchange="cbchange($(this));" /></td>
				<td colspan="3"><a href="#" onClick="userRemove();" class="btn btn-mini"><?php echo _('Remove selected items'); ?></a></td>
				<td colspan="<?php echo $colspan ?>" class="pagination"><?php $app->pagination($app->apiLoad('user')->total, $app->apiLoad('user')->limit, $filter['offset'], 'index?cf&offset=%s'); ?></td>
			<?php }else{ ?>
				<td colspan="<?php echo (4 + $colspan) ?>">&nbsp;</td>
			<?php } ?>
			</tr>
		</tfoot>
	</table>
	</form>

</div>	

<?php include(COREINC.'/end.php'); ?>
<script src="../core/vendor/datatables/jquery.dataTables.js"></script>
<script>

	function userRemove(){
		if(confirm("SUPPRIMER ?")){
			$('#listing').submit();
		}
	}

</script>

</body></html>