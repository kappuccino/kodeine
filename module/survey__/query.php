<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	if($_REQUEST['id_survey'] == NULL){
		header("Location: index");
		exit();
	}


	# Suppression
	#
	if($_GET['removeGroup']){
		$app->apiLoad('survey')->surveyGroupRemove($_GET['removeGroup']);
		header("Location: query?id_survey=".$_GET['id_survey']);
	}else
	if($_GET['removeQuery']){
		$app->apiLoad('survey')->surveyQueryRemove($_GET['removeQuery']);
		header("Location: query?id_survey=".$_GET['id_survey']);
	}



	# Mise a jour de la QUERY et des REPONSE 
	#
	if(isset($_POST['id_survey'])){

		// Gerer le GROUP
		if(trim($_POST['surveyGroupName']) != '' OR $_POST['id_surveygroup'] > 0){
			$def = array('k_surveygroup' => array(
				'id_survey'					=> array('value' => $_POST['id_survey']),
				'surveyGroupName'			=> array('value' => $_POST['surveyGroupName']),
				'surveyGroupDescription'	=> array('value' => $_POST['surveyGroupDescription'], 	'null' => true)
			));

			$app->apiLoad('survey')->surveyGroupSet(array(
				'id_surveygroup'	=> $_POST['id_surveygroup'],
				'def'				=> $def,
				'debug'				=> false
			));
		}

		// Gerer la QUERY
		if(trim($_POST['surveyQueryName']) != '' OR $_POST['id_surveyquery'] > 0){
			$def = array('k_surveyquery' => array(
				'id_survey'			=> array('value' => $_POST['id_survey']),
				'id_surveygroup'	=> array('value' => $_POST['id_surveygroupK']),
				'surveyQueryName'	=> array('value' => $_POST['surveyQueryName']),
				'surveyQueryType'	=> array('value' => $_POST['surveyQueryType']),
				'allow_other'		=> array('value' => $_POST['allow_other'], 		'zero' => true),
				'allow_empty'		=> array('value' => $_POST['allow_empty'], 		'zero' => true)
			));

			$app->apiLoad('survey')->surveyQuerySet(array(
				'id_surveyquery'	=> $_POST['id_surveyquery'],
				'def'				=> $def,
				'debug'				=> false
			));
		}
		
		// Gerer les ITEM
		if(sizeof($_POST['item']) > 0){
			foreach($_POST['item'] as $id_surveyqueryitem => $item){
				if(trim($item['name']) == ''){
					$app->apiLoad('survey')->surveyQueryItemRemove($id_surveyqueryitem);
				}else{
					$def = array('k_surveyqueryitem' => array(
						'surveyQueryItemName'	=> array('value' => $item['name']),
						'surveyQueryItemIsTrue'	=> array('value' => $item['true'], 	'zero' => true),
					));
	
					$app->apiLoad('survey')->surveyQueryItemSet(array(
						'id_surveyqueryitem'	=> $id_surveyqueryitem,
						'def'					=> $def,
						'debug'					=> false
					));
				}
			}
		}
		
		if(isset($_REQUEST['id_surveyquery'])){
			$and = '&id_surveyquery='.$_REQUEST['id_surveyquery'];
		}
		
		header("Location: query?id_survey=".$_POST['id_survey'].$and); #.'&id_surveyquery='.$app->apiLoad('survey')->id_surveyquery);
	}

	$groups = $app->apiLoad('survey')->surveyGroupGet(array(
		'id_survey' => $_REQUEST['id_survey'],
		'debug'		=> false
	));

	if($_REQUEST['id_surveyquery'] > 0){
		$query = $app->apiLoad('survey')->surveyQueryGet(array(
			'id_surveyquery'	=> $_REQUEST['id_surveyquery'],
			'debug'				=> false
		));
	}else
	if($_REQUEST['id_surveygroup'] > 0){
		$group = $app->apiLoad('survey')->surveyGroupGet(array(
			'id_surveygroup'	=> $_REQUEST['id_surveygroup'],
			'debug'				=> false
		));
	}

	if(is_array($query)){
		$title = 'Modification de la question';
		$manageQuery = true;
	}else
	if(is_array($group)){
		$manageGroup = true;
		$title  = 'Modification du groupe de question';
		$titleG = 'Modification du groupe de question';
	}else{
		$title 	= 'Ajouter une nouvelle question';
		$titleG = 'Ajouter un nouveau groupe de questions';
		$manageGroup = true;
		$manageQuery = true;
	}
	
	if(sizeof($groups) == 0){
		$manageQuery = false;
	}

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/survey.css" />
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<?php
	if($message != NULL){
		list($class, $message) = $app->helperMessage($message);
		echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
	}
