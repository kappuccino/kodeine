<?php
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Last release		2011.12.13
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

class newsletter extends coreApp {

function __clone(){}
public function newsletter(){
    $config	            = $this->configGet('newsletter');
    $this->connector    = $config['connector'];
    //$this->pre($config);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function connector(){

	$pref = $this->configGet('newsletter');

	if($pref['connector'] == 'mailChimp'){
		$con = 'mailChimp';
	}else{
		$con = 'cloudApp';
	}

	return $this->apiLoad('newsletter'.ucfirst($con));	
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function newsletterSet($id_newsletter, $def){

	if($id_newsletter > 0){
		$q = $this->dbUpdate($def)." WHERE id_newsletter=".$id_newsletter;
	}else{
		$q = $this->dbInsert($def);
	}

	@$this->dbQuery($q);
	if($this->db_error != NULL) return false;

	$this->id_newsletter = ($id_newsletter > 0) ? $id_newsletter : $this->db_insert_id;

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function newsletterGet($opt=array()){

	# Gérer les options
	#
	$limit		= ($opt['limit'] != '') 	? $opt['limit']		: 30;
	$offset		= ($opt['offset'] != '') 	? $opt['offset']	: 0;

	if($opt['id_newsletter'] > 0){
		$dbMode = 'dbOne';
		$cond[] = "k_newsletter.id_newsletter=".$opt['id_newsletter'];
	}else{
		$dbMode = 'dbMulti';
	}

	# Gerer la recherche
	#
	if($opt['search'] != NULL){
		$cond[] = "(newsletterName LIKE '%".addslashes($opt['search'])."%' OR newsletterTitle LIKE '%".addslashes($opt['search'])."%')";
	}

	# Former les CONDITIONS
	#
	if(sizeof($cond) > 0) $where = "WHERE ".implode(" AND ", $cond);


	# Former les LIMITATIONS et ORDRE
	#
	if($dbMode == 'dbMulti'){
		$sqlOrder = (isset($opt['order']) && isset($opt['direction']))
			? "\nORDER BY ".$opt['order']." ".$opt['direction']
			: "\nORDER BY id_newsletter DESC";

		$sqlLimit = "\nLIMIT ".$offset.",".$limit;
	}

	# NEWSLETTER
	#
	$newsletter = $this->$dbMode(
		"SELECT SQL_CALC_FOUND_ROWS * FROM k_newsletter\n".
		$where . $sqlOrder . $sqlLimit
	);

	$this->total	= $this->db_num_total;
	$this->limit	= $limit;
	
	if($dbMode == 'dbOne' && $newsletter['id_newsletter'] == $opt['id_newsletter']){
		$newsletterType = array_values(explode('@@', $newsletter['newsletterType']));
		unset($newsletterType[sizeof($newsletterType)-1], $newsletterType[0]);
		$newsletter['newsletterType'] = $newsletterType;

		$newsletterSearch = array_values(explode('@@', $newsletter['newsletterSearch']));
		unset($newsletterSearch[sizeof($newsletterSearch)-1], $newsletterSearch[0]);
		$newsletter['newsletterSearch'] = $newsletterSearch;

		$newsletterGroup = array_values(explode('@@', $newsletter['newsletterGroup']));
		unset($newsletterGroup[sizeof($newsletterGroup)-1], $newsletterGroup[0]);
		$newsletter['newsletterGroup'] = $newsletterGroup;

		$newsletterList = array_values(explode('@@', $newsletter['newsletterList']));
		unset($newsletterList[sizeof($newsletterList)-1], $newsletterList[0]);
		$newsletter['newsletterList'] = $newsletterList;

		$newsletter['newsletterStyle'] = json_decode($newsletter['newsletterStyle'], true);
	}

	if($opt['debug']) $this->pre($this->db_query, $this->db_error, $newsletter__);
	
	return $newsletter;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function newsletterRemove($id_newsletter){
	if($id_newsletter == NULL) return false;
	$this->dbQuery("DELETE FROM k_newsletter WHERE id_newsletter=".$id_newsletter);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function newsletterPreview($id_newsletter){

    $pref = $this->configGet('newsletter');
    // Envoi de mail de test
    $data = $this->apiLoad('newsletter')->newsletterGet(array(
        'id_newsletter'     => $id_newsletter
    ));
    require_once(PLUGIN.'/phpmailer/class.phpmailer.php');
    $mail = new PHPMailer();
    $mail->CharSet = "UTF-8";
    $mail->SetFrom('noreply@'.$_SERVER['HTTP_HOST'], $_SERVER['HTTP_HOST']);
    $mails = explode(',', $pref['test']);

    foreach($mails as $m) {
        if(trim($m) != '') $mail->AddAddress(trim($m));
    }

    $mail->Subject  = $data['newsletterTitle'];
    $mail->AltBody  = strip_tags($data['newsletterHtml']);
    $mail->MsgHTML($data['newsletterHtml']);

    return $mail->Send();
    /*
        $pref = $this->configGet('newsletter');
        $data = $this->newsletterGet(array('id_newsletter' => $id_newsletter));
        $body = $this->newsletterPrepareBody($id_newsletter, $newsletter['newsletterHtml']);

        $send = array(
            'mails'				=> array_map('trim', explode(',', $pref['test'])),
            'newsletterName'	=> $data['newsletterTitle'],
            'newsletterHtml'	=> $body
        );

        $rest = new newsletterREST($pref['auth'], $pref['passw']);
        $prev = $rest->request('/preview.php', 'POST', $send);
        $prev = json_decode($prev, true);

        if(!$prev['success']) die($this->pre($prev));

        return $prev['success'];*/
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function newsletterDuplicate($id_newsletter){

	# Originale
	$from = $this->dbOne("SELECT * FROM k_newsletter WHERE id_newsletter=".$id_newsletter);

    foreach($this->dbMulti("SHOW COLUMNS FROM k_newsletter WHERE Field NOT IN('id_newsletter', 'newsletterSendDate', 'newsletterConnectorValue', 'newsletterConnectorId')") as $e){
        $fields[] = $e['Field'];
    }

	foreach($fields as $df){
		$tmp[] = "'".addslashes($from[$df])."'";
	}

	$this->dbQuery("INSERT INTO k_newsletter (".implode(', ', $fields).") VALUES (".implode(',', $tmp).")");
	if($this->db_error) die($this->pre($this->db_query, $this->db_error));
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function newsletterListImport($id_newsletterlist, $file){

	$content	= file_get_contents($file);
	$lines		= explode("\n", $content);
	$lines		= array_map('trim', $lines);

	if(sizeof($lines) == 0) return false;
	
	return $this->newsletterListImportJob(array(
		'id_newsletterlist' => $id_newsletterlist,
		'items'				=> $lines
	));
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function newsletterListImportJob($opt){

	$id_newsletterlist  = $opt['id_newsletterlist'];
	$items				= $opt['items']; 

	foreach($items as $mail){
		$mail = trim($mail);
		$flag = (filter_var($mail, FILTER_VALIDATE_EMAIL) === FALSE) ? 'ERROR' : 'VALID';
		$ext  = $this->dbOne("SELECT id_newslettermail FROM k_newslettermail WHERE mail='".addslashes($mail)."' LIMIT 1");

		if($ext['id_newslettermail'] != ''){
			$last = $ext['id_newslettermail'];
		}else{
			$this->dbQuery("INSERT INTO k_newslettermail (mail, flag) VALUES ('".addslashes($mail)."', '".$flag."')");
			$last = $this->db_insert_id;
		}

		$this->dbQuery("INSERT IGNORE INTO k_newsletterlistmail (id_newslettermail, id_newsletterlist) VALUES ('".$last."', '".$id_newsletterlist."')");
		#$this->pre($this->db_query, $this->db_error);
	}
	
	return true;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function newsletterListGet($opt=array()){

	if($opt['id_newsletterlist']){
		$type = $this->dbOne("SELECT * FROM k_newsletterlist WHERE id_newsletterlist=".$opt['id_newsletterlist']);
	}else{
		$type = $this->dbMulti("SELECT * FROM k_newsletterlist");
	}
	
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);

	return $type;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function newsletterListSet($id_newsletterlist, $def){

	$def['k_newsletterlist']['listDateUpdate'] = array('function' => 'NOW()');

	if($id_newsletterlist  > 0){
		$q = $this->dbUpdate($def)." WHERE id_newsletterlist=".$id_newsletterlist;
	}else{
		$def['k_newsletterlist']['listDateCreation'] = array('function' => 'NOW()');
		$q = $this->dbInsert($def);
	}

	@$this->dbQuery($q);
	if($this->db_error != NULL) return false;

	$this->id_newsletterlist = ($id_newsletterlist > 0) ? $id_newsletterlist : $this->db_insert_id;

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function newsletterListRemove($id_newsletterlist){
	if($id_newsletterlist == NULL) return false;

	$this->dbQuery("DELETE FROM k_newsletterlist 	 WHERE id_newsletterlist=".$id_newsletterlist);
	$this->dbQuery("DELETE FROM k_newsletterlistmail WHERE id_newsletterlist=".$id_newsletterlist);

	return true;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function newsletterListEmpty($id_newsletterlist){
	if($id_newsletterlist == NULL) return false;

	$ms = $this->dbMulti("SELECT * FROM k_newsletterlistmail WHERE id_newsletterlist=".$id_newsletterlist);

	foreach($ms as $m){
		$count = $this->dbOne("SELECT COUNT(id_newsletterlist) AS c FROM k_newsletterlistmail WHERE id_newslettermail=".$m['id_newslettermail']);
		if($count['c'] == 1) $del[] = $m['id_newslettermail'];
	}

	$this->dbQuery("DELETE FROM k_newsletterlistmail WHERE id_newsletterlist=".$_GET['id_newsletterlist']);

	if(sizeof($del) > 0){
		$this->dbQuery("DELETE FROM k_newslettermail WHERE id_newslettermail IN(".implode(',', $del).")");
	}

	return true;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
public function newsletterListClearUnaffected(){
	$this->dbQuery("DELETE FROM k_newslettermail WHERE id_newslettermail NOT IN(SELECT id_newslettermail FROM k_newsletterlistmail)");
}
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function newsletterListMailSubscribed($opt=array()){

	$email = $opt['email'];
	$valid = (filter_var($email, FILTER_VALIDATE_EMAIL) === FALSE) ? false : true;

	if(!$valid) return array();

	$r = $this->dbMulti("
		SELECT id_newsletterlist FROM k_newsletterlistmail
		INNER JOIN k_newslettermail ON k_newsletterlistmail.id_newslettermail = k_newslettermail.id_newslettermail
		WHERE mail = '".$email."'
	");
	
	return $this->dbKey($r, 'id_newsletterlist', true);
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
Newsletter a envoyer (nettoyage du corps du mail pour les images et les liens)
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function newsletterPrepareBody($id_newsletter, $data=NULL){

	$from = $this->newsletterGet(array('id_newsletter' => $id_newsletter));
	if($data == NULL) $data = $from['newsletterHtml'];
	
	# Image
	#
	if(preg_match_all("#<img(.*)?/>#msU", $data, $imgsTag, PREG_SET_ORDER)){
		foreach($imgsTag as $imgTag){
            $props = array();
		    if(preg_match_all('#(alt|title|src|height|width)=\"([^"]*)\"#msU', $imgTag[1], $props, PREG_SET_ORDER)){

                $myprop = array();
		    	// Recuperer simplement les properties
		    	foreach($props as $prop){
		    		$myprop[strtolower($prop[1])] = $prop[2];
		    	}
		    	// Si je n'ai pas la taille de definit
		    	if(intval($myprop['height']) == 0 && intval($myprop['width']) == 0 && is_file(KROOT.$myprop['src'])){
		    		$size = getimagesize(KROOT.$myprop['src']);
		    		$data = str_replace($imgTag[1], ' '.$imgTag[1].' '.$size[3].' ', $data);
		    	}else {

                    $urllocal = str_replace("http://".$_SERVER['HTTP_HOST'], "", $myprop['src']);
                    $urllocal = str_replace("http://".str_replace('www.', '', $_SERVER['HTTP_HOST']), "", $myprop['src']);


                    //$this->pre(str_replace('www.', '', $_SERVER['HTTP_HOST'])$myprop,$urllocal, is_file(KROOT.$urllocal), strpos($urllocal, '.cache') );

                    //if(intval($myprop['height']) > 0 && intval($myprop['width']) > 0) echo $urllocal.' ok--';
                    //if(is_file(KROOT.$urllocal) && strpos($urllocal, '.cache') === false) echo KROOT.$urllocal.' ok2--';
                    //echo '<br>';
                    if(is_file(KROOT.$urllocal)) {
                        $size = getimagesize(KROOT.$urllocal);
                        $ratio = $size[0] / $size[1];
                        if($ratio != 0) {
                            if(intval($myprop['width']) > 0 && intval($myprop['height']) == 0) {
                                $myprop['height'] = round($myprop['width'] / $ratio);
                            }
                            if(intval($myprop['height']) > 0 && intval($myprop['width']) == 0) {
                                $myprop['width'] = round($myprop['height'] * $ratio);
                            }
                        }
                    }
                    //$this->pre($myprop);

                    if(intval($myprop['height']) > 0 && intval($myprop['width']) > 0 && is_file(KROOT.$urllocal) && strpos($urllocal, '.cache') === false){

                        $size = getimagesize(KROOT.$urllocal);

                        $img = $this->mediaUrlData(array(
                            'url'       => $urllocal,
                            'mode'      => 'crop',
                            'value'     => $myprop['width'],
                            'second'    => $myprop['height']
                        ));
                        $data = str_replace("src=\"".$myprop['src']."\"", "src=\"".$img['img']."\"", $data);
                        $myprop['src'] = $img['img'];
                    }
                }
                //$this->pre($myprop, $size);

		    	// Mettre le http:// devant les URL des images
				if(!preg_match("#^http#", $myprop['src'])){
					$data = str_replace("src=\"".$myprop['src']."\"", "src=\"http://".$_SERVER['HTTP_HOST'].$myprop['src']."\"", $data);
				}

		    	unset($myprop, $size, $size);
		    }
		}
	}
	
	# Lien
	#
	preg_match_all("#href=\"([^\"].*?)\"#", $data, $link, PREG_SET_ORDER);
	if(sizeof($link) > 0){
		foreach($link as $e){
			if(!preg_match("@^(http|#)@", $e[1])){
				$data = str_replace("href=\"".$e[1]."\"", "href=\"http://".($_SERVER['SERVER_NAME'].$e[1])."\"", $data);
			}
		}
	}

	
	# Date
	#
	$data = str_replace("<currentday>",			date("d"),		$data);
	$data = str_replace("<currentdayname>",		strftime("%A"), $data);
	$data = str_replace("<currentmonth>",		strftime("%m"),	$data);
	$data = str_replace("<currentmonthname>",	strftime("%B"), $data);
	$data = str_replace("<currentyear>",		date("Y"),		$data);

	# Wrapp
	#
	if(!preg_match("#<body>#", $data) && $from['newsletterHtmlDesigner'] == ''){

		// Image de fond
		if($from['newsletterStyle']['backgroundImage'] != ''){
			$data = "<div style=\"background: ".($from['newsletterStyle']['backgroundColor'])." url(".($from['newsletterStyle']['backgroundImage']).") no-repeat center top; background-attachment:fixed;\">".$data."</div>";
		}else
	
		// Couleur de fond
		if($from['newsletterStyle']['backgroundColor'] != ''){
			$data = "<div style=\"background:".$from['newsletterStyle']['backgroundColor'].";\">".$data."</div>";
		}

		$data = "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n".
		"<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" /></head>\n".
		"<body>\n\n".$data."\n\n</body></html>";
	}

	return $data;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
Newsletter provenant du Designer > suppression de toutes les balises propres au designer
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function newsletterDesignerCompil($html){

    $html = preg_replace('#<!--TEMPLATE-->(.*?)<!--/TEMPLATE-->#is', $newtext, $html);
    $html = preg_replace('#\<\!\-\-TEMPLATE\-\-\>(.*)\<\!\-\-\/TEMPLATE\-\-\>#i', '', $html);
    $html = str_replace('<a class="btn duplicate">Dupliquer</a>', '', $html);
    $html = str_replace('<a class="btn delete">Supprimer</a>', '', $html);
    $html = preg_replace("#<script([^>]*)>(.*)</script>#", "", $html);
    $html = preg_replace("#<link([^>]*)>#", "", $html);

    // Doctype
    $doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
';
    $html = $doctype.$html;
	
	return $html;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function newsletterPoolPopulation($id_newsletter){

	$data = $this->newsletterGet(array('id_newsletter' => $id_newsletter));

	# Liste tous les user qui match les criteres de recherche
	if(sizeof($data['newsletterSearch']) > 0){
		foreach($data['newsletterSearch'] as $e){
			$grp = $this->apiLoad('user')->userSearch(array('id_search' => $e, 'limit' => 9999999));
			if(sizeof($grp) > 0){
				foreach($grp as $e){
					$id[] = $e['id_user'];
				}
			}
		}
	}

	# Liste tous les user des groupes selectionnes
	if(sizeof($data['newsletterGroup']) > 0){
		foreach($data['newsletterGroup'] as $e){
			$grp = $this->dbMulti("
				SELECT * FROM k_user
				INNER JOIN k_userdata ON k_user.id_user = k_userdata.id_user
				WHERE id_group=".$e
			);
			if(sizeof($grp) > 0){
				foreach($grp as $e){
					$id[] = $e['id_user'];
				}
			}
		}
	}
	
	# Final, demande tous les user
	#
	if(sizeof($id) > 0){
		//$protect	= ($data['newsletterAllUser']) ? NULL : "userNewsletter=1 AND ";
		$population = $this->dbMulti("
			SELECT * FROM k_user
			INNER JOIN k_userdata ON k_user.id_user = k_userdata.id_user
			WHERE ".$protect." k_user.id_user IN(".implode(',', array_unique($id)).")"
		);
	}else{
		$population = array();
	}

	# Constuir la liste des mails utilises
	$used = array();
	foreach($population as $e){
		$used[$e['userMail']] = $e[''];
	}

	# AjoutNewsletter List
	# 
	if(sizeof($data['newsletterList']) > 0){
		foreach($data['newsletterList'] as $e){
			$tmp = $this->dbMulti("
				SELECT * FROM  k_newslettermail
				INNER JOIN k_newsletterlistmail ON k_newslettermail.id_newslettermail = k_newsletterlistmail.id_newslettermail
				WHERE k_newsletterlistmail.id_newsletterlist=".$e." AND k_newslettermail.flag = 'VALID'"
			);
			foreach($tmp as $t){
				if(!in_array($t['mail'], $used)) $add[] = $t['mail'];
			}
		}
	}

	# Raw mails (on rajoute a ADD si il n'existe pas)
	#
	if(strlen(trim($data['newsletterListRaw'])) > 0){
		foreach(explode("\n", $data['newsletterListRaw']) as $line){
			$line = trim($line);
			if(!in_array($line, $used)) $add[] = $line;
		}
		$add = array_unique($add);
	}

	
	# Nettoyer les mails en double
	# Unifier tous les mails dans la variable POPULATION
	$used = array_unique($used);
	if(sizeof($add) > 0){
		foreach($add as $e){
			$population[$e] = array('userMail' => $e);
		}
	}

	# Si j'ai pas de donnee, pas de chocolat baby
	#
	#die($this->pre($population));
	if(sizeof($population) == 0) return false;
	$population = array_values($population);

	# Reformater les champs
	#
	$fields = $this->apiLoad('field')->fieldGet(array('user' => true));
	if(sizeof($fields) > 0 ){
		foreach($population as $pIDX => $p){
			foreach($fields as $f){
				if(array_key_exists('field'.$f['id_field'], $p)){
					$population[$pIDX][$f['fieldKey']] = $p['field'.$f['id_field']];
					unset($population[$pIDX]['field'.$f['id_field']]);
				}
			}
		}
	}

	foreach($population as $n => $e){
		if(filter_var($e['userMail'], FILTER_VALIDATE_EMAIL) === FALSE) unset($population[$n]);
	}

	return $population;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
public function newsletterPoolPopulate($id_newsletter){

	$pref = $this->configGet('newsletter');

	$data = $this->newsletterGet(array('id_newsletter' => $id_newsletter));
	$data['newsletterHtml'] = $this->newsletterPrepareBody($id_newsletter);

	#$this->pre($data);

	# Stats sur les liens
	preg_match_all("#href=\"(.*?)\"#", $data['newsletterHtml'], $href, PREG_SET_ORDER);
	if(sizeof($href) > 0){
		foreach($href as $i => $e){
			$link[] = $e[1];
		}
	}

	# Liste tous les user qui match les criteres de recherche
	if(sizeof($data['newsletterSearch']) > 0){
		foreach($data['newsletterSearch'] as $e){
			$grp = $this->apiLoad('user')->userSearch(array('id_search' => $e));
			if(sizeof($grp) > 0){
				foreach($grp as $e){
					$id[] = $e['id_user'];
				}
			}
		}
	}

	# Liste tous les user des groupes selectionnes
	if(sizeof($data['newsletterGroup']) > 0){
		foreach($data['newsletterGroup'] as $e){
			$grp = $this->dbMulti("
				SELECT *
				FROM k_user INNER JOIN k_userdata ON k_user.id_user = k_userdata.id_user
				WHERE id_group=".$e
			);
			if(sizeof($grp) > 0){
				foreach($grp as $e){
					$id[] = $e['id_user'];
				}
			}
		}
	}
	
	# Final, demande tous les user
	#
	$used = array();
	if(sizeof($id) > 0){
		$protect	= ($data['newsletterAllUser']) ? NULL : "userNewsletter=1 AND ";
		$population = $this->dbMulti("
			SELECT *
			FROM k_user INNER JOIN k_userdata ON k_user.id_user = k_userdata.id_user
			WHERE ".$protect." k_user.id_user IN(".implode(',', array_unique($id)).")"
		);
	}else{
		$population = array();
	}

	# Constuir la liste des mails utilises
	foreach($population as $e){
		$used[] = $e['userMail'];
	}
	
	# AjoutNewsletter List
	# 
	if(sizeof($data['newsletterList']) > 0){
		foreach($data['newsletterList'] as $e){

			$tmp = $this->dbMulti("
				SELECT * FROM  k_newslettermail
				INNER JOIN k_newsletterlistmail ON k_newslettermail.id_newslettermail = k_newsletterlistmail.id_newslettermail
				WHERE k_newsletterlistmail.id_newsletterlist=".$e." AND k_newslettermail.flag = 'VALID'"
			);

			foreach($tmp as $t){
				if(!in_array($t['mail'], $used)) $add[] = $t['mail'];
			}
		}
	}

	# Raw mails (on rajoute a ADD si il n'existe pas)
	#
	if(strlen(trim($data['newsletterListRaw'])) > 0){
		foreach(explode("\n", $data['newsletterListRaw']) as $line){
			$line = trim($line);
			if(!in_array($line, $used)) $add[] = $line;
		}
		$add = array_unique($add);
	}

	
	# Nettoyer les mails en double
	# Unifier tous les mails dans la variable POPULATION
	$used = array_unique($used);
	if(sizeof($add) > 0){
		foreach($add as $e){
			$population[$e] = array('userMail' => $e);
		}
	}

	# Si j'ai pas de donnee, pas de chocolat baby
	#
	#die($this->pre($population));
	if(sizeof($population) == 0) return false;
	$population = array_values($population);

	# Reformater les champs
	#
	$fields = $this->apiLoad('field')->fieldGet(array('user' => true));
	if(sizeof($fields) > 0 ){
		foreach($population as $pIDX => $p){
			foreach($fields as $f){
				if(array_key_exists('field'.$f['id_field'], $p)){
					$population[$pIDX][$f['fieldKey']] = $p['field'.$f['id_field']];
					unset($population[$pIDX]['field'.$f['id_field']]);
				}
			}
		}
	}
	
	# Boucler et remplir le POOL pour cette POPULATION
	#
	foreach($population as $user){
	
		$newsletterHtml = $data['newsletterHtml'];

		// Si le ne trouve pas de lien pour le desabonnement, alors le forcer en pieds de mail
		if(substr_count($newsletterHtml, '{unsubscribe}') == 0){
			$newsletterHtml .= "\n\n{unsubscribe}";
		}

		// Le lien "j'arrive pas a lire le mail"
		$user['read'] = "\n\n".
		"<div style=\"text-align:center; padding:5px; margin-top:5px; color:#000000; background:#FFFFFF; font-faimy:Verdana; font-size:12px;\">".
			"<a href=\"http://".$_SERVER['SERVER_NAME']."\" style=\"color:#000000\">".
				"Cliquer ici si vous ne pouvez pas lire la newsletter correctement".
			"</a>".
		"</div>\n\n";
	
		// Le tracking de lecture + le lien de desabonnement
		$user['unsubscribe'] = "\n\n".
		"<div style=\"text-align:center; padding:5px; margin-top:5px; color:#000000; background:#FFFFFF; font-faimy:Verdana; font-size:12px;\">".
			"<img src=\"http://".$_SERVER['SERVER_NAME']."/app/helper/newsletter.php?t=1&id_newsletter=".$id_newsletter."&mail=".$user['userMail']."\" height=\"5\" width=\"5\" />".
			 "<a href=\"http://".$_SERVER['SERVER_NAME']."/app/helper/newsletter.php?r=1&id_newsletter=".$id_newsletter."&mail=".$user['userMail']."\">".
				"Cliquer ici pour ne plus recevoir de mail".
			"</a>".
		"</div>\n\n";

		// Remplacer a la volee, les {?} par leur valeurs
		$newsletterHtml = $this->helperReplace($newsletterHtml, $user, '{}');

		// Remplacer les liens par le tracking
		if(sizeof($link) > 0){
			foreach($link as $e){
				$newsletterHtml = str_replace("href=\"".$e."\"", "href=\"http://".$_SERVER['SERVER_NAME']."/app/helper/newsletter.php?l=1&id_newsletter=".$data['id_newsletter']."&mail=".urlencode($user['userMail'])."&url=".urlencode($e)."\"", $newsletterHtml);
			}
		}

		// Preparer l'enregistrement
		$raw = array(
			'id_user'			=> $user['id_user'],
			'userMail'			=> $user['userMail'],
			'newsletterName'	=> (($data['newsletterTitle'] == NULL) ? $data['newsletterName'] : $data['newsletterTitle']),
			'newsletterHtml'	=> $newsletterHtml,
			'link'				=> $link,
			'headers'			=> array(
				'name'		=> $pref['name'],
				'from'		=> $pref['sender'],
				'track'		=> $id_newsletter.'-'.$user['userMail']
			)
		);

		#$this->pre($raw);
		#die('-------');

		$this->dbQuery("INSERT INTO k_newsletterpool (id_newsletter, poolRaw) VALUES (".$id_newsletter.", '".addslashes(serialize($raw))."')");
		#$this->pre($this->db_query, $this->db_error);
		#die();
	}
	

	# Renseigner la date de l'envois
	#
	$this->dbQuery("UPDATE k_newsletter SET newsletterSendDate=NOW() WHERE id_newsletter=".$id_newsletter);


	return true;
}
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
public function newsletterPoolEmpty($id_newsletter){
	if($id_newsletter == NULL) return true;
	$this->dbQuery("DELETE FROM k_newsletterpool WHERE id_newsletter=".$id_newsletter);

	return true;
}
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
public function newsletterAnalytic($id_newsletter){

	$t		= $this->dbMulti("SELECT trackingMail FROM k_newslettertracking WHERE id_newsletter=".$id_newsletter." GROUP BY trackingMail");
	$total	= sizeof($t);

	$u = $this->dbMulti("SELECT COUNT(trackingFlag) AS how, trackingFlag FROM k_newslettertracking WHERE id_newsletter=".$id_newsletter." GROUP BY trackingFlag");
	foreach($u as $e){
		$out[$e['trackingFlag']] = $e['how'];
	}

	$todo = $this->dbOne("SELECT COUNT(id_newsletter) AS how FROM k_newsletterpool WHERE id_newsletter=".$id_newsletter);
	$out['mailingSentTotal'] = $total; 
	$out['mailingSentTodo']  = (($todo['how'] > 0) ? $todo['how'] : 0);
	$out['mailingSentDone']  = $out['mailingSentTotal'] - $out['mailingSentTodo'];




	//

	$click 	= $this->dbOne("SELECT SQL_CALC_FOUND_ROWS clickMail FROM k_newsletterclick WHERE id_newsletter=".$id_newsletter." GROUP BY clickMail LIMIT 1");
	$out['mailingClick'] = (($this->db_num_total > 0) ? $this->db_num_total : 0);

	return $out;
}
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function newsletterTemplateGet($opt=array()){

	if($opt['id_newslettertemplate']){
		$template = $this->dbOne("SELECT * FROM k_newslettertemplate WHERE id_newslettertemplate=".$opt['id_newslettertemplate']);
		$template['templateStyle'] = json_decode($template['templateStyle'], true);
	}else{
		$template = $this->dbMulti("SELECT * FROM k_newslettertemplate");
	}
	
	if($opt['debug']) $this->pre($this->db_query, $this->db_error, $template);

	return $template;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function newsletterTemplateSet($id_newslettertemplate, $def){

	$def['k_newslettertemplate']['templateDateUpdate'] = array('function' => 'NOW()');

	if($id_newslettertemplate  > 0){
		$q = $this->dbUpdate($def)." WHERE id_newslettertemplate=".$id_newslettertemplate;
	}else{
		$def['k_newslettertemplate']['templateDateCreation'] = array('function' => 'NOW()');
		$q = $this->dbInsert($def);
	}

	@$this->dbQuery($q);
	if($this->db_error != NULL) return false;

	$this->id_newslettertemplate = ($id_newslettertemplate > 0) ? $id_newslettertemplate : $this->db_insert_id;

	return true;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function newsletterTemplateRemove($id_newslettertemplate){
	if($id_newslettertemplate == NULL) return false;

	$this->dbQuery("DELETE FROM k_newslettertemplate WHERE id_newslettertemplate=".$id_newslettertemplate);

	return true;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function newsletterSubscribe($opt=array()){

	$email	= addslashes(trim($opt['email']));
	$mail	= $this->dbOne("SELECT * FROM k_newslettermail WHERE `mail`='".$email."'");

	if($mail['id_newslettermail'] == NULL){
		$this->dbQuery("INSERT INTO k_newslettermail (mail, flag) VALUES ('".$email."', 'VALID')");
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
		$id_newslettermail = $this->db_insert_id;
	}else{
		$id_newslettermail = $mail['id_newslettermail'];
		$this->dbQuery("UPDATE k_newslettermail SET flag='VALID' WHERE id_newslettermail=".$id_newslettermail);
	}

	if($opt['clean']){
		$this->dbQuery("DELETE FROM k_newsletterlistmail WHERE id_newslettermail=".$id_newslettermail);
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	}

	if(sizeof($opt['list']) > 0){
		foreach($opt['list'] as $e){
			if($e != '') $sql[] = "(".$id_newslettermail.", ".$e.")";
		}

		if(sizeof($sql) > 0){
			$this->dbQuery("INSERT IGNORE INTO k_newsletterlistmail (id_newslettermail, id_newsletterlist) VALUES ".implode(',', $sql));
			if($opt['debug']) $this->pre($this->db_query, $this->db_error);
			
			// Envoyer un SIGNAL pour UNBLACKLISTER le MAIL
			$pref	= $this->configGet('newsletter');
			$rest	= new newsletterREST($pref['auth'], $pref['passw']);
			$black	= @$rest->request('/controller.php', 'POST', array('blackListRemove' => true, 'mail' => $email));
		}

		return true;
	}

	return false;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function newsletterUnsubscribe($opt=array()){

	$email	= addslashes(trim($opt['email']));
	$mail	= $this->dbOne("SELECT * FROM k_newslettermail WHERE `mail`='".$email."'");

	if($mail['id_newslettermail'] == NULL) return false;

	if(sizeof($opt['list']) > 0){
		$this->dbQuery("DELETE FROM k_newsletterlistmail WHERE id_newslettermail='".$mail['id_newslettermail']."' AND id_newsletterlist IN(".implode(',', $opt['list']).")");
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);

		return true;
	}else
	if($opt['listAll']){
		$this->dbQuery("DELETE FROM k_newsletterlistmail WHERE id_newslettermail='".$mail['id_newslettermail']."'");
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
		
		return true;
	}

	return false;
}

}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
class newsletterREST{

const RESTVERB_POST            = 'POST';
const RESTVERB_GET             = 'GET';
const ENCODING_MULTIPART       = 'multipart/form-data';
const ENCODING_FORM            = 'application/x-www-form-urlencoded';
const ENCODING_XML             = 'application/xml';
const ENCODING_JSON            = 'application/json';
const ENCODING_DEFAULT         = self::ENCODING_JSON;

protected $_credentials			= '';
protected $_host				= 'mailing.cloudapp.me';
protected $_connectionSecure	= false;
protected $_timeout				= 30;
protected $_persistent			= true;
protected $_curlHandle;

public function __construct($login, $password){
	$this->_credentials = $login.':'. $password;
	$this->_host		= 'mailing.cloudapp.me';
	$this->curlReset();
}

function __destruct(){
	$this->curlReset();
}

public function curlReset(){
	if($this->_curlHandle != null){
		curl_close($this->_curlHandle);
		$this->_curlHandle = null;
    }
}

public function request($uri, $restVerb, $data = array(), $debug=false){

	$this->_curlHandle = curl_init();

	$url = 'http://'. $this->_host . $uri;

    curl_setopt_array($this->_curlHandle, array(
    	CURLOPT_URL				=> $url,
        CURLOPT_HEADER 			=> false,
        CURLOPT_VERBOSE 		=> true,
        CURLOPT_RETURNTRANSFER 	=> true,
        CURLOPT_FOLLOWLOCATION 	=> true,
        CURLOPT_USERAGENT      	=> "Mozilla/4.0 (compatible;)",
        CURLOPT_HTTPAUTH        => CURLAUTH_BASIC,
        CURLOPT_USERPWD         => $this->_credentials,
        CURLOPT_CUSTOMREQUEST	=> $restVerb
    ));

    is_array($data)
    	? curl_setopt($this->_curlHandle, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'))
 		: curl_setopt($this->_curlHandle, CURLOPT_POSTFIELDS, $data);

    $result = curl_exec($this->_curlHandle);

    if($result === false) throw new Exception('System error: ' . curl_error($this->_curlHandle));

 	$this->curlReset();

	return $result;
#	return json_decode($result, true);
}

} ?>