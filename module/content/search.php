<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	if($_POST['actionParam']){
	#	$app->pre($_POST);
		$app->dbQuery("UPDATE k_search SET searchChain='".$_POST['searchChain']."', searchParam='".serialize($_POST['searchParam'])."' WHERE id_search=".$_POST['id_search']);
	#	$app->pre($app->db_query, $app->db_error);
	}else
	if(sizeof($_POST['remove']) > 0){
		foreach($_POST['remove'] as $e){
			$app->dbQuery("DELETE FROM k_search WHERE id_search=".$e);
		}
	}else
	if($_POST['action']){
		$do = true;

		$def['k_search'] = array(
			'searchName' 	=> array('value' => $_POST['searchName'], 'check' => '.'),
			'searchType'	=> array('value' => $_POST['searchType'])
		);

		if(!$app->formValidation($def)) $do = false;
			
		if($do){
			$result = $app->searchSet($_POST['id_search'], $def);
			$message = ($result) ? 'OK' : 'PAS OK';
			if($result) $_REQUEST['id_search'] = $app->id_search;
		}else{
			$message = 'validation failed';
		}
	}

	if($_REQUEST['id_search'] != NULL){
		$data = $app->searchGet(array(
			'id_search' => $_REQUEST['id_search']
		));

		$title	= "Modification ".$data['searchName'];
	}else{
		$title 	= "Nouvelle recherche";
	}
	
	foreach($app->apiLoad('type')->typeGet() as $e){
		$type[$e['id_type']] = $e['typeName'];
	}

	$search = $app->searchGet();
?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
    <link rel="stylesheet" type="text/css" href="ui/css/search.css" />
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div id="app">
<div class="wrapper clearfix">
	<div style="float:left; width:39%;">
	<form action="search" method="post" id="listing">
	
		<?php $search = $app->searchGet(array('type' => 'content')); ?>
	
		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing sortable">
			<thead>
				<tr>
					<th width="30"><i class="icon-remove icon-white"></i></th>
					<th><b>Nom</b></th>
					<th width="200"><input type="text" style="float:right;" id="filter" class="field nomargin input-small" size="10" placeholder="Rechercher" /></th>
				</tr>
			</thead>
			<tbody><?php
			if(sizeof($search) > 0){
				foreach($search as $e){ ?>
				<tr class="<?php if($e['id_search'] == $_REQUEST['id_search']) echo "selected" ?>">
					<td><input type="checkbox" name="remove[]" value="<?php echo $e['id_search'] ?>" /></td>
					<td class="sniff"><a href="search?id_search=<?php echo $e['id_search'] ?>"><?php echo $e['searchName'] ?></a></td>
					<td align="right"><a href="search?id_search=<?php echo $e['id_search'] ?>&param">Modifier</a></td>
				</tr>
				<?php }
			}else{ ?>
				<tr>
					<td colspan="3"><div class="noData">Il n'y a aucune recherche enregistré</div></td>
				</tr>
			<?php } ?>
			</tbody>
			<tfoot>
				<tr>
					<td height="30"></td>
					<td colspan="2">
					<?php if(sizeof($search) > 0){ ?>
						<a href="javascript:$('#listing').submit();" class="btn btn-mini">Valider</a>
					<?php } ?>
					</td>
				</tr>
			</tfoot>
		</table>
	</form>
	</div>
	
	<div style="float:right; width:60%;" class="ee"><?php
	if(isset($_REQUEST['param'])){
			
		$field = $app->apiLoad('field')->fieldGet(array(
			'utf8'		=> true,
			'id_type'	=> $data['searchType'],
			'user'		=> false,
			'debug'		=> false
		));
	
		#$app->pre($data, $field);
	
		$chain = array(
			'AND' 	=> 'toutes les conditions',
			'OR' 	=> 'n\'importe laquelle des conditions'
		);
	
		?>
		<form action="search" method="post" onsubmit="save(); return false;" id="f" name="f">
			<input type="hidden" name="actionParam" value="1" />
			<input type="hidden" name="param" value="1" />
			<input type="hidden" name="id_search" value="<?php echo $_REQUEST['id_search'] ?>" />
			
			<div id="mainChain" style="visibility:hidden;">
				Satisfaire <select name="searchChain"><?php
					foreach($chain as $k => $e){
						$sel = ($data['searchChain'] == $k) ? ' selected' : NULL;
						echo "<option value=\"".$k."\"".$sel.">".$e."</option>\n";
					}
				?></select> suivantes
			</div>
	
			<div style="margin:5px;">
				<ul id="param" class="clearfix"></ul>
				<div style="margin-top:30px;">
					<a href="#" onClick="save();return false;" class="btn btn-mini">Sauver</a> ou 
					<a href="search?id_search=<?php echo $_REQUEST['id_search'] ?>" class="btn btn-mini">Annuler</a>
				</div>
			</div>
		
			<?php
				$f = $app->apiLoad('content')->contentSearch(array(
					'id_search' => $data['id_search'],
					'debug' 	=> false
				));
	
				$t = $app->apiLoad('content')->total;
	
				echo "<p>Total : ".(($t > 0) ? $t : 0)."</p>";
			?>
	
		</form>
	
		<script>
			var field_ = <?php echo json_encode($field); ?>;
			var param_ = <?php echo json_encode((is_array($data['searchParam']) ? $data['searchParam'] : array())); ?>; 
		</script>
		<script type="text/javascript" src="ressource/js/search.js"></script>
	
	<?php }else{ ?>
	
		<form action="search" method="post" id="data">
			<input type="hidden" name="action" value="1" />
			<input type="hidden" name="id_search" value="<?php echo $data['id_search'] ?>" />
			
			<table cellpadding="5" border="0">
				<tr>
					<td width="75">Nom</td>
					<td><input type="text" name="searchName" value="<?php echo $app->formValue($data['searchName'], $_POST['searchName']); ?>" /></td>
				</tr>
				<tr>
					<td>Type</td>
					<td><?php $v = $app->formValue($data['searchType'], $_POST['searchType']); ?>
						<select name="searchType">
						<?php foreach($type as $k => $e){ ?>
							<option value="<?php echo $k ?>"<?php echo (($k == $v) ? ' selected' : NULL) ?>><?php echo $e ?></option>
						<?php } ?>				
						</select>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<a href="javascript:$('#data').submit();" class="btn btn-mini">Valider</a> 
						<a href="search" class="btn btn-mini">Nouveau</a> 
					</td>
				</tr>
			</table>
		</form>
		<?php } ?>
	</div>
</div>

<?php include(COREINC.'/end.php'); ?>
<script src="/app/module/core/vendor/datatables/jquery.dataTables.js"></script>
<script>
	/*function recherche(f){
		$$('.sniff').each(function(me){
			if(!me.get('html').test(f.value, 'i')){
				me.getParent().setStyle('display', 'none');
			}else{
				me.getParent().setStyle('display', '');
			}
		});
	}*/
</script>


</div></body></html>