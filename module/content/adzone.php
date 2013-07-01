<?php

if(!defined('COREINC')) die('Direct access not allowed');

// Filter
if(isset($_GET['cf'])){
    $app->filterSet('content'.$id_type, $_GET);
    $filter = array_merge($app->filterGet('content'.$id_type), $_GET);
}else
    if(isset($_POST['filter'])){
        $app->filterSet('content'.$id_type, $_POST['filter']);
        $filter = array_merge($app->filterGet('content'.$id_type), $_POST['filter']);
    }else{
        $filter = $app->filterGet('content'.$id_type);
    }

$dir = ($filter['direction'] == 'ASC') ? 'DESC' : 'ASC';

$zones = $app->apiLoad('ad')->adZoneGet();

?><!DOCTYPE html>
<html lang="fr">
<head>
    <?php include(COREINC.'/head.php'); ?>
</head>
<body>

<header><?php
    include(COREINC.'/top.php');
    include(__DIR__.'/ui/menu.php')
    ?></header>

<div class="inject-subnav-right hide">
    <li><a onclick="filterToggle('content<?php echo $id_type ?>');" class="btn btn-small"><?php echo _('Display settings'); ?></a></li>
</div>

<div id="app">

    <div class="quickForm" style="display:<?php echo ($filter['open']) ? 'block' : 'none;' ?>;">
        <form action="adzone" method="post" class="form-horizontal">

            <input type="hidden" name="filter[open]"	value="1" />
            <input type="hidden" name="filter[offset]"	value="0" />

            <button class="btn btn-mini" type="submit"><?php echo _('Filter'); ?></button>
        </form>
    </div>


    <table border="0" cellpadding="0" cellspacing="0" class="listing">
        <thead>
        <tr>
            <th width="250" class="icone">Emplacement</th>
            <th>Campagnes</th>
        </tr>
        </thead>
        <tbody>
        <?php if(sizeof($zones) == 0){ ?>
        <tr>
            <td colspan="2" style="padding:40px 0px 40px 0px; text-align:center; font-weight:bold">
                <?php echo _('No data'); ?>
            </td>
        </tr>
        <?php }else{
            $count = 0;
            foreach($zones as $z){
                $ad = $app->apiLoad('content')->contentGet(array(
                   'id_type'
                ));
        ?>
            <tr>
               <td><?php echo $z['zoneName']; ?></td>
               <td></td>
            </tr>
        <?php

            }
        }
        ?>
        </tbody>
    </table>

</div>

<?php include(COREINC.'/end.php'); ?>
<script src="../core/vendor/datatables/jquery.dataTables.js"></script>
<script src="../core/vendor/bootstrap/js/bootstrap-dropdown.js"></script>

<script>


</script>

</body></html>
