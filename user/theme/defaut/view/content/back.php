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

		<h1>Retour de la banque</h1>

		<?php	
		
			# Inclure le fichier de l'API
			include(USER.'/bank/'.APIBANK.'/_back.php');


			if($apiSuccess){
				echo "<p>OK ca passe, commande #".$apiIdCart."</p>";
			}else{
				echo "Error : ".$apiOutput;	
			}

		?>

	</div></div>

</div>

<script type="text/javascript" src="/media/ui/js/script.php"></script> 
<?php $this->themeInclude('ui/html-end.php'); ?>

</body></html>
