<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	if($_POST['actionParam']){
		// pre
		$app->apiLoad('user')->userSearchCache(array(
			'debug'		=> false,
			'id_search'	=> $_POST['id_search'],
			'clean'		=> true
		));

		// update
		$app->dbQuery("UPDATE k_search SET searchChain='".$_POST['searchChain']."', searchParam='".serialize($_POST['searchParam'])."' WHERE id_search=".$_POST['id_search']);

		// post
		$app->apiLoad('user')->userSearchCache(array(
			'debug'		=> false,
			'id_search'	=> $_POST['id_search']
		));
	
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
			'id_search' 	=> $_REQUEST['id_search'],
		));

		$title	= "Modification ".$data['searchName'];
	}else{
		$title 	= "Nouvelle recherche";
	}
	
	$search = $app->searchGet();

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
	<style>
		.trigger { display: block; }
		.before-cond {
			margin-top: 10px;
			border-top: 1px solid #444;
			padding-top: 10px;
		}
		.foo-sel {
			margin: 0 10px 10px 10px;
		}
	</style>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div id="app"><div class="wrapper">

	<div class="app">
		
		<div class="span5">
		<form action="search" method="post" id="listing">
		
			<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing">
				<thead>
					<tr>
						<th width="30" class="icone"><i class="icon-remove icon-white"></i></th>
						<th><b>Nom</b></th>
						<th width="100">
							<input type="text" class="field input-small nomargin" onkeyup="recherche($(this))" onkeydown="recherche($(this))" size="10" style="float:right;" />
						</th>
					</tr>
				</thead>
				<tbody><?php
				$search = $app->searchGet(array('type' => 'user'));
				//$app->pre($search);
				if(sizeof($search) > 0){
					foreach($search as $e){ $chkdel++; ?>
					<tr class="<?php if($e['id_search'] == $_REQUEST['id_search']) echo "selected" ?>">
						<td class="check-red"><input type="checkbox" name="remove[]" id="chkdel<?php echo $chkdel ?>" class="chk" value="<?php echo $e['id_search'] ?>" /></td>
						<td class="sniff"><a href="search?id_search=<?php echo $e['id_search'] ?>"><?php echo $e['searchName'] ?></a></td>
						<td align="right"><a href="search?id_search=<?php echo $e['id_search'] ?>&param">Paramètres</a></td>
					</tr>
					<?php }
					}else{ ?>
					<tr>
						<td colspan="3" style="font-weight:bold; padding:30px 0px 30px 0px; text-align:center;">
							Aucune recherche enregistré
						</td>
					</tr>
					<? }
				?></tbody>
				<tfoot>
					<tr>
						<td height="30"></td>
						<td colspan="2"><?php
						if(sizeof($search) > 0){ ?>
							<a href="javascript:$('#listing').submit();" class="btn btn-mini">Supprimer</a>
						<?php } ?></td>
					</tr>
				</tfoot>
			</table>
		</form>
		</div>

		<div class="ee span7"><?php
		if(isset($_REQUEST['param'])){
			
			if($data['searchType'] == 'user'){
				$field = $app->apiLoad('field')->fieldGet(array(
					'utf8'		=> true,
					'user'		=> true,
					'debug'		=> false
				));

                $field = array_merge(array(array(
                    'id_field' 	=> 'userMail',
                    'fieldType'	=> 'texte',
                    'fieldName'	=> 'Mail'
                )), $field);

                $field = array_merge(array(array(
                    'id_field' 	=> 'is_deleted',
                    'fieldType'	=> 'texte',
                    'fieldName'	=> 'Supprimé'
                )), $field);

                $field = array_merge(array(array(
                    'id_field' 	=> 'id_group',
                    'fieldType'	=> 'texte',
                    'fieldName'	=> 'ID Groupe'
                )), $field);
			}
			
			#$app->pre($data, $field);
		
			$chain = array(
				'AND' 	=> 'toutes les conditions',
				'OR' 	=> 'n\'importe laquelle des conditions'
			);
			?>
			<form action="search" method="post" onsubmit="" id="f" name="f">
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
						<a href="#" onClick="save();return false;" class="btn btn-mini">Sauver</a>
						<a href="search?id_search=<?php echo $_REQUEST['id_search'] ?>" class="btn btn-mini">Annuler</a>
					</div>
				</div>
			
				<?php
					if($data['searchType'] == 'user'){
						$f = $app->apiLoad('user')->userSearch(array('id_search' => $data['id_search'], 'debug' => 0));
						$t = $app->apiLoad('user')->total;
					}else{
						$f = $app->apiLoad('content')->contentSearch(array('id_search' => $data['id_search'], 'debug' => 0));
						$t = $app->apiLoad('content')->total;
					}
					
					echo "<p>Total : ".$t."</p>";
				?>
		
			</form>
		
			<script>
				var field_ = <?php echo json_encode($field); ?>;
				var param_ = <?php echo json_encode((is_array($data['searchParam']) ? $data['searchParam'] : array())); ?>; 
			</script>

		<?php }else{ ?>
		
			<form action="search" method="post" id="data">
				<input type="hidden" name="action" value="1" />
				<input type="hidden" name="id_search" value="<?php echo $data['id_search'] ?>" />
				<input type="hidden" name="searchType" value="user" />
				
				<table cellpadding="5" border="0">
					<tr>
						<td width="75">Nom</td>
						<td><input type="text" name="searchName" value="<?php echo $app->formValue($data['searchName'], $_POST['searchName']); ?>" /></td>
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

</div></div>

<?php include(COREINC.'/end.php'); ?>
<script type="text/javascript" src="ui/js/search.js"></script>

</div></body></html>