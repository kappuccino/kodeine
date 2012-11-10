<!DOCTYPE html> 
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

	<div class="grid_9 omega center"><div class="center-item">

		<h1>Newsletter</h1>
	
		<p><?
			if($NEED_VALIDE_EMAIL) 			echo "Le mail n'est pas au bon format";
			if($NEED_TO_SELECT_LIST)		echo "Vous devez choisir au moins une liste";
			if($SUBSCRIBED)					echo "Ok, vous etes dans la base";
			if($UNSUBSCRIBED)				echo "Ok, vous n'etes plus dans la base";
			if($PLEASE_CHECK_INBOX)			echo "Votre inscription est presque terminée, consultez vos mails et suivez les instructions que nous venons de vous envoyer";
		?></p>

		<form action="./" method="post">
			<input type="hidden" name="subscribe" value="1" />
	
			Adresse email
			<input name="email" type="text" value="<?= $_POST['email'] ?>" />
	
			<div><?
	
				// Pas très secure car certaines listes ne seront peut etre pas publiques
	
				$lists = $this->apiLoad('newsletter')->newsletterListGet();
	
				foreach($lists as $e){
					echo "<input type=\"checkbox\" name=\"id_newsletterlist[]\" value=\"".$e['id_newsletterlist']."\" /> ".$e['listName']."<br />";	
				}
	
			?></div>
		
			<input type="submit" value="Valider" />
		
		</form>

	</div></div>

</div>

<script type="text/javascript" src="/media/ui/js/script.php"></script> 
<?php $this->themeInclude('ui/html-end.php'); ?>

</body></html>