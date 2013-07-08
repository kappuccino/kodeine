<?php

if(!defined('COREINC')) die('Direct access not allowed');

// Filter
if(isset($_GET['cf'])){
    $app->filterSet('pubs', $_GET);
    $filter = array_merge($app->filterGet('pubs'), $_GET);
}else
    if(isset($_POST['filter'])){
        $app->filterSet('pubs', $_POST['filter']);
        $filter = array_merge($app->filterGet('pubs'), $_POST['filter']);
    }else{
        $filter = $app->filterGet('pubs');
    }

$dir = ($filter['direction'] == 'ASC') ? 'DESC' : 'ASC';

//$app->pre($filter);

$zones = $app->apiLoad('ad')->adZoneGet();

$types = $app->apiLoad('type')->typeGet();
foreach($types as $k=>$type) {
    if($type['is_ad'] == 0) unset($types[$k]);
}
$types = array_merge($types);

?><!DOCTYPE html>
<html lang="fr">
<head>
    <?php include(COREINC.'/head.php'); ?>
    <link rel="stylesheet" type="text/css" href="../core/vendor/datepicker/css/datepicker.css" />
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

            Début : <input type="text" name="filter[dateStart]" class="datePicker" value="<?php echo $filter['dateStart']; ?>">
            Fin : <input type="text" name="filter[dateEnd]" class="datePicker" value="<?php echo $filter['dateEnd']; ?>">


            <button class="btn btn-mini" type="submit"><?php echo _('Filter'); ?></button>
        </form>
    </div>


    <table border="0" cellpadding="0" cellspacing="0" class="listing">
        <thead>
        <tr>
            <th width="200" class="icone">Emplacement</th>
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
                $campaigns = array();
                foreach($types as $type){
                    $opt = array(
                        'id_type'   => $type['id_type'],
                        'useGroup'  => false,
                        'sqlWhere'  => ' AND k_contentad.id_adzone = "'.$z['id_adzone'].'" ',
                        'debug'     => false
                    );
                    if($filter['dateStart'] != '' && $filter['dateEnd'] != '') {
                        $opt['sqlWhere'] .= '
                        AND (
                         (contentDateStart IS NULL AND contentDateEnd IS NULL)
                            OR
                         ((contentDateStart BETWEEN "'.$filter['dateStart'].'" AND "'.$filter['dateEnd'].'")
                          OR
                          (contentDateEnd BETWEEN "'.$filter['dateStart'].'" AND "'.$filter['dateEnd'].'"))
                         ) ';
                    }
                    //$app->pre($opt);
                    $ad = $app->apiLoad('content')->contentGet($opt);
                    $campaigns = array_merge($campaigns, $ad);
                }
        ?>
            <tr>
               <td><strong><?php echo $z['zoneName']; ?></strong></td>
               <td>
                   <?php
                        foreach($campaigns as $campaign) {
                            echo '- <a href="data?id_content='.$campaign['id_content'].'">'.$campaign['contentName'].' ';
                            if($campaign['contentDateStart'] != NULL && $campaign['contentDateEnd'] != NULL) {
                                echo '('.$app->helperDate($campaign['contentDateStart'], '%d %B %G'). ' > '.$app->helperDate($campaign['contentDateEnd'], '%d %B %G').')';
                            }
                            echo '</a><br />';
                        }
                   ?>
               </td>
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
<script src="../core/vendor/datepicker/js/bootstrap-datepicker.js" charset="UTF-8"></script>

<script>

    $(function(){
        $('.datePicker').datepicker({
            format: 'yyyy-mm-dd'
        });
    });

</script>

</body></html>