?>

<input type="hidden" name="id_survey" value="<?php echo $_REQUEST['id_survey'] ?>" />

<div class="app"><div class="wrapper clearfix">
	
<div class="save alert alert-success" id="save">
	<span id="recording">Enregistrement</span>
</div>

<div style="float:left; width:25%;">
	<ul id="items"><?php

		foreach($groups as $g){

			$qs = $app->apiLoad('survey')->surveyQueryGet(array(
				'id_surveygroup'	=> $g['id_surveygroup'],
				'debug'				=> false
			));

			echo "<li>";
				echo "<div class=\"groupName ".(($g['id_surveygroup'] == $_REQUEST['id_surveygroup']) ? 'me': '')."\">";
					echo "<a href=\"query?id_survey=".$g['id_survey']."&id_surveygroup=".$g['id_surveygroup']."\" ><b>".$g['surveyGroupName']."</b></a><span class=\"move\"></span>";
					if(sizeof($qs) == 0){
						echo "<a onClick=\"removeGroup(".$g['id_surveygroup'].")\" class=\"r\">Supprimer</a>";
					}
				echo "</div>";
			
				echo "<ul class=\"wrapper sortable\" id=\"".$g['id_surveygroup']."\">";
				foreach($qs as $q){
					echo "<li id=\"".$q['id_surveyquery']."\" class=\"clearfix ".(($q['id_surveyquery'] == $_REQUEST['id_surveyquery']) ? 'me': '')."\">";
						echo "<a href=\"query?id_survey=".$q['id_survey']."&id_surveyquery=".$q['id_surveyquery']."\">".$q['surveyQueryName']."</a>";
					echo "</li>";
				}
				echo "</ul>";			
			echo "</li>";

		}
	?></ul>&nbsp;
</div>

