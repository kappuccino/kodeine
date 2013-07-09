<?php

namespace Kodeine;

class type extends appModule{

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function get($opt=array()){

		if(BENCHME) $this->bench->marker($bmStep='typeGet() @='.json_encode($opt));

		$order 		= isset($opt['order']) 		? $opt['order'] 	: 'typePos';
		$direction	= isset($opt['direction'])	? $opt['direction']	: 'ASC';

		$dbMode     = 'multi';
		$cond       = array();
		$sqlWhere   = $sqlOrder = NULL;

		if($opt['cp']) $cond[] = "is_cp=1";

		if($opt['profile']){
			if(strlen(trim($this->profile['type'])) == 0) return array();
			$cond[] = "id_type IN(".$this->profile['type'].")";
		}

		if($opt['id_type'] > 0){
			$dbMode = 'one';
			$cond[] = "id_type=".$opt['id_type'];
		}else
		if($opt['typeKey'] != NULL){
			$dbMode = 'one';
			$cond[] = "typeKey='".addslashes($opt['typeKey'])."'";
		}

		if(sizeof($cond) > 0) $sqlWhere = " WHERE ".implode(" AND ", $cond)." ";

		if($dbMode == 'multi' && $order != NULL && $direction != NULL){
			$sqlOrder = "ORDER BY ".$order." ".$direction;
		}

		$type = $this->mysql->$dbMode("SELECT * FROM k_type " . $sqlWhere . $sqlOrder);

		if($dbMode == 'one'){
			$type['typeFormLayout'] = json_decode($type['typeFormLayout'], true);

			if(!is_array($type['typeFormLayout'])){

				$type['typeFormLayout'] = array(
					'tab' => array(
						'view0' => array(
							'label' => 'Défaut',
							'field' => array()
						)
					),
					'bottom' => array()
				);
			}

			$type['typeListLayout'] = json_decode($type['typeListLayout'], true);
			if(!is_array($type['typeListLayout'])) $type['typeListLayout'] = array();
		}

		if($opt['debug']) $this->pre($this->db_query, $this->db_error, $type);

		if(BENCHME) $this->bench->marker($bmStep);

		return $type;
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function set($id_type, $def){

		if(!$this->formValidation($def)) return false;

		if($id_type > 0){
			$q = $this->dbUpdate($def)." WHERE id_type=".$id_type;
		}else{
			$q = $this->dbInsert($def);
		}

		@$this->mysql->query($q);
		if($this->db_error != NULL) return false;
		$this->id_type = ($id_type > 0) ? $id_type : $this->db_insert_id;

		if($id_type == NULL){

			$pattern = "CREATE TABLE `%s` (
				`id_content` MEDIUMINT(64) NOT NULL ,
				`language`   CHAR(2) NOT NULL
			) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci";

			# Creation de la table pour les ITEMS
			#
			if($def['k_type']['is_gallery']['value']){

				// album
				$this->mysql->query(sprintf($pattern, 'k_contentalbum'.$this->id_type));
				$this->mysql->query("ALTER TABLE `k_contentalbum".$this->id_type."` ADD PRIMARY KEY (`id_content`, `language`)");
				$this->mysql->query("ALTER TABLE `k_contentalbum".$this->id_type."` ADD INDEX (`language`)");

				// items
				$this->mysql->query(sprintf($pattern, 'k_contentitem'.$this->id_type));
				$this->mysql->query("ALTER TABLE `k_contentitem".$this->id_type."` ADD PRIMARY KEY (`id_content`, `language`)");
				$this->mysql->query("ALTER TABLE `k_contentitem".$this->id_type."` ADD INDEX (`language`)");
			}

			# Content
			#
			else{
				$this->mysql->query(sprintf($pattern, 'k_content'.$this->id_type));
				$this->mysql->query("ALTER TABLE `k_content".$this->id_type."` ADD PRIMARY KEY (`id_content`, `language`)");
				$this->mysql->query("ALTER TABLE `k_content".$this->id_type."` ADD INDEX (`language`)");
			}
		}

		return true;
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function remove($id_type){

		if(intval($id_type) == 0) return false;

		# Remove CONTENTS
		#
		$ids = $this->mysql->multi("SELECT id_content FROM k_content WHERE id_type=".$id_type);
		if(sizeof($ids) > 0){
			$ids_ = implode(',', $this->dbKey($ids, 'id_content', true));

			// Supprimer tous les contents de ce type
			$this->mysql->query("DELETE FROM k_content			    WHERE id_type=".$id_type);

			// Supprimer les valeurs des content
			$this->mysql->query("DELETE FROM k_content			    WHERE id_content IN(".$ids_.")");
			$this->mysql->query("DELETE FROM k_contentdata 		    WHERE id_content IN(".$ids_.")");
			$this->mysql->query("DELETE FROM k_contentcomment 	    WHERE id_content IN(".$ids_.")");
			$this->mysql->query("DELETE FROM k_contentgroup		    WHERE id_content IN(".$ids_.")");
			$this->mysql->query("DELETE FROM k_contentversion        WHERE id_content IN(".$ids_.")");
			$this->mysql->query("DELETE FROM k_contentitem		    WHERE id_content IN(".$ids_.")");
			$this->mysql->query("DELETE FROM k_contentalbum		    WHERE id_content IN(".$ids_.")");

			// Supprimer les associations
			$this->mysql->query("DELETE FROM k_contentsearch		    WHERE id_content IN(".$ids_.")");
			$this->mysql->query("DELETE FROM k_contentgroup		    WHERE id_content IN(".$ids_.")");
			$this->mysql->query("DELETE FROM k_contentgroupbusiness  WHERE id_content IN(".$ids_.")");
			$this->mysql->query("DELETE FROM k_contentchapter        WHERE id_content IN(".$ids_.")");
			$this->mysql->query("DELETE FROM k_contentcategory       WHERE id_content IN(".$ids_.")");
		}

		# Remome TYPE
		#
		$this->mysql->query("DELETE FROM k_type			WHERE id_type=".$id_type);
		$this->mysql->query("DELETE FROM k_fieldaffect	WHERE id=".$id_type); // ID equivaut a ID_TYPE

		# Update TABLES
		#
		$this->mysql->query("DROP TABLE IF EXISTS k_content".$id_type);
		$this->mysql->query("DROP TABLE IF EXISTS k_contentitem".$id_type);
		$this->mysql->query("DROP TABLE IF EXISTS k_contentalbum".$id_type);

		# Update CACHE
		#
		$this->apiLoad('field')->fieldCacheBuild();

		return true;
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function setProfile($id_type){

		$id_pro  = intval($this->user['id_profile']);
		$profile = $this->mysql->one("SELECT profileRule FROM k_userprofile WHERE id_profile=".$id_pro);
		$rules	 = unserialize($profile['profileRule']);

		$ids   = $rules['id_type'];
		$ids[] = $id_type;
		$ids   = array_values(array_unique($ids));

		$rules['id_type'] = $ids;

		$this->apiLoad('user')->userProfileSet($this->user['id_profile'], array(
			'k_userprofile' => array(
				'profileRule' => array('value' => serialize($rules))
			)
		));
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function removeProfile($id_type){

		$id_pro  = intval($this->user['id_profile']);
		$profile = $this->mysql->one("SELECT profileRule FROM k_userprofile WHERE id_profile=".$id_pro);
		$rules	 = unserialize($profile['profileRule']);

		$ids = $rules['id_type'];
		foreach($ids as $n => $id){
			if($id == $id_type) unset($ids[$n]);
		}
		$ids = array_values(array_unique($ids));
		$rules['id_type'] = $ids;

		$this->apiLoad('user')->userProfileSet($id_pro, array(
			'k_userprofile' => array(
				'profileRule' => array('value' => serialize($rules))
			)
		));
	}
}