<?php

	// Upload
	if($_FILES['file']['tmp_name'] != ''){
		$dossier	= MEDIA.'/upload/user/';
		$myExt		= strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
		$fichier	= rand().'.'.$myExt;
	
		umask(0);
	#	if(!file_exists($dossier)) mkdir($dossier, 0755, true);
	#	if(file_exists($fichier)) unlink($fichier);
	
		if(move_uploaded_file($_FILES['file']['tmp_name'], $dossier.'/'.$fichier)){
			echo "OK";
		}else{
			echo "PAS OK";
		}
	}else{
		echo ":(";
	}
?>