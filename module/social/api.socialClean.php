<?php

class socialClean extends social{

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function socialCleanUser(array $opt){

		$id_user = $opt['id_user'];
		if(intval($id_user) <= 0) return false;

		$me = $this->apiLoad('socialUser')->socialUserGet(array(
			'raw'     => true,
			'id_user' => $id_user
		));

		// POST /////////////////////////////////////////////////////////////////////

		$posts = $this->apiLoad('socialPost')->socialPostGet(array(
			'mid_socialpost' => '*',
			'id_user'        => $id_user,
			'noLimit'        => true
		));

		if($opt['trace']) echo "POST: ".count($posts)."\n";
		foreach($posts as $e){
			if($opt['trace']) echo "- HIDE POST ".$e['id_socialpost']."\n";

			$this->apiLoad('socialPost')->socialPostHide(array(
				'id_socialpost' => $e['id_socialpost'],
			));
		}
		if($opt['trace']) echo "\n";

		// EVENT ////////////////////////////////////////////////////////////////////

		$events_player = $this->apiLoad('socialEvent')->socialEventGet(array(
			'id_socialevent'	=> $me['userSocialEventMember']
		));

			if($opt['trace']) echo "EVENTS PLAYER: ".count($events_player)."\n";
			foreach($events_player as $e){
				if($opt['trace']) echo "- LEAVE ".$e['id_socialevent']."\n";
				$this->apiLoad('socialEvent')->socialEventMemberRemove(array(
					'id_socialevent' => $e['id_socialevent'],
					'user'           => $id_user
				));
			}
			if($opt['trace']) echo "\n";

		$events_owner = $this->apiLoad('socialEvent')->socialEventGet(array(
			'id_user' => $me['id_user']
		));

			if($opt['trace']) echo "EVENTS OWNER: ".count($events_owner)."\n";
			foreach($events_owner as $e){
				if($opt['trace']) echo "- REMOVE ".$e['id_socialevent']."\n";
				$this->apiLoad('socialEvent')->socialEventHide(array(
					'id_socialevent' => $e['id_socialevent']
				));
			}
			if($opt['trace']) echo "\n";

		// CIRCLE ///////////////////////////////////////////////////////////////////

		$circles_player = $this->apiLoad('socialCircle')->socialCircleGet(array(
			'id_socialcircle' => $me['userSocialCircleMember']
		));

			if($opt['trace']) echo "CIRCLE PLAYER: ".count($circles_player)."\n";
			foreach($circles_player as $e){
				if($opt['trace']) echo "- LEAVE ".$e['id_socialcircle']."\n";
				$this->apiLoad('socialCircle')->socialCircleMemberRemove(array(
					'id_socialcircle'	=> $e['id_socialcircle'],
					'user'				=> $id_user
				));
			}
			if($opt['trace']) echo "\n";

		$circles_owner = $this->apiLoad('socialCircle')->socialCircleGet(array(
			'id_user' => $id_user
		));

			if($opt['trace']) echo "CIRCLE OWNER: ".count($circles_owner)."\n";
			foreach($circles_owner as $e){
				if($opt['trace']) echo "- REMOVE ".$e['id_socialcircle']."\n";
			}
			if($opt['trace']) echo "\n";

		// HOOK /////////////////////////////////////////////////////////////////////

		$this->hookAction('socialCleanUser', $opt);

		return true;
	}

}
