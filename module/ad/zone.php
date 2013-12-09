<?php
if(!defined('COREINC')) die('Direct access not allowed');

	if(!$app->userIsAdmin) header("Location: ./");

	if(sizeof($_POST['remove']) > 0){
		foreach($_POST['remove'] as $e){
			$app->apiLoad('ad')->adZoneRemove($e);
		}
		header("Location: ad.zone.php");
	}else
	if($_POST['action']){
		$do = true;

		$def['k_adzone'] = array(
			'zoneName'	=> array('value' => $_POST['zoneName'], 'check' => '.'),
			'zoneCode'	=> array('value' => $_POST['zoneCode'], 'check' => '.'),
			'zoneSize'	=> array('value' => $_POST['zoneSize']),
		);
		if(!$app->formValidation($def)) $do = false;

		if($do){
			$result = $app->apiLoad('ad')->adZoneSet($_POST['id_adzone'], $def);
			$message = ($result)
				? 'OK: Enregistrement dans la base'
				: 'KO: Une erreur est survenue, APP:<br />'.$app->db_error;
			
		}else{
			$message = 'KO: Validation failed';
		}
	}

	if($_REQUEST['id_adzone'] != NULL){
		$data = $app->apiLoad('ad')->adZoneGet(array(
			'id_adzone'	=> $_REQUEST['id_adzone'],
			'debug'		=> false
		));
	}

	$zone = $app->apiLoad('ad')->adZoneGet(array('debug' => false));


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

<div id="app">

<div style="float:left; width:35%; margin-right:20px;">
<div class="searchBox clearfix">
	<div class="label">Zone</div>
	<input type="text" class="field roundTextInput roundSearchInput" onkeyup="recherche(this)" onkeydown="recherche(this)" size="15" />
</div>

<form action="ad.zone.php" method="post" id="listing">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing">
	<thead>
		<tr>
			<th width="30" class="icone"><img src="ressource/img/ico-delete-th.png" height="20" width="20" /></th>
			<th>Nom</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($zone as $e){ ?>
		<tr class="<?php if($e['id_adzone'] == $_REQUEST['id_adzone']) echo "selected" ?>">
			<td><input type="checkbox" name="remove[]" value="<?php echo $e['id_adzone'] ?>" class="cb" /></td>
			<td class="sniff"><a href="ad.zone.php?id_adzone=<?php echo $e['id_adzone'] ?>"><?php echo $e['zoneName'] ?></a></td>
		</tr>
		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td width="30" height="25"><input type="checkbox" onchange="$$('.cb').set('checked', this.checked);" /></td>
			<td><a href="#" onClick="apply();" class="button rButton">Supprimer la selection</a></td>
		</tr>
	</tfoot>
</table>
</div>
</form>

<div style="float:right; width:63%;">
	<?php
		if($message != NULL){
			list($class, $message) = $app->helperMessage($message);
			echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
		}
	?>
	
	<form action="ad.zone.php" method="post" id="data" enctype="multipart/form-data">
	<input type="hidden" name="action" value="1" />
	<input type="hidden" name="id_adzone" value="<?php echo $data['id_adzone'] ?>" />
	<table cellpadding="3" border="0">
		<tr>
			<td width="75">Nom</td>
			<td><input type="text" name="zoneName" value="<?php echo $app->formValue($data['zoneName'], $_POST['zoneName']); ?>" /></td>
		</tr>
		<tr>
			<td>Clé</td>
			<td><input type="text" name="zoneCode" value="<?php echo $app->formValue($data['zoneCode'], $_POST['zoneCode']); ?>" /></td>
		</tr>
		<tr>
			<td>Taille</td>
			<td><select name="zoneSize"><?php
				foreach($app->apiLoad('ad')->size as $e){
					$sel = ($e['width'].'-'.$e['height'] == $app->formValue($data['zoneSize'], $_POST['zoneSize'])) ? ' selected' : NULL;
					echo "<option value=\"".$e['width'].'-'.$e['height']."\"".$sel.">".$e['name']."</option>";
				}
			?></select></td>
		</tr>
		<tr>
			<td height="30"></td>
			<td>
				<a href="javascript:$('data').submit();" class="button rButton">Enregistrer</a>
				<a href="ad.zone.php" class="button rButton">Nouveau</a>
			</td>
		</tr>
	</table>

</div>



    <?php include(COREINC.'/end.php'); ?>
<script>

	function apply(){
		if(confirm("SUPPRIMER ?")){
			$('listing').submit();
		}
	}

	function recherche(f){
		$$('.sniff').each(function(me){
			if(!me.get('html').test(f.value, 'i')){
				me.getParent().setStyle('display', 'none');
			}else{
				me.getParent().setStyle('display', '');
			}
		});
	}

</script>



</body></html>