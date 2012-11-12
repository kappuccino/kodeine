<?php
/*
UploadiFive
Copyright (c) 2012 Reactive Apps, Ronnie Garcia
*/

// Set the uplaod directory
$uploadDir = '/uploads/';

if (!empty($_FILES)) {
	$tempFile   = $_FILES['Filedata']['tmp_name'][0];
	$uploadDir  = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;
	$targetFile = $uploadDir . $_FILES['Filedata']['name'][0];

	// Validate the file type
	$fileTypes = array('jpg', 'jpeg', 'gif', 'png'); // Allowed file extensions
	$fileParts = pathinfo($_FILES['Filedata']['name'][0]);

	// Validate the filetype
	if (in_array($fileParts['extension'], $fileTypes)) {

		// Save the file
		move_uploaded_file($tempFile,$targetFile);
		echo 1;

	} else {

		// The file type wasn't allowed
		echo 'Invalid file type.';

	}
}
?>