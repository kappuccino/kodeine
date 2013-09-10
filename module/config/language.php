<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	// Remove
	if(sizeof($_POST['del']) > 0){
		foreach($_POST['del'] as $e){
			$app->dbQuery("DELETE FROM k_country WHERE iso='".$e."'");
		}

		# Cache Country
		$app->configSet('boot', 'jsonCacheCountry', json_encode($app->countryGet(array('is_used' => true))));

		header("Location: language");
		exit();

	}else
	if($_POST['action']){
		$do = true;

		$_POST['iso'] = strtolower($_POST['iso']);
		if($_POST['iso_ref'] == NULL) $_POST['iso_ref'] = $_POST['iso'];

		$def['k_country'] = array(
			'iso'					=> array('value' => $_POST['iso'], 					'check' => '[a-z]{2}'),
			'iso_ref'				=> array('value' => $_POST['iso_ref'], 				'check' => '.'),
			'is_used'				=> array('value' => $_POST['is_used'], 				'zero' 	=> true),
			'is_delivered'			=> array('value' => $_POST['is_delivered'], 		'zero' 	=> true),
			'is_priced'		    	=> array('value' => $_POST['is_priced'], 		    'zero' 	=> true),
			'countryZone'			=> array('value' => $_POST['countryZone'], 			'check' => '.'),
			'countryName'			=> array('value' => $_POST['countryName'], 			'check' => '.'),
			'countryLanguage'		=> array('value' => $_POST['countryLanguage'], 		'check' => '.'),
			'countryLocale'			=> array('value' => $_POST['countryLocale'], 		'check' => '.'),
		);

		if(!$app->formValidation($def)) $do = false;

		if($do){
			$result = $app->countrySet($def);

			$message = ($result)
				? 'OK: Enregistrement dans la base'
				: 'KO: Une erreur est survenue, APP:<br />'.$app->db_error;

			# Cache Country
			$app->configSet('boot', 'jsonCacheCountry', json_encode($app->countryGet(array('is_used' => true))));

		}else{
			$message = 'KO: Validation failed';
		}

	}

	if($_REQUEST['iso'] != NULL){
		$data = $app->countryGet(array('iso' => $_REQUEST['iso']));
	}

	//////////////////////////////////////////////////////////////////

	$country = $app->countryGetByZone();

	foreach($country as $e){
		$out[$e['countryZone']][] = $e;
	}

	$country = $out;

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
	<li><a href="language-import" class="btn btn-mini"><?php echo _('Import more languages') ?></a></li>
	<li><a href="language" class="btn btn-small"><?php echo _('Cancel') ?></a></li>
	<li><a onclick="$('#data').submit();" class="btn btn-small btn-success"><?php echo _('Save') ?></a></li>
</div>

