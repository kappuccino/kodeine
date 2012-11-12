<?php
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Last release		2011.05.28
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

class ad extends coreApp {

function __clone(){}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function ad(){
	$this->size = array(
		array('width' => 300, 'height' => 100, 'name' => '300x100 Rectangle'),
		array('width' => 728, 'height' => 90,  'name' => '728x90  Leaderboard'),
		array('width' => 468, 'height' => 60,  'name' => '468x60  Full Banner'),
		array('width' => 234, 'height' => 60,  'name' => '234x60  Half Banner'),
		array('width' => 120, 'height' => 420, 'name' => '210x240 Vertical Banner'),
		array('width' => 300, 'height' => 250, 'name' => '300x250 Medium Rectangle'),
		array('width' => 720, 'height' => 300, 'name' => '720x300 Pop-Under'),
		array('width' => 120, 'height' => 90,  'name' => '120x90  Button 1'),
		array('width' => 120, 'height' => 60,  'name' => '120x60  Button 2'),
		array('width' => 88,  'height' => 31,  'name' => '88x31   Micro Bar '),
		array('width' => 300, 'height' => 600, 'name' => '300x600 Half Page'),
		array('width' => 160, 'height' => 600, 'name' => '160x600 Wide Skycrapper'),
		array('width' => 120, 'height' => 600, 'name' => '120x600 Skycrapper'),
		array('width' => 250, 'height' => 250, 'name' => '250x250 Square Pop-U'),
		array('width' => 336, 'height' => 280, 'name' => '336x280 Large Rectangle'),
		array('width' => 240, 'height' => 400, 'name' => '240x400 Vertical Rectangle'),
		array('width' => 180, 'height' => 150, 'name' => '180x150 Rectangle'),
		array('width' => 125, 'height' => 125, 'name' => '125x125 Square Button'),
		array('width' => 'x', 'height' => 'x', 'name' => 'Habillage (Background cliquable)')
	);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function adSet($id_ad, $def){

	if($id_ad > 0){
		$q = $this->dbUpdate($def)." WHERE id_ad=".$id_ad;
	}else{
		$q = $this->dbInsert($def);
	}

	@$this->dbQuery($q);
	if($this->db_error != NULL) return false;

	$this->id_ad = ($id_ad > 0) ? $id_ad : $this->db_insert_id;

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function adGet($opt=array()){

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep='adGet() @='.json_encode($opt));

	# Gérer les options
	#
	$limit		= ($opt['limit'] != '') 	? $opt['limit']		: 30;
	$offset		= ($opt['offset'] != '') 	? $opt['offset']	: 0;
	
	if($opt['id_ad'] > 0){
		$dbMode = 'dbOne';
		$cond[] = "k_ad.id_ad=".$opt['id_ad'];
	}else{
		$dbMode = 'dbMulti';
	}

	if($dbMode == 'dbMulti'){
		if(!isset($opt['is_active'])) $opt['is_active'] = true;
		$cond[] = "k_ad.is_active=".(($opt['is_active']) ? '1' : '0');
	}

	
	# Demander la ZONE par sa CLE
	#
	if($opt['zoneCode'] != NULL){
		#$zone = $this->adZoneGet(array('zoneCode' => $opt['zoneCode']));
		#if($zone['id_adzone'] == NULL) return false;

		#$opt['withZone'] = true;
		$dbMode = 'dbMulti';
	#	$cond[] = 'k_adzone.id_adzone='.$zone['id_adzone'];
		$cond[] = 'k_adzone.zoneCode=\''.$opt['zoneCode']."'";
	}
	
	#if($opt['withZone']){
		$inner = "\nINNER JOIN k_adzone ON k_ad.id_adzone = k_adzone.id_adzone\n";
	#}


	# Gerer la recherche
	#
	if($opt['search'] != NULL){
		$cond[] = "adName LIKE '%".addslashes($opt['search'])."%'";
	}


	# Former les CONDITIONS
	#		
	if(sizeof($cond) > 0) $where = "WHERE ".implode(" AND ", $cond);


	# Former les LIMITATIONS et ORDRE
	#
	if($dbMode == 'dbMulti'){
		if(!$opt['noOrder']){
			$sqlOrder = (isset($opt['order']) && isset($opt['direction']))
				? "\nORDER BY ".$opt['order']." ".$opt['direction']
				: "\nORDER BY id_ad DESC";
		}

		if(!$opt['noLimit']) $sqlLimit = "\nLIMIT ".$offset.",".$limit;
	}


	# AD
	#
	if($sqlLimit != '') $found = 'SQL_CALC_FOUND_ROWS';
	$ad = $this->$dbMode("SELECT ".$found." * FROM k_ad\n". $inner . $where . $sqlOrder . $sqlLimit);

	$this->total	= $this->db_num_total;
	$this->limit	= $limit;
	
	if($dbMode == 'dbOne'){
		$ad = array($ad);
		$reverse = true;
	}
	
	foreach($ad as $idx => $e){
		$ad[$idx]['adMediaRaw'] = $e['adMedia'];
		$ad[$idx]['adMedia']	= json_decode($e['adMedia'], true);
	}
	
	if($reverse) $ad = $ad[0];	

	if($opt['debug']) $this->pre($this->db_query, $this->db_error, $ad);

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep);

	return $ad;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function adPick($opt){

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep='adPick() @='.json_encode($opt));

	$opt['noLimit'] = true;
	$opt['noOrder'] = true;

	$ads = $this->adGet($opt);

	if(sizeof($ads) > 0){
		$ad = $ads[array_rand($ads, 1)];

		$this->adCountView($ad['id_ad']);

		if($ad['adCode'] == NULL && sizeof($ad['adMedia']) > 0){
			$ad['html'] = "<a href=\"/ad".$ad['id_ad']."\" target=\"_blank\"><img src=\"".$ad['adMedia'][0]['url']."\" /></a>";
		}

		$out = $ad;
	}else{
		$out = array();
	}

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep);

