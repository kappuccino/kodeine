<?php
	if(isset($_GET['csv'])){

		$data	= $app->apiLoad('newsletter')->newsletterListGet(array('id_newsletterlist' => $_GET['id_newsletterlist']));
		$flt	= ($_GET['flag'] != NULL) ? " AND flag='".$_GET['flag']."'" : NULL; 
		$name	= ($_GET['flag'] != NULL) ? '-'.strtolower($_GET['flag']) 	: NULL;

		$users	= $app->dbMulti(
			"SELECT SQL_CALC_FOUND_ROWS * FROM  k_newslettermail ".
			"INNER JOIN k_newsletterlistmail ON k_newslettermail.id_newslettermail = k_newsletterlistmail.id_newslettermail ".
			"WHERE k_newsletterlistmail.id_newsletterlist = ".$_GET['id_newsletterlist']." ".$flt
		);

		if(sizeof($users) > 0){
			foreach($users as $e){
				$tmp[] = $e['mail'];
			}
	
			$out = implode(PHP_EOL, $tmp);
		}else{
			$out = '';
		}

		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: ".gmdate("D,d M YH:i:s")." GMT");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Content-Length: ".strlen($out));
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=\"".$data['listName'].$name.".csv\"" );
		echo $out;
		exit();
	}else
	if(isset($_POST['speed'])){
		if(filter_var($_REQUEST['speed'], FILTER_VALIDATE_EMAIL)){
			$app->apiLoad('newsletter')->newsletterSubscribe(array(
				'email'		=> $_POST['speed'],
				'clean'		=> true,
				'list'		=> $_POST['list']
			));
			header("Location: list?speed=".$_REQUEST['speed']);
		}
	}else
	if($_GET['desabo'] != ''){
		$mail = $app->dbOne("SELECT * FROM k_newslettermail WHERE mail='".trim(urldecode($_GET['desabo']))."'");
		if($mail['id_newslettermail'] > 0){
			$app->dbQuery("DELETE FROM k_newsletterlistmail WHERE id_newslettermail=".$mail['id_newslettermail']);
			$app->dbQuery("UPDATE k_newslettermail SET flag='IGNORE' WHERE id_newslettermail=".$mail['id_newslettermail']);
			header("Location: list?speed=".urlencode($_GET['desabo']));
		}

	}else
	if($_GET['allow'] != '' && filter_var($_GET['allow'], FILTER_VALIDATE_EMAIL)){

		$mail = $app->dbOne("SELECT * FROM k_newslettermail WHERE mail='".trim(urldecode($_GET['allow']))."'");
		if($mail['id_newslettermail'] > 0){
			$app->dbQuery("UPDATE k_newslettermail SET flag='VALID' WHERE id_newslettermail=".$mail['id_newslettermail']);
			header("Location: list?speed=".urlencode($_GET['allow']));
		}

	}else
	if(isset($_REQUEST['purge'])){
		$error = $app->dbMulti("SELECT mail, id_newslettermail FROM k_newslettermail WHERE flag='ERROR'");

		foreach($error as $e){
			$tmp[] = $e['id_newslettermail'];
		}
		if(sizeof($tmp) > 0){
			$app->dbQuery("DELETE FROM k_newslettermail 	WHERE id_newslettermail IN(".implode(',', $tmp).")");
			$app->dbQuery("DELETE FROM k_newsletterlistmail WHERE id_newslettermail IN(".implode(',', $tmp).")");
		}

		header("Location: list?message=".urlencode("OK: Purge terminée. Suppression de ".sizeof($tmp)." mail(s) dans l'intégralité des listes"));

	}else
	if($_POST['todo'] == 'createListFromFlag' && trim($_POST['listName']) != NULL){
	
		# Liste des ITEMS depuis la selection du FLAG
		$users = $app->dbMulti("SELECT * FROM k_newslettertracking WHERE id_newsletter = ".$_POST['id_newsletter']." AND trackingFlag='".$_POST['flag']."'");
		foreach($users as $e){
			$items[] = $e['trackingMail'];
		}

		# Creation de la LIST
		$app->apiLoad('newsletter')->newsletterListSet(NULL, array('k_newsletterlist' => array(
			'listName' => array('value' => $_POST['listName'], 'check' => '.')
		))); $id_newsletterlist = $app->apiLoad('newsletter')->db_insert_id;

		# Import des ITEMS dans la LIST
		$app->apiLoad('newsletter')->newsletterListImportJob(array(
			'id_newsletterlist' => $id_newsletterlist,
			'items'				=> $items
		));

		header("Location: list?id_newsletterlist=".$id_newsletterlist.'&user');
	}else
	if($_POST['todo'] == 'createListFromClick' && trim($_POST['listName']) != NULL){
		
		# Liste des ITEMS depuis la selection
		$users = $app->dbMulti("SELECT * FROM k_newsletterclick WHERE id_newsletter = ".$_POST['id_newsletter']);
		foreach($users as $e){
			$items[] = $e['clickMail'];
		}	
		
		# Creation de la LIST
		$app->apiLoad('newsletter')->newsletterListSet(NULL, array('k_newsletterlist' => array(
			'listName' => array('value' => $_POST['listName'], 'check' => '.')
		))); $id_newsletterlist = $app->apiLoad('newsletter')->db_insert_id;

		# Import des ITEMS dans la LIST
		$app->apiLoad('newsletter')->newsletterListImportJob(array(
			'id_newsletterlist' => $id_newsletterlist,
			'items'				=> $items
		));

		header("Location: list?id_newsletterlist=".$id_newsletterlist.'&user');
	}else
	if(sizeof($_POST['killmail']) > 0){

		foreach($_POST['killmail'] as $id_newslettermail){
			$count = $app->dbOne("SELECT COUNT(id_newsletterlist) AS c FROM k_newsletterlistmail WHERE id_newslettermail=".$id_newslettermail);
			if($count['c'] == 1) $del[] = $id_newslettermail;
		}
		$app->dbQuery("DELETE FROM k_newsletterlistmail WHERE id_newsletterlist=".$_POST['id_newsletterlist']." AND id_newslettermail IN(".implode(',', $_POST['killmail']).")");

		if(sizeof($del) > 0){
			$app->dbQuery("DELETE FROM k_newslettermail WHERE id_newslettermail IN(".implode(',', $del).")");
		}

		header("Location: list?id_newsletterlist=".$_POST['id_newsletterlist'].'&user');
	}else
	if(isset($_GET['empty'], $_GET['id_newsletterlist'])){
		$app->apiLoad('newsletter')->newsletterListEmpty($_GET['id_newsletterlist']);
		header("Location: list?id_newsletterlist=".$_GET['id_newsletterlist'].'&user');
	}else
	if(sizeof($_POST['remove']) > 0){
		foreach($_POST['remove'] as $e){
			$app->apiLoad('newsletter')->newsletterListRemove($e);
		}
		header("Location: list");
	}else
	if($_POST['action']){
		$do = true;

		if($_FILES['upFile']['tmp_name'] != NULL){

			if($_SESSION['upFile'] == NULL) $_SESSION['upFile'] = uniqid();

			$up = USER.'/'.$_SESSION['upFile'];
			if(file_exists($up)) unlink($up);
			umask(0);

			# Si le fichier est bien deplace dans le bon dossier
			if(move_uploaded_file($_FILES['upFile']['tmp_name'], $up)){
				$app->apiLoad('newsletter')->newsletterListImport($_POST['id_newsletterlist'], $up);
				unlink($up);
			}else{
				echo 'Erreur lors de l\'envois du fichier';
			}
		}

		if($_POST['listExternalUrl'] != NULL){
			$get = @file_get_contents($_POST['listExternalUrl']);

			if(strlen($get) > 5){
				$app->apiLoad('newsletter')->newsletterListImportJob(array(
					'id_newsletterlist' => $_POST['id_newsletterlist'],
					'items'				=> explode("\n", trim($get))
				));
			}
		}

		$def['k_newsletterlist'] = array(
			'listName'			=> array('value' => $_POST['listName'],			'check' => '.'),
			'listExternalUrl'	=> array('value' => $_POST['listExternalUrl'])
		);

		if(!$app->formValidation($def)) $do = false;

		if($do){
			$result = $app->apiLoad('newsletter')->newsletterListSet($_POST['id_newsletterlist'], $def);
			$message = ($result)
				? 'OK: Enregistrement dans la base'
				: 'KO: Une erreur est survenue, APP:<br />'.$app->db_error;

			if(strlen(trim($_POST['raw'])) > 0){
				$app->apiLoad('newsletter')->newsletterListImportJob(array(
					'id_newsletterlist' => $app->apiLoad('newsletter')->id_newsletterlist,
					'items'				=> explode("\n", $_POST['raw'])
				));
			}
		}else{
			$message = 'KO: Validation failed';
		}
	}

	if($_REQUEST['id_newsletterlist'] != NULL){
		$data = $app->apiLoad('newsletter')->newsletterListGet(array(
			'id_newsletterlist'	=> $_REQUEST['id_newsletterlist'],
			'debug'				=> false
		));
	}

	$list = $app->apiLoad('newsletter')->newsletterListGet(array('debug' => false));
