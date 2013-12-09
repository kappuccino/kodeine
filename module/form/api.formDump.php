<?php
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Last release		2011.06.09
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

class formDump extends coreApp {

function __clone(){}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function formDump(){
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function formDumpGet($opt=array()){

    $limit				= ($opt['limit'] != '') 			? $opt['limit']				: 30;
    $offset				= ($opt['offset'] != '')			? $opt['offset']			: 0;

    $q = "SELECT SQL_CALC_FOUND_ROWS * FROM k_formDump WHERE 1";

    if($opt['id_form'] > 0) $q .= " AND id_form=\"".$opt['id_form']."\" ";
    if(isset($opt['formKey'])) $q .= " AND formKey=\"".addslashes($opt['formKey'])."\" ";
    if(isset($opt['search'])) $q .= " AND (json LIKE \"%".addslashes($opt['search'])."%\" OR formTitle LIKE \"%".addslashes($opt['search'])."%\") ";

    // Tri
    if(isset($opt['order']) && isset($opt['direction'])){
        $q .= " \nORDER BY ".$opt['order']." ".$opt['direction'];
    }

    // Limites
    $q .= " LIMIT ".$offset.",".$limit;

    $data = $this->dbMulti($q);

    $this->total = $this->db_num_total;
    $this->limit = $limit;

    if($opt['debug']) $this->pre($this->db_query, $this->db_error);
    if($this->db_error != NULL) return false;

    return $data;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function formDumpSet($opt=array()){
    $def = array();
    if(!isset($opt['id_user'])) $opt['id_user']     = $this->user['id_user'];
    if(!isset($opt['formDate'])) $opt['formDate']   = date('Y-m-d H:i:s');
    $opt['json'] = '';
    if(is_array($opt['data'])) $opt['json'] = addslashes($this->helperJsonEncode($opt['data']));
    unset($opt['data']);

    foreach($opt as $k=>$o) {
        $def['k_formDump'][$k] = array('value' => $o);
    }

    $q = ($opt['id_form'] > 0)
        ? $this->dbUpdate($def)." WHERE id_form=".$opt['id_form']
        : $this->dbInsert($def);

    @$this->dbQuery($q);

    if($opt['debug']) $this->pre($this->db_query, $this->db_error);
    if($this->db_error != NULL) return false;

    return true;
}
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function formDumpKeyGet(){

    $q = "SELECT formKey FROM k_formDump GROUP BY formKey ORDER BY formKey ASC";

    $data = $this->dbMulti($q);

    return $data;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function formDumpRemove($id_form){

	if($id_form == NULL) return false;

	$this->dbQuery("DELETE FROM k_formDump	WHERE id_form=".$id_form);

	return true;
}




} ?>