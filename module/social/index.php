<?php
	
	$opt = array();
	
	$posts = $app->apiLoad('socialPost')->socialPostGet(array(
		
	
	));
	
	#$app->pre($posts);
	
	# Liste des forums
	/*$socialForum = $app->apiLoad('socialForum')->socialForumGet();
	
	if (isset($_GET['forum'])) {
		$socialPost = $app->apiLoad('socialPost')->socialPostGet(array(
			'id_socialforum' => $_GET['forum']
		));
	}*/	
	

?><!DOCTYPE html>
<html lang="fr">
<head>
	<title>Kodeine</title>
	<?php if(COREONAIR){ ?>
	<?php }else{ ?>
	<?php } ?>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div id="app">

</div>

</body></html>