<?php

class socialFollow extends social{


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialFollowerGet($opt=array()){

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep='socialFollowerGet() @='.json_encode($opt));

	if($opt['debug']) $this->pre("[OPT]", $opt);

	$id_user = $opt['id_user']; if(intval($id_user) <= 0) return array();

	# GET
	#
	$data = $this->dbMulti("SELECT id_followed FROM k_userfollow WHERE id_follower=".$id_user);
	foreach($data as $n => $d){
		$data[$n] = $d['id_followed'];
	}

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep);

	return $data;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialFollowIt($opt){

	if($opt['debug']) $this->pre("[OPT]", $opt);

	$follower = $opt['id_follower']; 	if(intval($follower) <= 0) return array();
	$followed = $opt['id_followed']; 	if(intval($followed) <= 0) return array();

	if($opt['undo'] == true OR $opt['undo'] == '1'){
		$query	= "DELETE FROM k_userfollow WHERE id_follower=".$follower." AND id_followed=".$followed;
		$remove = true;
	}else{
		$query	= "INSERT IGNORE INTO k_userfollow (id_follower, id_followed, timeline) VALUES (".$follower.",".$followed.", ".time().")";
	}

	$this->dbQuery($query);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);

	# Remove the FOLLOW LINK
	#
	if($remove){
		$myCircles = $this->dbMulti("SELECT id_socialcircle FROM k_socialcircle WHERE id_user=".$follower);
		foreach($myCircles as $e){
			$this->dbQuery("DELETE FROM k_socialcircleuser WHERE id_socialcircle=".$e['id_socialcircle']." AND id_user=".$follower);
		}

		// Remove Activity & Notification
		$this->apiLoad('socialActivity')->socialActivitySet(array(
			'debug'					=> false,
			'remove'				=> true,
			'id_user'				=> $follower,
			'socialActivityKey'		=> 'follow',
			'socialActivityId'		=> $followed
		));
	}else{
		// Add Activity + Notification
		$this->apiLoad('socialActivity')->socialActivitySet(array(
			'debug'					=> false,
			'id_user'				=> $follower,		// ACTIVITY au nom du FOLLOWER
			'notification'			=> true,
			'notificationUser'		=> $followed, 		// Notifier le FOLLLOWED qu'il est suivit
			'socialActivityKey'		=> 'follow',
			'socialActivityId'		=> $followed,
			'socialActivityFlag'	=> 'FOLLOW'
		));
	}

	# Update COUNT
	#
	$this->socialFollowFix(array('debug' => false, 'id_user' => $follower));
	$this->socialFollowFix(array('debug' => false, 'id_user' => $followed));
	
	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialFollowedGet($opt=array()){

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep='socialFollowedGet() @='.json_encode($opt));

	if($opt['debug']) $this->pre("[OPT]", $opt);

	$id_user = $opt['id_user']; if(intval($id_user) <= 0) return array();

	$data = $this->dbMulti("SELECT id_follower FROM k_userfollow WHERE id_followed=".$id_user);
	foreach($data as $n => $d){
		$data[$n] = $d['id_follower'];
	}

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep);

	return $data;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialFollowFix($opt=array()){

	if($opt['debug']) $this->pre("[OPT]", var_export($opt, true));

	$id_user = $opt['id_user'];
	if(intval($id_user) <= 0){
		if($opt['debug']) $this->pre("ERROR: ID_USER (NUMERIC)", "GIVEN", var_export($id_user, true));
		return false;
	}

	# GET
	#
	$er = array();
	$ed = array();

	foreach($this->dbMulti("SELECT id_followed FROM k_userfollow WHERE id_follower=".$id_user." ORDER BY timeline ASC") as $n => $d){
		$er[] = intval($d['id_followed']);
	}

	foreach($this->dbMulti("SELECT id_follower FROM k_userfollow WHERE id_followed=".$id_user." ORDER BY timeline ASC") as $n => $d){
		$ed[] = intval($d['id_follower']);
	}

	$countER = sizeof($er);				$jsonER = json_encode($er);	
	$countED = sizeof($ed);				$jsonED = json_encode($ed);	

	# SAVE
	#
	$query	= $this->dbInsert(array('k_usersocial' => array(
		'id_user'					=> array('value' => $id_user),
		'userSocialFollowerUser' 	=> array('value' => $jsonER),
		'userSocialFollowerCount' 	=> array('value' => $countER),
		'userSocialFollowedUser' 	=> array('value' => $jsonED),
		'userSocialFollowedCount' 	=> array('value' => $countED),
	)));

	$query .= "\nON DUPLICATE KEY UPDATE\n".
		"userSocialFollowerUser='".$jsonER."',\n".
		"userSocialFollowerCount=".$countER.",\n".
		"userSocialFollowedUser='".$jsonED."',\n".
		"userSocialFollowedCount=".$countED;

	$this->dbQuery($query);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);
}






































} ?>