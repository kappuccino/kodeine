<?php

	define('__daevel_start', microtime());
	ignore_user_abort(true);
	
	$r = getrusage();
	define('__daevel_rUser', bcadd(bcdiv($r['ru_utime.tv_usec'], 1000000, 3), $r['ru_utime.tv_sec'], 3));
	define('__daevel_rSys',  bcadd(bcdiv($r['ru_stime.tv_usec'], 1000000, 3), $r['ru_stime.tv_sec'], 3));
	unset($r);
	
	function __daevel_profiling(){

	  $tmp = explode(' ', __daevel_start);
	  $start = $tmp[1] . substr($tmp[0], 1);
	
	  $tmp = explode(' ', microtime());
	  $end = $tmp[1] . substr($tmp[0], 1);
	
	  $elapsed = round(bcmul(bcsub($end, $start, 6), 1000, 3));
	
	  $r = getrusage();
	  $rUser = bcsub(bcadd(bcdiv($r['ru_utime.tv_usec'], 1000000, 3), $r['ru_utime.tv_sec'], 3), __daevel_rUser, 3);
	  $rSys  = bcsub(bcadd(bcdiv($r['ru_stime.tv_usec'], 1000000, 3), $r['ru_stime.tv_sec'], 3), __daevel_rSys, 3);
	
#	  $msg = $_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI'].' '.connection_aborted().' execTime:'.$elapsed, 6, ' ', STR_PAD_LEFT).'ms rUser:'.$rUser.' rSys:'.$rSys;
	  $msg = $_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI'].' '.connection_aborted().' execTime:'.$elapsed.'ms rUser:'.$rUser.' rSys:'.$rSys;
	
		#  syslog(LOG_INFO, $msg);
		$mem = max(memory_get_peak_usage(true), memory_get_peak_usage(true)); 
		$mem = number_format((($mem  / 1024) / 1024), 2);
	
	  echo  "<br /><br /><br /><br />".$msg." MEM:".$mem."Mo";
	}
	

	register_shutdown_function('__daevel_profiling');
