<?php

class socialUser extends social{

function __clone(){}
function socialUser(){}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialUserGet($opt){

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep='socialUserGet() @='.json_encode($opt));

	if(intval($opt['id_user']) == 0){
		if($opt['debug']) $this->pre("ERROR: ID_USER (NUMERIC)", "GIVEN", var_export($opt['id_user'], true));
		return array();
	}

//	//	if($this->user['id_user'] > 0){
	if($opt['id_user'] > 0){
	
		$so = $this->dbOne("SELECT * FROM k_usersocial WHERE id_user=".$opt['id_user']);
		$me = $this->apiLoad('user')->userGet(array_merge(array(
			'id_user'	=> $opt['id_user'],
			'useMedia'	=> true
		), $opt));

		// array
		$as = array(
			'userSocialPostReply',
			'userSocialPostOwner',

			'userSocialPostRatePlus',

			'userSocialFollowerUser', 
	    	'userSocialFollowedUser', 

	    	'userSocialCircleOwner', 
	    	'userSocialCircleMember',
	    	'userSocialCirclePending',

	    	'userSocialForumWatcher',

	    	'userSocialEventOwner',
	    	'userSocialEventMember',
	    	'userSocialEventPending',
	    );
		foreach($as as $k){
			$so[$k] = ($so[$k] != '') ? json_decode($so[$k], true) : array();
		}

		// integer
		$ns = array(
			'userSocialPostRateMinus'
		);
		foreach($ns as $k){
			$so[$k] = intval($so[$k]);
		}

		$user = array_merge($me, $so);
	}else{
		$user = array();
	}

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep);
	
	return $user;
}







































} ?>