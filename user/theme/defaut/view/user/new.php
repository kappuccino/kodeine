<?php

	if(intval($this->user['id_user']) > 0) header("Location: new");

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
				if($BOOK_VALIDATION_FAILED)		$m = 'BOOK_VALIDATION_FAILED';
				if($FIELD_VALIDATION_FAILED) 	$m = 'FIELD_VALIDATION_FAILED';
				if($FORM_VALIDATION_FAILED) 	$m = 'FORM_VALIDATION_FAILED';
				if($ERROR_INSERTED)				$m = 'ERROR_INSERTED';
				if($USER_ALREADY_EXIST)			$m = 'USER_ALREADY_EXIST';
				if($USER_INSERTED)				$m = 'USER_INSERTED';
				
				if($m != NULL) $this->pre($m);
			?>
		
			<h1>Nouveau compte</h1>
			
			<form action"new.html" method="post">
				<input type="hidden" name="insert" 			value="1" />
				<input type="hidden" name="id_group" 		value="1" />
				<input type="hidden" name="autologin" 		value="1" />
				<input type="hidden" name="useraddressbook" value="1" />
		
				<input type="hidden" name="id_community[1]"	value="1" />
		
		
				<table width="100%" class="debug">
					<tr>
						<td width="150">Identifiant</td>
						<td><input type="text" name="userMail" value="<?= $this->formValue('', $_POST['userMail']) ?>" /></td>
					</tr>
					<tr>
						<td>Mot de passe</td>
						<td><input type="text" name="userPasswd" value="<?= $this->formValue('', $_POST['userPasswd']) ?>" /> Que des lettres et des chiffres de 4 à 16 caractères</td>
					</tr>
				</table>
		
				<input type="submit" value="Valider" />

			</form>
				

		</div>

	</div>

</div>

</body></html>