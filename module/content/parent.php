<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	if(isset($_GET['add'])){
		$last = $app->dbOne("SELECT MAX(pos_parent) AS la FROM k_content WHERE id_parent=".$_GET['id_content']);
		$app->dbQuery("UPDATE k_content SET id_parent=".$_GET['id_content'].", pos_parent=".($last['la'] + 1)." WHERE id_content=".$_GET['add']);
		header("Location: parent?id_content=".$_GET['id_content']."&id_type=".$_GET['id_type']);
	}else
	if(isset($_GET['pos'])){
		foreach(explode(',', $_GET['pos']) as $i => $e){
			$app->dbQuery("UPDATE k_content SET pos_parent=".($i+1)." WHERE id_content=".$e);
		}
		header("Location: parent?id_content=".$_GET['id_content']);
	}else
	if(isset($_GET['remove'])){
		$app->dbQuery("UPDATE k_content SET id_parent=0, pos_parent=0 WHERE id_content=".$_GET['remove']);
		header("Location: parent?id_content=".$_GET['id_content']);
	}

	$data = $app->apiLoad('content')->contentGet(array(
		'id_content' 	=> $_GET['id_content'],
		'language'		=> 'fr',
		'debug'	 		=> false,
		'raw'			=> true
	));

	$type = $app->apiLoad('type')->typeGet(array(
		'id_type'		=> $data['id_type']
	));

	$types = $app->apiLoad('type')->typeGet(array(
		'profile'		=> true
	));
	
	$id_type 	= ($_GET['id_type'] == NULL) ? $types[0]['id_type'] : $_GET['id_type'];
	$language	= 'fr';
	
	// Utilises
	$ids = $app->dbMulti("SELECT id_content FROM k_content WHERE id_parent=".$_GET['id_content']." ORDER BY pos_parent ASC");
	if(is_array($ids)){
		foreach($ids as $e){
			$noId[] = $e['id_content'];
		}
	}else{
		$noId = array();
	}

	$used		=  $noId;
	$noId[] 	= $_GET['id_content'];
	$language	= ($filter['language'] != '') ? $filter['language'] : 'fr';
	$opt		= array(
		'debug'	 			=> false,
		'id_type' 			=> $id_type,
		'useChapter'		=> false,
		'useGroup'			=> false,
		'contentSee'		=> 'ALL',
		'language'			=> $language,
	#	'limit'				=> $filter['limit'],
	#	'offset'			=> $filter['offset'],
		'noId'				=> $noId,
		'search'			=> $_GET['q'],
	);

	// Content
	$content	= $app->apiLoad('content')->contentGet($opt);
	$total		= $app->apiLoad('content')->total;
	$limit		= $app->apiLoad('content')->limit;
	$lang		= $app->countryGet(array('is_used' => true));

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
	<style>
		table.table td {
			padding: 2px 0 2px 7px;
		}
	
		#used{
			margin: 0px;
			padding: 0px;
		}
		#used ul{	
			border: 1px solid rgb(220, 220, 220);
			-moz-border-radius:4px; -webkit-border-radius:4px;
			padding: 5px 0px 0px 5px;
			margin:0px 5px 0px 0px;
		}
		
			#used li{
				list-style-type: none;
				float: left;
				background-color: #e1e1e1;
				padding:5px;
				margin: 0px 5px 5px 0px;
				width: 175px;
				height: 40px;
				-moz-border-radius:4px; -webkit-border-radius:4px;
			}
		
				#used li a{
					color:#333;
				}
		
			#used li:hover{
				background-color: #f1f1f1;
			}
	</style>

</head>
<body>
	
<header><?php
	include(COREINC.'/top.php');
	include(dirname(__DIR__).'/content/ui/menu.php')
?></header>

<div class="inject-subnav-right hide">
	<li>
		<div class="btn-group">
			<a class="btn btn-mini dropdown-toggle" data-toggle="dropdown" href="#"><?php echo $type['typeName']; ?> <span class="caret"></span></a>
			<ul class="dropdown-menu"><?php
			foreach($app->apiLoad('type')->typeGet(array('profile' => true)) as $e){
				echo '<li class="clearfix">';
				echo '<a href="'.(($e['is_gallery']) ? 'gallery' : 'parent').'?id_content='.$_GET['id_content'].'&id_type='.$e['id_type'].'" class="left">'.$e['typeName'].'</a>';
				echo '<a href="'.(($e['is_gallery']) ? 'gallery-album' : 'data' )."?id_type=".$e['id_type'].'" class="right"><i class="icon icon-plus-sign"></i></a>';
				echo '</li>';
			}
			?></ul>
		</div>
	</li>
