<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	// Remove
	if(sizeof($_POST['del']) > 0){
		foreach($_POST['del'] as $e){
			$app->apiLoad('survey')->surveyRemove($e);
		}
	}

	// Filter
	if(isset($_GET['cf'])){
		$app->filterSet('survey', $_GET);
		$filter = array_merge($app->filterGet('survey'), $_GET);	
	}else
	if(isset($_POST['filter'])){
		$app->filterSet('survey', $_POST['filter']);
		$filter = array_merge($app->filterGet('survey'), $_POST['filter']);	
	}else{
		$filter = $app->filterGet('survey');
	}

	$dir = ($filter['direction'] == 'ASC') ? 'DESC' : 'ASC';

	$surveys = $app->apiLoad('survey')->surveyGet(array(
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
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div class="inject-subnav-right hide">
	<li><a onclick="showOpt();" class="btn btn-mini">Options d'affichage</a></li>
	<li class="opt">
		<a href="data">
			<span>Ajouter une enqu&ecirc;te</span>
		</a>
	</li>
</div>


<div id="app"><div>

	<div class="menu-inline-left clearfix">
				
		<form class="form-horizontal nomargin" action="" method="post" id="filter" style="display:none;">
	
			<input type="hidden" name="id_type"			value="1" />
			<input type="hidden" name="filter[open]"	value="1" />
			<input type="hidden" name="filter[offset]"	value="0" />
			
			Recherche
			<input type="text" name="filter[q]" value="<?php echo $filter['q'] ?>" />
			
			Combien
			<input type="text" name="filter[limit]" value="<?php echo $filter['limit'] ?>" size="3" />

			<button class="btn btn-mini" type="submit">Filter les résultats</button>
			<button class="btn btn-mini">Annuler</button>
		</form>
	</div>
	
	<form method="post" action="index" id="listing">
	<table border="0" cellpadding="0" cellspacing="0" class="listing">
		<thead>
			<tr>
				<th width="30"  class="icone"><i class="icon-remove icon-white"></i></th>
				<th width="80"  class="order <?php if($filter['order'] == 'k_survey.id_survey')			echo 'order'.$dir; ?>" onClick="document.location='index?cf&order=k_survey.id_survey&direction=<?php echo $dir ?>'"><span>#</span></th>
				<th width="110" class="order <?php if($filter['order'] == 'k_survey.surveyDateCreate')	echo 'order'.$dir; ?>" onClick="document.location='index?cf&order=k_survey.surveyDateCreate&direction=<?php echo $dir ?>'"><span>Création</span></th>
				<th			    class="order <?php if($filter['order'] == 'k_survey.surveyName')		echo 'order'.$dir; ?>" onClick="document.location='index?cf&order=k_survey.surveyName&direction=<?php echo $dir ?>'"><span>Nom</span></th>
			</tr>
		</thead>
		<tbody><?php
		if(sizeof($surveys) > 0){
			foreach($surveys as $e){
			?>
			<tr>
				<td><input type="checkbox" name="del[]" value="<?php echo $e['id_survey'] ?>" class="cb" /></td>
				<td><?php echo $e['id_survey'] ?></td>
				<td class="dateTime">
					<span class="date"><?php echo $app->helperDate($e['surveyDateCreate'], '%d.%m.%Y')?></span>
					<span class="time"><?php echo $app->helperDate($e['surveyDateCreate'], '%Hh%M') 	 ?></span>
				</td>
				<td><a href="data?id_survey=<?php echo $e['id_survey'] ?>"><?php echo $e['surveyName'] ?></a></td>
			</tr>
			<?php }
		}else{ ?>
			<tr>
				<td colspan="4" style="text-align:center; font-weight:bold; padding:30px 0px 30px 0px;">
					Aucun résultat avec cette recherche
				</td>
			</tr>
		<?php } ?>
		</tbody>
		<tfoot>
			<?php if(sizeof($surveys) > 0){ ?>
			<tr>
				<td height="25"><input type="checkbox" onchange="cbchange($(this));" /></td>
				<td colspan="2"><a href="#" onClick="applyRemove();" class="btn btn-mini">Supprimer la selection</a></td>
				<td class="pagination"><?php
					$app->pagination($app->apiLoad('survey')->total, $app->apiLoad('survey')->limit, $filter['offset'], 'index?cf&offset=%s');
				?></td>
			</tr>
			<?php }else{ ?>
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>
			<?php } ?>
		</tfoot>
	</table>
	</form>

</div></div>

<?php include(COREINC.'/end.php'); ?>
<script src="/app/module/core/vendor/datatables/jquery.dataTables.js"></script>
<script>
	
	function showOpt() {
		shown = $('.menu-inline-left .form-horizontal').css('display');
		if (shown == 'block') {
			$('.menu-inline-left .form-horizontal').css('display', 'none');
			$('.showopt i').attr('class', 'icon-chevron-down icon-white');
		} else {
			$('.menu-inline-left .form-horizontal').fadeTo(218, 1);
			$('.showopt i').attr('class', 'icon-chevron-up icon-white');
		}
	}
	
	function applyRemove(){
		if(confirm("SUPPRIMER ?")){
			$('#listing').submit();
		}
	}

</script>

</body></html>