<?php
	if(!$app->userIsAdmin) header("Location: ./");

	$api  = $app->apiLoad('newsletter');
	$pref = $app->configGet('newsletter');

	$rest = new newsletterREST($pref['auth'], $pref['passw']);

?><!DOCTYPE html>
<head>
	<?php include(COREINC.'/head.php'); ?>

	<style>
		h1{
			font-weight: 100;
		}
		.month td{
			background: #7e96b5;
			font-weight: bold; font-size: 13px; color: #FFF;
		}
		.day td{
			font-size: 11px;
		}
			.day td.n{
				padding-left: 20px;
			}
		table{
			border: 1px solid #7e96b5;
		}
	</style>
</head>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<body>

<div id="app"><div class="wrapper"><?php

	$usage	= $rest->request('/controller.php', 'POST', array('accountActivity'	=> true));
	$usage	= json_decode($usage, true);
	$raw	= $usage['raw'];

	if($usage['success'] && is_array($raw)){

		foreach($raw as $r){
			$all[$r['year']][$r['month']][$r['day']] = $r['sent'];
		}	

		foreach($all as $year => $monthes){
			echo "<h1>".$year."</h1><br />";

			echo "<table border=\"0\" cellpadding=\"5\" cellspacing=\"0\" width=\"100%\" align=\"center\">";
			foreach($monthes as $month => $days){

				echo "<tr class=\"month\">";
				echo "<td width=\"50%\">".ucfirst(strftime("%B", $app->helperDate($year.'-'.((strlen($month) == 1) ? '0'.$month : $month).'-01', TIMESTAMP)))."</td>";
				echo "<td>".array_sum($days)."</td>";
				echo "</tr>";
				
				echo "<tr class=\"day\">";
				foreach($days as $day => $sent){
					echo "<td class=\"n\">".$day."</td>";
					echo "<td>".$sent."</td>";
				}
				echo "</tr>";
			}
			echo "</table>";



		}
	}

?></div>
</div>

<?php
	#$app->pre($all);
?>

</body></html>