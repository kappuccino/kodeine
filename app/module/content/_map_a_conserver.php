<?php
	require(dirname(dirname(__FILE__)).'/api/core.admin.php');
	$app = new coreAdmin();

	if(!$app->userIsAdmin) header("Location: ./");

	include(ADMINUI.'/doctype.php');
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
	<title>Kodeine</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	
	<?php include(ADMINUI.'/head.php'); ?>
	<style>
		.map{
			padding: 20px 0px 0px 10px;
		}
		.map h1{
			font-weight: 100;
			padding-left: 0px;
		}
		.map li{
			padding-top: 2px;
			padding-bottom: 2px;
			padding-left: 0px;
			list-style-type: none;
		}
		.map li ul{
			margin-left: 25px;
		}
		.map .toggA{
			cursor: pointer;
		}
		.map .arrow{
			margin-right: 10px;
		}
		.label{
			color:#999;
			font-size: 20px;
			font-weight: 100;
		}
		.map ul.close{
			display: none;
		}
		.map ul{
			padding-left: 0px;
		}
		ul.items a{
			text-decoration: none;
			color:#000;
		}
		ul.items{
			padding-left: 10px;
		}

		ul.items li{
			list-style-type: none;
			border-left:4px solid #666;
			padding: 0px 0px 0px 10px;
			margin: 2px 0px 2px 0px;
		}
		.action a{
			color:#515151;
			text-decoration: none;
		}
			.action a:hover{
				text-decoration: underline;
			}
	</style>
</head>
<body>
<div id="pathway" class="is-edit">
	<a href="core.panel.php">Admin</a> &raquo;
	<a href="content.index.php">Contenu</a> &raquo;
	Vue d'ensemble de tout le contenu
	<?php include(ADMINUI.'/pathway.php'); ?>
</div>

<?php include('ressource/ui/menu.content.php'); ?>

