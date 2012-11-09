<!DOCTYPE html>
<html lang="fr">
<head>
	<title>Envoyer des images</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

	<?php include(COREINC.'/head.php'); ?>
	<script src="/app/module/core/ui/_jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="/admin/media/ui/_uploadifive/jquery.uploadifive-v1.0.js"></script>
	<link rel="stylesheet" type="text/css" href="/admin/media/ui/_uploadifive/uploadifive.css">
	<style>
		body {
			position: absolute;
			top: 0;
			left: 0;
			bottom: 0;
			right: 0;
		}
		
	</style>
	<script>
		function rel(){
			parent.opener.folderNav('<?php echo $_REQUEST['f'] ?>');
		}
	</script>
</head>
<body style="background: white;">

<div class="uploadcontainer clearfix">
	<form id="uploadembed">
		<div class="left clearfix">
			<input id="file_upload" name="file_upload" type="file" multiple="true"><br />
			<a class="btn" href="javascript:$('#file_upload').uploadifive('upload')">Envoyer les fichiers</a>
		</div>
		<div id="queue" class="clearfix">
		</div>
	</form>
</div>

	<script type="text/javascript">
		$(function() {
			$('#file_upload').uploadifive({
				'buttonText'   : 'Parcourir',
				'auto'         : false,
				'formData'     : {'test' : 'something'},
				'queueID'      : 'queue',
				'uploadScript' : 'upload-action?f=<?php echo $_REQUEST['f'] ?>',
				'onUploadComplete' : function(file, data) {

				}
			});
		});
	</script>
</body>
</html>