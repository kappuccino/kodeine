<?php
	if($_POST['action']){
		$do = true;
	
		$def['k_ad'] = array(
			'id_adzone'	=> array('value' => $_POST['id_adzone']),
			'is_active'	=> array('value' => $_POST['is_active'],	'zero'	=> true),
			'adName' 	=> array('value' => $_POST['adName'], 		'check' => '.'),
			'adTogo' 	=> array('value' => $_POST['adTogo'], 		'check' => '.'),
			'adStart' 	=> array('value' => $_POST['adStart'], 		'null' => true),
			'adEnd' 	=> array('value' => $_POST['adEnd'], 		'null' => true),
			'adCode'	=> array('value' => $_POST['adCode'], 		'null' => true),
			'adMedia'	=> array('value' => $_POST['adMedia'], 		'null' => true),
		);
		
		if($_POST['resetView'])  $def['k_ad']['adView']  = array('value' => '0');
		if($_POST['resetClick']) $def['k_ad']['adClick'] = array('value' => '0');

		if(!$app->formValidation($def)) $do = $false;

		if($do){
			$result = $app->apiLoad('ad')->adSet($_POST['id_ad'], $def);
			$message = ($result) ? 'OK: Enregistrement' : 'KO: Probleme, APP : <br />'.$app->db_error;
		}else{
			$message = 'KO: Validation failed';
		}
	}

	if($_REQUEST['id_ad'] != NULL){
		$data = $app->apiLoad('ad')->adGet(array(
			'id_ad' 	=> $_REQUEST['id_ad']
		));

		$title = $data['adName'];
	}else{
		$title = 'Nouvelle publicité';
	}
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
				<h1><a href="index">Publicité</a> &raquo; 
					<a href="data">Editer</a>
						&raquo; <?php echo $title ?></h1>
			</div>
		</div>
	</div>

<div class="bocontainer">
	<div class="row-fluid">
		
	<?php include('lib/menu-ad.php'); ?>
	
	<br style="clear:both" />
	
	<div class="app">
	
	<?php
		if($message != NULL){
			list($class, $message) = $app->helperMessage($message);
			echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
		}
	?>
	
	<div style="margin:3px 0px 10px 0px;">
		<a href="javascript:$('#data').submit()" class="button button-green">Enregistrer</a>
		<a href="data" class="button button-blue">Nouveau</a>
	</div>
	
	<br style="clear:both" />
	
	<form action="data" method="post" id="data">
	<input type="hidden" name="action" value="1" />
	<input type="hidden" name="id_ad" value="<?php echo $data['id_ad'] ?>" />
	
	<div class="tabset">
		<div class="view clearfix">
		
			<div class="span6">
				<table cellpadding="3" width="100%" border="0">
					<tr>
						<td>Nom</td>
					</tr>
					<tr valign="top">
						<td height="30"><input type="text" name="adName" value="<?php echo $app->formValue($data['adName'], $_POST['adName']); ?>" size="40" style="width:100%;" /></td>	
					</tr>
					<tr>
						<td>Zone</td>
					</tr>
					<tr valign="top">
						<td height="30"><select name="id_adzone"><?php
							foreach($app->apiLoad('ad')->adZoneGet() as $e){
								$sel = ($app->formValue($data['id_adzone'], $_POST['id_adzone']) == $e['id_adzone']) ? ' selected' : NULL;
								echo "<option value=\"".$e['id_adzone']."\"".$sel.">".$e['zoneName']."</option>";
							}
						?></select></td>
					</tr>
					<tr>
						<td>Adresse de redirection suite au click</td>
					</tr>
					<tr valign="top">
						<td height="30"><input type="text" name="adTogo" value="<?php echo $app->formValue($data['adTogo'], $_POST['adTogo']); ?>" size="90" style="width:100%;" /></td>	
					</tr>
					<tr>
						<td>
							<table border="0">
								<tr>
									<td width="120">Débute</td>
									<td width="120">Termine</td>
									<td width="120">Active</td>
								</tr>
								<tr valign="top">
									<td height="30"><input type="text" name="adStart" value="<?php echo $app->formValue($data['adStart'], $_POST['adStart']); ?>" size="12" /></td>	
									<td><input type="text" name="adEnd" value="<?php echo $app->formValue($data['adEnd'], $_POST['adEnd']); ?>" size="12" /></td>	
									<td><input type="checkbox" name="is_active" value="1" <?php if($app->formValue($data['is_active'], $_POST['is_active'])) echo "checked" ?> /></td>
								</tr>
							</table>
						</td>
					</tr>
					<?php if($data['id_ad'] > 0){ ?>
					<tr>
						<td>
							<table border="0">
								<tr align="right">
									<td width="120">Vue</td>
									<td width="120">Click</td>
								</tr>
								<tr valign="top" align="right">
									<td><?php echo number_format($data['adView'],  0, '.', ' '); ?></td>	
									<td><?php echo number_format($data['adClick'], 0, '.', ' '); ?></td>	
								</tr>
								<tr align="right">
									<td>Remettre à zéro <input type="checkbox" value="1" name="resetView" /></td>
									<td>Remettre à zéro <input type="checkbox" value="1" name="resetClick" /></td>
								</tr>
							</table>
						</td>
					</tr>
					<?php } ?>
				</table>
			</div>
			
			<div class="span6">
				<table cellpadding="3" width="100%">
					<tr>
						<td>Coller ici le code Javascript ou Flash pour afficher la publicité</td>
					</tr>
					<tr>
						<td><textarea name="adCode" style="height:60px; width:99%;"><?php echo $app->formValue($data['adCode'], $_POST['adCode']) ?></textarea></td>
					</tr>
					<tr>
						<td>Liste des images qui seront affiché pour cette campagne</td>
					</td>
					</tr>
						<td>
							<ul class="field-list"><?php
								echo $app->apiLoad('field')->fieldForm(
									NULL,
									$app->formValue($data['adMediaRaw'], $_POST['adMedia']),
									array(
										'name' 	=> 'adMedia',
										'id' 	=> 'adMedia',
										'style' => 'width:100%',
										'field' => array(
											'fieldType' => 'media'
										)
									)
								);
							?></ul>
						</td>
					</tr>
				</table>
			</div>
			
		</div>	
	</div>
	
	
	</form>
	
	<?php include(COREINC.'/end.php'); ?>
	<script src="../content/ui/js/content.js"></script>
	
	<script type="text/javascript">
	
	$(function(){
		//	mediaList = ['adMedia'];
		replace = [];
		useEditor = false;
		boot();

			/*mediaList.each(function(list){
				mediaEnableSort(list);
				doMediaAction(list);
			
				$(list).addEvent('change', function(){
					mediaEnableSort(this.id);
				});
			});*/
	});
	
	</script>
	</div>
</div>
</div>	
</body></html>