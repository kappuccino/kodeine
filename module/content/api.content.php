<?php

class content extends coreApp {

function __clone(){}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function contentGet($opt=array()){

		if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep='contentGet() @='.json_encode($opt));

		if($opt['debug']) $this->pre("[OPTION]", $opt);

		# Shortcourt
		#
		if($opt['raw']){
			if(!isset($opt['useField']))		$opt['useField']		= false;
			if(!isset($opt['human'])) 			$opt['human']			= false;
			if(!isset($opt['useChapter'])) 		$opt['useChapter']		= false;
			if(!isset($opt['useGroup'])) 		$opt['useGroup']		= false;
			if(!isset($opt['assoChapter'])) 	$opt['assoChapter']		= true;
			if(!isset($opt['assoCategory']))	$opt['assoCategory']	= true;
			if(!isset($opt['assoGroup']))		$opt['assoGroup']		= true;
			if(!isset($opt['assoSearch']))		$opt['assoSearch']		= true;
			if(!isset($opt['assoShop']))		$opt['assoShop']		= true;
			if(!isset($opt['assoSocialForum']))	$opt['assoSocialForum']	= true;
			if(!isset($opt['contentSee']))		$opt['contentSee']		= 'ALL';
		}

		# Gerer les OPTIONS :: valeurs par defaut
		#
		$id_group			= isset($opt['id_group']) 			? $opt['id_group'] 			: $this->kodeine['id_group'];
		$id_chapter			= isset($opt['id_chapter']) 		? $opt['id_chapter'] 		: $this->kodeine['id_chapter'];
		$language			= isset($opt['language']) 			? $opt['language'] 			: $this->kodeine['language'];
		$country            = isset($opt['country']) 			? $opt['country'] 			: $this->kodeine['country'];
		$useField			= isset($opt['useField']) 			? $opt['useField']			: true;
		$useGroup			= isset($opt['useGroup']) 			? $opt['useGroup']			: 'checkType';
		$useChapter			= isset($opt['useChapter']) 		? $opt['useChapter']		: 'checkType';
		$useSocialForum		= isset($opt['useSocialForum'])		? $opt['useSocialForum']	: 'checkType';
		$useShop			= isset($opt['id_shop'])			? $opt['id_shop']			: false;
		$chapterThrough		= isset($opt['chapterThrough'])		? $opt['chapterThrough']	: false;
		$categoryThrough	= isset($opt['categoryThrough'])	? $opt['categoryThrough']	: false;
		$albumThrough		= isset($opt['albumThrough'])		? $opt['albumThrough']		: false;
		$groupThrough		= isset($opt['groupThrough'])		? $opt['groupThrough']		: false;
		$human				= isset($opt['human'])				? $opt['human']				: true;
		$searchLink			= isset($opt['searchLink']) 		? $opt['searchLink']		: 'OR';
		$noLimit			= isset($opt['noLimit'])			? $opt['noLimit']			: false;
		$limit				= ($opt['limit'] != '') 			? $opt['limit']				: 30;
		$offset				= ($opt['offset'] != '')			? $opt['offset']			: 0;
		$opt['searchMode']	= ($opt['searchMode'] != NULL) 		? $opt['searchMode'] 		: 'OR';

		# Security
		#
		if(strlen($language) != 2) $language = 'fr';
		if(strlen($country)  != 2) $country  = 'fr';

		# Trouver le CONTENT d'apres l'URL ou ID
		#
		$get = "SELECT * FROM k_contentdata INNER JOIN k_content ON k_contentdata.id_content = k_content.id_content";
		if($opt['contentUrl'] != NULL){
			$data = $this->dbOne($get." WHERE contentUrl='".$opt['contentUrl']."' AND language='".$language."'");
		}else
		if($opt['id_content'] != NULL && !is_array($opt['id_content'])){
			$data = $this->dbOne($get." WHERE k_contentdata.id_content=".$opt['id_content']." AND language='".$language."'");
		} unset($get);

		if(isset($data)){
			if($data['id_content'] == NULL){
				if($opt['debug']) $this->pre("contentGet IS NULL", $opt);
				return array();
			}else
			if($data['is_album']){
				$opt['is_album'] = true;
			}else
			if($data['is_item']){
				$opt['is_item'] = true;
			}

			$opt['id_type'] 	= $data['id_type'];
			$opt['id_content']	= $data['id_content'];

			$cond[]	= "k_content.id_content=".$data['id_content'];
			$dbMode	= 'dbOne';
		}else{
			$dbMode	= 'dbMulti';
		}

		# Detecter si je suis en mode IS_ITEM ou IS_ALBUM
		#
		$is_item	= is_bool($opt['is_item'])  ? $opt['is_item']  : (($data['is_item']  === '1') ? true : false);
		$is_album	= is_bool($opt['is_album']) ? $opt['is_album'] : (($data['is_album'] === '1') ? true : false);
		if($is_item && $is_album){
			if($opt['debug']) $this->pre("Fatal Error : IS_ITEM=TRUE + IS_ALBUM=TRUE !");
			return array();
		}

		# Trouver le TYPE
		#
		$type = $this->apiLoad('type')->typeGet(array(
			'id_type'	=> $opt['id_type'],
			'typeKey'	=> $opt['typeKey']
		));

		// Check
		if($type['id_type'] == NULL){
			if($opt['debug']) $this->pre("Fatal Error : TYPE NOT DEFINED, OR UNKNOWN");
			return array();
		}

		// Affecter les bon type de maniere automatique
		if($useGroup 		=== 'checkType') $useGroup			= ($type['use_group']		=== '1');
		if($useChapter 		=== 'checkType') $useChapter		= ($type['use_chapter']		=== '1');
		if($useSocialForum 	=== 'checkType') $useSocialForum	= ($type['use_socialforum'] === '1');


		# Demander les FIELDS pour ce CONTENT
		#
		$field = $this->apiLoad('field')->fieldGet(array(
			'id_type'		=> $type['id_type'],
			'albumField'	=> $is_album,
			'itemField'		=> $is_item
		));

		foreach($field as $f){
			$param = json_decode($f['fieldParam'], true);

			$fieldKey[$f['fieldKey']]= $f;
			if($f['is_search'])					$fieldSearch[]	 = $f;
			if($f['fieldType'] == 'user')		$fieldAssoUser[] = $f;
			if($f['fieldType'] == 'dbtable')	$fieldAssoDb[]	 = $f;

			if($f['fieldType'] == 'content' && $f['fieldContentType'] > 0 && $param['type'] == 'solo'){
				$fieldAssoContentSingle[] = $f;
			}
			if($f['fieldType'] == 'content' && $f['fieldContentType'] > 0 && $param['type'] != 'solo'){
				$fieldAssoContentMulti[] = $f;
			}
		}
		unset($param);



		# Gerer le CHAPTER lie a ce CONTENT
		#
		if($id_chapter > 0){
			if(array_key_exists($id_chapter, $this->kodeine['chaptersIds'])){
				$chapter = $this->kodeine['chaptersIds'][$id_chapter];
			}else{
				$chapter = $this->apiLoad('chapter')->chapterGet(array(
					'id_chapter'	=> $id_chapter,
					'language'		=> $language
				));
			}

			if($chapter['id_chapter'] != $id_chapter){
				if($opt['debug']) $this->pre("No CHAPTER found by ID : ".$id_chapter);
				return array();
			}
		}
		if(is_array($chapter)) $id_chapter = ($chapterThrough) ? " IN(".$chapter['chapterChildren'].")" : "=".$chapter['id_chapter'];


		# Gerer le SOCIAL FORUM lie a ce CONTENT
		#
		if(array_key_exists('id_socialforum', $opt)){
			if(is_array($opt['id_socialforum']) && sizeof($opt['id_socialforum']) > 0){
				$cond[] = "k_contentsocialforum.id_socialforum IN(".implode(', ', $opt['id_socialforum']).")";
			}else
			if(intval($opt['id_socialforum']) > 0){
				$cond[] = "k_contentsocialforum.id_socialforum=".$opt['id_socialforum'];
			}else{
				if($opt['debug']) $this->pre("ERROR: ID_SOCIALFORUM (ARRAY, NUMERIC > 0)", "GIVEN", var_export($opt['id_socialforum'], true));
				return array();
			}
		}else{
			$useSocialForum = false;
		}

		# Former la JOINTURE d'apres le TYPE
		#
		if($is_item){
			$jTable	= "k_contentitem".$type['id_type'];
			$join[] = "INNER JOIN k_content			ON ".$jTable.".id_content = k_content.id_content";
			$join[] = "INNER JOIN k_contentdata		ON ".$jTable.".id_content = k_contentdata.id_content";
			$join[] = "INNER JOIN k_contentitem		ON ".$jTable.".id_content = k_contentitem.id_content";

			if(isset($opt['proxyKey'])){
				$key 	= is_array($opt['proxyKey']) ? json_encode($opt['proxyKey']) : $opt['proxyKey'];
				$cond[] = "contentItemProxyKey='".addslashes($key)."'";
			}
		}else
		if($is_album){
			$jTable	= "k_contentalbum".$type['id_type'];
			$join[] = "INNER JOIN k_content			ON ".$jTable.".id_content = k_content.id_content";
			$join[] = "INNER JOIN k_contentdata		ON ".$jTable.".id_content = k_contentdata.id_content";
			$join[] = "INNER JOIN k_contentalbum 	ON ".$jTable.".id_content = k_contentalbum.id_content";
		}else{
			$jTable	= "k_content".$type['id_type'];
			$join[] = "INNER JOIN k_content 		ON ".$jTable.".id_content = k_content.id_content";
			$join[] = "INNER JOIN k_contentdata		ON ".$jTable.".id_content = k_contentdata.id_content";
		}

