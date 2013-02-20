<!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>
	
	<div class="pbg">
		
		<!-- BANDEAU TOP - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - --> 
		<div class="top">
			<div class="logo"><a href="../">Logo</a></div>
			<div class="pathway clearfix">
				<h1>Contenu &raquo; <a href="type">Type</a> &raquo; 
					<a href="search">Recherche</a>
				</h1>
			</div>
		</div>

	</div>

<div class="bocontainer">
	<div class="row-fluid">

	<div class="app">
	
		<div id="safe">
			<div class="section" id="section-internalLink">
				<div class="head clearfix">
					<div class="title">Vérifier les liens internes</div>
					<div class="action" role="link"><a onclick="lancer('internalLink')">Lancer</a></div>
				</div>
				<div class="progress"><div class="bar"></div></div>
				<div class="verbose"></div>
				<div class="listing"></div>
	
				<div class="description">
					Cette routine permet de vérifier si les liens présent dans les pages sont encore valides.
				</div>
			</div>
	
	
	
			<div class="section"  id="section-contentCache">
				<div class="head clearfix">
					<div class="title">Consolider la cache du contenu</div>
					<div class="action"><a onclick="lancer('contentCache')">Lancer</a></div>
				</div>
	
				<div class="progress"><div class="bar"></div></div>
				<div class="verbose"></div>
	
				<div class="description">
					La cache du contenu permet d'accélerer la récupération d'inforamtion annexes, comme
					la liste des catégories associé à un article. 
				</div>
			</div>
	
	
	
			<div class="section"  id="section-mediaCache">
				<div class="head clearfix">
					<div class="title">Vérifier la cache des media</div>
					<div class="action"><a onclick="lancer('mediaCache')">Lancer</a></div>
				</div>
	
				<div class="progress"><div class="bar"></div></div>
				<div class="verbose"></div>
	
				<div class="description">
					Cette routine permet de faire le ménage parmi les images de cache déjà créées qui ne sont
					plus utilisées si les originaux ont disparu.
				</div>
			</div>
	
		</div>
		
	<?php include(COREINC.'/end.php'); ?>
	<script type="text/javascript" src="ressource/js/safe.js"></script> 
	
	</div>
	
</div>
</div>
	
</body>
</html>