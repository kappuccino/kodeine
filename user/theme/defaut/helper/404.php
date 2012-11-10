<?php

	header("HTTP/1.0 404 Not Found");

?><!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html>
<head>
	<title>404 Not Found</title>
</head>
<body>
	<h1>Internal 404 Not Found</h1>
	<p>CoreApp could not find the requested file - Hope this dump could be usefull</p>

	<pre><? 
		unset($this->kodeine['configMailTo'], $this->kodeine['configMailCc'], $this->kodeine['configMailBcc']);
	#	print_r($this->kodeine);

		echo "\ncontroller	".$this->kTalk($controller);
		echo "\nview		".$this->kTalk($view);

	?></pre>

	<hr>
	<address>Kappuccino - <?= $this->appRelease(); ?></address>
</body>
</html>