?><!DOCTYPE html>
<head>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>
	
<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div id="app"><div class="wrapper">

<div style="float:left; width:35%; margin-right:20px;">

	<form action="list" method="post" id="listing">
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing">
		<thead>
			<tr>
				<th width="30" class="icone"><i class="icon-remove icon-white"></i></th>
				<th>Liste</th>
				<th width="80">Total</th>
				<th width="80">Valides</th>
			</tr>
		</thead>
		<tbody><?php 
		if(sizeof($list) > 0){
			foreach($list as $e){ ?>
			<tr class="<?php if($e['id_newsletterlist'] == $_REQUEST['id_newsletterlist']) echo "selected" ?>">
				<td><input type="checkbox" name="remove[]" value="<?php echo $e['id_newsletterlist'] ?>" class="cb" /></td>
				<td class="sniff"><a href="list?id_newsletterlist=<?php echo $e['id_newsletterlist'] ?>"><?php echo $e['listName'] ?></a></td>
				<td>
					<a href="list?id_newsletterlist=<?php echo $e['id_newsletterlist'] ?>&user" class="btn btn-mini"><?php
						$how = $app->dbOne("SELECT COUNT(*) AS how FROM k_newsletterlistmail WHERE id_newsletterlist=".$e['id_newsletterlist']);
						echo $how['how'];
					?></a>
				</td>
				<td>
					<a href="list?id_newsletterlist=<?php echo $e['id_newsletterlist'] ?>&user" class="btn btn-mini"><?php
						$val = $app->dbOne(
							"SELECT COUNT(*) AS how FROM k_newsletterlistmail\n".
							"INNER JOIN k_newslettermail ON k_newsletterlistmail.id_newslettermail = k_newslettermail.id_newslettermail\n".
							"WHERE flag='VALID' AND id_newsletterlist=".$e['id_newsletterlist']
						);
						echo $val['how'];
					?></a>
				</td>
			</tr>
			<?php }
		}else{ ?>
			<tr>
				<td colspan="4" style="text-align:center; padding:30px 0px 30px 0px; font-weight:bold;">Aucune liste existante.</td>
			</tr>
		<?php } ?>
		</tbody>
		<tfoot>
			<tr>
			<?php if(sizeof($list) > 0){ ?>
				<td width="30" height="25"><input type="checkbox" onchange="$('.cb').set('checked', this.checked);" /></td>
				<td><a href="#" onClick="applyRemove();" class="btn btn-mini">Supprimer la selection</a></td>
				<td colspan="2" align="right">
					<a href="#" onClick="purge();"	class="btn btn-mini">Purger les mails invalides</a>
				</td>
			<?php }else{ ?>
				<td colspan="4">&nbsp;</td>
			<?php } ?>
			</tr>
		</tfoot>
	</table>
	</form>

	<form action="list" method="get" style="margin-top:20px;">
		<b>Action rapide</b><br />
		<div class="clearfix">
			<input type="text" name="speed" style=" width:200px;" />
			<br />
			<input type="submit" value="Abonner / Désabonner ce membre" />
		</div>
	</form>

