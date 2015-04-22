<?php

if(!defined('COREINC')) die('Direct access not allowed');

// Filter
if(isset($_GET['cf'])){
    $app->filterSet('adstat', $_GET);
    $filter = array_merge($app->filterGet('adstat'), $_GET);
}else
    if(isset($_GET['filter'])){
        //$_REQUEST['filter']['date'] = ($_REQUEST['filter']['date'] == 1) ? 1 : 0;
        $app->filterSet('adstat', $_GET['filter']);
        $filter = array_merge($app->filterGet('adstat'), $_GET['filter']);
    }else{
        $filter = $app->filterGet('adstat');
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

<div id="app">

    <form action="adstat" method="get" class="form-horizontal">

        <input type="hidden" name="filter[open]"	value="1" />
        <input type="hidden" name="filter[offset]"	value="0" />


        <?php
            //if($filter['dateStart'] != '' && $filter['dateEnd'] != '') {

                $campaigns = array();
                foreach($types as $type){
                    $opt = array(
                        'id_type'           => $type['id_type'],
                        'useGroup'          => false,
                        //'sqlWhere'          => $sqlWhere,
                        'contentSee'        => 'ALL',
                        'order'             => 'contentName',
                        'direction'         => 'ASC',
                        'limit'             => 9999,
                        'assoCategory'      => false,
                        'debug'             => false
                    );
                    //$app->pre($opt);
                    $ad = $app->apiLoad('content')->contentGet($opt);
                    $campaigns = array_merge($campaigns, $ad);
                }
                //$app->pre($campaigns);
                if(sizeof($campaigns) > 0) {

            ?>
                    <p>
                        <!--<label class="control-label"><?php echo _('Campagne'); ?></label>-->
                        <select name="id_content" style="padding: 5px 10px;">
                            <option value="">Sélectionnez une campagne</option>
                            <?php
                                foreach($campaigns as $campaign) {
                                    $selected   = ($_REQUEST['id_content'] == $campaign['id_content']) ? ' selected="selected" ' : '';
                                    if($_REQUEST['id_content'] == $campaign['id_content']) $currentCampaign   = $campaign;
                            ?>
                                <option value="<?php echo $campaign['id_content']; ?>" <?php echo $selected; ?>>
                                    <?php echo $campaign['contentName']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </p>

                    <?php
                    if($_REQUEST['id_content'] > 0) {

                        $start      = '';
                        $end        = '';
                        $affstats   = array();

                        $results    = array();
                        $view       = 0;
                        $click      = 0;



                        $stats          = $app->dbMulti("SELECT * FROM k_contentadstats WHERE id_content='".$_REQUEST['id_content']."'");

                        $sqlDateStart   = $_REQUEST['dateStart'][$_REQUEST['id_content']];
                        $sqlDateEnd     = $_REQUEST['dateEnd'][$_REQUEST['id_content']];
                        if($sqlDateStart == '') $sqlDateStart   = '1900-01-01';
                        if($sqlDateEnd == '')   $sqlDateEnd       = '9999-01-01';

                        foreach($stats as $s) {
                            $date   = $s['year'].'-'.str_pad($s['month'], 2, "0", STR_PAD_LEFT).'-'.str_pad($s['day'], 2, "0", STR_PAD_LEFT);

                            if($date >= $sqlDateStart && $date <= $sqlDateEnd) {

                                if($start == '')$start  = $date;
                                if($end == '')  $end    = $date;

                                if($start > $date)  $start  = $date;
                                if($end < $date)    $end    = $date;

                                $key    = $s['year'].'-'.str_pad($s['month'], 2, "0", STR_PAD_LEFT);
                                $affstats[$key]['view'] += $s['view'];
                                $affstats[$key]['click'] += $s['click'];
                                //if($date >= $filter['dateStart'] && $date <= $filter['dateEnd']) {
                                //$results[$date] = array('view' => $s['view'], 'click' => $s['click']);
                                $view      += $s['view'];
                                $click     += $s['click'];
                                //}
                            }
                        }

                        //$app->pre($currentCampaign);?>
                        <p>
                            <label class="control-label">Du</label>
                            <input type="text" name="dateStart[<?php echo $_REQUEST['id_content']; ?>]" class="datePicker form-control" value="<?php echo ($_REQUEST['dateStart'][$_REQUEST['id_content']] != '') ? $_REQUEST['dateStart'][$_REQUEST['id_content']] : $start; ?>" style="padding: 5px 10px;">
                        </p>
                        <p>
                            <label class="control-label">au</label>
                            <input type="text" name="dateEnd[<?php echo $_REQUEST['id_content']; ?>]" class="datePicker form-control" value="<?php echo ($_REQUEST['dateEnd'][$_REQUEST['id_content']] != '') ? $_REQUEST['dateEnd'][$_REQUEST['id_content']] : $end; ?>" style="padding: 5px 10px;">
                        </p>
                    <?php } ?>
                    <button class="btn btn-medium" type="submit"><span class="icon-signal"></span> <?php echo _('Afficher les statistiques'); ?></button>
            <?php

                }else {
                    echo 'Aucune campagne trouvée sur cette période';
                }
            //}else {
            ?>
            <!--<button class="btn btn-mini" type="submit"><?php echo _('OK'); ?></button>-->
            <?php
            //}
            ?>


    <div id="results">
        <?php

            if($_REQUEST['id_content'] > 0) {
        ?>
                <h1><?php echo $currentCampaign['contentName']; ?></h1>
                <div class="clearfix"></div>
                <table border="0" cellpadding="0">
                    <tr>
                        <td valign="top" style="width: 350px;">
                            <h3 style="line-height: 1.5em;">
                                <span class="icon-calendar"></span> Du <?php echo $app->helperDate($start, "%d %B %Y"); ?> au <?php echo $app->helperDate($end, "%d %B %Y"); ?><br />
                                <span class="icon-eye-open"></span> Total affichages : <?php echo $view; ?><br />
                                <span class="icon-share-alt"></span> Total clics : <?php echo $click; ?>
                            </h3>
                            <?php if(sizeof($affstats) > 0) { ?>
                                <table class="listing" cellpadding="3" border="0" cellspacing="0">
                                    <thead>
                                    <tr>
                                        <th><span class="icon-white icon-calendar"></span> Mois</th>
                                        <th><span class="icon-white icon-eye-open"></span> Affichages</th>
                                        <th><span class="icon-white icon-share-alt"></span> Clics</th>
                                    </tr>
                                    </thead>
                                    <?php foreach($affstats as $month => $s) { ?>
                                        <tr>
                                            <td><?php echo ucfirst($app->helperDate($month.'-01', "%B %Y")); ?></td>
                                            <td><?php echo $s['view']; ?></td>
                                            <td><?php echo $s['click']; ?></td>
                                        </tr>
                                    <?php } ?>
                                </table>
                            <?php } ?>
                        </td>
                        <td valign="top" style="padding-left: 30px;">
                            <em>Aperçu de la campagne</em><br />
                            <?php
                            if($currentCampaign['adCode'] == NULL && sizeof($currentCampaign['contentMedia']['image']) > 0){
                                echo "<a href=\"/ad".$currentCampaign['id_content']."\" target=\"_blank\"><img src=\"".$currentCampaign['contentMedia']['image'][0]['url']."\"  /></a>";
                            }
                            ?>
                        </td>
                    </tr>
                </table>
        <?php

            }
        ?>
    </div>


    </form>
</div>

<?php include(COREINC.'/end.php'); ?>
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