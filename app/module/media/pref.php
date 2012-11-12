<?php

	if($_POST['action']){
		foreach($_POST['pref'] as $k => $p){
			$app->configSet('media', $k, $p);
		}
		$app->go('pref');
	}

	$pref = $app->configGet('media');

?><!DOCTYPE html>
<html lang="fr">
<head>
	<title>Kodeine</title>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>


<div id="app"><div class="wrapper">
		
	<form action="pref" method="post">
		<input type="hidden" name="action" value="1" />

		<table border="0" cellpadding="0" cellspacing="0" class="listing">
			<thead>
				<tr>
					<th width="20%">Paramètre</th>
					<th width="30%">Valeur</th>
					<th width="50%">&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Utiliser la cache</td>
					<td class="check-green">
						<input type="hidden" 	name="pref[useCache]" value="0" />
						<input type="checkbox" name="pref[useCache]" value="1" <?php if($pref['useCache']) echo 'checked' ?> id="usecache"></input>
					</td>
					<td>Ne pas utiliser la cache peut ralentir l'affiche des grosses images.</td>
				</tr>
				<tr>
					<td>R&eacute;&eacute;criture automatique</td>
					<td>
						<input type="hidden" 	name="pref[urlEncode]" value="0" />
						<input type="checkbox" 	name="pref[urlEncode]" value="1" <?php if($pref['urlEncode']) echo 'checked' ?> />
					</td>
					<td>Formatage des noms de fichiers et de dossiers (suppression des espaces et caractères sp&eacute;ciaux)</td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="3">
						<button type="submit" class="btn btn-mini">Enregistrer</button>
					</td>
				</tr>
			</tfoot>
		</table>

	</form>
		
</div></div>

<?php include(COREINC.'/end.php'); ?>

</body></html>