		if($useChapter){
			$join[] = "INNER JOIN k_contentchapter 	ON ".$jTable.".id_content = k_contentchapter.id_content";
		}

		if($type['is_businessloc'] == '1' && $useGroup){
			$join[] = "INNER JOIN k_contentgroupbusinessloc ON ".$jTable.".id_content = k_contentgroupbusinessloc.id_content";
			$cond[] = "k_contentgroupbusinessloc.country='".$country."'";
			$cond[] = "k_contentgroupbusinessloc.id_group=".$id_group;
		}else
		if($type['is_business'] == '1' && $useGroup){
			$join[] = "INNER JOIN k_contentgroupbusiness ON ".$jTable.".id_content = k_contentgroupbusiness.id_content";
		}else
		if($useGroup){
			$join[] = "INNER JOIN k_contentgroup ON ".$jTable.".id_content = k_contentgroup.id_content";
		}

		if($useShop){
			$join[] = "INNER JOIN k_contentshop ON k_content.id_content = k_contentshop.id_content";
		}


		if($useSocialForum){
			$join[] = "INNER JOIN k_contentsocialforum ON k_content.id_content = k_contentsocialforum.id_content";
		}

		if($type['is_ad']){
			$join[] = "INNER JOIN k_contentad ON ".$jTable.".id_content = k_contentad.id_content";
			$cond[] = "k_contentad.language='".$language."'";
		}

		if($opt['assoUser'] == true){
			$join[] = "INNER JOIN k_user     ON k_content.id_user = k_user.id_user";
			$join[] = "INNER JOIN k_userdata ON k_user.id_user = k_userdata.id_user";
		}

		# Gerer les CATEGORY lie a ce CONTENT
		#
		if($opt['categoryUrl'] != ''){
			$monoCat = $this->apiLoad('category')->categoryGet(array(
				'categoryUrl'	=> $opt['categoryUrl'],
				'language' 		=> $language,
				'debug'			=> $opt['debug']
			));
			if(sizeof($monoCat) == 0){
				if($opt['debug']) $this->pre("No CATEGORY found by URL : ".$opt['categoryUrl']);
				return array();
			}
		}else
		if($opt['id_category'] != NULL){
			if(is_array($opt['id_category'])){
				$multiCat = $this->apiLoad('category')->categoryGet(array(
					'id_category'	=> $opt['id_category'],
					'language'		=> $language,
					'debug'			=> $opt['debug']
				));
				if(sizeof($multiCat) == 0){
					if($opt['debug']) $this->pre("NO MULTI-CATEGORY found by IDs : ", $opt['id_category']);
					return array();
				}else{
					foreach($multiCat as $e){
						$multiCatItem[] = $e['id_category'];
					}
				}
			}else{
				$monoCat = $this->apiLoad('category')->categoryGet(array(
					'id_category'	=> $opt['id_category'],
					'language'		=> $language,
					'debug'			=> $opt['debug']
				));
				if(sizeof($monoCat) == 0){
					if($opt['debug']) $this->pre("NO CATEGORY found by ID : ".$opt['id_category']);
					return array();
				}
			}
		}

		if(is_array($multiCat)){
			$useCategory = true;

			if($opt['categoryAll']){
				foreach($multiCatItem as $e){
					$join[] = "RIGHT JOIN k_contentcategory AS cat".$e." ON ".$jTable.".id_content = cat".$e.".id_content AND cat".$e.".id_category=".$e;
				}
			}else{
				$id_category = " IN(".implode(',', $multiCatItem).")";
			}
		}else
		if(is_array($monoCat)){
			$useCategory = true;
			$id_category = ($categoryThrough)
					? " IN(".$monoCat['id_category'].(($monoCat['categoryChildren'] != NULL) ? ',' : '').$monoCat['categoryChildren'].")"
					: " =".$monoCat['id_category'];
		}

		if($id_category != '' && !$opt['categoryAll']){
			$join[] = "INNER JOIN k_contentcategory ON ".$jTable.".id_content = k_contentcategory.id_content";
		}

		# Search
		#
		if($opt['id_search'] > 0){
			$searchData = $this->dbMulti("SELECT * FROM k_searchparam WHERE id_search=".$opt['id_search']);
			if(sizeof($searchData)) $opt['search'] = $searchData;
		}
		if(is_array($opt['search'])){
			foreach($opt['search'] as $e){
				if($e['searchField'] > 0){
					$tmp[] = $this->dbMatch("field".$e['searchField'], $e['searchValue'], $e['searchMode']);
				}else
				if($fieldKey[$e['searchField']]['id_field'] != NULL){
					$f = $fieldKey[$e['searchField']]; $f['fieldParam'] = json_decode($f['fieldParam'], true);

					// Keyword
					if($f['fieldType'] == 'keyword'){
						$needToGroup = true;
						if($e['strict']){
							foreach($e['searchValue'] as $n => $z){
								$t = ($n == 0) ? 'k_content'.$type['id_type'] : 'k'.($n-1);
								$z = array(
									"k".$n.".keyword='".$z."'",
									"k".$n.".language='".$language."'",
									"k".$n.".id_field=".$f['id_field']
								);

								$join[] = "RIGHT JOIN k_contentkeyword AS k".$n." ON ".$t.".id_content = k".$n.".id_content AND ".implode(' AND ', $z);
								unset($t, $z);
							}
						}else{
							$join[] = "INNER JOIN k_contentkeyword ON k_content".$type['id_type'].".id_content = k_contentkeyword.id_content";
							$cond[] = "k_contentkeyword.language = k_contentdata.language";
							$cond[] = "k_contentkeyword.keyword IN(".implode(', ', $this->helperArrayWrapp($e['searchValue'], "'")).")";
						}
					}else

					// Content
					if($f['fieldType'] == 'content'){
						#if($f['fieldParam']['type'] == 'multi'){
						#	$tmp[] = "k_content.id_content IN(".
						#				"SELECT aContent AS id_content FROM k_contentasso ".
						#				"INNER JOIN k_contentdata ON k_contentasso.bContent = k_contentdata.id_content ".
						#				"WHERE aType=".$type['id_type']." AND bType=".$f['fieldContentType']." AND ".$this->dbMatch("contentName", $e['searchValue'], $e['searchMode']).
						#			 ")";
						#}else{
							$tmp[] = $this->dbMatch("field".$f['id_field'], $e['searchValue'], $e['searchMode']);
						#}
					}else{
						$tmp[] = $this->dbMatch("field".$f['id_field'], $e['searchValue'], $e['searchMode']);
					}
				}else{
					$tmp[] = $this->dbMatch($e['searchField'], $e['searchValue'], $e['searchMode']);
				}
			}
			if(sizeof($tmp) > 0) $cond[] = "(".implode(' '.$searchLink.' ', $tmp).")";
		}else
		if($opt['search'] != ''){
			unset($tmp);
			$tmp[] = $this->dbMatch('contentName', $opt['search'], 'CT');
			if(sizeof($fieldSearch) > 0){
				foreach($fieldSearch as $e){
					$tmp[] = $this->dbMatch("field".$e['id_field'], $opt['search'], 'CT');
				}
			}

			$cond[] = "(".implode($opt['searchMode'], $tmp).")";

			if(sizeof($asso_) > 0){
				$cond[] = "k_content.id_content IN(".implode(',', $asso_).")";
			}

			// verifier le cas suivant qui resulte vide
			if(sizeof($fieldSearch) ==  0 && sizeof($asso_) == 0){
			#	$cond[] = "0";
			}
		}

		# Asso
		#
		if(isset($opt['asso'])){
			foreach($opt['asso'] as $ass){
				$linked = $this->dbMulti("
					SELECT k_content.id_content FROM k_content
					INNER JOIN k_contentasso ON k_content.id_content = k_contentasso.aContent
					WHERE aType='".$type['id_type']."' AND aField='".$ass['id_field']."' AND bType = ".$ass['id_type']."  AND bContent = ".$ass['id_content']
				);
				if(sizeof($linked) > 0){
					foreach($linked as $tmp_){
						$asso_[] = $tmp_['id_content'];
					}
				}
			}

			if(sizeof($asso_)) $cond[] = "k_content.id_content IN(".implode(',', $asso_).")";
		}

		# Former les CONDITIONS
		#
		if($is_item OR $is_album){
			if(is_array($opt['id_album'])){
				$albumCond = "IN(".implode(',', $opt['id_album']).")";
			}else{
				if($albumThrough){
					// Demander albumThrough depuis la racine = de pas preciser le id_album
					if($opt['id_album'] == '0'){
						unset($opt['id_album']);
					}else{
						$alb = $this->dbOne("SELECT * FROM k_contentalbum WHERE id_content='".$opt['id_album']."'");
						if($alb['contentAlbumChildren'] != NULL){
							$albumCond = " IN(".implode(',', array_merge(array($opt['id_album']), explode(',', $alb['contentAlbumChildren']))).")";
						}else{
							$albumCond = "=".$opt['id_album'];
						}
					}
				}else{
					$albumCond = "=".$opt['id_album'];
				}
			}
		}

		if($is_item){
			$from = 'k_contentitem'.$type['id_type'];
			if(isset($opt['id_album'])) $cond[] = "k_contentitem.id_album ".$albumCond;

		}else
		if($is_album){
			$from = 'k_contentalbum'.$type['id_type'];
			if(isset($opt['id_album'])) $cond[] = "k_contentalbum.id_album ".$albumCond;

		}else{
			$from = 'k_content'.$type['id_type'];
		}

