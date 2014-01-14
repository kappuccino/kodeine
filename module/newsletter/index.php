<?php
	if(isset($_REQUEST['duplicate'])){
		$app->apiLoad('newsletter')->newsletterDuplicate($_REQUEST['duplicate']);
		$app->go('./');
	}else
	if(sizeof($_POST['del']) > 0){
		foreach($_POST['del'] as $e){
			$app->apiLoad('newsletter')->newsletterRemove($e);
		}
		$app->go('./');
	}

	// Filter
	if(isset($_GET['cf'])){
		$app->filterSet('newsletter', $_GET);
		$filter = array_merge($app->filterGet('newsletter'), $_GET);	
	}else
	if(isset($_POST['filter'])){
		$app->filterSet('newsletter', $_POST['filter']);
		$filter = array_merge($app->filterGet('newsletter'), $_POST['filter']);	
	}else{
		$filter = $app->filterGet('newsletter');
	}

	$newsletter = $app->apiLoad('newsletter')->newsletterGet(array(
		'search'	=> $filter['q'],
		'debug'		=> false,
		'limit'		=> $filter['limit'],
		'offset'	=> $filter['offset'],
		'order'		=> $filter['order'],
		'direction'	=> $filter['direction'],
		'debug'		=> false
	));

	$total	= $app->apiLoad('newsletter')->total;
	$limit	= $app->apiLoad('newsletter')->limit;
	$dir 	= ($filter['direction'] == 'ASC') ? 'DESC' : 'ASC';
?><!DOCTYPE html>
<head>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div class="inject-subnav-right hide">
	
	<li><a onclick="filterToggle('newsletter');" class="btn btn-small">Affichage</a></li>
	<!--<li><a href="dsnr" class="btn btn-small btn-success">Designer une nouvelle newsletter</a></li>-->
	<!--<li><a href="data-options?designer=1" class="btn btn-small btn-error">old news</a></li>-->
	<li><a href="data-options" class="btn btn-small btn-success">Ajouter une newsletter</a></li>
</div>

<div id="app">

	<div class="quickForm" style="display:<?php echo $filter['open'] ? 'block' : 'none;' ?>;">
		<form action="./" method="post" class="form-horizontal">

			<input type="hidden" name="id_type"			value="1" />
			<input type="hidden" name="filter[open]"	value="1" />
			<input type="hidden" name="filter[offset]"	value="0" />
			
			<label class="control-label" for="prependedInput">Recherche</label>
			<input type="text" name="filter[q]" value="<?php echo $filter['q'] ?>" />

			<label class="control-label" for="prependedInput">Combien</label>
			<input type="text" name="filter[limit]" value="<?php echo $filter['limit'] ?>" size="3" />

			<button class="btn btn-mini" type="submit">Filter les résultats</button>
			<button class="btn btn-mini">Annuler</button>
		</form>
	</div>

	<form method="post" action="index" id="listing">
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing">
		<thead>
			<tr>
				<th width="20"	class="icone"><i class="icon-remove icon-white"></i></th>
				<th width="20"	class="icone"><i class="icon-tags icon-white"></i></th>
				<th width="20"	class="icone"><i class="icon-signal icon-white"></i></th>
				<th width="80"	class="order <?php if($filter['order'] == 'k_newsletter.id_newsletter') echo 'order'.$dir; ?>" 	onClick="document.location='index?cf&order=k_newsletter.id_newsletter&direction=<?php echo $dir ?>'"><span>#</span></th>
				<th width="120"	class="order <?php if($filter['order'] == 'newsletterSendDate') echo 'order'.$dir; ?>"		 	onClick="document.location='index?cf&order=newsletterSendDate&direction=<?php echo $dir ?>'"><span>Date d'envoi</span></th>
				<th 			class="order <?php if($filter['order'] == 'newsletterName')  echo 'order'.$dir; ?>" 			onClick="document.location='index?cf&order=newsletterName&direction=<?php echo $dir ?>'"><span>Nom</span></th>
				<th width="45%" class="order <?php if($filter['order'] == 'newsletterTitle') echo 'order'.$dir; ?>" 			onClick="document.location='index?cf&order=newsletterTitle&direction=<?php echo $dir ?>'"><span>Titre de l'email</span></th>
			</tr>
		</thead>
		<tbody>
		<?php if(sizeof($newsletter) > 0){ foreach($newsletter as $e){ ?>
			<tr>
				<td><input type="checkbox" name="del[]" value="<?php echo $e['id_newsletter'] ?>" class="cb" <?php echo $disabled ?> /></td>
				<td class="icone"><a href="javascript:duplicate(<?php echo $e['id_newsletter'] ?>);"><i class="icon-tags"></i></a></td>
				<td style="padding-left:3px;">
                    <?php if($e['newsletterSendDate'] != NULL) { ?>
                    <a href="analytic?id_newsletter=<?php echo $e['id_newsletter'] ?>"><i class="icon-signal"></i></a>
                    <?php } ?>
                </td>
				<td><a href="data-options?id_newsletter=<?php echo $e['id_newsletter'] ?>"><?php echo $e['id_newsletter'] ?></a></td>
				<td><a href="data-options?id_newsletter=<?php echo $e['id_newsletter'] ?>"><?php echo $e['newsletterSendDate']; ?></a></td>
				<td><a href="data-options?id_newsletter=<?php echo $e['id_newsletter'] ?>"><?php echo $e['newsletterName'] ?></a></td>
				<td><a href="data-options?id_newsletter=<?php echo $e['id_newsletter'] ?>"><?php echo $e['newsletterTitle'] ?></a></td>
			</tr>
		<?php }}else{ ?>
			<tr>
				<td colspan="7" style="padding:40px 0px 40px 0px; text-align:center; font-weight:bold">
					Aucun newsletter disponible
					<!--<br /><br />
					<a href="data">Ajouter une nouvelle Newsletter</a>-->
				</td>
			</tr>
		<?php } ?>
		</tbody>
		<?php if(sizeof($newsletter) > 0){ ?>
		<tfoot>
			<tr>
				<td><input type="checkbox" onchange="cbchange($(this));" /></td>
				<td colspan="5">
					<a href="#" onClick="newsletterRemove();" class="btn btn-mini">Supprimer la selection</a>
                </td>
                <td align="right">
					<span class="pagination"><?php $app->pagination($total, $limit, $filter['offset'], 'index?cf&offset=%s'); ?></span>
				</td>
			</tr>
		</tfoot>
		<?php } ?>
	</table>
	</form>

</div>

<?php include(COREINC.'/end.php'); ?>
<script src="../core/vendor/datatables/jquery.dataTables.js"></script>
<script>

	function duplicate(id){
		if(confirm("DUPLIQUER ?")){
			document.location='index?duplicate='+id;
		}
	}
	
	function newsletterRemove(){
		if(confirm("SUPPRIMER ?")){
			$('#listing').submit();
		}
	}

</script>

</body></html>