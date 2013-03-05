<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	if(empty($_REQUEST['id_type'])) $app->go('asso?id_type=category');

	if(intval($_REQUEST['id_type']) > 0){
		$type	= $app->apiLoad('type')->typeGet(array('id_type' => $_REQUEST['id_type']));
		if($_GET['map'] == '' && $type['is_gallery']) $_GET['map'] = 'album';

		if($_GET['map'] == 'typealbum'){
			$map = 'typealbum';
			$exp = "("._('Album').") <a href=\"asso?id_type=".$type['id_type']."&map=typeitem\">"._('Item')."</a>";
			$opt = array('id_type' => $_REQUEST['id_type'], 'albumField' => true);
		}else
		if($_GET['map'] == 'typeitem'){
			$map = 'typeitem';
			$exp = "("._('Item').") <a href=\"asso?id_type=".$type['id_type']."&map=typealbum\">"._('Album')."</a>";
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
		$app->go("asso?id_type=".$_GET['id_type'].'&map='.$_GET['map']);
	}

#	- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 


	$types	= $app->apiLoad('type')->typeGet(array('profile'	=> true));
	$field	= $app->apiLoad('field')->fieldGet($opt);
	$use	= array_merge(array('-1'), $app->dbKey($field, 'id_field', true));
	$not	= $app->dbMulti("SELECT * FROM k_field WHERE id_field NOT IN(".implode(',', $use).")");
	$core	= array(
		'category'	=> array('name' => _('Categories'),	'link' => "../category/"),
		'chapter'	=> array('name' => _('Chapters'),	'link' => "../chapter/"),
		'user'		=> array('name' => _('Users'),		'link' => "../user/")
	);

	if($app->configGet('business', 'enabled') == 'YES'){
		$core = array_merge($core, array(
			'businessCart'		=> array('name' => _('Business cart')),
			'businessCartLine'	=> array('name' => _('Business cart (ligne)')),
		));
	}

	if($app->configGet('social', 'enabled') == 'YES'){
		$core = array_merge($core, array(
			'socialForum'			=> array('name' => _('Social Forum')),
			'socialCircle'			=> array('name' => _('Social Circle')),
			'socialAlert'			=> array('name' => _('Social Alert')),
			'socialActivity'		=> array('name' => _('Social Activity')),
			'socialEvent'			=> array('name' => _('Social Event')),
			'socialEventUserData'	=> array('name' => _('Social Event (user)'))
		));
	}	

?><!DOCTYPE html>
<html lang="fr">
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
	<li><a href="./" class="btn btn-mini"><?php echo _('Manage fields'); ?></a></li>
    <li><a href="../content/type" class="btn btn-mini"><?php echo _('Manage types'); ?></a></li>
</div>

<div id="app"><div class="wrapper"><div class="row-fluid">
	
	<div class="span3">

		<ul id="asso">
			<li class="section clearfix">
				<span class="left"><?php echo _('Contents'); ?></span>
			</li>
			<?php
				if(sizeof($types) > 0){
					foreach($types as $e){
		
						$class	= ($_REQUEST['id_type'] == $e['id_type']) ? 'me' : NULL;
						$link	= ($e['is_gallery'])  ? '&map=typealbum'  	: NULL;
						$more	= ($e['is_gallery'])  ? _('(Album)') 		: NULL;
						$more	= ($e['is_business']) ? _('(eBusiness)')  	: $more;
		
						echo "<li class=\"clearfix ".$class."\">";
						echo "<a class=\"l\" href=\"asso?id_type=".$e['id_type'].$link."\">".$e['typeName'] .' '.$more."</a>";
						echo "<a class=\"r\" href=\"../content/index?id_type=".$e['id_type']."\">Liste</a>";
						echo "</li>";
					}
				}else{
					echo "<li><a href=\"../content/type\">"._('Add a type')."</a></li>";
				}
			?>
			<li class="section clearfix">
				<span><?php echo _('System items'); ?></span>
			</li>
			<?php
				foreach($core as $k => $e){
					echo "<li class=\"".(($_REQUEST['id_type'] == $k) ? 'me' : NULL)." clearfix\">";
					echo "<a class=\"l\" href=\"asso?id_type=".$k."\">".$e['name']."</a>";
					if($e['link'] != '') echo "<a class=\"r\" href=\"".$e['link']."\">"._('List')."</a>";
					echo "</li>";
				}
			?>
		</ul>
	</div>

	<div class="span9 message messageWarning" id="messageWarning" style="display:none; margin-bottom:30px;">
		<p><?php echo _('<b>WARNING</b> you have removed a field. If you save this page, this field will be
		immediately removed and all data stored inside it will be lost.
		<b>You will not be able to cancel this operation</b>'); ?></p>
		<a href="asso?id_type=<?php echo $_GET['id_type'] ?>" class="btn"><?php echo _('Cancel remove'); ?></a>
	</div>

	<div class="span5">
		<b><?php echo _('Not used fields'); ?></b>
		<ul id="lb" class="myList clearfix" style="background: #E3E3E3;">
			<?php foreach($not as $e){ ?>
			<li  id="<?php echo $e['id_field'] ?>">
				<?php echo $e['fieldName'] ?>
				<a href="index?id_field=<?php echo $e['id_field']; ?>">(<?php echo $e['fieldKey'] ?>)</a>
			</li>
			<?php } ?>
		</ul>
	</div>

	<div class="span4">
		<b><?php echo _('Used fields').' '.$exp; ?></b>
		<ul id="la" class="myList clearfix">
			<?php foreach($field as $e){ ?>
			<li id="<?php echo $e['id_field'] ?>" class="in-place">
				<?php echo $e['fieldName'] ?>
				<a href="index?id_field=<?php echo $e['id_field']; ?>">(<?php echo $e['fieldKey'] ?>)</a>
			</li>
			<?php } ?>
		</ul>
	</div>

	<div class="span8 clearfix">
		<a onclick="sauver();" class="btn btn-mini"><?php echo _('Save'); ?></a>
		<a href="asso?id_type=<?php echo $_GET['id_type'] ?>" class="btn btn-mini"><?php echo _('Cancel'); ?></a>
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