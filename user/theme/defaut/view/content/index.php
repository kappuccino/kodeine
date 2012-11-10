<!DOCTYPE html> 
<html lang="<?php echo LOC ?>">
<head>
	<title></title>
	<meta name="description" content="" />
	<meta name="keywords" content="" />

	<?php include(UI.'/html-head.php') ?> 
</head>
<body class="body">


<div id="main">
	<div class="clearfix row-fluid show-grid">
		<div class="span3"><?php include(UI.'/menu.php') ?></div>

		<div class="span9">

			<h1>Home</h1>
			<?php echo HELLO_WORLD ?>

		</div>
	</div>
</div>





<?php include(UI.'/html-end.php'); ?>
</body></html>