<?php

					die('-- class non testee --');

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Last release		2010.11.02
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

class pdf extends coreApp {

function __clone(){}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function pdf(){
//	$this->coreApp();
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function pdfRender($opt=array()){

	if($opt['url'] == NULL) return false;

	$render  = file_get_contents($opt['url']);
	$render  = gzcompress($render, 9);

	$context = stream_context_create(array( 
		'http' => array( 
			'method'  => 'POST',
			'content' => http_build_query(array(
				'render'	=> $render,
				'zlib'		=> true,
				'option'	=> $opt['args']
			))
		)
	));

	$pdf = file_get_contents('http://pdf.kappuccino.org/pdf.php', false, $context); 

	header('Content-type: application/pdf');

	if($opt['view']){
		echo gzuncompress($pdf);
	}else{
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: ".gmdate("D,d M YH:i:s")." GMT");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Content-Disposition: attachment; filename=\"".time().".pdf\"" );
		echo gzuncompress($pdf);
	}
}

} ?>