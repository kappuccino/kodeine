<?php
	$data = $app->apiLoad('newsletter')->newsletterGet(array(
		'id_newsletter' => $_GET['id_newsletter']
	));
	$mails	= $app->apiLoad('newsletter')->newsletterPoolPopulation($data['id_newsletter']);
	$rest	= sizeof($mails);
	$chunk	= 200;
	$mails	= ceil(sizeof($mails) / $chunk);

?><!DOCTYPE html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

	<?php include(COREINC.'/head.php'); ?>
	<link rel="stylesheet" type="text/css" media="all" href="../../ui/css/newsletter.css" />
</head>
<body>
	
<header><?php
	include(COREINC.'/top.php');
	include(dirname(dirname(__DIR__)).'/ui/menu.php');
    $step = 'send';
    include(dirname(dirname(__DIR__)).'/ui/steps.php');
?></header>	

<div id="app">
	<div class="wrapper">

<table border="0" cellpadding="0" cellspacing="0" width="900" align="center" style="margin-top:40px;">
	<tr>
		<td width="350" align="right" style="padding-right:30px;">
			Il y a <span style="font-size:22px; color:#666; vertical-align: middle;" id="r"><?php echo $rest ?></span> mails en attente de transfert
		</td>
		<td width="200">
			<div id="bar" style="width:200px; background: #CCCCCC; 						-moz-border-radius:10px; -webkit-border-radius:10px;">
				<div id="progress" style="width:20px; height:20px; background:#058dc7;  -moz-border-radius:10px; -webkit-border-radius:10px;"></div>
			</div>
		</td>
		<td width="350" align="left" style="padding-left:30px;">
			Actuellement <span style="font-size:22px; color:#666; vertical-align: middle;" id="s">0</span> mails transf&eacute;r&eacute;s
		</td>
	</tr>
	<tr>
		<td colspan="3" align="center" height="50">
			<a href="#" onclick="sendToCloud();" id="go" class="button rButton">Transf&eacute;rer cette campagne sur le cloudapp</a>
			<span id="tmp" style="display:none;">Transfert en cours <b style="color:red;">NE FERMER PAS CETTE FENETRE</b></span>
			<a href="../../analytic?id_newsletter=<?php echo $_GET['id_newsletter'] ?>" id="analytic" style="display:none" class="btn btn-mini">Afficher les stats</a>
		</td>
	</tr>
</table>


</div>
</div>

<?php include(COREINC.'/end.php'); ?>
<script>

	current = 0;
	sent	= 0;
	chunk	= <?php echo $chunk ?>;
	rest	= <?php echo $rest; ?>;
	total	= <?php echo $mails ?>;

	function sendToCloud(){

		$('#go').css('display', 'none');
		$('#tmp').css('display', 'inline');


		$.ajax({
			'url' : 'push-ajax',
			'dataType' : 'json',
			'data' : {	'send': true,
						'current': current,
						'chunk': chunk,
						'id_newsletter': <?php echo $data['id_newsletter'] ?>
					 }	
		}).done(function(data) {
			
			
			if(data.already){
				$('#tmp').html('Vous avez d&eacute;j&agrave; envoy&eacute; cette campagne');
				return true;
			}
			
			rest = rest - chunk;
			if(rest < 0) rest = 0;
			$('#r').html(rest);

			sent = sent + data.pushed;
			$('#s').html(sent);

			var percent = ((current == 0) ? 0 : (current/total));
			var percent = percent; 
			var width	= Math.round(200 * percent);

			$('#progress').css('width', width+'px');

			current++;
			
			if(current >= total){
				finished();
			}else{
				setTimeout(function() {
					sendToCloud();
				}, 1000)
			}
		});
	}
	
	function finished(){
		$('#progress').css('width', '200px');

		$.ajax({
			'url' : 'push-ajax',
			'data' : {'id_newsletter' : <?php echo $data['id_newsletter'] ?>, 'end' : true}
		}).done(function(d) {
			$('#tmp').css('display', 'none');
			$('#analytic').css('display', 'inline');
		});
			
	}

</script>

</body></html>