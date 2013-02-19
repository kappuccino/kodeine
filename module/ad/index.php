<?php
	// Remove
	if(sizeof($_POST['del']) > 0){
		foreach($_POST['del'] as $e){
			$app->apiLoad('ad')->adRemove($e);
		}
		header("Location: index");
	}

	// Filter
	if(isset($_GET['cf'])){
		$app->filterSet('ad', $_GET);
		$filter = array_merge($app->filterGet('ad'), $_GET);	
	}else
	if(isset($_POST['filter'])){
		$app->filterSet('ad', $_POST['filter']);
		$filter = array_merge($app->filterGet('ad'), $_POST['filter']);	
	}else{
		$filter = $app->filterGet('ad');
	}

	$ad = $app->apiLoad('ad')->adGet(array(
		'search'	=> $filter['q'],
		'withZone'	=> true,
		'debug'		=> false,
		'is_active'	=> true,
		'order'		=> $filter['order'],
		'direction'	=> $filter['direction'],
		'limit'		=> $filter['limit'],
		'offset'	=> $filter['offset']
	));

	$total	= $app->apiLoad('ad')->total;
	$limit	= $app->apiLoad('ad')->limit;
	$dir	= ($filter['direction'] == 'ASC') ? 'DESC' : 'ASC';
?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>
	
	<div class="pbg">
		
		<!-- BANDEAU TOP - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - --> 
		
		<div class="top">
			<div class="logo">Logo</div>
			<div class="pathway clearfix">
				<h1><a href="index">Publicité</a>&raquo; 
					<a href="index">Liste</a></h1>
			</div>
		</div>
	</div>


<div class="bocontainer">
	<div class="row-fluid">
		
	<?php include('lib/menu-ad.php'); ?>
	<div class="app">
	
	<div class="quickForm clearfix">
		<div class="upper clearfix">
			<a href="javascript:filterToggle('ad');" class="button button-green">OPTIONS</a>
		</div>
		<form action="index" method="post" id="filter" style="<?php echo ($filter['open']) ? '' : 'display:none;' ?>">
			<input type="hidden" name="optForm"			value="1" />
			<input type="hidden" name="filter[open]"	value="1" />
			<input type="hidden" name="filter[offset]"	value="0" />
	
			Recherche
			<input type="text" name="filter[q]" value="<?php echo $filter['q'] ?>" />
	
			Combien
			<input type="text" name="filter[limit]" value="<?php echo $filter['limit'] ?>" size="3" />
	
			<input type="submit" />
		</form>
	</div>
	
	<form method="post" action="index" id="listing">
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing table table-striped">
		<thead>
			<tr>
				<th width="30"  class="icone"><i class="icon-remove icon-white"></i></th>
				<th	width="50"  class="order <?php if($filter['order'] == 'id_ad')		echo 'order'.$dir; ?>"  onClick="document.location='index?cf&order=id_ad&direction=<?php echo $dir ?>'"><span>#</span></th>
				<th				class="order <?php if($filter['order'] == 'adName')	echo 'order'.$dir; ?>"  onClick="document.location='index?cf&order=adName&direction=<?php echo $dir ?>'"><span>Nom</span></th>
				<th width="200" class="order <?php if($filter['order'] == 'zoneName')	echo 'order'.$dir; ?>"  onClick="document.location='index?cf&order=zoneName&direction=<?php echo $dir ?>'"><span>Zone</span></th>
				<th width="100" class="order <?php if($filter['order'] == 'adView') 	echo 'order'.$dir; ?>"  onClick="document.location='index?cf&order=adView&direction=<?php echo $dir ?>'"><span>Vue</span></th>
				<th width="100" class="order <?php if($filter['order'] == 'adClick')	echo 'order'.$dir; ?>"  onClick="document.location='index?cf&order=adClick&direction=<?php echo $dir ?>'"><span>Click</span></th>
			</tr>
		</thead>
		<?php if(sizeof($ad) > 0){ foreach($ad as $e){ ?>
			<tr>
				<td><input type="checkbox" name="del[]" value="<?php echo $e['id_ad'] ?>" class="cb" <?php echo $disabled ?> /></td>
				<td><a href="data?id_ad=<?php echo $e['id_ad'] ?>"><?php echo $e['id_ad'] ?></a></td>
				<td><a href="data?id_ad=<?php echo $e['id_ad'] ?>"><?php echo $e['adName'] ?></a></td>
				<td><?php echo $e['zoneName'] ?></td>
				<td><?php echo number_format($e['adView'],  0, '.', ' '); ?></td>
				<td><?php echo number_format($e['adClick'], 0, '.', ' '); ?></td>
			</tr>
		<?php } }else{ ?>
		<tr>
			<td colspan="6" style="text-align:center; padding:50px 0px 50px 0px; font-weight:bold;">Il n'y aucune publicité avec ces critères de recherche.<br /><br /><a href="data" class="button button-green" style="float:none">Ajouter une publicité maintenant</a></td>
		</tr>
		<?php } ?>
		</tbody>
		<tfoot>
			<?php if(sizeof($ad) > 0){ ?>
			<tr>
				<td height="25"><input type="checkbox" onchange="cbchange($(this));" /></td>
				<td colspan="4">
					<a href="#" onClick="apply();" class="button button-red">Supprimer la selection</a>
					<span class="pagination"><?php $app->pagination($app->total, $app->limit, $filter['offset'], 'index?cf&offset=%s'); ?></span>
				</td>
				<td class="pagination"><?php $app->pagination($total, $limit, $filter['offset'], 'index?cf&offset=%s'); ?></td>
			</tr>
			<?php } ?>
		</tfoot>
	</table>
	</form>
	
	<?php include(COREINC.'/end.php'); ?>
	<script>
		function apply(){
			if(confirm("SUPPRIMER ?")){
				$('#listing').submit();
			}
		}
	</script>
	
	</div>

</body>
</html>