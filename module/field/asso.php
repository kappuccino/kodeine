<?php

	$i18n = $app->apiLoad('coreI18n')->languageSet('fr')->load('field');

	# Les TYPE numeric (content + album)
	#
	if(empty($_REQUEST['id_type'])) {
		header("Location: asso?id_type=category");
		exit(0);
	}
	
	if(intval($_REQUEST['id_type']) > 0){
		$type	= $app->apiLoad('content')->contentType(array('id_type' => $_REQUEST['id_type']));
		if($_GET['map'] == '' && $type['is_gallery']) $_GET['map'] = 'album';


		if($_GET['map'] == 'typealbum'){
			$map = 'typealbum';
			$exp = "(Album) <a href=\"asso?id_type=".$type['id_type']."&map=typeitem\">Item</a>";
			$opt = array('id_type' => $_REQUEST['id_type'], 'albumField' => true);
		}else
		if($_GET['map'] == 'typeitem'){
			$map = 'typeitem';
			$exp = "(Item) <a href=\"asso?id_type=".$type['id_type']."&map=typealbum\">Album</a>";
			$opt = array('id_type' => $_REQUEST['id_type'], 'itemField' => true);
		}else{
			$map = 'type';
			$opt = array('id_type' => $_REQUEST['id_type']);
		}
	}else
	

	# Les Elements systeme (CATEGORY + CHAPTER + USER + SOCIAL*)
	#
	if($_GET['id_type'] == 'category'){				$is_category			= true;	$map = 'category';				$opt = array($map => true); }else
	if($_GET['id_type'] == 'chapter'){				$is_chapter				= true; $map = 'chapter';				$opt = array($map => true); }else
	if($_GET['id_type'] == 'user'){					$is_user				= true;	$map = 'user';					$opt = array($map => true); }else
	if($_GET['id_type'] == 'businessCart'){			$is_businesscart		= true; $map = 'businessCart';			$opt = array($map => true); }else
	if($_GET['id_type'] == 'businessCartLine'){		$is_businesscartline	= true; $map = 'businessCartLine';		$opt = array($map => true); }else
	if($_GET['id_type'] == 'socialForum'){			$is_socialforum			= true; $map = 'socialForum';			$opt = array($map => true); }else
	if($_GET['id_type'] == 'socialCircle'){			$is_socialcircle		= true; $map = 'socialCircle';			$opt = array($map => true); }else
	if($_GET['id_type'] == 'socialAlert'){			$is_socialalert			= true; $map = 'socialAlert';			$opt = array($map => true); }else
	if($_GET['id_type'] == 'socialEvent'){			$is_socialevent			= true; $map = 'socialEvent';			$opt = array($map => true); }else
	if($_GET['id_type'] == 'socialEventUserData'){	$is_socialeventuser		= true; $map = 'socialEventUserData';	$opt = array($map => true); }else
	if($_GET['id_type'] == 'socialActivity'){		$is_socialactivity		= true; $map = 'socialActivity';		$opt = array($map => true); }else
	if($_GET['id_type'] == 'socialMessage'){		$is_socialmessage		= true; $map = 'socialMessage';			$opt = array($map => true); }

	if(isset($_GET['ordre'])){
		$id = intval($_REQUEST['id_type'] > 0) ? $_REQUEST['id_type'] : 0;

		# On AJOUTE
		#
		foreach(explode(',', $_GET['ordre']) as $id_field){
			if(intval($id_field) > 0){
				// Ajouter le FIELD a la base d'AFFECT
				$app->apiLoad('field')->fieldAffectPush($_GET['map'], $id_field, $id);

				// Ajouter le FIELD TEMPORAIRE a la TABLE correponsante
				$app->apiLoad('field')->fieldAffectNew($_GET['map'], $id_field, $id);

				// On affecte le bon TYPE pour ce champs (toutes les tables)
				$app->apiLoad('field')->fieldAffectType($id_field);

				$order[] = $id_field;
			}
		}

		if(sizeof($order) > 0) $app->apiLoad('field')->fieldAffectSet($_GET['map'], $order, $id);

		# On SUPPRIMER le FIELD dans la base AFFECT + ALTER TABLE
		#
		foreach(explode(',', $_GET['move']) as $id_field){
			if(intval($id_field) > 0){
				$app->apiLoad('field')->fieldAffectRemove($id_field, $_GET['map'], $_GET['id_type']);
			}
		}

		// Cache
		$app->apiLoad('field')->fieldCacheBuild();
		header("Location: asso?id_type=".$_GET['id_type'].'&map='.$_GET['map']);
		exit();
	}

#	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 


	$types	= $app->apiLoad('content')->contentType(array('profile'	=> true));
	$field	= $app->apiLoad('field')->fieldGet($opt);
	$use	= array_merge(array('-1'), $app->dbKey($field, 'id_field', true));
	$not	= $app->dbMulti("SELECT * FROM k_field WHERE id_field NOT IN(".implode(',', $use).")");
	$core	= array(
		'category'				=> array('name' => 'Catégories',				'link' => "/admin/category/index"),
		'chapter'				=> array('name' => 'Arborescence',				'link' => "/admin/chapter/index"),
		'user'					=> array('name' => 'Utilisateurs',				'link' => "/admin/user/index")
	);

	if($app->configGet('business', 'enabled') == 'YES'){
		$core = array_merge($core, array(
			'businessCart'		=> array('name' => 'Business cart'),
			'businessCartLine'	=> array('name' => 'Business cart (ligne)'),
		));
	}

	if($app->configGet('social', 'enabled') == 'YES'){
		$core = array_merge($core, array(
			'socialForum'			=> array('name' => 'Social Forum',			'link' => "/admin/social/forum"),
			'socialCircle'			=> array('name' => 'Social Cercle'),
			'socialAlert'			=> array('name' => 'Social Alerte'),
			'socialActivity'		=> array('name' => 'Social Activité'),
			'socialEvent'			=> array('name' => 'Social Evenement'),
			'socialEventUserData'	=> array('name' => 'Social Evenement (utilisateur)')
		));
	}	

