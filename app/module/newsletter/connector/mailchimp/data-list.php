<?php
	$api	= $app->apiLoad('newsletter');
	$pref	= $app->configGet('newsletter');

	$apiConnector	= $app->apiLoad('newsletterMailChimp');

	if($_REQUEST['id_newsletter'] != NULL){
		$data = $app->apiLoad('newsletter')->newsletterGet(array(
			'id_newsletter' 	=> $_REQUEST['id_newsletter']
		));

		$title = $data['newsletterName'];
	}else{
		$title = 'Nouvelle newsletter';
	}


?><!DOCTYPE html>
<head>
	<title>Kodeine</title>
	<?php include(COREINC.'/head.php'); ?>
	
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/newsletter.css" /> 
</head>

<body>

<header><?php
	include(COREINC.'/top.php');
	include(dirname(dirname(__DIR__)).'/ui/menu.php');
?></header>

<div id="app">

<?php
	if($message == NULL && $_GET['message'] != NULL) $message = urldecode($_GET['message']);
	if($message != NULL){
		list($class, $message) = $app->helperMessage($message);
		echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
	}
?>

<form action="data-list?id_newsletter=<?php echo $data['id_newsletter'] ?>" method="post" id="data" enctype="multipart/form-data">

	<input type="hidden" name="action" value="1" />
	<input type="hidden" name="id_newsletter" value="<?php echo $data['id_newsletter'] ?>" />
	<input type="hidden" name="do" id="do" value="" />

	<table cellpadding="5" width="100%">
		<tr>
			<td height="30" colspan="2">
				<?php if($data['newsletterSendDate'] == NULL){ ?>
				<a href="#" id="send" onclick="return false;" class="btn btn-mini">Envoyer sur MailChimp</a>
				<a href="data?id_newsletter=<?php echo $_REQUEST['id_newsletter'] ?>" class="btn btn-mini">Annuler et revenir à l'éditeur</a>
				<?php } if($_REQUEST['id_newsletter'] > 0){ ?>
				<a href="preview?id_newsletter=<?php echo $_REQUEST['id_newsletter'] ?>" class="btn btn-mini" target="_blank">Prévisualiser</a>
				<?php } if($data['newsletterSendDate'] != NULL){ ?>
				<a href="analytic?id_newsletter=<?php echo $_REQUEST['id_newsletter'] ?>" class="btn btn-mini">Consulter les statistiques</a>
				<?php } ?>
			</td>
		</tr>
	</table>
	
	<?php if($data['newsletterSendDate'] != NULL){ ?>
		
		<div class="message">La newsletter a déjà été envoyée sur MailChimp</div>
		
	<?php } else { ?>
	
		<?php 
			unset($api);				 
			//$app->pre($apis);
			$lists	= $apiConnector->listGet();
			//$app->pre($lists);
		?>
			Sélectionner une liste<br /><select name="id_newsletterList" id="id_newsletterList" style="width:250px;">
				<option value=""></option>
		<?php
			foreach($lists['data'] as $list) {		
		?>
				<option value="<?php echo $list['id']; ?>">
					<?php echo $list['name'].' ('.$list['stats']['member_count'].')'; ?>
				</option>
		<?php
			}				
		?>
		
		
		
			</select>
			
			<div id="groups"></div>
			
			
	<?php } ?>
			
			

</form>


</div>

<?php include(COREINC.'/end.php'); ?>

<script>
$(document).ready(function() {

	$('#id_newsletterList').change(function() {
		$.ajax({
			'url' : 'connector/mailchimp/get-groups',
			'data' : {'id' : $(this).val() }
		}).done(function(d) {
			$('#groups').html(d);
		});
		
	});
	
	$('#send').click( function() {
		if(confirm("Etes vous sur de vouloir envoyer cette newsletter sur MailChimp ?")) {
			$.ajax({
				'url' : 'connector/mailchimp/push',
				'data' : {'id_newsletter' : <?php echo $data['id_newsletter'] ?>, 'id_newsletterList' : $('#id_newsletterList').val(), 'listInterestGroupings' : $('#listInterestGroupings').val() }
			}).done(function(d) {
				//alert('done : '+d);
				if(d == '1') {
					document.location = 'data-list?id_newsletter=<?php echo $data['id_newsletter'] ?>';
				}else {
					alert(d);
				}
			});
		
		}else {
			return false;
		}
	});
});
</script>
</body></html>