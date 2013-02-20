<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	$data = $app->apiLoad('survey')->surveyGet(array(
		'id_survey' => $_REQUEST['id_survey'],
	));
	
	$groups = $app->apiLoad('survey')->surveyGroupGet(array(
		'id_survey' => $_REQUEST['id_survey']
	));
	
	$stat = $app->apiLoad('survey')->surveyStat($data['id_survey']);
?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>

<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div class="app"><div class="wrapper">

<?php
	if($message != NULL){
		list($class, $message) = $app->helperMessage($message);
		echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
	}
?>

<div style="padding:5px 0px 5px 5px">
	<a href="data?id_survey=<?php echo $data['id_survey'] ?>"  class="btn btn-mini">Revenir</a>
</div>

<div style="margin-top:20px;"><?php

foreach($groups as $g){
	$query = $app->apiLoad('survey')->surveyQueryGet(array(
		'id_surveygroup' => $g['id_surveygroup']
	));
?>
<fieldset style="margin-bottom:20px;">
<legend><?php echo $g['surveyGroupName'] ?></legend>

<?php

	foreach($query as $q){
		echo "<h2>".$q['surveyQueryName']."</h2>";
		
		echo "<p>".$stat[$q['id_surveyquery']]['player']." personne(s) qui ont répondus</p>";

		$items = $app->apiLoad('survey')->surveyQueryItemGet(array(
			'id_surveyquery' => $q['id_surveyquery']
		));

		if(sizeof($items) > 0){
			echo "<table border=\"1\" width=\"100%\">";
			foreach($items as $i){
				echo "<tr>";
					echo "<td>".$i['surveyQueryItemName']."</td>";
					echo "<td width=\"100\">".$stat[$q['id_surveyquery']]['item'][$i['id_surveyqueryitem']]['count']."</td>";
					echo "<td width=\"100\">".$stat[$q['id_surveyquery']]['item'][$i['id_surveyqueryitem']]['percent']." %</td>";
				echo "</tr>";
			}
			if($q['allow_other']){
				echo "<tr>";
					echo "<td><a onClick=\"op($(this), 'o".$q['id_surveyquery']."')\" class=\"closed\">Autre</a></td>";
					echo "<td width=\"100\">".$stat[$q['id_surveyquery']]['item']['@']['count']."</td>";
					echo "<td width=\"100\">".$stat[$q['id_surveyquery']]['item']['@']['percent']." %</td>";
				echo "</tr>";
			}
			echo "</table>";
			
			if($q['allow_other']){
				echo "<table border=\"1\" width=\"100%\" id=\"o".$q['id_surveyquery']."\" style=\"display:none; margin-top:5px;\">";

				$ot = $app->dbMulti("SELECT * FROM k_surveyslotitem WHERE id_surveyquery=".$q['id_surveyquery']." AND id_surveyqueryitem IS NULL");

				foreach($ot as $e){
					echo "<tr>";
						echo "<td>".$e['surveySlotItemText']."</td>";
					echo "</tr>";
				}
				echo "</table>";
			}

		}else
		if($q['surveyQueryType'] == 'FREE'){

			echo "<a onClick=\"op($(this), 'q".$q['id_surveyquery']."')\" class=\"closed\">Afficher toutes les réponses libres</a>";

			$es = $app->dbMulti("SELECT * FROM k_surveyslotitem WHERE id_surveyquery=".$q['id_surveyquery']);
			
			echo "<div id=\"q".$q['id_surveyquery']."\" style=\"display:none\">";
			echo "<table border=\"1\" width=\"100%\">";
			foreach($es as $e){
				echo "<tr>";
					echo "<td>".$e['surveySlotItemText']."</td>";
				echo "</tr>";
			}
			echo "</table>";
			echo "</div>";
		}
	}
	
#	$app->pre($end);

?>

</fieldset>
<?php } ?></div>


<script>

function op(a, id){
	if(a.hasClass('closed')){
		a.removeClass('closed').addClass('opened');
		$('#'+id).css('display', '');
	}else{
		a.removeClass('opened').addClass('closed');
		$('#'+id).css('display', 'none');
	}

}

</script>

</div></div>
</body></html>