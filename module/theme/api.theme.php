<?php

namespace Kodeine;

class theme extends appModule {

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function get($opt=array()){

		if($opt['id_theme'] > 0){
			$dbMode = 'dbOne';
			$cond[] = "k_theme.id_theme=".$opt['id_theme'];
		}else{
			$dbMode = 'dbMulti';
		}

		# Former les conditions
		#
		if(sizeof($cond) > 0) $where = "WHERE ".implode(" AND ", $cond);

		# Theme
		#
		$theme = $this->$dbMode("SELECT * FROM k_theme ".$where);

		if($opt['debug']) $this->pre($opt, $this->db_query, $this->db_error, $theme);

		return $theme;
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function set($id_theme, $def){

		if($id_theme > 0){
			$q = $this->dbUpdate($def)." WHERE id_theme=".$id_theme;
		}else{
			$q = $this->dbInsert($def);
		}

		@$this->mysql->query($q);
		if($this->db_error != NULL) return false;

		$this->id_theme = ($id_theme > 0) ? $id_theme : $this->db_insert_id;

		return true;
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function file($file){

		$inc = $this->helper->kTalkCheck($file)
			? $this->helper->kTalk($file)
			: '/theme/'.$this->kodeine['themeFolder'].'/'.$file;

		return USER.$inc;
	}

}