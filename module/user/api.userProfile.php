<?php

namespace Kodeine;

class userProfile extends appModule{

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userProfileGet($opt=array()){

		if($opt['id_profile'] > 0){
			$dbMode = 'dbOne';
			$cond[] = "id_profile=".$opt['id_profile'];
		}else{
			$dbMode = 'dbMulti';
		}


		# Former les conditions
		#
		if(sizeof($cond) > 0) $where = "WHERE ".implode(" AND ", $cond);


		# PROFILE
		#
		$profile = $this->$dbMode("SELECT * FROM k_userprofile ".$where." ORDER BY profileName ASC");
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);


		#  PARAM
		#
		if($dbMode == 'dbMulti'){
			foreach($profile as $idx => $c){
				$profile[$idx]['profileRule'] = unserialize($profile[$idx]['profileRule']);
				if(!is_array($profile[$idx]['profileRule'])) $profile[$idx]['profileRule'] = array();
			}
		}else
		if($dbMode == 'dbOne'){
			$profile['profileRule'] = unserialize($profile['profileRule']);
			if(!is_array($profile['profileRule'])) $profile['profileRule'] = array();
		}

		return $profile;
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userProfileSet($id_profile, $def){

		if($id_profile > 0){
			$q = $this->dbUpdate($def)." WHERE id_profile=".$id_profile;
		}else{
			$q = $this->dbInsert($def);
		}

		@$this->mysql->query($q);
		if($this->db_error != NULL) return false;

		$this->id_profile = ($id_profile > 0) ? $id_profile : $this->db_insert_id;

		return true;
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userProfileCheckChapter($chapter){

		if(!is_array($chapter)) return array();

		foreach($chapter as $e){
			unset($autre);

			foreach($chapter as $a){
				if($a != $e) $autre[] = $a;
			}

			$me = $this->apiLoad('chapter')->chapterGet(array(
				'language'		=> 'fr',
				'id_chapter'	=> $e
			));

			foreach(explode(',', $me['chapterChildren']) as $c){
				if(@in_array($c, $autre)) $louche[] = $c;
			}
		}

		foreach($chapter as $e){
			if(!@in_array($e, $louche)) $rest[] = $e;
		}

		return is_array($rest) ? $rest : array();
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userProfileCheckCategory($category){

		if(!is_array($category)) return array();

		foreach($category as $e){
			unset($autre);

			foreach($category as $a){
				if($a != $e) $autre[] = $a;
			}

			$me = $this->apiLoad('category')->categoryGet(array(
				'language' 		=> 'fr',
				'id_category' 	=> $e
			));

			foreach(explode(',', $me['categoryChildren']) as $c){
				if(@in_array($c, $autre)) $louche[] = $c;
			}
		}

		foreach($category as $e){
			if(!@in_array($e, $louche)) $rest[] = $e;
		}

		return is_array($rest) ? $rest : array();
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userProfileCheckGroup($group){

		if(!is_array($group)) return array();

		foreach($group as $e){
			unset($autre);

			foreach($group as $a){
				if($a != $e) $autre[] = $a;
			}

			$me = $this->userGroupGet(array('id_group' => $e));

			foreach(explode(',', $me['groupChildren']) as $c){
				if(@in_array($c, $autre)) $louche[] = $c;
			}
		}

		foreach($group as $e){
			if(!@in_array($e, $louche)) $rest[] = $e;
		}

		return is_array($rest) ? $rest : array();
	}

}