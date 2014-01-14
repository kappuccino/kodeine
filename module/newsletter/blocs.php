<?php

	if (is_numeric($_GET['delete'])) {
		$bloc = $app->dbQuery('DELETE FROM `@nlblocs` WHERE id_bloc='.intval($_GET['delete']));
	}
	// Va chercher les blocs dans une table à part en attendant une modif de api.newsletter
	$blocs = $app->dbMulti('SELECT * FROM `@nlblocs` LIMIT 0, 100');
	$layouts = $app->dbMulti('SELECT * FROM `@nlwrap`');

?><!DOCTYPE html>
<head>
	<?php include(COREINC.'/head.php'); ?>
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/bootstrap3/css/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/flatui/css/flat-ui.css" />
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/dsnr-ui.css" />
</head>

<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php');
	include(__DIR__.'/ui/steps.php');
	?></header>

<div class="inject-subnav-right hide"></div>

<div id="app">

	<div class="col-lg-8" style="margin: 0 auto;display:block;float:none;">

		<div class="alert alert-info">
			<strong>Protip - </strong> L'éditeur de votre bloc utilisera les propriétés du gabarit sélectionné
			afin d'avoir un rendu au plus proche de votre future newsletter.
		</div>

		<h4>Gabarits disponibles</h4>
		<ul class="list-group blocselect">
			<?php
			foreach($layouts as $k=>$l) {

				$attr = '';
				if ($k == 0) $attr = 'checked="checked"';

				echo '<li class="list-group-item">
						<input type="radio" name="layoutradio" value="'. $l['id_wrap'] .'" '. $attr .' />&nbsp;
						'. $l['name'] .'
					</li>';
			}
			?>
		</ul>

		<h4>Blocs disponibles</h4>
		<ul class="list-group blocselect">
			<?php
				foreach($blocs as $b) {
					echo '<li class="list-group-item">
							<a href="dsnr?bloc='.$b['id_bloc'].'&layout='.$layouts[0]['id_wrap'].'">'.$b['blocName'].'</a>
							<a class="delete" href="?delete='.$b['id_bloc'].'">Supprimer</a>
						</li>';
				}
			?>
		</ul>
	</div>

</div>


<?php include(COREINC.'/end.php'); ?>
<script>

	$(function() {

	});

</script>
</body></html>