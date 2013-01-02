<?php

	$i18n = $app->apiLoad('coreI18n')->languageSet('fr')->load('media');
	$pref = $app->configGet('media');

?><!DOCTYPE html>
<html lang="fr">
<head>
	<title>Kodeine</title>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>
<div class="wrapper" style="width:600px;">
		
	<form action="/admin/media/pref" method="post">
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
			</tbody>
			<tfoot>
				<tr>
					<td colspan="3">
						<button onclick="close()" id="ok" type="submit" class="btn btn-mini">Enregistrer</button>
					</td>
				</tr>
			</tfoot>
		</table>

	</form>
		
</div>
<script>
	var btn = document.getElementById('ok');
	btn.addEventListener('click',function (e) {
		parent.modalHideUpload();
	},false);
</script>
</body>
</html>