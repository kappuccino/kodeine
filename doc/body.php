<?php
	if($_GET['d']){
		$file = dirname(__FILE__).'/kb-doc/'.$_GET['d'].'.php';
	}else
	if($_GET['f']){
		$file = dirname(__FILE__).'/kb-class/'.$_GET['f'].'.php';
	}else{
		$file = dirname(__FILE__).'/home.php';
	}
 
?>

<!--<p class="lastMod">Dernières mise à jour de ce document <?php $s = stat($file); echo date("Y/m/d", $s['ctime']); ?></p>-->

<?php
	include($file);
?>