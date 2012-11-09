<?php
	if(!defined('COREINC')) die('@');
?><!DOCTYPE html>
<html lang="fr">
<head>
	<title>Kodeine</title>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/menu.php')
?></header>

<div id="app"><div class="wrapper"><pre><?php 


	$root	= dirname(dirname(dirname(__DIR__)));
	$mods	= $app->moduleList(array('all' => true));
	$files	= array();

	foreach($mods as $e){
		if(is_array($e['less'])){
			foreach($e['less'] as $less){
				$files[] = KROOT.$less;
			}
		}
	}
	
	

	# KILL CSS FILE
	#
	if(isset($_GET['nocss'])){
		foreach($files as $file){
			$css = substr($file, 0, -4).'css';
			if(file_exists($css)) unlink($css);

			echo $file."\n";
		}

		$app->go("/admin/core/less");
	}else

	# CREATE CSS FILE
	#
	if(isset($_GET['compile'])){

		function r($file, $less=NULL){
	
			if(file_exists($file)){
				$tmp = file_get_contents($file);
	
				if($less == NULL) $less = $tmp;
	
				foreach(explode("\n", $tmp) as $line){
					if(preg_match('#^@import "(.*)";#', $line, $m)){
						$m = array_map('trim', $m);
						$content = file_get_contents($_SERVER['DOCUMENT_ROOT'].$m[1]);
						$less = str_replace($m[0], $content, $less);
					}	
				}
	
				return $less;
	
			}
		}
	
		function s($end, $less){
	
			$css = file_get_contents('http://less.cloudapp.me/less.php', false,  stream_context_create(array(
				'http' => array(
					'method'  => 'POST',
					'content' => http_build_query(array(
						'less'			=> $less,
						'yuicompress' 	=> true
					))
				)
			)));
			
			if(preg_match("#Syntax Error#", $css)){
				echo "PAS OK\n";
				echo time()."\n";
				echo $css."\n\n\n\n\n\n";
				die();
			}
	
			// https://github.com/cloudhead/less.js/pull/800
			$css = str_replace('#NaNbbaaNaN00NaN00NaN00NaN00NaN', 'transparent', $css);
	
			umask(0);
			if(file_exists($end)) unlink($end);
			file_put_contents($end, $css);
			chmod($end, 0755);
		
			echo "OK\n";
			echo time()."\n";
			echo $css."\n\n\n\n\n\n";
		}	
	
	#	- + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + 
	
		#print_r($files);
	
		foreach($files as $file){
			$css  = substr($file, 0, -4).'css';
	
			echo $file." => ".basename($css)."\n";
	
			$less = r($file);
			s($css, $less);
		}
	}else{
		echo "Actuellement le back office utilise le ".(file_exists(__DIR__.'/ui/css/_style.css') ? 'CSS' : 'LESS');
	}





?></pre></div></div>

</body></html>
