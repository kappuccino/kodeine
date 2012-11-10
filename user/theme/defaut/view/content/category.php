<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
	<title></title>
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<link rel="stylesheet" type="text/css" media="all" href="/media/ui/css/style.php" />
	<? $this->themeInclude('ui/html-head.php') ?>
</head>
<body>

<div class="container_12 container clearfix">

	<? $this->themeInclude('ui/nav.php'); ?>

	<div class="grid_12">
		<h1>Cat&eacute;gorie : <?= $this->kodeine['categoryUrl'] ?></h1>
		
		<?php
			if($this->kodeine['categoryUrl']){

				$content = $this->apiLoad('content')->contentGet(array(
					'categoryUrl'		=> $this->kodeine['categoryUrl'],
					'id_type'		=> 4,
					'debug'			=> false
				));

				echo "<ul>";
				foreach($content as $e){
					echo "<li><a href=\"".$e['contentUrl'].".html\">".$e['contentName']."</a></li>";
				}
				echo "</ul>";
			}
		?>
	
		<br /><br /><br /><h1>Liste</h1>
		<?
			$cat = $this->apiLoad('category')->categoryGet(array(
				'order'		=> 'categoryName',
				'direction'	=> 'ASC',
				'limit'		=> 20,
				'offset'	=> 0
			));

			echo "<ul>";
			foreach($cat as $e){
				echo "<li><a href=\"cat-".$e['categoryUrl'].".html\">".$e['categoryName']."</a></li>";
			}
			echo "</ul>";				
		?>

		<p>&nbsp;</p>
	</div>

</div>

<script type="text/javascript" src="/media/ui/js/script.php"></script> 
<? $this->themeInclude('ui/html-end.php') ?>

</body>
</html>
