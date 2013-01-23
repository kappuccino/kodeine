<?php

	$i18n = $app->apiLoad('coreI18n')->languageSet('fr')->load('content');

	if($_REQUEST['id_type'] == NULL){
		$type = $app->apiLoad('content')->contentType(array('profile' => true));
		$goto = ($type[0]['is_gallery']) ? 'gallery.index' : 'index';
		header("Location: content.".$goto.".php?id_type=".$type[0]['id_type']);
	}

	// Type
	$id_type	= $_REQUEST['id_type'];
	$cType		= $app->apiLoad('content')->contentType(array('id_type' => $id_type));
	if($id_type == NULL) die("APP : id_type IS NULL");

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
    <link rel="stylesheet" type="text/css" href="ui/css/gallery.css" />
</head>
<body>
	
<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php');
?></header>

<div id="app">
	<div class="row-fluid">
	
	<div class="span7">	
		<div id="galleyPathAction" class="clearfix">
			<a id="buttonAddAlbum" class="btn btn-mini">Nouvel album</a>
			<a id="buttonEdit" class="btn btn-mini">Editer l'album</a>
			<a id="buttonImport" class="btn btn-mini">Ajouter des elements</a>
		
			<input type="radio" name="mode" onclick="makeSort()" checked="checked" /> Classement &nbsp; &nbsp;
			<input type="radio" name="mode" onclick="makeMove()" /> Déplacement
		</div>
	</div>
	
	<div class="span5">
		<div class="menu-inline clearfix"><?php
			foreach($app->apiLoad('content')->contentType(array('profile' => true)) as $e){
				echo "<div class=\"button item ".($e['id_type'] == $_REQUEST['id_type'] ? 'btn btn-mini' : 'btn btn-mini')."\">";
				echo "<a href=\"content.".(($e['is_gallery']) ? 'gallery.index' : 'index').".php?id_type=".$e['id_type']."\" class=\"text\">".$e['typeName']."</a>";
				echo "<a href=\"content.".(($e['is_gallery']) ? 'gallery.album' : 'data' ).".php?id_type=".$e['id_type']."\" class=\"\">&nbsp;<i class=\"icon-plus\"></i></a>";
				echo "</div>";
			}
		?></div>
	</div>
	

<div id="galleyPath" class="clearfix">
	<div id="galleyPathAlbum"></div>
</div>

<ul id="galleryTree"></ul>

<ul id="galleryView" class="clearfix"></ul>

<div id="galleryInfo"></div>

</div></div>

</body>

<?php include(COREINC.'/end.php'); ?>
<script type="text/javascript" src="ui/js/gallery.js"></script>

<script>
	id_type 	= <?php echo $id_type ?>;
	id_album	= (getHash() == '') ? 0 : getHash();

	$(function(){
		galleryAlbum(id_album, true);
	//	galleryTree(0, 0, $('galleryTree'));
	});
</script>
</html>