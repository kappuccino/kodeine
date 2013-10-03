<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	$limit = 10;

	$conf	= $app->configGet('business', 'row');
    if(is_array($conf)) $data = array();
    elseif(is_array(json_decode($conf))) $data = json_decode($conf, true);
    else $data = array();


	$do = false;

	if((isset($_GET['add']) || isset($_GET['remove']) ) && $_GET['field'] != '') {
	    $do = true;

	    if(isset($_GET['add'])) $action = 'add';
	    else $action = 'remove';

	    $used = $data;

	    foreach($used as $k=>$e) {
	        if($e['field'] == $_GET['field']) {
	            unset($used[$k]);
	        }
	    }
	    if($action == 'add') $used[] = array('field' => $_GET['field'], 'width' => 200);
	    $used = array_merge($used);

	    if(sizeof($used) > $limit && $action == 'add') {
	        $do = false;
	        $message = 'KO: Le nombre de colonnes supplémentaires est limité à '.$limit;
	    }

	}
	if(isset($_GET['pos'])) {
	    $do         = true;

	    $used       = $data;

	    $pos        = explode(',', $_GET['pos']);
        $width      = explode(',', $_GET['width']);
        $title      = explode(',', $_GET['title']);
	    $newused    = array();

	    $i = 0;
	    foreach($pos as $p) {
	        foreach($used as $k=>$e) {
	            if($p == $e['field']) {
                    $e['width'] = $width[$i];
                    $e['title'] = $title[$i];
	                $newused[]  = $e;
	                $i ++;
	            }
	        }
	    }
	    $newused    = array_merge($newused);
	    $used       = $newused;
	    if(sizeof($used) != sizeof($newused)) $do = false;
	}

	if($do) {
        $app->configSet('business', 'row', json_encode($used));

        $conf	= $app->configGet('business', 'row');
        if(is_array($conf)) $data = array();
        elseif(is_array(json_decode($conf))) $data = json_decode($conf, true);
        else $data = array();

	}

	$opt    = array('businessCart' => true);
	$field	= $app->apiLoad('field')->fieldGet($opt);
	$used   = $data;
	$tmp    = array();

	// Champs de k_businesscart autorises
	$contentField = array(
        'cartEmail', 'cartDeliveryName', 'cartBillingName', 'cartDeliveryStatus', 'cartSerial', 'cartWeight', 'cartCmdNumber',
        'DELIVERYaddressbookPhone1', 'DELIVERYaddressbookPhone2', 'BILLINGaddressbookPhone1', 'BILLINGaddressbookPhone2'
    );

	// Champs utilises
	foreach($data as $e) $tmp[] = $e['field'];

	// Champs de k_businesscart non utilises
	$not    = array();
	foreach($field as $f) {
	    if(!in_array($f['id_field'], $tmp)) $not[] = $f;
	}

	// Champs de k_businesscart non utilises
	$notC   = array();
	foreach($contentField as $f) {
	    if(!in_array($f, $tmp)) $notC[] = $f;
	}
	//$app->pre($not, $notC);

?><!DOCTYPE html>
<html lang="fr">
<head>
    <?php include(COREINC . '/head.php'); ?>
    <style>
        table.table td {
            padding: 2px 0 2px 7px;
        }

        #used{
            margin: 0px;
            padding: 0px;
        }
        #used ul{
            border: 1px solid rgb(220, 220, 220);
            -moz-border-radius:4px; -webkit-border-radius:4px;
            padding: 5px 0px 0px 5px;
            margin:0px 5px 0px 0px;
        }

        #used li{
            list-style-type: none;
            float: left;
            background-color: #e1e1e1;
            padding:5px;
            margin: 0px 5px 5px 0px;
            width: 175px;
            height: 40px;
            -moz-border-radius:4px; -webkit-border-radius:4px;
        }

        #used li a{
            color:#333;
        }

        #used li:hover{
            background-color: #f1f1f1;
        }
    </style>

</head>
<body>

<header><?php
    include(COREINC . '/top.php');
	include(dirname(__DIR__) . '/business/ui/menu.php')