?><!DOCTYPE html>
<html lang="fr" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html"
      xmlns="http://www.w3.org/1999/html">
<head>
	<?php include(COREINC.'/head.php'); ?>
    <link rel="stylesheet" type="text/css" href="ui/css/asso.css" />
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(dirname(__DIR__).'/content/ui/menu.php')
?></header>

<div class="inject-subnav-right hide">
	<li><a href="./" class="btn btn-mini">G&eacute;rer les champs</a></li>
    <li><a href="../content/type" class="btn btn-mini">G&eacute;rer les types</a></li>
</div>

<div id="app"><div class="wrapper"><div class="row-fluid">
	
	<div class="span3">

		<ul id="asso">
			<li class="section clearfix">
				<span class="left">Contenu</span>
			</li>
			<?php
				if(sizeof($types) > 0){
					foreach($types as $e){
		
						$class	= ($_REQUEST['id_type'] == $e['id_type']) ? 'me' : NULL;
						$link	= ($e['is_gallery'])  ? '&map=typealbum'  	: NULL;
						$more	= ($e['is_gallery'])  ? ' (Album)' 			: NULL;
						$more	= ($e['is_business']) ? ' (eBusiness)'  	: $more;
		
						echo "<li class=\"clearfix ".$class."\">";
						echo "<a class=\"l\" href=\"asso?id_type=".$e['id_type'].$link."\">".$e['typeName'] . $more."</a>";
						echo "<a class=\"r\" href=\"/admin/content/index?id_type=".$e['id_type']."\">Liste</a>";
						echo "</li>";
					}
				}else{
					echo "<li><a href=\"../content/type\">Ajouter un type</a></li>";
				}
			?>
			<li class="section clearfix">
				<span>Elements syst&egrave;mes</span>
			</li>
			<?php
				foreach($core as $k => $e){
					echo "<li class=\"".(($_REQUEST['id_type'] == $k) ? 'me' : NULL)." clearfix\">";
					echo "<a class=\"l\" href=\"asso?id_type=".$k."\">".$e['name']."</a>";
					if($e['link'] != '') echo "<a class=\"r\" href=\"".$e['link']."\">Liste</a>";
					echo "</li>";
				}
			?>
		</ul>
	</div>

	<div class="span9 message messageWarning" id="messageWarning" style="display:none; margin-bottom:30px;">
		<p><b>ATTENTION</b> vous avez supprim&eacute; un champs, si vous enregistrer cette page, ce champ
		sera imm&eacute;diatement supprim&eacute; et l'int&eacute;gralit&eacute; des donn&eacute;es contenue dans ce champs sera
		perdu. &mdash; <b>Vous ne pouvez plus revenir en arri&egrave;re une fois cette action effectu&eacute;e</b>.</p>

		<center>
			<a href="asso?id_type=<?php echo $_GET['id_type'] ?>" class="btn">Annuler la suppression</a>
		</center>
	</div>

	<div class="span5">
		<b>Champs non utilis&eacute;s</b>
		<ul id="lb" class="myList clearfix" style="background: #E3E3E3;">
			<?php foreach($not as $e){ ?>
			<li  id="<?php echo $e['id_field'] ?>">
				<?php echo $e['fieldName'] ?>
				<a href="index?id_field=<?php echo $e['id_field']; ?>">(<?php echo $e['fieldKey'] ?>)</a>
			</li>
			<?php }Â ?>
		</ul>
	</div>

	<div class="span4">
		<b>Champs utilis&eacute;s <?php echo $exp ?></b>
		<ul id="la" class="myList clearfix">
			<?php foreach($field as $e){ ?>
			<li id="<?php echo $e['id_field'] ?>" class="in-place">
				<?php echo $e['fieldName'] ?>
				<a href="index?id_field=<?php echo $e['id_field']; ?>">(<?php echo $e['fieldKey'] ?>)</a>
			</li>
			<?php }Â ?>
		</ul>
	</div>

	<div class="span8 clearfix">
		<a onclick="sauver();" class="btn btn-mini">Sauver</a>
		<a href="asso?id_type=<?php echo $_GET['id_type'] ?>" class="btn btn-mini">Annuler</a>
	</div>
	
	<input type="text" id="move" size="80" style="opacity: 0;"/>
	
</div></div></div>

<?php include(COREINC.'/end.php'); ?>
<script>

	var mySort = $('#la, #lb').sortable({

		connectWith: ".myList",
		stop: function(e,ui) {
			parent = $(this).parent();
			
			console.log($(this));
			console.log( $(ui.item[0]).parent() )
	    	if($(ui.item[0]).parent().attr('id') == 'lb'){
	    		if($(ui.item[0]).hasClass('in-place')) $('#messageWarning').css('display', '');
	    		$('#move').val($('#move').val() + ',' + $(ui.item[0]).attr('id')); 
	    	}
		}
	});
	
	function sauver(){
		var ordre = $('#la').sortable('toArray');
		var ordre = ordre.join(',');
		
		document.location='asso?id_type=<?php echo $_GET['id_type'] ?>&map=<?php echo $map ?>&ordre='+ordre+'&move='+$('#move').val();
	}
</script>
		
</body>
</html>