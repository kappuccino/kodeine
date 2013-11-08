<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	$uploaddir 	= USER.'/temp';
	$file		= '/user.tmp';

	if($_POST['action'] && $_FILES['upTemplate']['tmp_name'] != NULL){

		umask(0);
		
		if(!file_exists($uploaddir)){
			mkdir($uploaddir, 0755, true);
		}

		# Si le fichier est bien deplacé dans le bon dossier
		if(@move_uploaded_file($_FILES['upTemplate']['tmp_name'], $uploaddir.'/'.$file)){
			$_POST['myFile'] = $uploaddir.'/'.$file;
			$message = "Le fichier est sur le serveur, vous allez &ecirc;tre guid&eacute; pour les &eacute;tapes suivantes.";
		}else{
			$type	 = 'error'; 
			$message = "Echec de la mise en ligne de la base utilisateur, verifier que le dossier <i>/module/custom</i> existe et que Kappuccino poss&egrave;de bien les droits d'ecriture pour celui ci (755)";
		}
	}

	if($_POST['myFile'] != NULL && !file_exists($_POST['myFile'])){
		$type 		= 'error';
		$message	= 'Le fichier d\'import n\'est plus accessible';
		unset($_POST['myFile']);
	}
	
	foreach($app->apiLoad('field')->fieldGet(array('user' => true)) as $e){
		$field[$e['id_field']] = $e;
	}

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