	return $out;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function adCountView($id_ad){
	$this->dbQuery("UPDATE k_ad SET adView = adView +1 WHERE id_ad=".$id_ad);
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function adRemove($id_ad){
	if($id_ad == NULL) return false;
	$this->dbQuery("DELETE FROM k_ad WHERE id_ad=".$id_ad);
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function adZoneGet($opt=array()){

	if($opt['zoneCode'] != NULL){
		$zone = $this->dbOne("SELECT * FROM k_adzone WHERE zoneCode = '".$opt['zoneCode']."'");
	}else
	if($opt['id_adzone'] != NULL){
		$zone = $this->dbOne("SELECT * FROM k_adzone WHERE id_adzone = ".$opt['id_adzone']);
	}else{
		$zone = $this->dbMulti("SELECT * FROM k_adzone");
	}

	if($opt['debug']) $this->pre($this->db_query, $this->db_error);

	return $zone;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function adZoneSet($id_adzone, $def){

#	$def['k_newsletterlist']['listDateUpdate'] = array('function' => 'NOW()');

	if($id_adzone  > 0){
		$q = $this->dbUpdate($def)." WHERE id_adzone = ".$id_adzone;
	}else{
#		$def['k_adzonz']['listDateCreation'] = array('function' => 'NOW()');
		$q = $this->dbInsert($def);
	}

	@$this->dbQuery($q);
	if($this->db_error != NULL) return false;

	$this->id_adzone = ($id_adzone > 0) ? $id_adzone : $this->db_insert_id;

	return true;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function asZoneRemove($id_adzone){

	if($id_adzone == NULL) return false;

	$this->dbQuery("DELETE FROM k_adzone WHERE id_adzone=".$id_adzone);

	return true;
}

}

?>