</div>

<div style="float:right; width:63%;">
	<?php
		if($_GET['message'] != NULL) $message = stripslashes(urldecode($_GET['message']));
		if($message != NULL){
			list($class, $message) = $app->helperMessage($message);
			echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
		}
		
		if(isset($_REQUEST['speed'])){ ?>
		
			<form method="post" action="list">
				
				<div style="padding:0px 0px 10px 0px;">
					Email : <input type="text" name="speed" value="<?php echo $_REQUEST['speed'] ?>" /> 
				</div>
				
				<?php $flag = (filter_var($_REQUEST['speed'], FILTER_VALIDATE_EMAIL) === FALSE) ? 'ERROR' : 'VALID';
				
				$mail = $app->dbOne("SELECT * FROM k_newslettermail WHERE mail='".addslashes($_REQUEST['speed'])."'");
				$abo = $app->dbMulti("SELECT id_newsletterlist FROM k_newsletterlistmail WHERE id_newslettermail='".$mail['id_newslettermail']."'");

				$abo = is_array($abo) ? $abo : array();

				foreach($abo as $e){
					$abo_[] = $e['id_newsletterlist'];
				}

				$abo = is_array($abo_) ? $abo_ : array();

				if($flag == 'ERROR'){
					echo "<p>Ce mail n'est pas au bon format</p>";
					echo "<input type=\"submit\" value=\"Valider\" />";
				}else{
					
					$me = $app->dbOne("SELECT * FROM k_newslettermail WHERE mail='".trim(urldecode($_GET['speed']))."'");

					if($me['flag'] == 'IGNORE'){
						echo "<p>Cet email est actuellement désabonné <a href=\"#\" onclick=\"allow();\">Autoriser de nouveau</a></p>";
					}
				?>
					<table class="listing" cellpadding="0" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th></th>
								<th>Sélectionnez les listes avec lesquelles cet email doit être relié.</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach($list as $e){
						
							$sel = in_array($e['id_newsletterlist'], $abo) ? 'checked="checked"' : NULL;

							echo "<tr>";
								echo "<td width=\"15\">";
								#	echo "<input type=\"hidden\"   name=\"list[".$e['id_newsletterlist']."]\" value=\"0\" /> ";
									echo "<input type=\"checkbox\" name=\"list[]\" value=\"".$e['id_newsletterlist']."\" ".$sel." /> ";
								echo "</td>";
								echo "<td>".$e['listName']."</td>";
							echo "</tr>";

						} ?>
						</tbody>
						<tfoot>
							<tr>
								<td></td>
								<td>
									<input type="submit" value="Mettre à jour les abonnement pour ce membre" />
									<?php if($me['flag'] != 'IGNORE'){ ?>
									ou <a href="#" onclick="desabo()" class="btn btn-mini">Désabonner cet email de toute les listes</a>
									<?php } ?>
								</td>
							</tr>
						</tfoot>
					</table>
				<?php } ?>

			</form>
		
		<?php }else		
		if(isset($_REQUEST['user'])){
			$ord = ($_GET['ord'] == '')		? 'mail' : $_GET['ord']; 
			$dir = ($_GET['dir'] == 'ASC')  ? 'DESC' : 'ASC';
			$p	 = ($_GET['p'] == NULL) ? 0 : $_REQUEST['p'];
			$max = 300;
			$flt = ($_GET['flag'] != NULL) ? " AND flag='".$_GET['flag']."'" : NULL; 

			$users = $app->dbMulti(
				"SELECT SQL_CALC_FOUND_ROWS * FROM  k_newslettermail ".
				"INNER JOIN k_newsletterlistmail ON k_newslettermail.id_newslettermail = k_newsletterlistmail.id_newslettermail ".
				"WHERE k_newsletterlistmail.id_newsletterlist = ".$_GET['id_newsletterlist']." ".$flt." ORDER BY ".$ord." ".$dir." ". 
				"LIMIT ".$p.",".$max
			);

			$total = $app->db_num_total;
			
			?>
			<form method="get" style="padding:5px 0px 5px 0px; text-align:center; background:rgb(250,250,250);">
				Trier les résultats : 
				<select onchange="document.location=this.options[this.selectedIndex].value"><?php
				foreach(array('', 'VALID', 'BOUNCE', 'IGNORE', 'ERROR') as $e){
					$sel = ($e == $_GET['flag']) ? ' selected' : NULL;
					echo "<option value=\"list?id_newsletterlist=".$_REQUEST['id_newsletterlist']."&user&flag=".$e."\"".$sel.">".$e."</option>\n";
				}
				?></select>

				<a href="javascript:empty()" class="btn btn-mini">Vider la liste</a>
				&nbsp; 
				<a href="list" class="btn btn-mini">Nouvelle liste</a>
				&nbsp; 
				<a href="list?id_newsletterlist=<?php echo $_GET['id_newsletterlist'].'&user&flag='.$_GET['flag'] ?>&csv" class="btn btn-mini">Export CSV</a>
			</form>
			<?php if(sizeof($users) > 0){ ?>
			<form method="post" action="list" name="mails" id="mails">
			<input type="hidden" name="id_newsletterlist" value="<?php echo $_REQUEST['id_newsletterlist'] ?>" />
			<table border="0" cellpadding="0" cellspacing="0" class="listing" width="100%">
				<thead>
					<tr>
						<th width="30" class="icone"><i class="icon-remove icon-white"></i></th>
						<th				class="order <?php if($ord == 'mail') echo 'order'.$dir; ?>" onClick="document.location='list?id_newsletterlist=<?php echo $_GET['id_newsletterlist'] ?>&user&ord=mail&dir=<?php echo $dir ?>'"><span>Mail</span></th>
						<th width="100" class="order <?php if($ord == 'flag') echo 'order'.$dir; ?>" onClick="document.location='list?id_newsletterlist=<?php echo $_GET['id_newsletterlist'] ?>&user&ord=flag&dir=<?php echo $dir ?>'"><span>Flag</span></th>
					</tr>
				</thead>
				<tbody>
				<?php if(sizeof($users) == 0){ ?>
					<tr>
						<td colspan="3" style="text-align:center; padding:20px;">Aucun mail</td>
					</tr>
				<?php }else{ foreach($users as $e){ ?>
					<tr>
						<td><input type="checkbox" name="killmail[]" value="<?php echo $e['id_newslettermail'] ?>" class="cbk" /></td>
						<td><input type="text" id="<?php echo $e['mail'] ?>" value="<?php echo $e['mail'] ?>" style="width:90%; margin:0px; padding:3px; border:0; background:none;" onBlur="saveEmail(this,'<?php echo $e['id_newslettermail'] ?>');" /></td>
						<td id="flag-<?php echo $e['id_newslettermail'] ?>"><?php echo $e['flag'] ?></td>
					</tr>
				<?php }} ?>
				</tbody>
				<tfoot>
					<?php if(sizeof($users) == 0){ ?>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
					<?php }else{ ?>
					<tr>
						<td width="30" height="25"><input type="checkbox" onchange="$('.cbk').set('checked', this.checked);" /></td>
						<td><a href="javascript:kill()" class="btn btn-mini">Supprimer les mails selectionnés</a></td>
						<td style="text-align:right">
							<span class="pagination"><?php $app->pagination($total, $max, $_GET['p'], 'list?id_newsletterlist='.$_GET['id_newsletterlist'].'&user&flag='.$_GET['flag'].'&p=%s'); ?></span>
						</td>
					</tr>
					<?php } ?>
				</tfoot>
			</table>
			</form>

			<script>
				function saveEmail(field, idmail){
					
					var json = $.ajax({
						url : 'helper/mail'
					});
					
					json.done(function(r){
						
						if(r.flag == 'doublon-cleaned'){
							field.parent().parent().remove();
						}else
						if(r.flag == 'doublon'){
							if(confirm("Le mail que vous modifié à la main est déja présent dans la base de données :\n\n\n Souhaitez vous associer ce mail à liste courante ?")){
								
								var newget = $.ajax({
																				
									'data' : {	'id_newsletterlist' : '<?php echo $_REQUEST['id_newsletterlist'] ?>',
												'id_newslettermail' : idmail,
												'error'				: field.id,
												'value'				: field.value,
												'force_link'		: true }
								}).done(function(d) {
								
								});
								json.get({
								
								});
							}else{
								field.value = field.id;
							}
						}else{
							field.id = field.value;
							if(!r.success) alert("Erreur lors de la sauvegarde");

							$('#flag-' + r.id).html(r.flag);
						}
					});
					
					if(field.id != field.value){
						if(confirm("Voulez vous enregistrer les changements dans l'intégratlité des listes ou ce mail est présent ?")){

							json.get({
								'id_newslettermail' : idmail,
								'value'				: field.value
							});

						}else{
							field.value = field.id;
						}
					}
				}

				function kill(){
					var chbx = $('.cbk');
					var count = 0;
					chbx.each(function(){
						if($(this).is(':checked')) count++;
					});

					if(count == 0) return false;
					if(confirm("Voulez vous supprimer tous les mails selectionnés ?")){
						$('#mails').submit();
					}
				}

				function empty(){
					if(confirm("Voulez vous supprimer tous les mails de cette liste ?")){
						document.location = 'list?id_newsletterlist=<?php echo $data['id_newsletterlist'] ?>&user&empty';
					}
				}
			</script>

			<?php }else{ ?>
			<p style="text-align:center; font-style:italic;">La liste est vide</p>
			<?php } ?>
			
		<?php }else{ ?>
	
	<form action="list" method="post" id="data" enctype="multipart/form-data">
	<input type="hidden" name="action" value="1" />
	<input type="hidden" name="id_newsletterlist" value="<?php echo $data['id_newsletterlist'] ?>" />
	<table cellpadding="3" border="0" width="80%;">
		<tr>
			<td width="150">Nom</td>
			<td><input type="text" name="listName" value="<?php echo $app->formValue($data['listName'], $_POST['listName']); ?>" style="width:100%;" /></td>
		</tr>
		<?php if($data['id_newsletterlist'] != NULL){ ?>
		<tr>
			<td>Importer un fichier</td>
			<td><input type="file" name="upFile" style="width:100%;" /></td>
		</tr>
		<tr>
			<td>Importer distant</td>
			<td><input type="text" name="listExternalUrl" value="<?php echo $app->formValue($data['listExternalUrl'], $_POST['listExternalUrl']); ?>" style="width:100%;" /></td>
		</tr>
		<tr valign="top">
			<td>Import rapide</td>
			<td><textarea name="raw" rows="10" style="width:100%; height:90px;"></textarea></td>
		</tr>
		<?php } ?>
		<tr>
			<td height="30"></td>
			<td>
				<a href="javascript:$('#data').submit();" class="btn btn-mini">Enregistrer</a>
				<?php if($data['id_newsletterlist'] != NULL){ ?>
				<a href="javascript:$('#data').submit();" class="btn btn-mini">Enregistrer et importer les données distantes</a>
				<?php } ?>
				<a href="list" class="btn btn-mini">Nouveau</a>
			</td>
		</tr>
		<?php if($_REQUEST['id_newsletterlist'] != NULL){
				$g = $app->dbMulti("
					SELECT COUNT(flag) as H, flag FROM k_newslettermail
					INNER JOIN k_newsletterlistmail ON k_newslettermail.id_newslettermail = k_newsletterlistmail.id_newslettermail
					WHERE id_newsletterlist=".$_REQUEST['id_newsletterlist']." GROUP BY flag
				");

				if(sizeof($g) > 0){ ?>
		<tr>
			<td colspan="2" height="50">&nbsp;</td>
		</tr>
		<tr valign="top">
			<td></td>
			<td>
				<table border="0" cellpadding="0" cellspacing="0" class="listing" width="100%">
					<thead>
						<tr>
							<th width="50%">Drapeau</th>
							<th>Nombre de mails</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach($g as $g_){ ?>
					<tr>
						<td><?php echo $g_['flag'] ?></td>
						<td><?php echo $g_['H'] ?></td>
					</tr>
					<?php } ?>
					</tbody>
				</table><?php
				}
			?></td>
		</tr>
		<?php } ?>
	</table>
	<?php } ?>

</div>


<?php include(COREINC.'/end.php'); ?>
<script>

	function purge(){
		if(confirm("Une purge supprime tous les mails au mauvais format dans toutes les listes, confirmez-vous ce nettoyage ?")){
			document.location = 'list?purge';
		}
	}

	function applyRemove(){
		if(confirm("SUPPRIMER ?")){
			$('#listing').submit();
		}
	}
	
	function desabo(){
		if(confirm("Voulez-vous ?")){
			document.location = 'list?desabo=<?php echo trim($_GET['speed']) ?>';
		}
	}
	
	function allow(){
		if(confirm("Voulez-vous ?")){
			document.location = 'list?allow=<?php echo trim($_GET['speed']) ?>';
		}
	}

</script>

</div></div></body></html>