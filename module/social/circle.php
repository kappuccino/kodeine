<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	if(sizeof($_POST['remove']) > 0){
		foreach($_POST['remove'] as $e){
			$app->apiLoad('social')->socialCircleHide(array('id_socialcircle' => $e));
		}
		$app->go('circle.php');
	}else
	if($_POST['action'] == 'circle'){
		$do		= true;
		$core	= array(
			'socialCircleName'	=> array('value' => $_POST['socialCircleName'])
		);

		if(!$app->formValidation(array('k_socialcircle' => $core))) $do = $false;

		if($do){
			$app->apiLoad('social')->socialCircleSet(array(
				'debug'				=> false,
				'id_socialcircle'	=> $_REQUEST['id_socialcircle'],
				'core'				=> $core
			));
			$message = ($result) ? 'OK: Enregistrement' : 'KO: Probleme, APP : <br />'.$app->db_error;
		}else{
			$message = 'KO: Validation failed';
		}
		
		# ADD/REMOVE members
		#
		$add = explode(',', $_POST['add']);
		if(sizeof($add) > 0 && $add[0] != ''){
			$app->apiLoad('social')->socialCircleMemberAdd($_POST['id_socialcircle'], $add);
		}
		
		$del = explode(',', $_POST['del']);
		if(sizeof($del) > 0 && $del[0] != ''){
			$app->apiLoad('social')->socialCircleMemberRemove($_POST['id_socialcircle'], $del);
		}
	}

	if($_REQUEST['id_socialcircle'] != NULL){
		$data = $app->apiLoad('social')->socialCircleGet(array(
			'id_socialcircle' => $_REQUEST['id_socialcircle']
		));

		$title = $data['socialCircleName'];
	}else{
		$title = 'Nouveau cercle';
	}

	$circles = $app->apiLoad('social')->socialCircleGet(array(
		'debug'		=> false,
		'public'	=> true
	));

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(ADMINUI.'/head.php'); ?>
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/social.css" />
</head>
<body>
<div id="pathway" class="is-edit">
	<a href="core.panel.php">Admin</a> &raquo;
	<a href="social.index.php">Social</a> &raquo;
	<a href="social.index.php">Index</a>
</div>

<?php include('ressource/ui/menu.social.php'); ?>

<div class="app">



<div style="float:left; width:25%;">
<form method="post" action="social.circle.php" id="fi">
	<ul id="items"><?php
		foreach($circles as $e){
			echo "<li class=\"clearfix ".(($e['id_socialcircle'] == $_REQUEST['id_socialcircle']) ? 'me' : '')."\">";
			
			echo "<div class=\"l\">";
				echo "<input type=\"checkbox\" name=\"remove[]\" value=\"".$e['id_socialcircle']."\" /> &nbsp; ";
				echo "<a href=\"social.circle.php?id_socialcircle=".$e['id_socialcircle']."\">".$e['socialCircleName']."</a>";
			echo "</div>";

			echo "<span class=\"r count\">".$e['socialCircleMemberCount']."</span>";
			echo "</li>";
		}
	?></ul>
	
	<div style="margin-top:10px;">
		<a href="#" onclick="removeCircle()" class="button rButton">Supprimer les cercles</a>
		&nbsp;
		<a href="social.circle.php" class="button rButton">Nouveau cercle</a>
	</div>

</form>
</div>

<div style="float:left; width:75%;">
<form method="post" action="social.circle.php" id="fo">
	<input type="hidden" name="action"			value="circle" />
	<input type="hidden" name="id_socialcircle" value="<?php echo $data['id_socialcircle'] ?>" />

	<div class="dataView">

		<fieldset>
			<legend>Cercle</legend>
			<table border="0" cellpadding="5" width="100%">
				<tr>
					<td><input name="socialCircleName" style="width:100%;" value="<?php echo $app->formValue($data['socialCircleName'], $_POST['socialCircleName']) ?>" /></td>
				</tr>
			</table>
		</fieldset>

		<?php if($data['id_socialcircle'] > 0){

			$members = $app->dbMulti("
				SELECT * FROM k_socialcircleuser
				INNER JOIN k_user ON k_socialcircleuser.id_user = k_user.id_user
				WHERE id_socialcircle=".$data['id_socialcircle']
			);
			
			$tmp[] = -1;
			foreach($members as $e){
				$tmp[] = $e['id_user'];
			}

			$not = $app->dbMulti("SELECT * FROM k_user WHERE id_user NOT IN(".implode(',', $tmp).")");
		?>

		<b>Ces utilisateurs font partis du cercle</b>
		<ul id="la" class="myList clearfix"><?php
			foreach($members as $e){
				echo "<li id=\"".$e['id_user']."\" class=\"in-place set\">".$e['userMail']."</li>";
			}
		?></ul>
		
		<b>Ces utilisateurs ne font pas parti du cercle</b>
		<ul id="lb" class="myList clearfix"><?php
			foreach($not as $e){
				echo "<li id=\"".$e['id_user']."\" class=\"in-place plus\">".$e['userMail']."</li>";
			}
		?></ul>

		<input type="hidden" id="del" name="del" />
		<input type="hidden" id="add" name="add" />
		<?php } ?>

		<a href="#" onclick="$('fo').submit();" class="button rButton">Enregistrer</a>
	
	</div>
</form>
</div>








</div> <!-- app -->


<script>

	remove = {};
	ajout = {};

	function removeCircle(){
		if(confirm("Voulez vous supprimer ces cercles ?")){
			$('fi').submit();
		}
	}

	var mySortables = new Sortables('#la, #lb', {
	    constrain: false,
	    clone: true,
	    revert: true,
	    onComplete: function(e){
	    	parent = e.getParent();

	    	if(e.hasClass('set')){
		    	if(parent.id == 'lb'){
		    		remove[e.id] = '-';
		    	}else
		    	if(parent.id == 'la'){
		    		if(remove[e.id] == '-') remove[e.id] = '';
		    	}

		    	var tmp = [];
				for(var index in remove){
					if(remove[index] == '-') tmp.push(index);
				}
				$('del').value = tmp.join(',');
			}else
	    	if(e.hasClass('plus')){
		    	if(parent.id == 'lb'){
		    		if(ajout[e.id] == '+') ajout[e.id] = '';
		    	}else
		    	if(parent.id == 'la'){
		    		ajout[e.id] = '+';
		    	}

		    	var tmp = [];
				for(var index in ajout){
					if(ajout[index] == '+') tmp.push(index);
				}
				$('add').value = tmp.join(',');
			}


		
	    }
	});

	function sauver(mode){
		var ordre = mySortables.serialize();
		var ordre = ordre[0].join(',');
		document.location='content.type.field.asso.php?id_type=<?php echo $_GET['id_type'] ?>&mode=<?php echo $mode ?>&ordre='+ordre+'&move='+$('move').value;
	}

</script>


</body></html>