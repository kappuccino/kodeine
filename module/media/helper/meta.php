<?php

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if($_SERVER['REQUEST_METHOD'] == 'GET'){

		$file = rawurldecode($_GET['url']);
		$meta = $app->mediaDataGet($file);

		$out = array(
			'url'       => $file,
			'title'     => $meta['mediaTitle'],
			'caption'   => $meta['mediaCaption']
		);

	}else

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	if($_SERVER['REQUEST_METHOD'] == 'POST'){

		$req = json_decode(file_get_contents('php://input'), true);
		$url = $req['url'];

		$app->mediaDataSet($url, array('k_media' => array(
			'mediaUrl'      => array('value' => $req['url']),
			'mediaTitle'    => array('value' => $req['title']),
			'mediaCaption'  => array('value' => $req['caption'])
		)));

		$out = $req;

	}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


	// Sortie
	$json = $app->helperJsonEncode($out);
	echo $app->helperJsonBeautifier($json);

