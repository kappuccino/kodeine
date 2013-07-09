<?php

namespace Kodeine;

class group extends appModule{

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userGroupGet($opt=array()){

		if($opt['distinctParent'] && is_array($opt['id_group'])){
			foreach($opt['id_group'] as $e){
				$list[] = $this->userGroupGet(array('id_group' => $e));
			}

			$parent = array();
			foreach($list as $e){
				$parent = array_merge($parent, explode(',', $e['groupParent']));
			}

			foreach($parent as $idx => $e){
				if($e == '0') unset($parent[$idx]);
			}

			return $parent;
		}else
		if($opt['distinctChildren'] && is_array($opt['id_group'])){
			foreach($opt['id_group'] as $e){
				$list[] = $this->userGroupGet(array('id_group' => $e));
			}
			foreach($list as $e){
				$str .= $e['groupChildren'].',';
			}
			$children = explode(',', $str);
			return $children;
		}else
		if($opt['threadFlat']){

			$group = $this->userGroupGet(array(
				'profile'			=> $opt['profile'],
				'thread'			=> true,
				'mid_group'			=> $opt['mid_group'],
				'noid_group'		=> $opt['noid_group'],
			));

			$this->threadFlatWork = array();

			$group = $this->userGroupGet(array(
				'threadFlatWork'	=> true,
				'mid_group'			=> $opt['mid_group'],
				'noid_group'		=> $opt['noid_group'],
				'group'				=> $group,
				'level'				=> 0
			));

			return $this->threadFlatWork;

		}else
		if($opt['threadFlatWork']){

			foreach($opt['group'] as $e){
				$e['level'] = $opt['level'];
				$tmp = $e; unset($tmp['sub']);

				$this->threadFlatWork[] = $tmp;

				if(is_array($e['sub'])){
					$this->userGroupGet(array(
						'profile'			=> $opt['profile'],
						'threadFlatWork'	=> true,
						'mid_group'			=> $opt['mid_group'],
						'noid_group'		=> $opt['noid_group'],
						'group'				=> $e['sub'],
						'level'				=> ($opt['level'] + 1)
					));
				}
			}

			return true;
		}else
		if($opt['thread']){

			if($opt['noid_group'] > 0) $cond[] = " id_group != ".$opt['noid_group'];

			$mid 	= isset($opt['mid_group']) ? $opt['mid_group'] : 0;
			$cond[] = ($opt['profile']) ? "id_group IN(".$this->profile['group'].")" : "mid_group=".$mid;

			if(sizeof($cond) > 0) $where = "WHERE ".implode(' AND ', $cond);

			$group = $this->mysql->multi("SELECT * FROM k_group ".$where." ORDER by pos_group");

			foreach($group as $idx => $e){
				if($e['id_group'] != $opt['noid_group']){

					$group[$idx]['sub'] = $this->userGroupGet(array(
						'thread'		=> true,
						'mid_group'		=> $e['id_group'],
						'noid_group'	=> $opt['noid_group']
					));
				}
			}

			return $group;

		}else
		if($opt['id_group'] != ''){
			$dbMode = 'dbOne';
			$cond[] = "id_group=".$opt['id_group'];
		}else
		if($opt['mid_group'] != ''){
			$dbMode = 'dbMulti';
			$cond[] = "mid_group=".$opt['mid_group'];
		}else{
			$dbMode = 'dbMulti';
		}

		if(sizeof($cond) > 0) $where = "WHERE ".implode(" AND ", $cond)." ";

		$group = $this->$dbMode("SELECT * FROM k_group ".$where);

		if($dbMode == 'dbOne' && $group['id_group'] != ''){
			$group['groupFormLayout'] = json_decode(utf8_decode($group['groupFormLayout']), true);

			if(!is_array($group['groupFormLayout'])){

				$group['groupFormLayout'] = array(
					'tab' => array(
						'view0' => array(
							'label' => 'Defaut',
							'field' => array()
						)
					),
					'bottom' => array(

					)
				);
			}
		}



		if($opt['debug']) $this->pre($this->db_query, $this->db_error, $group);

		return $group;
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userGroupSet($id_group, $def){

		if(!$this->formValidation($def)) return false;

		if($id_group != NULL){
			$q = $this->dbUpdate($def)." WHERE id_group=".$id_group;
		}else{
			$last = $this->mysql->one("SELECT MAX(pos_group) AS m FROM k_group WHERE mid_group=".$def['k_group']['mid_group']['value']);
			$def['k_group']['pos_group'] = array('value' => ($last['m'] + 1));
			$q = $this->dbInsert($def);
		}

		@$this->mysql->query($q);
		if($this->db_error != NULL) return false;
		$this->id_group = ($id_group > 0) ? $id_group : $this->db_insert_id;

		$this->userGroupFamily();

		return true;
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userGroupRemove($id_group){
		$this->mysql->query("DELETE FROM k_group WHERE id_group=".$id_group);
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userGroupSelector($opt){

		$form  = '';
		$group = $this->userGroupGet(array(
			'profile'		=> $opt['profile'],
			'mid_group'		=> 0,
			'threadFlat'	=> true
		));

		if($opt['multi']){
			$value = is_array($opt['value']) ? $opt['value'] : array();

			$form .= "<select name=\"".$opt['name']."\" id=\"".$opt['id']."\" size=\"".$opt['size']."\" multiple style=\"".$opt['style']."\" ".$opt['events'].">";
			foreach($group as $e){
				$selected = in_array($e['id_group'], $value) ? ' selected' : NULL;
				$form .= "<option value=\"".$e['id_group']."\"".$selected.">".str_repeat(' &nbsp; &nbsp;', $e['level']).'&nbsp;'.$e['groupName']."</option>";
			}
			$form .= "</select>";
		}else
		if($opt['one']){
			$value = is_array($opt['value']) ? $opt['value'][0] : $opt['value'];

			$form .= "<select name=\"".$opt['name']."\" id=\"".$opt['id']."\" style=\"".$opt['style']."\" ".$opt['events'].">";
			foreach($group as $e){
				$selected = ($e['id_group'] == $value) ? ' selected' : NULL;
				$form .= "<option value=\"".$e['id_group']."\"".$selected.">".str_repeat(' &nbsp; &nbsp;', $e['level']).'&nbsp;'.$e['groupName']."</option>";
			}
			$form .= "</select>";
		}

		return $form;
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
// Mettre a jour les PARENT et CHILDREN et les sauver en base
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userGroupFamily(){

		$group = $this->userGroupGet(array(
			'threadFlat'	=> true,
			'debug'			=> true
		));

		foreach($group as $e){
			$tree = $this->userGroupFamilyParent($e);
			$tree = sizeof($tree) > 0 ? implode(',', array_reverse($tree)) : '';

			$this->mysql->query("UPDATE k_group SET groupParent='".$tree."' WHERE id_group=".$e['id_group']);
		}

		foreach($group as $e){
			$tree = $this->userGroupFamilyChildren($e);
			$tree = (sizeof($tree) > 0) ? implode(',', $tree) : '';

			$this->mysql->query("UPDATE k_group SET groupChildren='".$tree."' WHERE id_group=".$e['id_group']);
		}
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
// Trouver tous les PARENTS pour un GROUP
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userGroupFamilyParent($e, $line=array()){

		if(abs(intval($e['mid_group'])) > 0){
			$next = $this->userGroupGet(array(
				'id_group' => $e['mid_group']
			));

			$line[] = $e['mid_group'];
			return $this->userGroupFamilyParent($next, $line);
		}else{
			return $line;
		}
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
// Trouver tous les CHILDREN pour un GROUP
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userGroupFamilyChildren($e, &$line=array()){

		$children = $this->userGroupGet(array(
			'mid_group' => $e['id_group']
		));

		foreach($children as $child){
			$line[] = $child['id_group'];
			$this->userGroupFamilyChildren($child, $line);
		}

		return $line;
	}

}