<div id="app"><div class="wrapper">
		
		<div class="alert" id="message" style="display:<?php if($message == '') echo 'none' ?>"><?php echo $message ?>&nbsp;</div>
		<div class="alert alert-error"	id="error" 	 style="display:none;"><?php echo _('Error') ?></div>
		<div class="alert alert-error"	id="doublon" style="display:none;"><?php echo _('Duplicate') ?></div>
		
		<?php if($_POST['myFile'] != NULL){ ?>
		<form action="import" method="POST" id="formulaire">
			<input type="hidden" name="myFile" value="<?php echo $_POST['myFile'] ?>" />
			
				<?php
		
					$return = $app->apiLoad('user')->userImportCSV($_POST['myFile'], $_POST);
					
					if(!is_array($return)){
						$k->pre($return);
					}else{		
						list($step, $data) = $return;
					#	$k->pre($step, $data, $error);
			
						/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
						 - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */
						function body($data){
							echo "<tbody>";
							foreach(array_splice($data['lignes'], 0, 6) as $ligne){
								if(trim($ligne) != ''){
									echo "<tr>";
										foreach(explode($data['sepColonne'], $ligne) as $colonne){
											echo "<td>".$colonne."</td>";
										}
									echo "</tr>";
								}
							}
							echo "</tbody>";
						}
			
						/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
						 - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */
			
						function menu($index, $label, $me){
						
							$out  =
							"<select name=\"headers[".$index."]\">".
								"<option value=\"\">"._('Do not use')."</option>".
								"<optgroup label=\""._('Mandatory fields')."\">";
									foreach(array('id_user' => _('User ID (uniq)'), 'userMail' => _('Email (login)')) as $field => $e){
										$sel  = ($field == $me) ? ' selected' : NULL;
										$out .= "<option value=\"".$field."\"".$sel.">".$e."</option>";
									}
								$out .= "</optgroup><optgroup label=\""._('Custom fields')."\">";
									foreach($label as $e){
										$sel  = ($e['id_field'] == $me) ? ' selected' : NULL;
										$out .= "<option value=\"".$e['id_field']."\"".$sel.">".$e['fieldName']."</option>";
									}
								$out .= "</optgroup>".
							"</select>";
						
							return $out;
						}
			
			
			
			
						switch($step){
							case 'needHeaders' :
								echo "<div class=\"step\">"._('Step 2 : Match each columns to kodeine custom user fields')."</div>";
								if(sizeof($data['lignes']) > 6){
									echo "<p>".sprintf(_('The file contains %s lines, only the six first lines are displayed'), sizeof($data['lignes']))."</p>";
								}
			
								echo "<table cellspacing=\"0\" class=\"listing\" border=\"0\"><thead><tr>";
								foreach($data['colonnes'] as $index => $colonne){
									echo "<th>".menu($index, $field, $_POST['headers'][$index])."</th>";
								}
								echo "</tr></thead>";
								body($data);
								echo "</table>".
			
								"<p><input type=\"submit\" value=\"Continuer\" /></p>";
			
							break;
						
							case 'needID' : 
								echo "<div class=\"step\">"._('Step 3 : You must check this options to complete the import')."</div>";
								if(sizeof($data['lignes']) > 6){
									echo "<p>".sprintf(_('The file contains %s lines, only the six first lines are displayed'), sizeof($data['lignes']))."</p>";
								}
		
								echo
								"<table cellspacing=\"0\" class=\"listing\" border=\"1\">".
								"<thead>".
									"<tr>";
										foreach($data['colonnes'] as $index => $colonne){
											echo "<th><b>";
											if(ereg('[0-9]{1,}', $_POST['headers'][$index])){
												echo $field[$_POST['headers'][$index]]['fieldName'];
											}else
											if($_POST['headers'][$index] != NULL){
												echo $_POST['headers'][$index];
											}else{
												echo "-";
											}
											echo "</b></th>";
										}
									echo "</tr>";
								echo "</tr>";
								echo "</thead>";
								body($data);
								echo "</table>";
		
								echo "<br />".
								"<table cellspacing=\"0\" class=\"listing\" width=\"100%\" border=\"0\">".
								"<thead>".
									"<tr>".
										"<th colspan=\"2\">"._('More informations')."</th>".
									"</tr>".
								"</thead>".
								"<tbody>".
									"<tr>".
										"<td width=\"400\">"._('The first line does not contain user data')."</td>".
										"<td><input type=\"checkbox\" name=\"removeFirst\" value=\"1\" /></td>".
									"</tr>".
									"<tr>".
										"<td>"._('Check if the user already exists')."</td>".
										"<td><input type=\"checkbox\" name=\"checkDoublon\" value=\"1\" /></td>".
									"</tr>".
									"<tr>".
										"<td>"._('If not defined, import the user in the group')."<td>".
										$app->apiLoad('user')->userGroupSelector(array(
											'one'	=> true,
											'name'	=> 'id_group'
										))."</td>".
									"</tr>".
									"<tr>".
										"<td>"._('If not defined, activate the user')."</td>".
										"<td><input type=\"checkbox\" name=\"activate\" value=\"1\" /></td>".
									"</tr>".
									/*"<tr valign=\"top\">".
										"<td>Si non pr&eacute;ciser, ces membres recevront les mailings</td>".
										"<td>";
										foreach($app->apiLoad('newsletter')->newsletterTypeGet() as $n){
											echo "<input type=\"checkbox\" name=\"id_newslettertype\" value=\"".$n['id_newslettertype']."\" /> ".$n['newsletterType']."<br />\n";
										}
										echo "</td>".
									"</tr>".*/
								"</tbody>".
								"</table>";
								
								foreach($_POST['headers'] as $indexH => $header){
									echo "<input type=\"hidden\" name=\"headers[".$indexH."]\" value=\"".$header."\" />\n";
								}
			
								echo "<p><input type=\"button\" onClick=\"debuter();\" name=\"js\" value=\"importer\" /></p>";
			
							break;
						
							case 'imported' : 
			
								echo ($data['count'] > 0)
									? "<p>".sprintf(_('Import finished: %s users imported in your database'), $data['count'])."</p>"
									: "<p>"._('No user imported')."</p>";
			
								if(sizeof($data['doublon']) > 0){
									echo "<p>"._('Duplicates accounts detected during the import')."<p>";
									echo "<p>";
									foreach($data['doublon'] as $doublon){
										echo "- ".$doublon['user']." (".$doublon['id_user'].")<br />";
									}
									echo "</p>";
								}
			
								if(sizeof($data['error']) > 0){
									echo "<p>"._('Errors occured during this import. Can not import :')."</p>";
									echo "<p>";
									foreach($data['error'] as $error){
										echo "- ".$error['user']." (".$error['id_user'].")<br />";
									}
									echo "</p>";
								}
			
							break;
						}
					}
				?>
				
			</form>
		
		<?php }else{
		
			if(file_exists($uploaddir.'/'.$file)) unlink($uploaddir.'/'.$file);
		?>
			
			<div class="step"><?php echo _('Step 1: Choose your import file on your computer') ?></div>
			
			<form action="import" method="post" enctype="multipart/form-data">
				<input type="hidden" name="max_file_size" value="100000000" />
				<input type="hidden" name="action" value="1" />
				<input type="file" name="upTemplate" /> <input type="submit" value="<?php echo _('Upload this file') ?>" />
			</form>	
			
			<p><br /><br /></p>
			<p><?php echo _('Import files must: ') ?></p>
			<ul>
				<li><?php echo _('Be texte files (.TXT, .CSV)') ?></li>
				<li><?php echo _('Containing one user a line') ?></li>
				<li><?php echo _('Containning user properties in distinct columnes') ?></li>
			</ul>
			<p><?php echo _('Kodeine detect automaticaly, the new line caracter.') ?></p>
			<p><?php echo _('If the file uses a protect caracter to wrapp data, it (ie: "value","value","value"...), it will be removed.') ?></p>
		
		<?php } ?>
		

</div></div>	

<?php include(COREINC.'/end.php'); ?>
<script type="text/javascript" src="ui/js/import.js"></script> 

</html>