?></header>

<div class="inject-subnav-right hide">

</div>

<div id="app"><div class="wrapper">

    <div class="row">
        <div class="span4">

            <table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing sortable" style="margin-top:10px;">
                <thead>
                <tr>
                    <th><?php echo _('Field'); ?></th>
                    <th width="80"></th>
                </tr>
                </thead>

            <tbody>
                <?php if(sizeof($not) == 0 && sizeof($notC) == 0){ ?>
            <tr>
                <td colspan="4" style="padding:40px 0px 40px 0px; text-align:center; font-weight:bold">Aucun champ disponible</td>
            </tr>
                <?php }else{
                foreach($not as $e){ ?>
                <tr>
                    <td><?php echo $e['fieldName']. '(' . $e['fieldKey'] . ')' ?></td>
                    <td><a class="btn btn-mini" href="row?field=<?php echo $e['id_field'] ?>&add"><?php echo _('Add'); ?></a></td>
                </tr>
                    <?php } ?>
                <?php
                foreach($notC as $e){ ?>
                <tr>
                    <td><b><?php echo $e; ?></b></td>
                    <td><a class="btn btn-mini" href="row?field=<?php echo $e ?>&add"><?php echo _('Add'); ?></a></td>
                </tr>
                    <?php } ?>
			</tbody>
                <?php } ?>
            </table>
        </div>

        <div class="span8">
            <?php
            if($message != NULL){
                list($class, $message) = $app->helperMessage($message);
                echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
            }
            ?>

            <p><b><?php printf(_('Columns for type'), $data['typeName']); ?></b></p>
            <div style="margin-bottom:10px;" class="clearfix">
                <a onclick="sauver();" class="btn btn-mini"><?php echo _('Save this order'); ?></a>
                <a href="raw" class="btn btn-mini"><?php echo _('Cancel'); ?></a>
            </div>

            <ul id="used" class="clearfix"><?php

                if(sizeof($used) > 0){
                    foreach($used as $e){
                        // Champs persos de k_businesscart
                        if(is_numeric($e['field'])) {
                            $field	    = $app->apiLoad('field')->fieldGet(array('id_field' => $e['field']));
                            $id_field   = $field['id_field'];
                            $fieldName  = $field['fieldName']. " (" . $field['fieldKey'] . ")";

                        // Champs natifs de k_businesscart
                        }else {
                            $id_field   = $e['field'];
                            $fieldName  = $e['field'];
                        }
                        echo "<li id=\"".$id_field."\" style=\"height: 100px;\">". $fieldName . "<br />";
                        echo "Titre <input type=\"text\" size=\"15\" id=\"t" . $id_field . "\" value=\"".$e['title']."\"><br />";
                        echo "Largeur <input type=\"text\" size=\"2\" id=\"w" . $id_field . "\" value=\"".$e['width']."\"><br />";
                        echo "<a href=\"row?field=".$id_field."&remove\" class=\"btn btn-mini\">"._('Remove')."</a>";
                        echo "</li>";
                    }
                }

                ?></ul>


            <?php include(COREINC.'/end.php'); ?>
            <script src="../core/vendor/datatables/jquery.dataTables.js"></script>
            <script src="../core/vendor/bootstrap/js/bootstrap-dropdown.js"></script>

            <script>
                var mySortables = $('#used').sortable({

                });


                /*var mySortables = new Sortables('used', {
                    constrain: false,
                    clone: true,
                    revert: true,
                    onComplete: function(e){
                    }
                });*/

                function sauver(){
                    ordreA = mySortables.sortable("toArray");
                    ordre = ordreA.join(',');
                    var w = new Array();
                    var t = new Array();
                    for(i=0;i<ordreA.length;i++) {
                        w.push($('#w'+ordreA[i]).val());
                        t.push($('#t'+ordreA[i]).val());
                    }
                    var width = w.join(',');
                    var title = t.join(',');
                    document.location='row?pos='+ordre+'&width='+width+'&title='+title;
                }

            </script>

        </div>
    </div>

</div></div>

</body>
</html>