<?php
	if(isset($_GET['apply'])){
		foreach(explode(',', $_GET['apply']) as $idx => $e){
			if($e != NULL){
				$exists = $app->dbOne("SELECT 1 FROM k_config WHERE configModule='bootExt' AND configName LIKE '%:id_field:".$e."'");
				if(!$exists[1]){
					$app->dbQuery("INSERT INTO k_config (configModule, configName) VALUES ('bootExt', '".$idx.":id_field:".$e."')");
				}
			}
		}
		
		foreach(explode(',', $_GET['move']) as $idx => $e){
			if($e != NULL){
				$exists = $app->dbOne("SELECT configName FROM k_config WHERE configModule='bootExt' AND configName LIKE '%:id_field:".$e."'");
				if($exists['configName'] != NULL){
					$app->dbQuery("DELETE FROM k_config WHERE configModule='bootExt' AND configName='".$exists['configName']."'");
				}
			}
		}

		header("Location: field");
	}

	$field_ = $app->dbMulti("SELECT * FROM k_config WHERE configModule = 'bootExt'");
	foreach($field_ as $e){
		list($pos,$n,$id) = explode(':', $e['configName']);
		$tmp = $app->apiLoad('field')->fieldGet(array('id_field' => $id));
		$field[$tmp['id_field']] = $tmp;
	}
	if(!is_array($field)) $field = array();
	
	$rest = $app->apiLoad('field')->fieldGet();
	foreach($rest as $idx => $e){
		if(array_key_exists($e['id_field'], $field)) unset($rest[$idx]);
	}	
?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php
		echo $app->less('/admin/config/ui/css/field.less');
		include(COREINC.'/head.php');
	?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div class="inject-subnav-right hide">
	<li>
		<a href="./" class="btn btn-small">Annuler</a>
	</li>
	<li>
		<a onclick="sauver()" class="btn btn-small btn-success">Enregistrer</a>
	</li>
</div>

<div id="app"><div class="wrapper"><div class="row-fluid">

	<div class="span6">
		<p><b>Champs utilisés</b></p>
		<ul id="la" class="myList clearfix">
			<?php foreach($field as $e){ ?>
			<li id="<?php echo $e['id_field'] ?>"><?php echo $e['fieldName'].' ('.$e['fieldKey'].')' ?></li>
			<?php } ?>
		</ul>
		<input type="hidden" id="move" size="80" value="" />
	</div>

	<div class="span6">
		<p class="t"><b>Autres champs utilisables</b></p>
		<ul id="lb" class="myList clearfix">
			<?php foreach($rest as $e){ ?>
			<li id="<?php echo $e['id_field'] ?>"><?php echo $e['fieldName'].' ('.$e['fieldKey'].')' ?></li>
			<?php } ?>
		</ul>
	</div>	

</div></div></div>

<?php include(COREINC.'/end.php'); ?>
<script src="ui/js/field.js" type="text/javascript"></script>

</body></html>
	
	
