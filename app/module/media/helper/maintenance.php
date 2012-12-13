<?php
	require(dirname(dirname(dirname(dirname(__FILE__)))).'/api/core.admin.php');
	$app = new coreAdmin();

?><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
	<title></title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<style>
		body{
			font-family: Arial;
			font-size: 12px;
		}
	</style>
</head>
<body><?php

	echo "Maintenance  NE PAS FERMER CE PANEL ...";

	$files = $app->dbMulti("SELECT * FROM k_media");
	if(sizeof($files) > 0){
		foreach($files as $file){
			if(!file_exists(KROOT.$file['mediaUrl'])){
				$app->dbQuery("DELETE FROM k_media 		WHERE mediaUrl='".$file['mediaUrl']."'");
				$app->dbQuery("DELETE FROM k_cachemedia WHERE cacheSource='".$file['mediaUrl']."'");
			}
		}
	}


	
	# Enlever les lignes de la BDD qui n'ont plus de fichier associe
	#
	$cache = $app->dbMulti("SELECT * FROM k_cachemedia");
	if(sizeof($cache) > 0){
		foreach($cache as $c){
			if(!file_exists(KROOT . $c['cacheUrl'])){
				$app->dbQuery("DELETE FROM k_cachemedia WHERE id_cache='".$c['id_cache']."'");
			}else
			if(!file_exists(KROOT . $c['cacheSource'])){
				$app->dbQuery("DELETE FROM k_cachemedia WHERE id_cache='".$c['id_cache']."'");
			}

		}
	}



	# Supprimer les lignes de la BDD expire + supprimer le fichier
	#
	$cache = $app->dbMulti("SELECT * FROM k_cachemedia WHERE cacheTTL < ".time());
	if(sizeof($cache) > 0){
		foreach($cache as $e){
			$app->dbQuery("DELETE FROM k_cachemedia WHERE id_cache='".$c['id_cache']."'");
	
			if(file_exists(KROOT.$c['cacheUrl'])) unlink(KROOT . $c['cacheUrl']);
		}
	}

	
	# Supprimer tous les fichiers de la cache non present en BDD
	#
	$cache = KROOT.'/media/.cache';
	if(file_exists($cache)){
		$files = $app->fsFile($cache, NULL, NOROOT);
		if(sizeof($files) > 0){
			foreach($files as $e){
				$tmp = $app->dbOne("SELECT 1 FROM k_cachemedia WHERE cacheUrl='".$e."'");
				if(!$tmp[1]) unlink(KROOT.$e);
			}
		}
	}
	
	
	# Supprimer les dossier vides
	#
	$cache = KROOT.'/media/.cache';
	if(file_exists($cache)){
	
		$folders = $app->fsFolder($cache);
		if(is_array($folders)){
			sort($folders);
			$folders = array_reverse($folders);
			foreach($folders as $e){
				$tmp = $app->fsFile($e, NULL, FLAT);
				if(sizeof($tmp) == 0){
					rmdir($e);
				}else{
					foreach($tmp as $f){
						unlink($f);
					}
					rmdir($e);
				}
			}
			
		}
		
		
	}
	
	
	echo " terminée - vous pouvez fermer ce panel.";


?></body></html>