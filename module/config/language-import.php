<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	function xmlList($app){

		$exist = $app->countryGet();
		foreach($exist as $e){
			$yet[] = $e['iso'];
		}
		
		
		$doc = new DOMDocument();
		$doc->preserveWhiteSpace = false;
		$doc->load(KROOT.'/app/module/core/helper/country.xml');
		$xpath = new DOMXPath($doc);

		foreach($xpath->query('//states/area') as $area){
			foreach($area->getElementsByTagName('state') as $state){
				if(!in_array(strtolower($state->getAttributeNode('iso')->nodeValue), $yet)){
					$list[$area->getAttributeNode('name')->nodeValue][] = array(
						'iso'		=> $state->getAttributeNode('iso')->nodeValue,
						'locale'	=> $state->getAttributeNode('locale')->nodeValue,
						'name'		=> utf8_decode($state->getElementsByTagName('name')->item(0)->nodeValue),
						'language'	=> utf8_decode($state->getElementsByTagName('language')->item(0)->nodeValue)
					);
				}
			}
		}
	
		return $list;	
	}
		
	if(sizeof($_POST['import']) > 0){
		$xmlList = xmlList($app);
		foreach($_POST['import'] as $iso){
			foreach($xmlList as $zone => $es){
				foreach($es as $e){
					if($e['iso'] == $iso){
						$app->dbQuery(
							"INSERT INTO k_country (iso, iso_ref, countryLocale, countryZone, countryName, countryLanguage)".
							"VALUES ('".strtolower($iso)."', '".strtolower($iso)."', '".$e['locale']."', '".$zone."', '".addslashes($e['name'])."', '".addslashes($e['language'])."')"
						);
					}
				}
			}	
		}

		# Cache Country
		$app->configSet('boot', 'jsonCacheCountry', json_encode($app->countryGet(array('is_used' => true))));

		header("Location: config.language.php");
		exit();
	}

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div class="inject-subnav-right hide">
	<li><a onclick="$('#data').submit();" class="btn btn-small btn-success"><?php echo _('Import seleced countries') ?></a></li>
</div>

<div id="app"><div class="wrapper">

	<form method="post" action="language-import" id="data">
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing sortable__">
		<thead>
			<tr>
				<th width="30"></th>
				<th width="100"><?php echo _('ISO') ?></th>
				<th width="300"><?php echo _('Country') ?></th>
				<th width="100"><?php echo _('Locale') ?></th>
				<th><?php echo _('Langue') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach(xmlList($app) as $zone => $es){ if(count($es) > 0){ ?>
			<tr class="separator">
				<td width="30">&nbsp;</td>
				<td style="font-weight: bold;"><?php echo $zone ?></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<?php foreach($es as $e){ ?>
			<tr>
				<td><input type="checkbox" name="import[]" value="<?php echo $e['iso'] ?>" /></td>
				<td><?php echo $e['iso'] ?></td>
				<td><?php echo $e['name'] ?></td>
				<td><?php echo $e['locale'] ?></td>
				<td><?php echo $e['language'] ?></td>
			</tr>
		<?php }}} ?>
		<tbody>
	</table>
	</form>

</div></div>

<?php include(COREINC.'/end.php'); ?>

</body>
</html>