		$cond[] = "k_content.id_type=".$type['id_type'];
		$cond[] = "k_contentdata.language='".$language."'";
		$cond[] = $from.".language='".$language."'";

		if(is_array($opt['id_content'])){
			if(sizeof($opt['id_content']) == 0){
				if($opt['debug']) $this->pre("Fatal Error : multiple id_content BUT id_content/array is empty");
				return array();
			}

			$cond[] = "k_content.id_content IN(".implode(',', $opt['id_content']).")";
		}

		if($dbMode == 'dbMulti' && $opt['id_parent'] != '*'){
			$cond[] = "k_content.id_parent=".(isset($opt['id_parent']) ? $opt['id_parent'] : '0');
		}

		if($useChapter){
			$cond[] = "id_chapter".$id_chapter;
			if(!$chapterThrough){
				$cond[] = "k_contentchapter.is_selected=1";
			}else{
				$needToGroup = true;
			}
		}

		if($useCategory != '' && !$opt['categoryAll']){
			$cond[] = "id_category".$id_category;

			if(!$categoryThrough){
				$cond[] = "k_contentcategory.is_selected=1";
			}

			$needToGroup = true;
		}

		if($useGroup){
			$cond[] = "id_group=".$id_group;
			if(!$type['is_business'] && $groupThrough) $needToGroup = true;
		}

		if($useShop){
			$cond[] = "k_contentshop.id_shop=".$opt['id_shop'];
		}

		if(array_key_exists('adZone', $opt)){
			if(intval($opt['adZone']) > 0){
				$cond[] = "k_contentad.id_adzone=".$opt['adZone'];
			}else
			if(is_string($opt['adZone'])){
				$zone	= $this->apiLoad('ad')->adZoneGet(array(
					'zoneCode'	=> $opt['adZone']
				));

				$cond[] = "k_contentad.id_adzone='".$zone['id_adzone']."'";
			}else{
				if($opt['debug']) $this->pre("No ADZONE found for ID/KEY : ".$opt['adZone']);
				return array();
			}
		}

		if($opt['id_user'] != NULL)		$cond[] = "k_content.id_user=".$opt['id_user'];
		if($opt['contentSee'] != 'ALL') {
	        $cond[] = " ('".date('Y-m-d H:i:s')."' >= contentDateStart OR contentDateStart IS NULL)";
	        $cond[] = " ('".date('Y-m-d H:i:s')."' <= contentDateEnd OR contentDateEnd IS NULL)";
	        $cond[] = "contentSee=".(isset($opt['contentSee']) ? $opt['contentSee'] : '1');
	    }
		if(isset($opt['is_buy']))		$cond[] = "is_buy=".(($opt['is_buy']) ? '1' : '0');
		if(isset($opt['social']))		$cond[] = "k_content.is_social=".$opt['is_social'];
		if(isset($opt['noId']))			$cond[] = "k_content.id_content".(is_array($opt['noId']) ? ' NOT IN('.implode(',', $opt['noId']).')' : '!='.$opt['noId']);

		if(sizeof($cond) > 0)			$where  = "\nWHERE\t".implode("\n\tAND ", $cond);

		// JOINT tables + WHERE conditions set in OPTIONS
		if($opt['sqlJoin'] != '') $join[] = $opt['sqlJoin'];
		if($opt['sqlWhere'] != ''){
			if(isset($where)){
				$where .= ' '.$opt['sqlWhere'];
			}else{
				$where  = "\nWHERE ".$opt['sqlWhere'];
			}
		}


		# Former les LIMITATIONS et ORDRE
		#
		if($dbMode == 'dbMulti'){
			if(isset($opt['order']) && isset($opt['direction'])){
				if($fieldKey[$opt['order']]['id_field'] != NULL && $opt['direction'] != NULL){
					$sqlOrder = "\nORDER BY field".$fieldKey[$opt['order']]['id_field']." ".$opt['direction'];
				}else{
					$sqlOrder = "\nORDER BY ".$opt['order']." ".$opt['direction'];
				}
			}else{
				if($is_item){
					$sqlOrder = "\nORDER BY contentItemPos ASC";
				}else
				if($is_album){
					$sqlOrder = "\nORDER BY contentAlbumPos ASC";
				}else{
					$sqlOrder = "\nORDER BY pos_content ASC";
				}
			}

			if($opt['sqlOrder'] != '') $sqlOrder .= $opt['sqlOrder'];

			if(!$noLimit) $sqlLimit = "\nLIMIT ".$offset.",".$limit;
		}

		if($needToGroup) $sqlGroup = "\nGROUP BY k_content.id_content";

		if($opt['sqlGroup'] != '') $sqlGroup = "\nGROUP BY ".$opt['sqlGroup'];

		# Demander le CONTENT
		#
		$content = $this->$dbMode(
			"SELECT SQL_CALC_FOUND_ROWS * FROM ".$from."\n".
			implode("\n", $join).
			$where . $sqlGroup . $sqlOrder . $sqlLimit
		);
		if($opt['debug']) $this->pre($this->db_query, $this->db_error, "Query Output", $content);

		if($dbMode == 'dbOne'){
			$flip 		= true;
			$content 	= (sizeof($content) > 0) ? array($content) : array();
		}

		$this->total = $this->db_num_total;
		$this->limit = $limit;

		# Gerer les ASSOCIATIONS
		#
		// CONTENT relies a ce CONTENT
		if(sizeof($fieldAssoContentMulti) > 0){
			foreach($content as $idx => $c){
				foreach($fieldAssoContentMulti as $f){
					$content[$idx]['field'.$f['id_field']] = $this->contentAssoGet($content[$idx]['id_content'], $content[$idx]['id_type'], $f['id_field'], $f['fieldContentType']);
				}
			}
		}
		if(sizeof($fieldAssoContentSingle) > 0){
			foreach($content as $idx => $c){
				foreach($fieldAssoContentSingle as $f){
					if(!empty($content[$idx]['field'.$f['id_field']])){
						$content[$idx]['field'.$f['id_field']] = array($content[$idx]['field'.$f['id_field']]);
					}
				}
			}
		}

		// USER relies a ce CONTENT
		if(sizeof($fieldAssoUser) > 0){
			foreach($content as $idx => $c){
				foreach($fieldAssoUser as $f){
					$content[$idx]['field'.$f['id_field']] = $this->contentAssoUserGet($content[$idx]['id_content'], $content[$idx]['id_type'], $f['id_field']);
				}
			}
		}

		// DBTABLE relies a ce CONTENT (on ne peut pas utiliser la fonction contentAssoGet() pour ce type de contenue
		// car les valeur sont stockes dans la champs et pas dans content_asso
		if(sizeof($fieldAssoDb) > 0){
			foreach($content as $idx => $c){
				foreach($fieldAssoDb as $f){
					$tmp = explode($this->splitter, $c['field'.$f['id_field']]);
					foreach($tmp as $tmpidx => $tmp_){
						if($tmp_ == NULL) unset($tmp[$tmpidx]);
					}
					$content[$idx]['field'.$f['id_field']] = array_values($tmp);
				}
			}
		}

