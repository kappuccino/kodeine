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
		Merci d'avoir répondu au sondage <?php

		$survey = $this->apiLoad('survey')->surveyGet(array(
			'id_survey' => $_REQUEST['id_survey']
		));
		
		echo "<b>".$survey['surveyName']."</b>";

	?></div>

</div>

<?php $this->themeInclude('ui/html-end.php'); ?>

</body></html>