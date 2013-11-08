<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	# Quel sont les groupes à exporter
	#
	$group = array(
		'ALL'	=> 'Tous les utilisteurs'
	);

	$group_ = $app->apiLoad('user')->userGroupGet();
	foreach($group_ as $e){
		$group['G'.$e['id_group']] = 'Groupe '.$e['groupName'];
	}

	$search = $app->searchGet(array('debug' => false, 'searchType' => 'user'));
	foreach($search as $e){
		$group['S'.$e['id_search']] = 'Groupe intelligent '.$e['searchName'];
	}

	# Export
	#
	if(isset($_GET['group'])){
		if(preg_match("#S(.*)#", $_GET['group'], $e)){
			$user = $app->apiLoad('user')->userSearch(array(
				'debug' 	=> false,
				'id_search'	=> $e[1],
				'noLimit'	=> true
			));
		}else
		if(preg_match("#G(.*)#", $_GET['group'], $e)){
			$user = $app->apiLoad('user')->userGet(array(
				'debug' 	=> false,
				'useField'	=> false,
				'id_group'	=> $e[1],
				'noLimit'	=> true
			));
		}else
		if($_GET['group'] == 'ALL'){
			$user = $app->apiLoad('user')->userGet(array(
				'debug'		=> false,
				'useField'	=> false,
				'noLimit'	=> true
			));
		}

		if(sizeof($user) > 0){
			$colonnes = array_keys($user[0]);
			foreach($colonnes as $e){
				if(preg_match("#^field([0-9]{1,})#", $e, $m)){
					$field = $app->apiLoad('field')->fieldGet(array('id_field' => $m[1]));
					$tmp[] = $field['fieldKey'];
				}else{
					$tmp[] = $e;
				}
			}

			$line[] = implode("\t", $tmp);

			foreach($user as $e){
				$row = array();
				foreach($e as $e_){
					if (!is_array($e_)) $row[] = htmlentities($e_);
				}
				$line[] = implode("\t", $row);
			}

			$out = implode("\n", $line);

			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: ".gmdate("D,d M YH:i:s")." GMT");
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");
			header("Content-Length: ".strlen($out));
			header("Content-type: text/utf8");
			header("Content-Disposition: attachment; filename=\"Export-".$group[$_GET['group']].".csv\"" );
			echo $out;
			exit();
		}
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

<div id="app"><div class="wrapper">

	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing">
		<thead>
			<tr>
				<th><?php echo _('Which users would you like to export ?') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($group as $k => $e){ ?>
			<tr>
				<td><a href="export?group=<?php echo $k ?>"><?php echo $e ?></a></td>
			</tr>
			<?php } ?>
		</tbody>
		<tfoot>
			<tr>
				<td></td>
			</tr>
		</tfoot>

	</table>
</div></div>

<?php include(COREINC.'/end.php'); ?>

</body>
</html>