		# Gerer les VALEURS DE RETOUR
		#
		if($human){
			foreach($content as $idx => $c){

				// Media
				if($c['contentMedia'] != ''){
					$contentMedia = json_decode(stripslashes($c['contentMedia']), true);
					if(sizeof($contentMedia) > 0){
						unset($media);
						foreach($contentMedia as $e){
							$v = $this->mediaInfos($e['url']);
							$v['caption'] = $e['caption'];
							$media[$e['type']][] = $v;
						}
						$content[$idx]['contentMedia'] = $media;
					}else{
						$content[$idx]['contentMedia'] = array();
					}
				}else{
					$content[$idx]['contentMedia'] = array();
				}

				// Fields
				foreach($field as $f){
					$v = $c['field'.$f['id_field']];
					$p = json_decode($f['fieldParam'], true);

					if($f['fieldType'] == 'media'){
						$v = json_decode($v, true); unset($media);

						if(sizeof($v) > 0 && is_array($v)){
							foreach($v as $e){
								$e_ = $this->mediaInfos($e['url']);
								$e_['caption'] = $e['caption'];
								$media[$e['type']][] = $e_;
							}
							$content[$idx]['field'.$f['id_field']] = $media;
						}else{
							$content[$idx]['field'.$f['id_field']] = array();
						}

					}else
					if($f['fieldType'] == 'category'){
						$tmp = array();

						if($p['type'] == 'solo' && intval($v) > 0){
							$tmp = $this->dbOne("SELECT id_category, categoryName FROM k_categorydata WHERE id_category=".intval($v));
						}else
						if($p['type'] == 'multi'){
							$v = explode($this->splitter, $v);
							unset($v[sizeof($v)-1], $v[0]);

							foreach($v as $vCat){
								$tmp[] = $this->dbOne("SELECT id_category, categoryName FROM k_categorydata WHERE id_category=".intval($vCat));
							}
						}

						$content[$idx]['field'.$f['id_field']] = $tmp;

					}else

					if(is_array($v) && $f['fieldType'] == 'user'){
						unset($tmp);
						foreach($v as $bUser){
							$tmp[] = $this->dbOne("SELECT * FROM k_user WHERE k_user.id_user=".$bUser." AND is_deleted=0");
						}
						$content[$idx]['field'.$f['id_field']] = $tmp;
					}else

					if(is_array($v) && $f['fieldType'] == 'content'){
						$param = json_decode($f['fieldParam'], true);
						#$this->pre($f, $param, $v);

						unset($tmp);
						foreach($v as $bContent){
							$tmp[] = $this->dbOne("
								SELECT * FROM k_contentdata
								INNER JOIN k_content ON k_contentdata.id_content = k_content.id_content
								WHERE k_contentdata.id_content=".$bContent." AND language='".$language."'");
						}
						$content[$idx]['field'.$f['id_field']] = (($param['type'] == 'solo' && sizeof($v) == 1) ? $tmp[0] : $tmp);
					}else

					if(in_array($f['fieldType'], array('onechoice', 'multichoice')) && substr($v, 0, 2) == $this->splitter && substr($v, -2) == $this->splitter && $v != $this->splitter){

						$part = explode($this->splitter, substr($v, 2, -2));
						$content[$idx]['field'.$f['id_field']] = implode("<br />", $part);

					}else{
						$content[$idx]['field'.$f['id_field']] = $v;
					}
				}

				// Cache
				if($c['contentCache'] != '') $content[$idx]['contentCache'] = json_decode($c['contentCache'], true);
			}
		}

		# Reformater le CONTENT avec les FIELDS
		#
		if($useField){
			$this->contentField = $fieldKey;
			foreach($content as $idx => $c){
				foreach($field as $f){
					$content[$idx]['field'][$f['fieldKey']] = $c['field'.$f['id_field']];
					unset($content[$idx]['field'.$f['id_field']]);
				}
			}
		}

		# Les VARIABLES de la TEMPLATE
		#
		foreach($content as $idx => $e){
			if($e['contentTemplateEnv'] != '') $content[$idx]['contentTemplateEnv'] = unserialize($e['contentTemplateEnv']);
		}



		# Obtenir les ASSO des CHAPITRES - admin
		#
		if($opt['assoChapter']){
			foreach($content as $idx => $c){
				$ids = $this->dbMulti("SELECT id_chapter FROM k_contentchapter WHERE id_content=".$c['id_content']." AND is_selected=1");
				$content[$idx]['id_chapter'] = $this->dbKey($ids, 'id_chapter');
			}
		}

		# Obtenir les ASSO des GROUPS - admin
		#
		if($opt['assoGroup']){
			foreach($content as $idx => $c){
				$ids = $this->dbMulti("SELECT id_group FROM k_contentgroup WHERE id_content=".$c['id_content']." AND is_selected=1");
				$content[$idx]['id_group'] = $this->dbKey($ids, 'id_group');
			}
		}

		# Obtenir les ASSO des CATEGORY - admin
		#
		if($opt['assoCategory']){
			foreach($content as $idx => $c){
				$ids = $this->dbMulti("SELECT id_category FROM k_contentcategory WHERE id_content=".$c['id_content']." AND is_selected=1");
				$content[$idx]['id_category'] = $this->dbKey($ids, 'id_category');
			}
		}

		# Obtenir les ASSO des SEARCH - admin
		#
		if($opt['assoSearch']){
			foreach($content as $idx => $c){
				$ids = $this->dbMulti("SELECT id_search FROM k_contentsearch WHERE id_content=".$c['id_content']);
				$content[$idx]['id_search'] = $this->dbKey($ids, 'id_search');
			}
		}

		# Obtenir les ASSO des SHOP - admin
		#
		if($opt['assoShop']){
			foreach($content as $idx => $c){
				$ids = $this->dbMulti("SELECT id_shop FROM k_contentshop WHERE id_content=".$c['id_content']);
				$content[$idx]['id_shop'] = $this->dbKey($ids, 'id_shop');
			}
		}

		# Obtenir les ASSO des SOCIAL-FORUM - admin
		#
		if($opt['assoSocialForum']){
			foreach($content as $idx => $c){
				$ids = $this->dbMulti("SELECT id_socialforum FROM k_contentsocialforum WHERE id_content=".$c['id_content']);
				$content[$idx]['id_socialforum'] = $this->dbKey($ids, 'id_socialforum');

				if(!is_array($content[$idx]['id_socialforum'])) $content[$idx]['id_socialforum'] = array();
			}
		}

		if($flip) $content = $content[0];



		if($opt['debug']) $this->pre("OUTPUT", $content);

		if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep);

	    $content  = $this->hookFilter('contentGet', $content);