<div style="float:left; width:75%;">	
<form method="post" action="query" id="fo">
	<input type="hidden" name="id_survey" value="<?php echo $_REQUEST['id_survey'] ?>" />

	<div class="dataView"><div class="padd">

		<?php if($manageQuery){ ?>
		<input type="hidden" name="id_surveyquery" id="id_surveyquery" value="<?php echo $query['id_surveyquery'] ?>" />
		<fieldset>
			<legend><?php echo $title ?></legend>
			<table border="0" cellpadding="5" width="100%">
				<tr>
					<td colspan="2">Question</td>
				</tr>
				<tr>
					<td colspan="2"><textarea name="surveyQueryName" style="height:40px; width:100%;"><?php echo $app->formValue($query['surveyQueryName'], $_POST['surveyQueryName']) ?></textarea></td>
				</tr>
				<tr>
					<td width="100">Type</td>
					<td>
						<select name="surveyQueryType"><?php
							$c = array(
								'RADIO'			=> 'Choix unique',
								'CHECKBOX'		=> 'Choix multiples',
								'GRADUATION'	=> 'Note 0 à 20',
								'FREE'			=> 'Texte libre'
							);

							foreach($c as $k => $v){
								echo "<option value=\"".$k."\"".(($k == $app->formValue($query['surveyQueryType'], $_POST['surveyQueryType'])) ? ' selected' : '').">".$v."</opton>";
							}
						?></select>
					</td>
				</tr>
				<tr>
					<td>Groupe</td>
					<td>
						<select name="id_surveygroupK"><?php
							foreach($groups as $g){
								echo "<option value=\"".$g['id_surveygroup']."\"".(($g['id_surveygroup'] == $app->formValue($query['id_surveygroup'], $_POST['id_surveygroupK'])) ? ' selected' : '').">".$g['surveyGroupName']."</opton>";
							}
						?></select>
					</td>
				</tr>
				<tr>
					<td>Réponse autre</td>
					<td><input type="checkbox" name="allow_other" value="1" <?php echo $app->formValue($query['allow_other'], $_POST['allow_other']) ? 'checked' : '' ?> /></td>
				</tr>
				<tr>
					<td>Réponse vide</td>
					<td>
						<input type="checkbox" name="allow_empty" value="1" <?php echo $app->formValue($query['allow_empty'], $_POST['allow_empty']) ? 'checked' : '' ?> />
						<i>(En cochant cette case, une reponse vide sera accepté)</i>
					</td>
				</tr>
			</table>
		</fieldset>

		<?php if($_REQUEST['id_surveyquery'] > 0){
			$items = $app->apiLoad('survey')->surveyQueryItemGet(array(
				'id_surveyquery'	=> $query['id_surveyquery'],
				'debug'				=> false
			));
		?>
		<fieldset>
			<legend>Réponses</legend>
			<ul class="qItems"><?php
				foreach($items as $i){
					echo "<li class=\"clearfix\" id=\"".$i['id_surveyqueryitem']."\">";
					echo "<div class=\"ordre\"></div>";
					echo "<div class=\"right\">";
						echo "<textarea 	name=\"item[".$i['id_surveyqueryitem']."][name]\">".$i['surveyQueryItemName']."</textarea>";
						echo "<input 		name=\"item[".$i['id_surveyqueryitem']."][true]\" type=\"checkbox\" value=\"1\" ".(($i['surveyQueryItemIsTrue']) ? ' checked' : '')." /> Bonne r&eacute;ponse<br />";
					echo "</div>";
					echo "</li>";
				}
			?></ul>

			
			<div style="width:90%">
				<textarea id="newItem"></textarea>
				<a id="newItemAdd">Ajouter</a>
			</div>

		</fieldset>
		<?php }} ?>








		<?php if($manageGroup){ ?>
		<input type="hidden" name="id_surveygroup" value="<?php echo $group['id_surveygroup'] ?>" />
		<fieldset>
			<legend><?php echo $titleG ?></legend>
			<table border="0" cellpadding="5" width="100%">
				<tr>
					<td width="100">Nom</td>
					<td><input type="text" name="surveyGroupName" value="<?php echo $app->formValue($group['surveyGroupName'], $_POST['surveyGroupName']) ?>" style="width:75%;" /></td>
				</tr>
				<tr>
					<td colspan="2">Texte de présentation</td>
				</tr>
				<tr>
					<td colspan="2"><textarea name="surveyGroupDescription" style="height:120px; width:100%;"><?php echo $app->formValue($group['surveyGroupDescription'], $_POST['surveyGroupDescription']) ?></textarea></td>
				</tr>
			</table>
		</fieldset>
		<?php } ?>

		<a href="javascript:$('#fo').submit()" class="btn btn-mini">Enregister</a>
		<a href="query?id_survey=<?php echo $_REQUEST['id_survey'] ?>" class="btn btn-mini">Nouvelle question</a>

		<?php if($query['id_surveyquery'] > 0){ ?>
		<a href="javascript:removeQuery(<?php echo $query['id_surveyquery'] ?>)" class="btn btn-mini">Supprimer définitivement cette question</a>
		<?php } ?>

	</div></div>

</div></div>

