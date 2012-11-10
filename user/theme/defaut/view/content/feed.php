<?php

	# 1 Get hot news
	$hot = $this->apiLoad('content')->contentGet(array(
		'typeKey'	=> 'voiture',
		'debug'		=> false
	));
		
	# 2  Build the feed
	$rss = $this->apiLoad('rss');
	$rss->rssChanelAssign('title', 			'Titre du flux RSS');
	$rss->rssChanelAssign('link', 			'http://'.$_SERVER['HTTP_HOST']);
	$rss->rssChanelAssign('description',	'Description du flux RSS');
	$rss->rssChanelAssign('language', 		'fr-fr');
	$rss->rssChanelAssign('copyright',		date("Y").' '.$_SERVER['HTTP_HOST']);
	$rss->rssChanelAssign('webMaster',		$this->kafeine['siteMailerEmail']);
	$rss->rssChanelAssign('category', 		'Rss feed');
	$rss->rssChanelAssign('generator', 		'Kapuccino');
	$rss->rssChanelAssign('docs', 			'http://cyber.law.harvard.edu/rss/rss.html');

	$latest = 0;
	foreach($hot as $e){
		#$date 	= $this->helperDate($e['contentDateUpdate'], TIMESTAMP);
		#$latest = ($date > $latest) ? $date : $latest;

		$rss->rssItemOpen();
		$rss->rssItemAssign('title',		$e['contentName']);
		$rss->rssItemAssign('link',			'http://'.$_SERVER['HTTP_HOST'].$this->kTalk('/{l}/content/'.$e['contentUrl'].'.html'));
		$rss->rssItemAssign('description',	$e['s']);
		#$rss->rssItemAssign('pubDate', 		date('r', $this->helperDate($e['contentDateUpdate'], TIMESTAMP)));
		$rss->rssItemClose();
	}

	# 3 Define feed with latest content date
	$rss->rssChanelAssign('pubDate', 		date('r'));
	$rss->rssChanelAssign('lastBuildDate',	date('r'));

	# 4 Send feed structure to browser
	# Add ?debug&1215 (random number) to view feed source instead of feed itself
	$output = $rss->rssBuild();

	if(isset($_GET['debugRSS'])){
		header("Content-type: text/html; charset=iso-8859-1");
		$this->pre(htmlentities($output));
	}else{
		header("Content-type: application/rss+xml; charset=iso-8859-1");
		echo $output;
	}

?>