		return $content;
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function contentSet($opt){


		$id_type       = $opt['id_type'];
		$language      = $opt['language'];
		$id_content    = $opt['id_content'];
		$def           = $opt['def'];
		$data          = $opt['data'];
		$field         = $opt['field'];
		$group         = $opt['group'];
		$item          = $opt['item'];
		$album         = $opt['album'];
		$contentFamily = ($opt['contentFamily'] !== false) ? true : false;

		# Get the TYPE
		$type = $this->apiLoad('type')->typeGet(array('id_type' => $id_type));

		# Core
		if($id_content == NULL){
			$this->dbQuery("INSERT INTO k_content (id_type, id_user) VALUES ('".$id_type."','".$this->user['id_user']."')");
			$id_content = $this->db_insert_id;
			if($opt['debug']) $this->pre($this->db_query, $this->db_error, 'ID_CONTENT >> '.$id_content);
		}
		$this->id_content = $id_content;


		# Core.Type
		#
		if($id_type != ''){
			// ITEM
			if($opt['def']['k_content']['is_item']['value'] == 1){
				$extType = $this->dbOne("SELECT 1 FROM k_contentitem".$id_type." WHERE id_content=".$this->id_content." AND language='".$language."'");

				if(!$extType[1]){
					$this->dbQuery("INSERT INTO k_contentitem".$id_type." (id_content,language) VALUES (".$this->id_content.",'".$language."')");
					if($opt['debug']) $this->pre($this->db_query, $this->db_error);
				}
			}else
			// ALBUM
			if($opt['def']['k_content']['is_album']['value'] == 1){
				$extType = $this->dbOne("SELECT 1 FROM k_contentalbum".$id_type." WHERE id_content=".$this->id_content." AND language='".$language."'");

				if(!$extType[1]){
					$this->dbQuery("INSERT INTO k_contentalbum".$id_type." (id_content,language) VALUES (".$this->id_content.",'".$language."')");
					if($opt['debug']) $this->pre($this->db_query, $this->db_error);
				}
			}
			// CORE
			else{
				$extType = $this->dbOne("SELECT 1 FROM k_content".$id_type." WHERE id_content=".$this->id_content." AND language='".$language."'");

				if(!$extType[1]){
					$this->dbQuery("INSERT INTO k_content".$id_type." (id_content,language) VALUES (".$this->id_content.",'".$language."')");
					if($opt['debug']) $this->pre($this->db_query, $this->db_error);
				}
			}
		}


		# Data
		#
		if(isset($opt['data'])){
			$extData = $this->dbOne("SELECT 1 FROM k_contentdata WHERE id_content=".$this->id_content." AND language='".$language."'");
			#if($opt['debug']) $this->pre($this->db_query, $this->db_error, $extData);

			if(!$extData[1]){
				$this->dbQuery("INSERT INTO k_contentdata (id_content, language) VALUES (".$this->id_content.",'".$language."')");
				if($opt['debug']) $this->pre($this->db_query, $this->db_error);
			}

			$q = $this->dbUpdate($data)." WHERE language='".$language."' AND id_content=".$this->id_content;
			@$this->dbQuery($q);
			if($opt['debug']) $this->pre($this->db_query, $this->db_error);
		}


		# Item
		#
		if(isset($opt['item'])){
			$isItem	 = true;
			$extItem = $this->dbOne("SELECT 1 FROM k_contentitem WHERE id_content=".$this->id_content);
			#if($opt['debug']) $this->pre($this->db_query, $this->db_error, $extItem);

			if(!$extItem[1]){
				$this->dbQuery("INSERT INTO k_contentitem (id_content) VALUES (".$this->id_content.")");
				if($opt['debug']) $this->pre($this->db_query, $this->db_error);
			}

			if(sizeof($item) > 0){
				@$this->dbQuery($this->dbUpdate($item)." WHERE id_content=".$this->id_content);
				if($opt['debug']) $this->pre($this->db_query, $this->db_error);
			}

			$id_album = $item['k_contentitem']['id_album']['value'];
			if($id_album > 0 && $def['k_content']['id_content']['value'] == ''){
				$last = $this->dbOne("SELECT MAX(contentItemPos) AS la FROM k_contentitem WHERE id_album=".$id_album);
				$this->dbQuery("UPDATE k_contentitem SET contentItemPos=".($last['la'] + 1)." WHERE id_content=".$this->id_content." AND id_album=".$item['k_contentitem']['id_album']['value']);
				if($opt['debug']) $this->pre($this->db_query, $this->db_error);
			}
		}

		# Album
		#
		else if($opt['def']['k_content']['is_album']['value'] == 1){
			$isAlbum = true;
			$extAlb  = $this->dbOne("SELECT 1 FROM k_contentalbum WHERE id_content=".$this->id_content);
			if($opt['debug']) $this->pre($this->db_query, $this->db_error, $extAlb);

			if(!$extAlb[1]){
				$this->dbQuery("INSERT INTO k_contentalbum (id_content) VALUES (".$this->id_content.")");
				if($opt['debug']) $this->pre($this->db_query, $this->db_error);
			}

			if(isset($opt['album'])){
				$this->dbQuery($this->dbUpdate($album)." WHERE id_content=".$this->id_content);
			}
		}


		# Core
		#
		if(isset($opt['def'])){
			$q = $this->dbUpdate($def)." WHERE id_content=".$this->id_content;
			@$this->dbQuery($q);
			if($opt['debug']) $this->pre($this->db_query, $this->db_error);
			if($this->db_error != NULL) return false;
		}


		# FIELD
		#
		if(sizeof($field) > 0){

			# Si on utilise le KEY au lieu des ID
			$opt_	= array('debug' => false, 'id_type' => $id_type);
			if($isItem) 	$opt_['itemField']	= true;
			if($isAlbum) 	$opt_['albumField']	= true;
			$fields = $this->apiLoad('field')->fieldGet($opt_);

			foreach($fields as $e){
				$fieldsKey[$e['fieldKey']] = $e;
				$fieldsIds[] = $e['id_field'];
			} $fields = $fieldsKey;

			unset($def);

			if(isset($opt['item'])){
				$table = 'k_contentitem'.$id_type;
			}else
			if($opt['def']['k_content']['is_album']['value'] == 1){
				$table = 'k_contentalbum'.$id_type;
			}else{
				$table = 'k_content'.$id_type;
			}

			foreach($field as $id_field => $value){
				if(!is_integer($id_field)) $id_field = $fields[$id_field]['id_field'];

				if(in_array($id_field, $fieldsIds)){
					$value = $this->apiLoad('field')->fieldSaveValue($id_field, $value, array(
						'id_content'	=> $this->id_content,
						'language'		=> $language
					));

					$def[$table]['field'.$id_field] = array('value' => $value);
				}
			}

			if(sizeof($def) > 0){
				$this->dbQuery($this->dbUpdate($def)." WHERE language='".$language."' AND id_content=".$this->id_content);
				if($opt['debug']) $this->pre($this->db_query, $this->db_error);
			}
		}

		# ASSOCIATION : Chapter
		if(array_key_exists('id_chapter', $opt)){
			$chapterParent = $this->apiLoad('chapter')->chapterGet(array('distinctParent' => true, 'id_chapter' => $opt['id_chapter'], 'language' => 'fr'));
			$this->dbAssoSet('k_contentchapter', 'id_content', 'id_chapter', $this->id_content, $opt['id_chapter'], 'PROFILE', $this->profile['id_profile'], 'chapterChildren', $chapterParent);
		}

		# ASSOCIATION : Category
		if(array_key_exists('id_category', $opt)){
			$opt['id_category'] = $this->contentCheckCategory($opt['id_category']);
			$categoryParent = $this->apiLoad('category')->categoryGet(array('distinctParent' => true, 'id_category' => $opt['id_category'], 'language' => 'fr'));
			$this->dbAssoSet('k_contentcategory', 'id_content', 'id_category', $this->id_content, $opt['id_category'], 'PROFILE', $this->profile['id_profile'], 'categoryChildren', $categoryParent);
		}

		# ASSOCIATION : Social Forum
		if(array_key_exists('id_socialforum', $opt)){
			$opt['id_socialforum'] = $this->apiLoad('socialForum')->socialForumCheck($opt['id_socialforum']);
			$this->dbAssoSet('k_contentsocialforum', 'id_content', 'id_socialforum', $this->id_content, $opt['id_socialforum'], 'ALL');
		}

		# ASSOCIATION : Search
		if(array_key_exists('id_search', $opt)){
			$this->dbAssoSet('k_contentsearch', 'id_content', 'id_search', $this->id_content, $opt['id_search'], 'ALL');
		}

		# ASSOCIATION : Shop
		if(array_key_exists('id_shop', $opt)){
			$this->dbAssoSet('k_contentshop', 'id_content', 'id_shop', $this->id_content, $opt['id_shop'], 'ALL');
		}

		# ASSOCIATION : Group (NOT business)
		if(array_key_exists('id_group', $opt)){
			$groupParent = $this->apiLoad('user')->userGroupGet(array('distinctParent' => true, 'id_group' => $opt['id_group']));
			$this->dbAssoSet('k_contentgroup', 'id_content', 'id_group', $this->id_content, $opt['id_group'], 'PROFILE', $this->profile['id_profile'], 'groupChildren', $groupParent);
		}

		# ASSOCIATION : Group (business)
		#
		if(sizeof($group) > 0 && $type['is_businessloc'] == '0'){
			$table = 'k_contentgroupbusiness';

			foreach($group as $id_group => $e){

				$exists = $this->dbOne("SELECT 1 FROM ".$table." WHERE id_content=".$this->id_content." AND id_group=".$id_group);

				if(!$exists['1'] && $e['is_view']){
					$this->dbQuery("INSERT INTO ".$table." (id_content, id_group) VALUES (".$this->id_content.", ".$id_group.")");
				}else
				if($exists['1'] && !$e['is_view']){
					$this->dbQuery("DELETE FROM ".$table." WHERE id_content=".$this->id_content." AND id_group=".$id_group);
				}

				$def = array($table => array(
					'is_view'				=> array('value' => $e['is_view'], 										'zero' => true),
					'is_buy'				=> array('value' => $e['is_buy'], 										'zero' => true),
					'contentPrice'			=> array('value' => str_replace(',', '.', $e['contentPrice']),			'null' => true),
					'contentPriceTax'		=> array('value' => str_replace(',', '.', $e['contentPriceTax']),		'null' => true),
					'contentPriceNormal'	=> array('value' => str_replace(',', '.', $e['contentPriceNormal']),	'null' => true),
					'contentPriceComment'	=> array('value' => $e['contentPriceComment'],							'null' => true)
				));

				$this->dbQuery($this->dbUpdate($def)." WHERE id_content=".$this->id_content." AND id_group=".$id_group);
				if($opt['debug']) $this->pre($this->db_query, $this->db_error);
			}
		}else

		# ASSOCIATION : Group (business + LOC)
		#
		if(sizeof($group) > 0 && $type['is_businessloc'] == '1'){
		#	$opt['debug'] = true;
		#	$this->pre($group);
			$table = 'k_contentgroupbusinessloc';

			foreach($group as $loc => $g){
				foreach($g as $id_group => $e){

					$upd    = true;
					$exists = $this->dbOne("SELECT 1 FROM ".$table." WHERE country='".$loc."' AND id_content=".$this->id_content." AND id_group=".$id_group);

					if(!$exists['1'] && $e['is_view']){
						$this->dbQuery("INSERT INTO ".$table." (country, id_content, id_group) VALUES ('".$loc."', ".$this->id_content.", ".$id_group.")");
						if($opt['debug']) $this->pre($this->db_query, $this->db_error);
					}else
					if($exists['1'] && !$e['is_view']){
						$upd = false;
						$this->dbQuery("DELETE FROM ".$table." WHERE country='".$loc."' AND id_content=".$this->id_content." AND id_group=".$id_group);
						if($opt['debug']) $this->pre($this->db_query, $this->db_error);
					}

					if($upd){
						$def = array($table => array(
							'is_view'				=> array('value' => $e['is_view'], 										'zero' => true),
							'is_buy'				=> array('value' => $e['is_buy'], 										'zero' => true),
							'contentPrice'			=> array('value' => str_replace(',', '.', $e['contentPrice']),			'null' => true),
							'contentPriceTax'		=> array('value' => str_replace(',', '.', $e['contentPriceTax']),		'null' => true),
							'contentPriceNormal'	=> array('value' => str_replace(',', '.', $e['contentPriceNormal']),	'null' => true),
							'contentPriceComment'	=> array('value' => $e['contentPriceComment'],							'null' => true)
						));

						$this->dbQuery($this->dbUpdate($def)." WHERE country='".$loc."' AND id_content=".$this->id_content." AND id_group=".$id_group);
						if($opt['debug']) $this->pre($this->db_query, $this->db_error);
					}
				}
			}
		}

	#	die('008');



		# AD
		#
		if(is_array($opt['ad'])){
			$opt['ad']['id_content'] = array('value' => $this->id_content);

			$cond	= " WHERE id_content=".$this->id_content." AND language='".$language."'";
			$exists = $this->dbOne("SELECT 1 FROM k_contentad".$cond);
			$query	= ($exists[1])
				? $this->dbUpdate(array('k_contentad' => $opt['ad'])).$cond
				: $this->dbInsert(array('k_contentad' => $opt['ad']));

			$this->dbQuery($query);
			if($opt['debug']) $this->pre($this->db_query, $this->db_error);
		}


		# Sauver en static les infos relative au content
		#
		$this->contentCacheBuild($this->id_content);
	#	$this->contentCacheTable($this->id_content);


		# Generer la famille si je suis un ALBUM
		#
		$isAlbum = $this->dbOne("SELECT id_content FROM k_contentalbum WHERE id_content=".$this->id_content);
		if($isAlbum['id_content'] > 0 && $contentFamily) $this->contentAlbumFamily($this->id_content);

		if(!$opt['noHook']) $this->hookAction('contentSet', $this->id_content, $opt);

		return true;
	}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function contentCheckCategory($category){

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

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function contentSearch($opt){

	$search = $this->dbOne("SELECT * FROM k_search WHERE id_search=".$opt['id_search']);
	$search['searchParam'] = unserialize($search['searchParam']);

	$where = $this->contentSearchSQL($search);
	if(trim($where) != '') $where = " WHERE \n".$where;
	
	$c = $this->dbMulti("SELECT SQL_CALC_FOUND_ROWS * FROM k_content".$search['searchType'] . $where);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error, $c);

	$this->total = $this->db_num_total;

	return $c;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function contentSearchSQL($param, $level=0){

	$prompt  = str_repeat("\t", $level);
	$prompt_ = $prompt."\t";

	if(is_array($param['searchParam']) && sizeof($param['searchParam']) > 0){
		foreach($param['searchParam'] as $i => $e){
	
			$last = ($i == sizeof($param['searchParam'])-1);

			if(is_array($e['searchValue'])){
				unset($tmp);
				foreach($e['searchValue'] as $n){
					$tmp[] = "`field".$e['searchField']."` = '".$n."'";
				}
				$q .= $prompt_."(".implode(" AND ", $tmp).")\n";
			}else{
				$q .= $prompt_.$this->dbMatch("field".$e['searchField'], $e['searchValue'], $e['searchMode'])."\n";
			}
			
			if(sizeof($e['searchParam']) > 0){
				if($last) $q .= $prompt_.$param['searchChain']."\n";
				$q .= $this->contentSearchSQL($e, ($level+1));
			}

			if(!$last) $q .= $prompt_.$param['searchChain']."\n";
		}
	}
	
	#if(sizeof($c) > 0) $q .= implode($prompt_.$param['searchChain']."\n", $c);

	$q = $prompt.((strlen($q) > 0) ? "(\n".$q.")\n" : NULL);

	return $q;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function contentDuplicate($id_content){

	# Originale
	#
	$from		= $this->dbOne("SELECT * FROM k_content WHERE id_content=".$id_content);

	# Trouver les champs à dupliquer
	#
	$core		= $this->dbMulti("SHOW COLUMNS FROM k_content WHERE Field != 'id_content'");
	$coreFields	= $this->dbKey($core, 'Field');

	$data		= $this->dbMulti("SHOW COLUMNS FROM k_contentdata WHERE Field != 'id_content'");
	$dataFields = $this->dbKey($data, 'Field');

	$more		= $this->dbMulti("SHOW COLUMNS FROM k_content".$from['id_type']." WHERE Field != 'id_content'");
	$moreFields	= $this->dbKey($more, 'Field');
	//die($this->pre('coreFields', $coreFields, 'dateFields', $dataFields, 'moreFields', $moreFields));


	# Dupliquer la partie Core
	#
	$this->dbQuery("INSERT INTO k_content (".implode(', ', $coreFields).") SELECT ".implode(', ', $coreFields)." FROM k_content WHERE id_content=".$id_content);
	if($this->db_error) die($this->pre($this->db_query, $this->db_error));
	$new = $this->db_insert_id;


	# On effectus quelques mise à jour pour la nouvelle version
	#
	$this->dbQuery("UPDATE k_content SET contentSee=0, contentDateUpdate=NOW() WHERE id_content=".$new);
	if($this->db_error) die($this->pre($this->db_query, $this->db_error));


	# On s'occupe des parties qui concerne les traductions DATA
	#
	$data = $this->dbMulti("SELECT * FROM k_contentdata WHERE id_content=".$id_content);
	if(sizeof($data) > 0){
		foreach($data as $e){
			$tmp = array();
			foreach($dataFields as $df){
				$tmp[] = ($df == 'contentName')
					? "'".addslashes($e[$df])." - copie'"
					: "'".addslashes($e[$df])."'";
			}

			$addData[] = "(".$new.", ".implode(',', $tmp).")";
		}
		$this->dbQuery("INSERT INTO k_contentdata (id_content, ".implode(', ', $dataFields).") VALUES ".implode(', ', $addData));
		if($this->db_error) die($this->pre($this->db_query, $this->db_error));
	}


	# On s'occupe des parties qui concerne les traductions MORE
	#
	$more = $this->dbMulti("SELECT * FROM k_content".$from['id_type']." WHERE id_content=".$id_content);
	if(sizeof($more) > 0){
		foreach($more as $e){
			$tmp = array();
			foreach($moreFields as $mf){
				$tmp[] = "'".addslashes($e[$mf])."'";
			}
			$addMore[] = "(".$new.", ".implode(',', $tmp).")";
		}
		$this->dbQuery("INSERT INTO k_content".$from['id_type']." (id_content, ".implode(', ', $moreFields).") VALUES ".implode(', ', $addMore));
		if($this->db_error) die($this->pre($this->db_query, $this->db_error));
	}


	# On s'occupe maintenant de tous les liens sur les tables externes
	#
	$proto = array(
		'k_contentchapter'			=> '', //array('key' => 'id_chapter'),
		'k_contentgroup'			=> '', //array('key' => 'id_group'),
		'k_contentgroupbusiness'	=> '', //array('key' => ''),
		'k_contentcategory'			=> '', //array('key' => 'id_category'),
		'k_contentasso'				=> '', //array('key' => ''),
		'k_contentsearch'			=> '', //array('key' => ''),
		'k_contentshop'				=> '', //array('key' => '')
		'k_contentad'				=> '', //array('key' => '')
	);

	foreach($proto as $table => $k){
		$where	= ($table == 'k_contentasso') ? 'aContent' : 'id_content';
		$get	= $this->dbMulti("SELECT * FROM ".$table." WHERE ".$where."=".$id_content);

		// si ca vaut le coup de faire la suite
		if(sizeof($get) > 0){
			$field	= $this->dbMulti("SHOW COLUMNS FROM ".$table." WHERE Field != '".$where."'");
			$field	= $this->dbKey($field, 'Field');

			foreach($get as $e){
				$tmp = array();
				foreach($field as $f){
					$tmp[] = ($f == 'contentName')
						? "'".addslashes($e[$f])." - copie'"
						: "'".addslashes($e[$f])."'";
				}
				$add[] = "(".$new.", ".implode(',', $tmp).")";
			}

			/*foreach($get as $e){
				$add[] = "(".$new.", ".$e[$key].", ".$e['is_selected'].")";	
			}*/

			$this->dbQuery("INSERT INTO ".$table." (".$where.", ".implode(', ', $field).") VALUES ".implode(', ', $add));
			#if($this->db_error) die($this->pre($table, $this->db_query, $this->db_error));
			unset($add);
		}
	}

	/*$asso = $this->dbMulti("SELECT * FROM k_contentasso WHERE aContent=".$id_content);
	if(sizeof($asso) > 0){
		foreach($asso as $e){
			$addAsso[] = "(".$new.", ".$e['aType'].", ".$e['aField'].", ".$e['bType'].", ".$e['bContent'].")";	
		}
		$this->dbQuery("INSERT INTO k_contentasso (aContent, aType, aField, bType, bContent) VALUES ".implode(', ', $addAsso));
		if($this->db_error) die($this->pre($this->db_query, $this->db_error));
	}


	$business = $this->dbMulti("SELECT * FROM  k_contentgroupbusiness WHERE id_content=".$id_content);
	if(sizeof($business) > 0){
		foreach($business as $e){
			$addBusiness[] = "(".$e['id_group'].", ".$new.", ".$e['is_view'].", ".$e['is_buy'].", '".$e['contentPrice']."', '".$e['contentPriceTax']."', '".$e['contentPriceNormal']."', '".$e['contentPriceComment']."')";	
		}
		$this->dbQuery("INSERT INTO k_contentgroupbusiness (id_group, id_content, is_view, is_buy, contentPrice, contentPriceTax, contentPriceNormal, contentPriceComment) VALUES ".implode(', ', $addBusiness));
		if($this->db_error) die($this->pre($this->db_query, $this->db_error));
	}


	$search = $this->dbMulti("SELECT * FROM k_contentsearch WHERE id_content=".$id_content);
	if(sizeof($search) > 0){
		foreach($search as $e){
			$addSearch[] = "(".$new.", ".$e['id_search'].")";	
		}
		$this->dbQuery("INSERT INTO k_contentsearch (id_content, id_search) VALUES ".implode(', ', $addSearch));
		if($this->db_error) die($this->pre($this->db_query, $this->db_error));
	}

	$shop = $this->dbMulti("SELECT * FROM k_contentshop WHERE id_content=".$id_content);
	if(sizeof($shop) > 0){
		foreach($shop as $e){
			$addShop[] = "(".$new.", ".$e['id_shop'].")";	
		}
		$this->dbQuery("INSERT INTO k_contentshop (id_content, id_shop) VALUES ".implode(', ', $addShop));
		if($this->db_error) die($this->pre($this->db_query, $this->db_error));
	}*/

}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function contentDuplicateLanguage($id_content, $fr, $to){

	# Originale
	$from = $this->dbOne("SELECT * FROM k_content WHERE id_content=".$id_content);

	$data = $this->dbMulti("SHOW COLUMNS FROM k_contentdata WHERE Field NOT IN('id_content', 'language')");
	foreach($data as $e){
		$dataFields[] = $e['Field'];
	}

	$more = $this->dbMulti("SHOW COLUMNS FROM k_content".$from['id_type']." WHERE Field NOT IN('id_content', 'language')");
	foreach($more as $e){
		$moreFields[] = $e['Field'];
	}

#	$this->pre('dateFields', $dataFields, 'moreFields', $moreFields);

	# On s'occupe des parties qui concerne les traductions DATA
	$data	= $this->dbOne("SELECT * FROM k_contentdata WHERE id_content=".$id_content." AND language='".$fr."'");
	$tmp	= array();
	foreach($dataFields as $df){
		$tmp[] = "'".addslashes($data[$df])."'";
	}

	$this->dbQuery("INSERT INTO k_contentdata (id_content, language, ".implode(', ', $dataFields).") VALUES ('".$id_content."', '".$to."', ".implode(',', $tmp).")");
	if($this->db_error) die($this->pre($this->db_query, $this->db_error));

	# On s'occupe des parties qui concerne les traductions MORE
	$select = '';
	$values = '';

	if(count($moreFields) > 0){
		$more   = $this->dbOne("SELECT * FROM k_content".$from['id_type']." WHERE id_content=".$id_content);
		$tmp    = array();

		foreach($moreFields as $mf){
			$tmp[] = "'".addslashes($more[$mf])."'";
		}

		$select = ', '.implode(',', $moreFields);
		$values = ', '.implode(',', $tmp);
	}

	$this->dbQuery("INSERT INTO k_content".$from['id_type']." (id_content, language".$select.") VALUES ('".$id_content."','".$to."'".$values.")");
	if($this->db_error) die($this->pre($this->db_query, $this->db_error));
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function contentVersionGet($opt=array()){

	if($opt['id_content'] != NULL){
		$version = $this->dbMulti("SELECT id_version, versionDate FROM k_contentversion WHERE id_content=".$opt['id_content']." AND language='".$opt['language']."'");
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	}else
	if($opt['id_version'] != NULL){
		$version = $this->dbOne("SELECT * FROM k_contentversion WHERE id_version=".$opt['id_version']);
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);

		$version = unserialize($version['versionRaw']);
	}
	
	return $version;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function contentVersionSet($opt=array()){

	$from = $this->contentGet(array(
		'id_content' 	=> $opt['id_content'],
		'language'		=> $opt['language'],
		'debug'	 		=> false,
		'raw'			=> true
	));

	$this->dbQuery(
		"INSERT INTO k_contentversion (id_content, language, versionDate, versionRaw) ".
		"VALUES (".$opt['id_content'].", '".$opt['language']."', NOW(), '".addslashes(serialize($from))."')"
	);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function contentRemove($id_type, $id_content, $language=''){

    if(intval($id_content) == 0) return false;

    if($id_type > 0) {
        $type = $this->apiLoad('type')->typeGet(array(
            'id_type' => $id_type
        ));
        // Si pas de langue renseignee alors on supprime le contenu et toutes ses langues
        $sqllang = ($language != '') ? " AND language='".$language."'" : "";

        if($type['is_ad'] == 1){
            $this->dbQuery("DELETE FROM k_contentad				WHERE id_content=".$id_content." ".$sqllang);
            $this->dbQuery("DELETE FROM k_contentadstats		WHERE id_content=".$id_content." ".$sqllang);
        }else
        if($type['is_gallery'] == 1){
            $this->dbQuery("DELETE FROM k_contentitem".$id_type."	WHERE id_content=".$id_content." ".$sqllang);
            $this->dbQuery("DELETE FROM k_contentalbum".$id_type."	WHERE id_content=".$id_content." ".$sqllang);
        }else{
            $this->dbQuery("DELETE FROM k_content".$id_type."		WHERE id_content=".$id_content." ".$sqllang);
        }
    }

    $this->dbQuery("DELETE FROM k_contentdata	WHERE id_content=".$id_content." ".$sqllang);
	$data = $this->dbMulti("SELECT * FROM k_contentdata WHERE id_content=".$id_content);

	// il n'y a plus de data (du tout !)
	if(sizeof($data) == 0){
		$this->dbQuery("DELETE FROM k_content 				WHERE id_content=".$id_content);
		$this->dbQuery("DELETE FROM k_contentitem			WHERE id_content=".$id_content);
		$this->dbQuery("DELETE FROM k_contentalbum			WHERE id_content=".$id_content);
		$this->dbQuery("DELETE FROM k_contentshop			WHERE id_content=".$id_content);

		$this->dbQuery("DELETE FROM k_contentcategory		WHERE id_content=".$id_content);
		$this->dbQuery("DELETE FROM k_contentchapter		WHERE id_content=".$id_content);
		$this->dbQuery("DELETE FROM k_contentcomment		WHERE id_content=".$id_content);
		$this->dbQuery("DELETE FROM k_contentgroup			WHERE id_content=".$id_content);
		$this->dbQuery("DELETE FROM k_contentgroupbusiness	WHERE id_content=".$id_content);
		$this->dbQuery("DELETE FROM k_contentrate			WHERE id_content=".$id_content);
		$this->dbQuery("DELETE FROM k_contentsearch			WHERE id_content=".$id_content);
		$this->dbQuery("DELETE FROM k_contentversion		WHERE id_content=".$id_content);

		$this->dbQuery("DELETE FROM k_userasso				WHERE id_content=".$id_content);
		$this->dbQuery("DELETE FROM k_contentasso			WHERE bContent=".$id_content." OR aContent=".$id_content);

		$this->hookAction('contentRemove', $this->id_content, $id_type, $id_content, $language);
	}

	if($type['is_gallery']) $this->contentAlbumFamily();
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function contentView($id_content){
	if(intval($id_content) == 0) return false;
	$this->dbQuery("UPDATE k_content SET contentView=contentView+1 WHERE id_content=".$id_content);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function contentCacheBuild($id_content, $opt=array()){

	if(intval($id_content) <= 0) return false;
	$cache = array();

	$languages = $this->dbMulti("SELECT * FROM k_contentdata WHERE id_content=".$id_content);
	foreach($languages as $e){
	
		// Langue
		$cache['language'][$e['language']] = array(
			'contentUrl'	=> $e['contentUrl'],
			'contentName'	=> $e['contentName']
		);

		// Categorie
		$cats = $this->dbMulti("
			SELECT * FROM k_contentcategory
			INNER JOIN k_categorydata ON k_contentcategory.id_category = k_categorydata.id_category
			WHERE id_content=".$id_content." AND language='".$e['language']."' AND is_selected=1
		");

		foreach($cats as $e){
			$cache['category'][$e['language']][] = array(
				'id_category'	=> $e['id_category'],
				'categoryUrl'	=> $e['categoryUrl'],
				'categoryName'	=> $e['categoryName'],
				'categoryMedia'	=> $e['categoryMedia']
			);
		}

	}

	// On sauve
	$def = array('k_content' => array(
		'contentCache' => array('value' => addslashes(json_encode($cache)))
	));

	$this->dbQuery($this->dbUpdate($def)." WHERE id_content=".$id_content);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	
	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function contentCacheTable($id_content, $opt=array()){

	$this->cache->sqlcacheClean();

	if(intval($id_content) <= 0) return false;
	$cache = array();

	# Autres langues
	#
	$languages = $this->dbMulti("SELECT * FROM k_contentdata WHERE id_content=".$id_content);
	foreach($languages as $e){
	
		// Get
		$e = $this->contentGet(array(
			'debug'			=> false,
			'cache'			=> false,
			'id_content'	=> $id_content,
			'language'		=> $e['language'],	
			'raw'			=> trye,
			'useGroup'		=> false,
			'useChapter'	=> false,
			'useField'		=> true,
			'human'			=> true,
			'contentSee'	=> 'ALL',
		));
		$e['contentCache'] = NULL;	


		// Find cat
		$cats = $this->dbMulti("
			SELECT * FROM k_contentcategory
			INNER JOIN k_categorydata ON k_contentcategory.id_category = k_categorydata.id_category
			WHERE id_content=".$id_content." AND language='".$e['language']."' AND is_selected=1
		");
		foreach($cats as $c){
			$c = $this->apiLoad('category')->categoryGet(array(	
				'language'		=> $c['language'],
				'id_category'	=> $c['id_category']
			));

			$e['contentCache']['category'][$c['language']][] = $c;
		}

		// Language
		foreach($languages as $l){
			$tmp = $this->apiLoad('content')->contentGet(array(
				'id_content'	=> $l['id_content'],
				'language'		=> $l['language'],
				'raw'			=> true,
				'assoSearch'	=> false,
				'assoShop'		=> false,
				'assoChapter'	=> false,
				'assoCategory'	=> false,
				'assoGroup'		=> false
			));
			
			$e['contentCache']['language'][$l['language']] = array(
				'contentName'	=> $l['contentName'],
				'contentUrl'	=> $l['contentUrl']
			);
		}
		
		// On sauve
		$chapter = $this->dbMulti("SELECT id_chapter FROM k_contentchapter WHERE id_content=".$e['id_content']);
		$group	 = $this->dbMulti("SELECT id_group   FROM k_contentgroup WHERE id_content=".$e['id_content']);
		$keys[]  = 'content:'.$e['language'].':id_content:'.$id_content;

		foreach($chapter as $c){
			foreach($group as $g){
				$keys[] = 'content:'.$e['language'].':'.$c['id_chapter'].':'.$g['id_group'].':id_content:'.$id_content;
			}
		}

		foreach($keys as $k){
			$this->cache->sqlcacheSet($k, $e, 0);
		}
	}

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
TODO: kill this function, check usage (use type module instead)
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function contentType($opt=array()){ // DEPRECATED
	return $this->apiLoad('type')->typeGet($opt);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
TODO: kill this function, check usage (use type module instead)
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function contentTypeSet($id_type, $def){ // DEPRECATED
	return $this->apiLoad('type')->typeSet($id_type, $def);
}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function contentGroupGet($id_content, $id_type, $language=NULL){

		$data   = array();
		$type   = $this->apiLoad('type')->typeGet(array(
			'id_type' => $id_type
		));
		$groups = $this->apiLoad('user')->userGroupGet(array(
			'profile'		=> true,
			'threadFlat'	=> true
		));

		foreach($groups as $e){
			$data[$e['id_group']] = $e;
		}

		if($id_content == NULL) return $data;

		if($type['is_businessloc'] == '1'){
			$table = 'k_contentgroupbusinessloc';
			$lan   = empty($language) ? NULL : " AND country='".$language."'";
		}else{
			$table = 'k_contentgroupbusiness';
			$lan   = empty($language) ? NULL : " AND language='".$language."'";
		}

		$group = $this->dbMulti("SELECT * FROM ".$table." WHERE id_content=".$id_content.$lan);

		foreach($group as $e){
			if(is_array($data[$e['id_group']])){
				$data[$e['id_group']] = array_merge($data[$e['id_group']], $e);
			}else{
				$data[$e['id_group']] = $e;
			}
		}

		return $data;
	}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function contentMediaLink($opt){

	if(!is_array($opt['url'])) $opt['url'] = array($opt['url']);

	# Get content
	#
	$content = $this->contentGet(array(
		'id_content'	=> $opt['id_content'],
		'raw'			=> true
	));
	if($content['id_content'] == NULL){
		if($opt['debug']) $this->pre("contet not found with id_content", $opt['id_content']);
		return false;
	}
	
	# CLEAR and Exit 
	#
	if($opt['clear']){
		$this->dbQuery("UPDATE k_content SET contentMedia='' WHERE id_content=".$opt['id_content']);
		if($opt['debug']) $this->pre("CLEAR", $this->db_query, $this->db_error);
		return true;
	}

	// Check if file EXIST
/*
	if(!file_exists(KROOT.$opt['url'])){
		if($opt['debug']) $this->pre("file not found : ".KROOT.$opt['url']);
		return false;
	}
*/

	# Update ARRAY
	#
	$media = json_decode($content['contentMedia'], true);
	$media = is_array($media) ? $media : array();
	
	// Si on souhait conserver les autres element, verifier s'il n'y a pas de doublon
	if(!$opt['onlyMe']){
		foreach($opt['url'] as $n => $e){
			foreach($media as $m){
				if($e == $m['url']) unset($opt['url'][$n]);
			}
		}
	}
	
	# Update BDD (if needed)
	#
	if(sizeof($opt['url']) > 0){
		if($opt['onlyMe']) $media = array();
		
		// Type (image=picture -- JS type corruption ?)
		$type		= ($opt['type'] == NULL) ? $this->mediaType($e) : $opt['type'];
		$type		= ($type == 'picture') ? 'image' : $type;

		foreach($opt['url'] as $e){
			$media[]	= array('type' => $type, 'url' => $e);
		}
	
		$def = array('k_content' => array(
			'contentMedia' => array('value' => json_encode($media))
		));
	
		$this->dbQuery($this->dbUpdate($def)." WHERE id_content=".$opt['id_content']);
		if($opt['debug']) $this->pre("INSERT", $this->db_query, $this->db_error);

		return true;
	}
	
	return false;
}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
// Mettre a jour les PARENT et CHILDREN et les sauver en base
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public function contentAlbumFamily($id_content=0){

	    $q = "SELECT id_content, id_album FROM k_contentalbum";

	    if($id_content > 0) $q .= " WHERE id_content = ".$id_content;

	    $albums = $this->dbMulti($q);
	    if(sizeof($albums) == 0) return true;

		foreach($albums as $e){
			$tree = $this->contentAlbumFamilyParent($e);
			$tree = (sizeof($tree) > 0) ? implode(',', array_reverse($tree)) : '';

			$this->dbQuery("UPDATE k_contentalbum SET contentAlbumParent='".$tree."' WHERE id_content=".$e['id_content']);
		#	$this->pre($this->db_query);
		}


		foreach($albums as $e){
			$tree = $this->contentAlbumFamilyChildren($e);
			$tree = (sizeof($tree) > 0) ? implode(',', $tree) : '';

			$this->dbQuery("UPDATE k_contentalbum SET contentAlbumChildren='".$tree."' WHERE id_content=".$e['id_content']);
		#	$this->pre($this->db_query);
		}

		return true;
	}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	Trouver tous les PARENTS pour un ALBUM
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function contentAlbumFamilyParent($e, $line=array()){

	if($e['id_album'] > 0){
		$line[] = $e['id_album'];
		$next	= $this->dbOne("SELECT id_content, id_album FROM k_contentalbum WHERE id_content=".$e['id_album']);

		return $this->contentAlbumFamilyParent($next, $line);
	}else{
		return $line;
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	Trouver tous les CHILDREN pour une CATEGORY
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function contentAlbumFamilyChildren($e, &$line=array()){

	$children = $this->dbMulti("SELECT id_content, id_album FROM k_contentalbum WHERE id_album=".$e['id_content']);

	foreach($children as $child){
		$line[] = $child['id_content'];
		$this->contentAlbumFamilyChildren($child, $line);
	}
	
	return $line;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function contentItemProxyPush($opt){
	
	# < Get
	#
	$proxy = $this->contentItemProxyGet($opt);


	# > Set
	#
	if($proxy){
	}


	# = Return
	#
	/*$img = $app->mediaUrlData(array(
		'url'	=> $item['contentItemUrl'],
		'mode'	=> 'width',
		'value'	=> $value,
		'cache'	=> true
	));*/

}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function contentItemProxyGet($opt=array()){
	
	$args = array(
		'id_type'	=> $opt['id_type'],
		'id_parent' => $opt['source'],
		'is_item'	=> true,
		'debug'		=> true,
		'raw'		=> true,
	);
	
	if(isset($opt['proxy'])){
		$args['proxyKey'] = $opt['proxy'];
		$solo = true;
	}

	$proxy = $this->contentGet($args);
	return ($solo) ? $proxy[0] : $proxy;

}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function contentItemProxySet($opt=array()){


	
	die();
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function contentAssoGet($aContent, $aType, $aField, $bType){

	$asso = $this->dbMulti(
		"SELECT * FROM k_contentasso WHERE ".
		"aContent=".$aContent." AND aType=".$aType." AND aField=".$aField." AND bType=".$bType." ".
		"ORDER BY assoOrder ASC"
	);
	
	foreach($asso as $e){
		$r[] = $e['bContent'];
	}

	return is_array($r) ? $r : array();
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
 + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function contentAssoSet($aContent, $aType, $aField, $bType, $bContent){

	$this->dbQuery("DELETE FROM k_contentasso WHERE aContent=".$aContent." AND aType=".$aType." AND aField=".$aField." AND bType=".$bType);
	#$this->pre($this->db_query, $this->db_error);

	if(sizeof($bContent) > 0){
		$used = array();
		
		foreach($bContent as $n => $id_content){
			if($id_content > 0 && !in_array($id_content, $used)){
				$added[]	= "(".$aContent.", ".$aType.", ".$aField.", ".$bType.", ".$id_content.", ".$n.")";
				$used[]		= $id_content;
			}
		}
		
		if(sizeof($added) > 0){
			$this->dbQuery("INSERT IGNORE INTO k_contentasso (aContent, aType, aField, bType, bContent, assoOrder) VALUES ".implode(',', $added));
			#$this->pre($this->db_query, $this->db_error);
		}
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function contentAssoUserGet($id_content, $id_type, $id_field){

	$asso = $this->dbMulti(
		"SELECT * FROM k_contentasso WHERE ".
		"aContent=".$id_content." AND aType=".$id_type." AND aField=".$id_field." AND bType IS NULL"
	);

	foreach($asso as $e){
		$r[] = $e['bUser'];
	}

	return is_array($r) ? $r : array();
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	id_content	Le contenu maitre de la liaison
	id_type		type type
 + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function contentAssoUserSet($id_content, $id_type, $id_field, $ids_user){

	$this->dbQuery("DELETE FROM k_contentasso WHERE aContent=".$id_content." AND aType=".$id_type." AND aField=".$id_field);
	#$this->pre($this->db_query, $this->db_error);

	if(sizeof($ids_user) > 0){
		foreach($ids_user as $id_user){
			if($id_user > 0) $added[] = "(".$id_content.", ".$id_type.", ".$id_field.", NULL, ".$id_user.")";
		}

		if(sizeof($added) > 0){
			$this->dbQuery("INSERT IGNORE INTO k_contentasso (aContent, aType, aField, bType, bUser) VALUES ".implode(',', $added));
		#	$this->pre($this->db_query, $this->db_error);
		}
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
 + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function contentAssoTag($id_content, $id_field, $language, $value){

	$this->dbQuery("DELETE FROM k_contenttag WHERE id_content=".$id_content." AND language='".$language."'");
	#$this->pre($this->db_query, $this->db_error);

	foreach($value as $e){

		// Sauver le TAG
		$def = array('k_contenttag' => array(
			'id_content'	=> array('value' => $id_content),
			'id_field'		=> array('value' => $id_field),
			'language'		=> array('value' => $language),
			'contentTag'	=> array('value' => addslashes(trim($e)))
		));
		
		$this->dbQuery($this->dbInsert($def, array('ignore' => true)));
		#$this->pre($this->db_query, $this->db_error);
		
		
		// Alimenter le CLOUD
		$def = array('k_contenttagcloud' => array(
			'language'		=> array('value' => $language),
			'contentTag'	=> array('value' => addslashes(trim($e)))
		));
		
		$this->dbQuery($this->dbInsert($def, array('ignore' => true)));
		#$this->pre($this->db_query, $this->db_error);
	
		$tmp[] = "'".$def['k_contenttagcloud']['contentTag']['value']."'";
	}

	$tmp = $this->dbMulti("
		SELECT contentTag, language, COUNT(*) AS how FROM k_contenttag
		WHERE contentTag IN (".implode(',', $tmp).")
		GROUP BY language, contentTag
	");

	foreach($tmp as $e){
		$this->dbQuery("UPDATE k_contenttagcloud SET how=".$e['how']." WHERE contentTag='".$e['contentTag']."' AND language='".$e['language']."'");
		#$this->pre($this->db_query, $this->db_error);
	}
}


}