</div>

<div id="app">
	
	<div class="row-fluid">
	
	<div class="span6">
	
		<!-- <div class="menu-inline-left clearfix"><?php
			foreach($types as $e){
				echo "<div class=\"button button-".($e['id_type'] == $_GET['id_type'] ? 'green' : 'blue')."\">";
					echo "<a href=\"parent?id_content=".$_GET['id_content']."&id_type=".$e['id_type']."\" class=\"text\">".$e['typeName']."</a>";
				echo "</div>";
			}
		?></div> -->
	
		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing sortable" style="margin-top:10px;">
			<thead>
				<tr>
					<th width="60">#</th>
					<th><?php echo _('Name'); ?></th>
					<th width="80"></th>
				</tr>
			</thead>
			
			<tbody>
			<?php if(sizeof($content) == 0){ ?>
				<tr>
					<td colspan="4" style="padding:40px 0px 40px 0px; text-align:center; font-weight:bold">Aucun contenu disponible</td>
				</tr>
			<?php }else{
				foreach($content as $e){ ?>
				<tr>
					<td><?php echo $e['id_content'] ?></td>
					<td><a href="data?id_content=<?php echo $e['id_content'] ?>"><?php echo $e['contentName'] ?></a></td>
					<td><a class="btn btn-mini" href="parent?id_content=<?php echo $_GET['id_content'] ?>&id_type=<?php echo $id_type ?>&add=<?php echo $e['id_content'] ?>"><?php echo _('Add'); ?></a></td>
				</tr>
				<?php } ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="5" class="pagination"><?php $app->pagination($total, $limit, $filter['offset'], 'parent?id_content='.$_GET['id_content'].'&id_type='.$id_type.'&offset=%s'); ?></td>
				</tr>
			</tfoot>
			<?php } ?>
		</table>
	</div>
	
	
	<div class="span6">
	
		<div style="margin-bottom:10px;" class="clearfix">
			<a href="javascript:sauver();" class="btn btn-mini"><?php echo _('Save this order'); ?></a>
			<a href="parent?id_content=<?php echo $_GET['id_content'] ?>" class="btn btn-mini"><?php echo _('Cancel'); ?></a>
		</div>
	
		<ul id="used" class="clearfix"><?php
		
			if(sizeof($used) > 0){
				foreach($used as $e){
					$content = $app->apiLoad('content')->contentGet(array(
						'useChapter'		=> false,
						'useGroup'			=> false,
						'contentSee'		=> 'ALL',
						'language'			=> $language,
						'id_content'		=> $e,
						'id_parent'			=> $_GET['id_content'],
						'debug'				=> false
					));
		
					echo "<li id=\"".$e."\">".$content['contentName']."<br />";
						echo "<a href=\"parent?id_content=".$_GET['id_content']."&remove=".$e."\" class=\"btn btn-mini\">"._('Remove')."</a> ";
						echo "<a href=\"data?id_content=".$e."\" class=\"btn btn-mini\">"._('Edit')."</a>";
					echo "</li>";
				}
			}
		
		?></ul>
	
	
	<?php include(COREINC.'/end.php'); ?>
	<script src="../core/vendor/datatables/jquery.dataTables.js"></script>
	<script src="../core/vendor/bootstrap/js/bootstrap-dropdown.js"></script>
	
	<script>
		var mySortables = $('#used').sortable({
			
		});
		
		
		/*var mySortables = new Sortables('used', {
		    constrain: false,
		    clone: true,
		    revert: true,
		    onComplete: function(e){
		    }
		});*/
		
		function sauver(){
			ordre = mySortables.sortable("toArray");
			ordre = ordre.join(',');
			document.location='parent?id_content=<?php echo $_GET['id_content'] ?>&pos='+ordre;
		}
	
	</script>
	
	</div>
	</div>
</div>
	
</body>
</html>