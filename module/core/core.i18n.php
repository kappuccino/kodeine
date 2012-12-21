<?php

class coreI18n extends coreApp{

private $language = 'fr';       // Langue par défaut
private $labels   = array();    // Les labels classés pas clé

public function __construct(){}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - +
 *
 */
function load($module=NULL){

	if($module != NULL) $sql = " OR module='".$module."'";
	$labs = $this->dbMulti("SELECT * FROM k_i18n WHERE (module=''".$sql.") AND language='".$this->language."'");

	foreach($labs as $e){
		$this->labels[$e['key']] = $e;
	}

	return $this;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - +
 *
 */
public function languageSet($lan){
	$this->language = $lan;
	return $this;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - +
 *
 */
public function _($string){
	return $string;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - +
 *
 */
public function __($string){
	if(array_key_exists($string, $this->labels)) return $this->labels[$string]['value'];
	return $string;
}



/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - +
 *
 */
public function parse($url){

	if(!file_exists($url)) return false;

	$raw = file_get_contents($url);

	$this->pre($url);
	if(preg_match_all("#->_\('(.*)'\)#msU", $raw, $m, PREG_SET_ORDER) > 0){

		foreach($m as $e){
			$tmp[] = $e[1];
		}

		$this->pre($tmp);
	#	$this->pre($url, $m);
	}


}



} ?>