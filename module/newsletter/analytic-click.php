<?php
	require(dirname(dirname(__FILE__)).'/api/core.admin.php');
	$app = new coreAdmin();

	if(!$app->userIsAdmin) header("Location: ./");
	
	$api  = $app->apiLoad('newsletter');
	$pref = $app->configGet('newsletter');

	$data = $api->newsletterGet(array(
		'id_newsletter' => $_REQUEST['id_newsletter']
	));

	$rest = new newsletterREST($pref['auth'], $pref['passw']);
	$stat = $rest->request('/controller.php', 'POST', array(
		'analytic'		=> true,
		'id_newsletter' => $data['id_newsletter']
	));
	$stat = json_decode($stat, true);


	# Simple stat
	#
	$total = $stat['campaign']['campaingSent'];


	include(ADMINUI.'/doctype.php');
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
	<?php include(ADMINUI.'/head.php'); ?>
	<link rel="stylesheet" type="text/css" media="all" href="ressource/css/newsletter.analytic.css" />

	<script language="javascript" type="text/javascript" src="ressource/plugin/flot/jquery.js"></script>
	<script language="javascript" type="text/javascript" src="ressource/plugin/flot/jquery.flot.js"></script>
	<script language="javascript" type="text/javascript" src="ressource/plugin/flot/jquery.flot.pie.min.js"></script> 
</head>
<body>
<div id="pathway">
	<a href="core.panel.php">Admin</a> &raquo;
	<a href="newsletter.index.php">Liste des newsletter</a> &raquo;
	<a href="newsletter.data.php?id_newsletter=<?php echo $data['id_newsletter'] ?>"><?php echo $data['newsletterName'] ?></a> &raquo;
	<a href="newsletter.analytic.php?id_newsletter=<?php echo $data['id_newsletter'] ?>">Statistiques</a> &raquo;
	Activité des clicks
</div>

<?php include('ressource/ui/menu.newsletter.php'); ?>

<div class="app">

<div style="width:900px; margin:0 auto; padding-top:20px;">

	<?php if(is_array($stat['campaign']) && $data['newsletterSendDate'] != NULL){ ?>
		
		<a href="newsletter.analytic.php?id_newsletter=<?php echo $data['id_newsletter'] ?>">Revenir a la vue d'ensemble</a>
		<h3 class="campaignName"><?php echo $stat['campaign']['campaignName'] ?></h3>
	
		<p class="campaignNameCaption">
			Envoy&eacute; a <?php echo $total ?> destinataires le <?php echo $app->helperDate($data['newsletterSendDate'], '%e %B %Y &agrave; %Hh%M') ?>
		</p>
			
	<?php }else{ ?>
	
		<div style="font-weight:bold; font-size:14px; text-align:center; padding-top:50px; color:#808080;">
			Cette newsletter n'a pas &eacute;t&eacute; encore envoy&eacute;, ou bien aucune statistiques ne sont encore disponibles
		</div>
	
	<?php } ?>

</div>


</body></html>