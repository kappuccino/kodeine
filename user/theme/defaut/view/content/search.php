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

		<form method="get" accept="search.html">
			<input type="text" name="q" value="<?= urldecode($_GET['q']) ?>" />
			<input type="submit" />
		</form>

		<? 
			$mySearch = $this->apiLoad('content')->contentGet(array(
				'debug' 		=> true,
				'useChapter'	=> false,
				'useGroup'		=> false,
		
				'id_type'		=> 49,
			#	'typeKey'		=> 'voiture',
		
			#	'category' 		=> 'categorie',
			#	'id_category' 	=> 2,
			#	'catthrough'	=> true,
		
				'offset'		=> 0,
				'limit'			=> 50,
			#	'order'			=> 'galleryDate',
			#	'direction'		=> 'DESC',
		
				'search'		=> $_GET['q'],
		#		'id_search'		=> 3,
		/*
				'search'		=> array(
					array('searchField' => 'url',			'searchValue' => 'folio',	'searchMode' => 'EW'),
					array('searchField' => 'galleryDate',	'searchValue' => 'main',	'searchMode' => 'CT')
				),
		*/
				'debug'			=> true
			));
			
			$this->pre($mySearch);
			
		?>
		
	</div></div>

</div>

<script type="text/javascript" src="/media/ui/js/script.php"></script> 
<?php $this->themeInclude('ui/html-end.php'); ?>

</body></html>
