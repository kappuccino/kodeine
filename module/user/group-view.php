<?php

	if(!defined('COREINC')) die('Direct access not allowed');
	if($_REQUEST['id_group'] == NULL) $app->go('group');
	
	$group = $app->apiLoad('user')->userGroupGet(array(
		'id_group' => $_REQUEST['id_group']
	));

	// Filter
	if(isset($_GET['cf'])){
		$app->filterSet('grouplist', $_GET);
		$filter = array_merge($app->filterGet('grouplist'), $_GET);	
	}else
	if(isset($_POST['filter'])){
		$app->filterSet('grouplist', $_POST['filter']);
		$filter = array_merge($app->filterGet('grouplist'), $_POST['filter']);	
	}else{
		$filter = $app->filterGet('grouplist');
	}
	
	$dir = ($filter['direction'] == 'ASC') ? 'DESC' : 'ASC';

	$users = $app->apiLoad('user')->userGet(array(
		'id_group'	=> $group['id_group'],
		'search'	=> $filter['q'],
		'useField' 	=> false,
		'debug'		=> false,
		'limit'		=> $filter['limit'],
		'offset'	=> $filter['offset'],
		'order'		=> $filter['order'],
		'direction'	=> $filter['direction']
	));

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
	<link rel="stylesheet" type="text/css" media="all" href="ressource/css/group.css" />
</head>
<body>
	
<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div id="app"><div class="wrapper">

	<div class="quickForm clearfix">
	
		<div class="upper clearfix">
			<div class="btn btn-mini"><a href="javascript:filterToggle('user');"><?php echo _('Options') ?></a></div>
		</div>
		
		<div class="span5">
			<form action="group-view" method="post" id="filter" style="<?php echo ($filter['open']) ? '' : 'display:none;' ?>" class="form-horizontal nomargin">
				<input type="hidden" name="optForm"			value="1" />
				<input type="hidden" name="filter[open]"	value="1" />
				<input type="hidden" name="filter[offset]"	value="0" />
				<input type="hidden" name="id_group"		value="<?php echo $group['id_group'] ?>" />
		
				<div class="control-group nomargin">
					<label class="control-label" for="prependedInput"><?php echo _('Search') ?></label>
					<div class="controls">
						<input type="text" name="filter[q]" value="<?php echo $filter['q'] ?>" />
					</div>
				</div>
		
				<div class="control-group nomargin">
					<label class="control-label" for="prependedInput"><?php echo _('How many') ?></label>
					<div class="controls">
						<input type="text" name="filter[limit]" value="<?php echo $filter['limit'] ?>" size="3" />
					</div>
				</div>
				
				<div class="form-actions nomargin">
					<button class="btn btn-mini" type="submit"><?php echo _('Filter results') ?></button>
				</div>
			</form>
		</div>
	</div>
	
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing">
		<thead>
			<tr>
				<th width="80"  class="order <?php if($filter['order'] == 'k_user.id_user')		echo 'order'.$dir; ?>"><span>#</span></th>
				<th width="110" class="order <?php if($filter['order'] == 'k_user.userDateCreate')	echo 'order'.$dir; ?>"><span><?php echo _('Creation') ?></span></th>
				<th width="110" class="order <?php if($filter['order'] == 'k_user.userDateUpdate')	echo 'order'.$dir; ?>"><span><?php echo _('Update') ?></span></th>
				<th			    class="order <?php if($filter['order'] == 'k_user.userMail')		echo 'order'.$dir; ?>"><span><?php echo _('Name') ?></span></th>
			</tr>
		</thead>
		<tbody><?php
		if(sizeof($users) > 0){
			foreach($users as $e){ ?>
			<tr>
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
			</tr><?php
			}
		}else{ ?>
			<tr>
				<td colspan="4" style="text-align:center; font-weight:bold; padding:30px 0px 30px 0px;">
					<?php echo _('No result') ?>
				</td>
			</tr>
		<?php } ?>
		</tbody>
		<tfoot>
			<?php if(sizeof($users) > 0){ ?>
			<tr>
				<td colspan="3"></td>
				<td class="pagination"><?php
					$app->pagination($app->apiLoad('user')->total, $app->apiLoad('user')->limit, $filter['offset'], 'group-view?id_group='.$group['id_group'].'&cf&offset=%s');
				?></td>
			</tr>
			<?php }else{ ?>
			<tr>
				<td colspan="5">&nbsp;</td>
			</tr>
			<?php } ?>
		</tfoot>
	</table>
	
	</div>
</div>
<?php include(COREINC.'/end.php'); ?>
<script src="/app/module/core/vendor/datatables/jquery.dataTables.js"></script>

</body></html>