<div class="app">

	<div style="width:700px; padding:10px; margin:0 auto; margin-top:20px; margin-bottom:10px; text-align:center; -moz-border-radius:2px; -webkit-border-radius:4px; background:#3f3f3f; color:#CCC;">
		<form action="content.map.php" method="get">

			Type
			<select name="id_type"><?php
				$types = $app->apiLoad('content')->contentType();
				if(!isset($_GET['id_type'])) $_GET['id_type'] = $types[0]['id_type'];

				foreach($types as $e){
					$sel = ($_GET['id_type'] == $e['id_type']) ? ' selected' : NULL;
					echo "<option value=\"".$e['id_type']."\"".$sel.">".$e['typeName']."</option>";
				}
			?></select>

			Vue par 
			<select name="mode"><?php
				foreach(array('chapter' => 'Chapitre', 'category' => 'Catégorie', 'group' => 'Groupe') as $k => $v){
					$sel = ($_GET['mode'] == $k) ? ' selected' : NULL;
					echo "<option value=\"".$k."\"".$sel.">".$v."</option>";
				}
			?></select>
			
			<input type="checkbox" name="inheritance" value="1" <?php if($_GET['inheritance']) echo ' checked' ?> /> Afficher les héritages
			
			<input type="submit" class="button rButton" style="margin:-2px 0px 0px 5px;" value="Afficher" />

		</form>
	</div>

	<div class="map"><?php

	$exp =
	"<div class=\"action\">".
		"<a href=\"javascript:allTogg(true);\">Tout afficher</a>, ".
		"<a href=\"javascript:allTogg(false);\">Tout réduire</a>".
	"</div>";

	/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

	function display($app, $id_type, $data, $name, $master, $pos){

		echo "<ul".(($master > 0) ? " id=\"ul-".$master."\" class=\"subs\"" : '').">";
		foreach($data as $e){
			
			if($name == 'chapterName'){
				$id  = 'id_chapter';
				$opt = array(
					'debug'				=> isset($_GET['debug']),
					'contentSee'		=> 'ALL',
					'id_type'			=> $id_type,
					'language'			=> 'fr',
					'noLimit'			=> true,
					'useGroup'			=> false,
					'id_chapter'		=> $e['id_chapter'],
					'chapterThrough'	=> (($_GET['inheritance']) ? true : false)
				);
			}else
			if($name == 'categoryName'){
				$id  = 'id_category';
				$opt = array(
					'debug'				=> isset($_GET['debug']),
					'contentSee'		=> 'ALL',
					'id_type'			=> $id_type,
					'language'			=> 'fr',
					'noLimit'			=> true,
					'useGroup'			=> false,
					'useChapter'		=> false,
					'id_category'		=> $e['id_category'],
					'categoryThrough'	=> (($_GET['inheritance']) ? true : false)
				);
			}else
			if($name == 'groupName'){
				$id  = 'id_group';
				$opt = array(
					'debug'				=> isset($_GET['debug']),
					'contentSee'		=> 'ALL',
					'id_type'			=> $id_type,
					'language'			=> 'fr',
					'noLimit'			=> true,
					'useChapter'		=> false,
					'id_group'			=> $e['id_group'],
					'groupThrough'		=> (($_GET['inheritance']) ? true : false)
				);
			}

			$content = $app->apiLoad('content')->contentGet($opt);

			echo "<li>";
				echo "<a onClick=\"togg('an-".$e[$id]."', 'ul-".$e[$id]."', '')\" class=\"toggA\" id=\"an-".$e[$id]."\"><img src=\"ressource/img/arrow-folder-open.png\" class=\"arrow\" /></a>";
				echo "<span class=\"label\">".$e[$name]."</span>";

				if(sizeof($content) > 0){
					echo "<ul id=\"ul-".$e[$id]."\" class=\"items subs\">";
					foreach($content as $c){
						echo "<li class=\"".(($c['is_parent']) ? 'inheritance' : '')."\">";
						echo "<a href=\"content.data.php?id_content=".$c['id_content']."\">".((!$c['contentSee']) ? '<s>'.$c['contentName'].'</s>' : $c['contentName'])."</a>";
						echo "</li>";
					}
					echo "</ul>";
				}
				
				if(sizeof($e['sub']) > 0) display($app, $id_type, $e['sub'], $name, $e[$id], ($pos+1));

			echo "</li>";
		}
		echo "</ul>";
	}

	/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

	if(in_array($_GET['mode'], array('', 'chapter'))){
		echo "<h1>Classement par chapitre (".((!$_GET['inheritance']) ? 'sans' : 'avec')." héritage)</h1>";
		echo $exp;

		$data = $app->apiLoad('chapter')->chapterGet(array(
			'language'	=> 'fr',
			'thread'	=> true,
			'debug'		=> false
		));

		display($app, $_GET['id_type'], $data, 'chapterName', 0, 0);

	}else
	if($_GET['mode'] == 'category'){
		echo "<h1>Classement par catégorie (".((!$_GET['inheritance']) ? 'sans' : 'avec')." héritage)</h1>";
		echo $exp;

		$data = $app->apiLoad('category')->categoryGet(array(
			'language'	=> 'fr',
			'thread'	=> true,
			'debug'		=> false
		));
		
		display($app, $_GET['id_type'], $data, 'categoryName', 0, 0);

	}else
	if($_GET['mode'] == 'group'){
		echo "<h1>Classement par groupe (".((!$_GET['inheritance']) ? 'sans' : 'avec')." héritage)</h1>";
		echo $exp;

		$data = $app->apiLoad('user')->userGroupGet(array(
			'thread'	=> true,
			'debug'		=> false
		));

		display($app, $_GET['id_type'], $data, 'groupName', 0, 0);
	}
	
?></div>


<script>

	function togg(an, id, open){
		
		myimg = $(an).getElement('img');
		
		if(open == ''){
			if($(id)) open = $(id).hasClass('close');
		}
		
		if(open){
			myimg.src = 'ressource/img/arrow-folder-open.png';
			if($(id)) $(id).removeClass('close');
		}else{
			myimg.src = 'ressource/img/arrow-folder-close.png';
			if($(id)) $(id).addClass('close');
		}
	}

	function allTogg(open){

		$$('ul.subs').each(function(me){
			var parent 	= me.getParent();
			var an		= parent.getElement('a');
			togg(an, me.id, open);
		});
	}


</script>

</div></body></html>