<?php include(COREINC.'/end.php'); ?>
<script>
	function removeGroup(id_surveygroup){
		if(confirm("Voulez vous supprimer ce groupe ?")){
			document.location = 'query?id_survey=<?php echo $_REQUEST['id_survey'] ?>&removeGroup='+id_surveygroup;
		}
	}
	
	function removeQuery(id_surveyquery){
		if(confirm("Voulez vous supprimer cette question ?")){
			document.location = 'query?id_survey=<?php echo $_REQUEST['id_survey'] ?>&removeQuery='+id_surveyquery;
		}
	}
	
	var get = $.ajax({
		url : 'order',
		dataType : 'json'
	}).done(function(d) {
		$('#save').fadeTo(218, 0);
	});
	
	$('#recording').css({
		'display'	: 'block',
		'opacity'	: 1
	});

	/*rec = new Fx.Morph('recording', {
		'duration'	: 1000,
		'chain' 	: 'cancel',
	});*/
	
	// Classement des QUERY
	
	$('#items ul.sortable').sortable({
		start : function(e, ui) {
			previousOrder = serialAll(true);
		},
		stop : function(e, ui) {
			var now = serialAll(true)
	    	if(previousOrder == now) return true;
				
			var get = $.ajax({
				url : 'order',
				data : {'todo' : 'orderQuery', 'order' : serialAll(false)},
				dataType : 'json'
			}).done(function(d) {
				showRec();
			});
		}
	});
		
	$('#items').sortable({
		handle: 'span.move',
		start : function(e, ui) {},
		stop : function(e, ui) {
			
			var tmp = [];
	    	$('.wrapper').each(function(i, e){
	    		tmp.push(e.id);
	    	});
	    	var order = tmp.join(',');
			showRec();
				
			var get = $.ajax({
				url : 'order',
				data : {'todo' : 'orderGroup', 'order' : order},
				dataType : 'json'
			}).done(function(d) {});
		}
	});

	function showRec() {
		$('#save').fadeTo(50, 1, function() {
			setTimeout(function() {
				$('#save').fadeTo(218, 0);
			}, 1000);
		});
	}

	// Classement des ITEM
	function m(){
		
		$('.qItems').sortable({
			handle: 'div.ordre',
			start : function(e, ui) {},
			stop : function(e, ui) {
				
				var tmp = [];
		    	$('.qItems li').each(function(i, e){
		    		tmp.push(e.id);
		    	});
		    	var order = tmp.join(',');
				showRec();
					
				var get = $.ajax({
					url : 'order',
					data : {'todo' : 'orderGroup', 'order' : order},
					dataType : 'json'
				}).done(function(d) {});
			}
		});
			
	}
	m();

	function serialAll(flat){
		var uls = $('#items ul.sortable');
		var all = {};
		var flt = [];

		uls.each(function(i, e){
			tmp = [];
			lis = $(e).find('li');
			for(i=0; i<lis.length; i++){
				if(lis[i].id != ''){
					tmp.push(lis[i].id);
					flt.push(lis[i].id);
				}
			}
			var r = tmp.join(',');
			eval('all.group'+e.id+'=r');
		});
		
		if(flat) return flt.join('-');

		return all;
	}
	
	if($('#newItemAdd').length > 0){
		$('#newItemAdd').on('click', function(){
			if($('#newItem').val() == '') return false;
			
			var get = $.ajax({
				'url' : 'order'
			}).done(function(d) {
				
				var li = $('<li class="clearfix" id="'+d.id_surveyqueryitem+'" />').appendTo('.qItems');
				var ordre = $('<div class="ordre" />').appendTo(li);
				var right = $('<div class="right" />').appendTo(li)
				
				$('<textarea name="item['+d.id_surveyqueryitem+']" value="'+$('#newItem').val()+'" />').appendTo(right);
				$('<input type="checkbox" value="1" name="'+item['+d.id_surveyqueryitem+'][true]+'" value="'+$('#newItem').val()+'" />').appendTo(right);
				$('<span>Bonne r&eacute;ponse</span>').appendTo(right);
				
				$('#newItem').val('');
				m(); // Add move behaviour
			});
			
			var newget = $.ajax({
				url : 'order',
				type : 'post',
				data : {
					'todo'	: 'newItem', 
					'item'	: $('#newItem').val(),
					'id_surveyquery' : $('#id_surveyquery').val()
				}
			}).done(function(d) {})
						
		});
	}

</script>

</body></html>