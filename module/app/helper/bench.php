<?php

	Kodeine\app::register('bench')->init();
	register_shutdown_function(array($app->bench, 'profiling'));
