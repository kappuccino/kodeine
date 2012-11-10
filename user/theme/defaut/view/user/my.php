<?php

	if(intval($this->user['id_user']) == 0) header("Location: login");

?><!DOCTYPE html> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
	<title></title>
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<link rel="stylesheet" type="text/css" media="all" href="/media/ui/css/style.php" />
	<?php include(MYTHEME.'/ui/html-head.php') ?>
</head>
<body class="body">

<div class="container_12 container clearfix">

	<div class="col grid_3 alpha">
		<?php include(MYTHEME.'/ui/menu.php') ?>
	</div>

	<div class="grid_9 omega center">

		<div class="center-item">
		<?
			if($FIELD_VALIDATION_FAILED) 	$m = 'FIELD_VALIDATION_FAILED';
			if($FORM_VALIDATION_FAILED) 	$m = 'FORM_VALIDATION_FAILED';
			if($ERROR_UPDATE)				$m = 'ERROR_UPDATE';
			if($USER_UPDATED)				$m = 'USER_UPDATED';
	
			if($m != NULL) $this->pre($m);
	
			$user = $this->apiLoad('user')->userGet(array(
				'id_user' => $this->user['id_user']
			));
	
			$fields = $this->apiLoad('field')->fieldGet(array(
				'id_group' => $user['id_group']
			));
	
			$userFull = $this->apiLoad('user')->userGet(array(
				'id_user' 		=> $user['id_user'],
				'community'		=> true,
				'debug'			=> false
			));
	
			$subcribed = $this->apiLoad('newsletter')->newsletterListMailSubscribed(array(
				'email' => $user['userMail'],
				'debug' => true
			));
		?>
	
		<h1>Mon compte</h1>
		
		<form action"my.html" method="post">
			<input type="hidden" name="update" value="1" />
	
			<table width="100%" class="debug">
				<tr>
					<td width="150">Identifiant</td>
					<td><input type="text" name="userMail" value="<?= $this->formValue($user['userMail'], $_POST['userMail']) ?>" /></td>
				</tr>
				<tr>
					<td>Mot de passe</td>
					<td><input type="text" name="userPasswd" /></td>
				</tr>
	
				<? foreach($fields as $e){ ?>
				<tr>
					<td><?= $e['fieldName'] ?></td>
					<td><?=
						$field = $this->apiLoad('field')->fieldForm(
							$e['fieldKey'],
							$this->formValue($user['field'][$e['fieldKey']], $_POST['field'][$e['fieldKey']])
						);			
					?></td>
				</tr>
				<? } ?>
				<tr>
					<td>Newsletter (list)</td>
					<td><?
					foreach($this->apiLoad('newsletter')->newsletterListGet() as $e){
	
						if(in_array($e['id_newsletterlist'], $subcribed)){
							$on = ' checked'; $off = ''; 
						}else{
							$off = ' checked'; $on = ''; 
						}
	
						echo "<p>";
							echo "<input type=\"checkbox\" name=\"id_newsletterlist[]\" value=\"".$e['id_newsletterlist']."\" ".$on." /> ";
							echo "<b>".$e['listName']."</b>";
						echo "</p>";
					}
					?></td>
				</tr>
			</table>
			
			<?
	
	
				#$this->pre($userFull);
			?>
	
	
			<input type="submit" value="Valider" />
	</form>
		
		</div>

	</div>

</div>

</body></html>