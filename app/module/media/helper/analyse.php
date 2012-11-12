<?php

	require(dirname(dirname(dirname(dirname(__FILE__)))).'/api/core.admin.php');
	$k = new filemanager();
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
	<title>Admin : <?php echo$_SERVER['SERVER_NAME']?> - Filemanager</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<link rel="stylesheet" type="text/css" href="../kafeine/ressource/css.kafeine.css" />

	<script type="text/javascript" src="../kafeine/ressource/mootools/mootools-1.2.1-core.js"></script>
	<script type="text/javascript" src="../kafeine/ressource/mootools/mootools-1.2.1-more.js"></script>
</head>
<body style="padding:5px;">

<div id="all">
	Analyse du dossier : <?php echo $_GET['folder'] ?>, <span id="total">?/?</span> Fichier en cours : <span id="legend">Analyse...</span>
</div>


<script>
	current		= 0;
	max			= 0;
	list 		= [];
	stepWidth	= 1;

	new Request.JSON({
		url: 'content/lib.analyse.php',
  		headers: {'contentType': 'text/html'},
		onComplete:function(data){
			if(data.max != null){
				max = data.max;
				$('total').set('html', '?/'+max);
			}

			if(data.files != null){
				list = data.files;
				launchTodo(0);
			}

		}
	}).get({'folder':'<?php echo $_GET['folder'] ?>'});


	function launchTodo(index){
		if(list[index] != null){
			current++;
			$('legend').set('html', list[index]);
			$('total').set('html', current+'/'+max);

			(function(){
				new Request.JSON({
					url: 'content/lib.analyse.php',
					headers: {'contentType': 'text/html'},
					onComplete:function(data){
						launchTodo(index + 1);		
					}
				}).get({'file':list[index]});

			}).delay(100);

		}else{
			$('all').set('html', 'Analyse terminée');
		}
	}

</script>

</body>
</html>