<?php
	/*require(dirname(dirname(__FILE__)).'/api/core.admin.php');
	$app = new coreAdmin();*/

	if(!$app->userIsAdmin) header("Location: ./");

	$api  = $app->apiLoad('newsletter');
	$pref = $app->configGet('newsletter');

	$rest = new newsletterREST($pref['auth'], $pref['passw']);

	$data = $api->newsletterGet(array(
		'id_newsletter' => $_REQUEST['id_newsletter']
	));

	$list = $rest->request('/controller.php', 'POST', array(
		'list'				=> true,
		'id_newsletter'		=> $data['id_newsletter'],

		'campaingOpened'	=> isset($_GET['campaingOpened']),
		'campaingBounced'	=> isset($_GET['campaingBounced']),
		'notSeen'			=> isset($_GET['notSeen']),
		'clicked'			=> isset($_GET['clicked']),
		'unsubscribed' 		=> isset($_GET['unsubscribed']),
	));

	$lis_ = $list;
	$list = json_decode($list, true);

	if(is_array($list['list']) && isset($_GET['clean'])){

		// Flag MAIL
		if($list['mode'] == 'campaingBounced'){
			$flag = 'BOUNCE';
		}else
		if($list['mode'] == 'notSeen'){
			$flag = 'ERROR';
		}
		
		// Clean list
		$api = $app->apiLoad('newsletter');
		foreach($list['list'] as $e){

			$api->newsletterUnsubscribe(array(
				'debug'		=> 0,
				'listAll'	=> true,
				'email' 	=> $e['mail']
			));

			$app->dbQuery("UPDATE k_newslettermail SET flag='".$flag."' WHERE mail = '".$e['mail']."'");
			#$app->pre($app->db_query, $app->db_error);
		}

		header("Location: newsletter.analytic.data.php?id_newsletter=".$_GET['id_newsletter']."&".$list['mode']);
		exit();
	}

?><!DOCTYPE html>


<head>
	<?php include(COREINC.'/head.php'); ?>
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/analytic.css" />
</head>

<body>
	
<header><?php
	include(COREINC.'/top.php');
    include(__DIR__ . '/ui/menu.php')
?></header>

<div id="app">
	

<div style="width:900px; margin:0 auto; padding-top:20px;">

	<?php if($data['newsletterSendDate'] != NULL){ $colspan = 4; ?>
		
		<h3 class="campaignName"><?php echo $data['newsletterTitle'] ?></h3>		
		<a href="analytic?id_newsletter=<?php echo $data['id_newsletter'] ?>">Revenir a la vue d'ensemble</a>

		<?php 
			if(isset($_GET['campaingBounced']) OR isset($_GET['notSeen'])){
				echo "<p>Les mails ci-dessous n'ont jamais rec&ccedil;us votre campagne, souhaitez-vous les <a href=\"#\" onclick=\"clean()\">supprimer de vos listes d'envois ?</a></p>";
			}
		?>

		<table border="0" cellpadding="0" cellspacing="0" width="100%" class="listing" style="margin-top:40px;">
			<thead>
				<tr>
					<th width="50">&nbsp;</th>
					<th>Email</th>
					<?php if($list['list'][0]['ccc']){  echo "<th width=\"150\">Compte</th>";	$colspan--; } ?>
					<?php if($list['list'][0]['date']){ echo "<th width=\"200\">Date</th>";		$colspan--; } ?>
				</tr>
			</thead>
			<tbody><?php
				$tmp = array();
				foreach($list['list'] as $e){
					$tmp[] = "'".$e['mail']."'";
				}
				if(sizeof($tmp) > 0) {
					$indb = $app->dbMulti("SELECT * FROM k_newslettermail WHERE mail IN(".implode(',', $tmp).")");
					unset($tmp);
					foreach($indb as $e){
						if($e['flag'] == 'VALID') $tmp[] = $e['mail'];					
					}
					$indb = is_array($tmp) ? $tmp : array();
	
					foreach($list['list'] as $e){
						echo "<tr>";
						
						echo "<td>";
						echo in_array($e['mail'], $indb) ? 'OUI' : 'NON';
						echo "</td>";
	
						echo "<td>".$e['mail']."</td>";
						if($e['ccc'] != '') 	echo "<td>".$e['ccc']."</td>";
						if($e['date'] != '')	echo "<td>".$e['date']."</td>";
	
						echo "</tr>";
					}
				}
			?></tbody>
			<tfoot>
				<tr>
					<td colspan="<?php echo $colspan; ?>">&nbsp;</td>
				</tr>
			</tfoot>
		</table>

	<?php }else{ ?>
	
		<div style="font-weight:bold; font-size:14px; text-align:center; padding-top:50px; color:#808080;">
			Cette newsletter n'a pas &eacute;t&eacute; encore envoy&eacute;, ou bien aucune statistiques ne sont encore disponibles
		</div>
	
	<?php } ?>

</div>

<?php include(COREINC.'/end.php'); ?>
<script>

function clean(){
	
	if(confirm("Confirmez-vous cette suppression ?")){
		document.location = document.location + '&clean';
	}
	
}

</script>

</body></html>