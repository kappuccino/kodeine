<!DOCTYPE html> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
	<title></title>
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<link rel="stylesheet" type="text/css" media="all" href="/media/ui/css/style.php" />
	<?php include(MYTHEME.'/ui/html-head.php') ?>
</head>
<body class="body">

<div class="container_12 container clearfix">

	<div class="col grid_3 alpha">
		<?php include(MYTHEME.'/ui/menu.php') ?>
	</div>

	<div class="grid_9 omega center"><div class="center-item">

		
		<form method="post" action="."><?php
		
		
			echo "<h1>Survey: ".$survey['surveyName']."</h1>";
			echo "<input type=\"hidden\" name=\"id_survey\" value=\"".$survey['id_survey']."\" />";
			echo "<input type=\"hidden\" name=\"id_surveyslot\" value=\"".$id_surveyslot."\" />";
		
			echo "<fieldset>";
			echo "<legend>".$group['surveyGroupName']."</legend>";
			
			$query = $api->surveyQueryGet(array(
				'id_surveygroup' => $group['id_surveygroup']
			));
		
			
			foreach($query as $q){
				$color = array_key_exists($q['id_surveyquery'], $api->formErrorLog) ? 'red' : 'black';
		
				echo "<h3 style=\"color:".$color.";\">Query: ".$q['surveyQueryName']."</h3>";
				echo "<input type=\"hidden\" name=\"query[".$q['id_surveyquery']."]\" value=\"\" />";
				
				$items = $api->surveyQueryItemGet(array(
					'id_surveyquery' => $q['id_surveyquery']
				));
			
				$val = $api->surveyFormValue($_POST['query'][$q['id_surveyquery']]);
		
				if(sizeof($items) > 0){
					echo "<ul>";
					foreach($items as $i){
						$form	= $api->surveyFormElement($val, $q, $i);
		
						echo "<li>";
						echo $form;
						echo $i['surveyQueryItemName'];
						echo "</li>";
					}
					
					if($q['allow_other']){
		
						$dis = (is_array($val)
							? (array_key_exists('@', $val) ? NULL : 'disabled')
							: (($val == '@') ? NULL : 'disabled'));
						
						$chk = ($dis == 'disabled') ? '' : 'checked';
						$vue = is_array($val) ? $val['@'] : $val;
		
						echo "<li><input type=\"checkbox\" onClick=\"tog(this,".$q['id_surveyquery'].")\" ".$chk." />";
						echo "Autre";
						echo "<input type=\"text\" name=\"query[".$q['id_surveyquery']."][@]\" value=\"".$vue."\" id=\"other-".$q['id_surveyquery']."\" ".$dis." />";
						echo "</li>";
					}
					echo "</ul>";
				}else
				if($q['surveyQueryType'] == 'FREE'){
					echo $api->surveyFormElement($val, $q);
				}
		
			}
		
			echo "</fieldset>";
			
			
			$this->pre($api->formErrorLog);
		?>
		
		<p>
			<input type="submit" value="Valider"/>
		</p>
		
		</form>

		</div>

	</div>

</div>

<?php $this->themeInclude('ui/html-end.php'); ?>

<script>

	function tog(box, id_surveyquery){
		document.getElementById('other-'+id_surveyquery).disabled = (box.checked) ? false : true;
	}

</script>

</body></html>