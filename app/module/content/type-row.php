<?php

if($_REQUEST['id_type'] != NULL){
    $data	= $app->apiLoad('content')->contentType(array(
        'id_type'			=> $_REQUEST['id_type']
    ));
    if($data['id_type'] == NULL)     header('Location: type');

}else{
    header('Location: type');
}

$do = false;

if((isset($_GET['add']) || isset($_GET['remove']) ) && $_GET['id_field'] > 0) {
    $do = true;

    if(isset($_GET['add'])) $action = 'add';
    else $action = 'remove';

    $used = $data['typeListLayout'];

    foreach($used as $k=>$e) {
        if($e['id_field'] == $_GET['id_field']) {
            unset($used[$k]);
        }
    }
    if($action == 'add') $used[] = array('id_field' => $_GET['id_field'], 'width' => 200);
    $used = array_merge($used);

    if(sizeof($used) > 2 && $action == 'add') {
        $do = false;
        $message = 'KO: Le nombre de colonnes supplémentaires est limité à 2';
    }

}
if(isset($_GET['pos'])) {
    $do = true;

    $used = $data['typeListLayout'];

    $pos = explode(',', $_GET['pos']);
    $newused = array();

    foreach($pos as $p) {
        foreach($used as $k=>$e) {
            if($p == $e['id_field']) $newused[] = $e;
        }
    }
    $newused    = array_merge($newused);
    $used       = $newused;
    if(sizeof($used) != sizeof($newused)) $do = false;
}

if($do) {

    $def['k_type'] = array(
        'typeListLayout'	=> array('value' => json_encode($used))
    );
    $result  = $app->apiLoad('content')->contentTypeSet($_REQUEST['id_type'], $def);
    $message = ($result) ? 'OK: Enregistrement' : 'KO: Erreur APP:'.$app->db_error;

    $data	= $app->apiLoad('content')->contentType(array(
        'id_type'			=> $_REQUEST['id_type']
    ));
}

$opt    = array('id_type' => $_REQUEST['id_type']);
$field	= $app->apiLoad('field')->fieldGet($opt);
$used   = $data['typeListLayout'];
$not    = array();
$tmp    = array();
foreach($data['typeListLayout'] as $e) $tmp[] = $e['id_field'];
foreach($field as $f) {
    if(!in_array($f['id_field'], $tmp)) $not[] = $f;
}


?><!DOCTYPE html>
<html lang="fr">
<head>
    <title>Kodeine</title>
    <?php include(COREINC.'/head.php'); ?>
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
    include(COREINC.'/top.php');
    include(dirname(__DIR__).'/content/ui/menu.php')
    ?></header>

<div class="inject-subnav-right hide">
    <li>
        <div class="btn-group">
            <a class="btn btn-mini dropdown-toggle" data-toggle="dropdown" href="#"><?php echo $type['typeName']; ?> <span class="caret"></span></a>
            <ul class="dropdown-menu"><?php
                foreach($app->apiLoad('content')->contentType(array('profile' => true)) as $e){
                    echo '<li class="clearfix">';
                    echo '<a href="'.(($e['is_gallery']) ? 'gallery-index' : 'index').'?id_type='.$e['id_type'].'" class="left">'.$e['typeName'].'</a>';
                    echo '<a href="'.(($e['is_gallery']) ? 'gallery-album' : 'data' )."?id_type=".$e['id_type'].'" class="right"><i class="icon icon-plus-sign"></i></a>';
                    echo '</li>';
                }
                ?></ul>
        </div>
    </li>
</div>

<div id="app">

    <div class="row-fluid">


        <div class="span4">

            <table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing sortable" style="margin-top:10px;">
                <thead>
                <tr>
                    <th>Champ</th>
                    <th width="80">Utiliser</th>
                </tr>
                </thead>

            <tbody>
                <?php if(sizeof($not) == 0){ ?>
            <tr>
                <td colspan="4" style="padding:40px 0px 40px 0px; text-align:center; font-weight:bold">Aucun champ disponible</td>
            </tr>
                <?php }else{
                foreach($not as $e){ ?>
                <tr>
                    <td><?php echo $e['fieldName']. '(' . $e['fieldKey'] . ')' ?></td>
                    <td><a class="btn btn-mini" href="type-row?id_type=<?php echo $_REQUEST['id_type'] ?>&id_field=<?php echo $e['id_field'] ?>&add">Ajouter</a></td>
                </tr>
                    <?php } ?>
			</tbody>
			<tfoot>
            <tr>
                <td colspan="5" class="pagination"><?php $app->pagination($total, $limit, $filter['offset'], 'parent?id_content='.$_GET['id_content'].'&id_type='.$id_type.'&offset=%s'); ?></td>
            </tr>
            </tfoot>
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

            <p><b>Colonnes du type <?php echo $data['typeName']; ?></b></p>
            <div style="margin-bottom:10px;" class="clearfix">
                <a href="javascript:sauver();" class="btn btn-mini">Enregister le nouvel ordre</a>
                <a href="parent?id_content=<?php echo $_GET['id_content'] ?>" class="btn btn-mini">Annuler</a>
            </div>

            <ul id="used" class="clearfix"><?php

                if(sizeof($used) > 0){
                    foreach($used as $e){
                        $field	= $app->apiLoad('field')->fieldGet(array('id_field' => $e['id_field']));
                        echo "<li id=\"".$field['id_field']."\">". $field['fieldName']. "(" . $field['fieldKey'] . ")<br />";
                        echo "<a href=\"type-row?id_type=".$_REQUEST['id_type']."&id_field=".$field['id_field']."&remove\" class=\"btn btn-mini\">Supprimer</a> ";
                        echo "</li>";
                    }
                }

                ?></ul>


            <?php include(COREINC.'/end.php'); ?>
            <script src="/app/module/core/ui/_datatables/jquery.dataTables.js"></script>
            <script src="/app/module/core/ui/_bootstrap/js/bootstrap-dropdown.js"></script>

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
                    ordre = mySortables.sortable("toArray");
                    ordre = ordre.join(',');
                    document.location='type-row?id_type=<?php echo $_REQUEST['id_type'] ?>&pos='+ordre;
                }

            </script>

        </div>
    </div>
</div>

</body>
</html>