<div id="app"><div class="wrapper"><div class="row-fluid">
			
	<?php if(!$app->userCan('core.language')){ ?>
		<div class="message messageError"><?php echo _('Enough privileges') ?></div>
	<?php }else{ ?>

	<div class="span6">
		<form action="language" method="post" id="form">
		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing">
			<thead>
				<tr>
					<th width="30" class="icone"><i class="icon-remove icon-white"></i></th>
					<th><?php echo _('Country') ?></th>
					<th><?php echo _('Language') ?></th>
					<th width="20" class="icone"><i class="icon-globe icon-white"></i></th>
					<th width="20" class="icone"><i class="icon-shopping-cart icon-white"></i></th>
					<th width="20" class="icone">$</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($country as $zone){ ?>
				<tr class="separator">
					<td width="30"></td>
					<td colspan="5" style="font-weight: bold;"><?php echo $zone[0]['countryZone'] ?></td>
				</tr>
				<?php foreach($zone as $e){ $chkdel++; ?>
				<tr class="<?php if($e['iso'] == $_REQUEST['iso']) echo "selected" ?>">
					<td class="check-red"><input type="checkbox" class="chk" name="del[]" id="chkdel<?php echo $chkdel ?>" value="<?php echo $e['iso'] ?>" /></td>
					<td><a href="language?iso=<?php echo $e['iso'] ?>"><?php echo $e['countryName'] ?></a></td>
					<td><?php echo $e['countryLanguage'] ?></td>
					<td><img src="../core/ui/img/_img/boxcheck<?php if($e['is_used']) 		echo "ed"; ?>.png" align="absmiddle" /></td>
					<td><img src="../core/ui/img/_img/boxcheck<?php if($e['is_delivered'])	echo "ed"; ?>.png" align="absmiddle" /></td>
					<td><img src="../core/ui/img/_img/boxcheck<?php if($e['is_priced'])	    echo "ed"; ?>.png" align="absmiddle" /></td>
				</tr>
				<?php if(!empty($e['sub'])){
					foreach($e['sub'] as $s){ ?>
					<tr>
						<td style="padding: 8px 0 8px 80px" colspan="2">
							<a href="language?iso=<?php echo $s['iso'] ?>"><?php echo $s['countryName'] ?></a>
						</td>
						<td colspan="2"><?php echo $e['countryLanguage'] ?></td>
						<td><img src="../core/ui/img/_img/boxcheck<?php if($s['is_delivered'])	echo "ed"; ?>.png" align="absmiddle" /></td>
						<td><img src="../core/ui/img/_img/boxcheck<?php if($s['is_priced'])    echo "ed"; ?>.png" align="absmiddle" /></td>
					</tr>
					<?php if(!empty($s['sub'])){
						foreach($s['sub'] as $p){ ?>
							<tr>
								<td style="padding: 8px 0 8px 120px" colspan="2">
									<a href="language?iso=<?php echo $p['iso'] ?>"><?php echo $p['countryName'] ?></a>
								</td>
								<td colspan="2"><?php echo $e['countryLanguage'] ?></td>
								<td><img src="../core/ui/img/_img/boxcheck<?php if($s['is_delivered'])	echo "ed"; ?>.png" align="absmiddle" /></td>
								<td></td>
							</tr>
			<?php }} } } }} ?>
			</tbody>
			<tfoot>
				<tr>
					<td height="30"></td>
					<td colspan="4"><a onClick="apply();" class="btn btn-mini"><?php echo _('Remove selected items') ?></a></td>
				</tr>
			</tfoot>
		</table>
		</form>
	</div>

	<div class="span6">

		<?php
			if($message != NULL){
				list($class, $message) = $app->helperMessage($message);
				echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
			}
		?>
		
		<form action="language" method="post" id="data">
		<input type="hidden" name="action" value="1" />
		<input type="hidden" name="iso" value="<?php echo $data['iso'] ?>" />
		
		<table cellpadding="0" cellspacing="0" border="0" class="form">
			<tr>
				<td width="100">Key</td>
				<td><input type="text" name="iso" value="<?php echo $app->formValue($data['iso'], $_POST['iso']); ?>" />
					<?php echo _('Used in the URL /fr/') ?>
				</td>
			</tr>
			<tr>
				<td><?php echo _('Name') ?></td>
				<td><input type="text" name="countryName" value="<?php echo $app->formValue($data['countryName'], $_POST['countryName']); ?>" />
					<?php echo _('France, Italia, Sweden...') ?>
				</td>
			</tr>
			<tr>
				<td><?php echo _('Language') ?></td>
				<td><input type="text" name="countryLanguage" value="<?php echo $app->formValue($data['countryLanguage'], $_POST['countryLanguage']); ?>" />
					<?php echo _('Français, English, Dutch') ?>
				</td>
			</tr>
			<tr>
				<td><?php echo _('Locale') ?></td>
				<td><input type="text" name="countryLocale" value="<?php echo $app->formValue($data['countryLocale'], $_POST['countryLocale']); ?>" />
					<?php echo _('fr_FR, en_EN, de_DE') ?>
				</td>
			</tr>
			<tr>
				<td><?php echo _('Reference') ?></td>
				<td><select name="iso_ref"><?php
					if($data['iso'] == $data['iso_ref']) $selSame = ' selected';
					echo '<option value=""'.$selSame.'>Pas de référence</option>';
	
					$all = $app->countryGet();
					foreach($all as $e){
						$sel = ($e['iso'] == $app->formValue($data['iso_ref'], $_POST['iso_ref']) && $selSame == '') ? ' selected' : NULL;
						echo "<option value=\"".$e['iso']."\"".$sel.">".strtoupper($e['iso'])." : ".$e['countryName']."</option>";
					}

				?></select></td>
			</tr>
			<tr>
				<td><?php echo _('Area') ?></td>
				<td><select name="countryZone" id="countryZone"><?php
					foreach($app->dbMulti("SELECT DISTINCT countryZone FROM k_country") as $e){
						$sel = ($app->formValue($data['countryZone'], $_POST['countryZone']) == $e['countryZone']) ? ' selected' : NULL;
						echo "<option value=\"".$e['countryZone']."\"".$sel.">".$e['countryZone']."</option>";
					}
					
				?></select>
				<a href="javascript:addZone();" class="btn btn-mini"><?php echo _('Add a zone') ?></a>
				</td>
			</tr>
			<tr>
				<td><?php echo _('Translate') ?></td>
				<td><input type="checkbox" name="is_used" value="1" <?php echo $app->formValue($data['is_used'], $_POST['is_used']) ? ' checked' : ''; ?> />
					<?php echo _('Enable data to be translated in the language in the back office') ?>
				</td>
			</tr>
			<tr>
				<td><?php echo _('Delivery') ?></td>
				<td><input type="checkbox" name="is_delivered" value="1" <?php echo $app->formValue($data['is_delivered'], $_POST['is_delivered']) ? ' checked' : ''; ?> />
					<?php echo _('Enable the country for delivery (eBusiness)') ?>
				</td>
			</tr>
			<tr>
				<td><?php echo _('Custom price') ?></td>
				<td><input type="checkbox" name="is_priced" value="1" <?php echo $app->formValue($data['is_priced'], $_POST['is_priced']) ? ' checked' : ''; ?> />
					<?php echo _('Allow to set a different price') ?>
				</td>
			</tr>
		</table>

		</form>
	</div>
	<?php } ?>
	
</div></div></div>

<?php include(COREINC.'/end.php'); ?>
<script>

	function apply(){
		if(confirm("<?php echo addslashes(_('Would you really want to remove this language ?')) ?>")) $('#form').submit();
	}
	
	function addZone(){
		zone = prompt("<?php echo addslashes(_('Give this zone a name')) ?>");

		if(zone.length > 0){
			$('#countryZone').append('<option value="'+zone+'" selected="selected">'+zone+'</option>');
		}
	}

</script>

</body></html>