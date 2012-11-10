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

			<h1>Address book</h1>
			<p><a href="addressbook">Revenir au carnet d'adresses</a></p>
			
			<form action="addressbookedit" method="post">
			<input type="hidden" name="action" value="1" />
			
			<? if($myAddressBook['id_addressbook']){ ?>
			<input type="hidden" name="id_addressbook" value="<?= $myAddressBook['id_addressbook'] ?>" />
			<? } ?>
			
			<? 
				if($ADDRESSBOOK_ERROR)		echo 'ADDRESSBOOK_ERROR';
				if($ADDRESSBOOK_FILLED)		echo 'ADDRESSBOOK_FILLED';
				if($ADDRESSBOOK_UPDATED)	echo 'ADDRESSBOOK_UPDATED';
			?>
	
			<table width="100%" border="1">
				<tr>
					<td width="150">Nom de l'adresse</td>
					<td><input type="text" name="addressbookTitle" value="<?= $this->formValue($myAddressBook['addressbookTitle'], $_POST['addressbookTitle']) ?>" /></td>
				</tr>
				<tr>
					<td colspan="2" height="30">&nbsp;</td>
				</tr>
				<tr>
					<td>Nom</td>
					<td><input type="text" name="addressbookLastName" value="<?= $this->formValue($myAddressBook['addressbookLastName'], $_POST['addressbookLastName']) ?>" /></td>
				</tr>
				<tr>
					<td>Prénom</td>
					<td><input type="text" name="addressbookFirstName" value="<?= $this->formValue($myAddressBook['addressbookFirstName'], $_POST['addressbookFirstName']) ?>" /></td>
				</tr>
				<tr>
					<td>Addresse 1</td>
					<td><input type="text" name="addressbookAddresse1" value="<?= $this->formValue($myAddressBook['addressbookAddresse1'], $_POST['addressbookAddresse1']) ?>" /></td>
				</tr>
				<tr>
					<td>Addresse 2</td>
					<td><input type="text" name="addressbookAddresse2" value="<?= $this->formValue($myAddressBook['addressbookAddresse2'], $_POST['addressbookAddresse2']) ?>" /></td>
				</tr>
				<tr>
					<td>Addresse 3</td>
					<td><input type="text" name="addressbookAddresse3" value="<?= $this->formValue($myAddressBook['addressbookAddresse3'], $_POST['addressbookAddresse3']) ?>" /></td>
				</tr>
				<tr>
					<td>Code postal</td>
					<td><input type="text" name="addressbookCityCode" value="<?= $this->formValue($myAddressBook['addressbookCityCode'], $_POST['addressbookCityCode']) ?>" /></td>
				</tr>
				<tr>
					<td>Ville</td>
					<td><input type="text" name="addressbookCityName" value="<?= $this->formValue($myAddressBook['addressbookCityName'], $_POST['addressbookCityName']) ?>" /></td>
				</tr>
				<tr>
					<td>Pays</td>
					<td><select name="addressbookCountryCode"><?
						foreach($this->countryGet() as $e){
							$sel = ($e['iso'] == $this->formValue($myAddressBook['addressbookCountryCode'], $_POST['addressbookCountryCode'])) ? ' selected' : NULL;
							echo "<option value=\"".$e['iso']."\"".$sel.">".$e['countryName']."</option>";
						}				
					?></select>
				</tr>
				<tr>
					<td>Etat</td>
					<td><input type="text" name="addressbookStateName" value="<?= $this->formValue($myAddressBook['addressbookStateName'], $_POST['addressbookStateName']) ?>" /></td>
				</tr>
				<tr>
					<td>Téléphone 1</td>
					<td><input type="text" name="addressbookPhone1" value="<?= $this->formValue($myAddressBook['addressbookPhone1'], $_POST['addressbookPhone1']) ?>" /></td>
				</tr>
				<tr>
					<td>Téléphone 2</td>
					<td><input type="text" name="addressbookPhone2" value="<?= $this->formValue($myAddressBook['addressbookPhone2'], $_POST['addressbookPhone2']) ?>" /></td>
				</tr>
			</table>
			
			<button type="submit" style="padding:4px;">Validate</button>
			</form>
		</div>

	</div>

</div>

</body></html>
