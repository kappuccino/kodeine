<?php

	if(intval($this->user['id_user']) > 0) header("Location: my");

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

			<h1>Login</h1>

			<? if($this->user['id_user'] != NULL){ ?>
				<p>Bonjour <?= $this->user['userMail'] ?>, vous pouvez 
					<a href="login?logout=1">fermer la session</a>, ou bien 
					<a href="my">editer votre compte</a>
				</p>
			<? }else{ ?>
				<form action="login" method="post">
					
					<input type="hidden" name="log" value="login"  />
					
					email 			<input type="text" 		name="login" />
					mot de passe 	<input type="password" 	name="password" />
									<input type="submit" />
			
					<p><a href="lost">Oublis du mot de passe</a></p>
				</form>

				<p><a href="new">Creation d'un nouveau compte</a></p>
			<? } ?>

		</div>

	</div>

</div>

</body></html>