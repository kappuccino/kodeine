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

	
			<h1>Mon carnet d'adresse</h1>
			
			<p><a href="my">Revenir a mon compte</a></p>
			
			<? if(sizeof($myAddressBook) > 0){ ?>
			
			<form action="addressbook" method="post">
			<input type="hidden" name="todo" value="updateBillingAndDelivery" />
			
			<table width="100%" border="1">
				<tr align="center">
					<td ><b>&nbsp;Address</b></td>
					<td width="100"><b>Livraison</b></td>
					<td width="100"><b>Facturation</b></td>
					<td width="100">Action</td>
				</tr>
				<? foreach($myAddressBook as $idx => $e){ ?>
				<tr>
					<td><?
						echo "<b>".$e['addressbookTitle']."</b><br />";
						echo $this->apiLoad('user')->userAddressBookFormat($e, array('name' => true, 'html' => true));
					?></td>
					<td><input type="radio" name="addressbookIsDelivery" value="<?= $e['id_addressbook'] ?>" <? if($e['addressbookIsDelivery']) echo "checked" ?> /></td>
					<td><input type="radio" name="addressbookIsBilling"  value="<?= $e['id_addressbook'] ?>" <? if($e['addressbookIsBilling'])  echo "checked" ?> /></td>
					<td>
						<a href="addressbookedit?id_addressbook=<?= $e['id_addressbook'] ?>">Edit</a>
						<? if($e['addressbookIsProtected'] == 0){ ?>
							<a href="#" onclick="s(<?= $e['id_addressbook'] ?>)"> - Remove</a>
						<? } ?>
					</td>
				</tr>
				<? } ?>
				<tr>
					<td colspan="5">
						<button type="submit" style="padding:4px;">Mettre a jour</button>
						&nbsp;
						<a href="addressbookedit">Ajouter une adresse</a>
						&nbsp;
						<a href="../content/overview">Revenir a la commande en cours</a>
					</td>
				</tr>
			</table>
			</form>
			
			<script>
				function s(id){
					if(confirm("Would you really want to remove this address ?")){
						document.location = 'addressbook?remove='+id;
					}
				}
			</script>
			
			<? }else{ ?>
				<p>Aucun carnet d'adresse de definit</p>
				<p><a href="addressbookedit">Ajouter</a></p>
			<? } ?>
		
		
		</div>

	</div>

</div>

</body></html>