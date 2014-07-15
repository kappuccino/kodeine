<?php

if(!defined('COREINC')) die('Direct access not allowed');

// Filter
if(isset($_GET['cf'])){
    $app->filterSet('adstat', $_GET);
    $filter = array_merge($app->filterGet('adstat'), $_GET);
}else
    if(isset($_POST['filter'])){
        $_POST['filter']['date'] = ($_POST['filter']['date'] == 1) ? 1 : 0;
        $app->filterSet('adstat', $_POST['filter']);
        $filter = array_merge($app->filterGet('adstat'), $_POST['filter']);
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

    <form action="adstat" method="post" class="form-horizontal">

        <input type="hidden" name="filter[open]"	value="1" />
        <input type="hidden" name="filter[offset]"	value="0" />

        <label class="control-label">Période du</label>
        <input type="text" name="filter[dateStart]" class="datePicker" value="<?php echo $filter['dateStart']; ?>">

        <label class="control-label"> au</label>
        <input type="text" name="filter[dateEnd]" class="datePicker" value="<?php echo $filter['dateEnd']; ?>">


        <?php
            if($filter['dateStart'] != '' && $filter['dateEnd'] != '') {

                $campaigns = array();
                foreach($types as $type){
                    $sqlWhere .= '
                                AND (
                                 (contentDateStart IS NULL AND contentDateEnd IS NULL)
                                    OR
                                 ((contentDateStart BETWEEN "'.$filter['dateStart'].'" AND "'.$filter['dateEnd'].'")
                                  OR
                                  (contentDateEnd BETWEEN "'.$filter['dateStart'].'" AND "'.$filter['dateEnd'].'"))
                                 ) ';
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
                    <label class="control-label"><?php echo _('Campagne'); ?></label>
                    <select name="id_content">
                        <option value=""></option>
                        <?php
                            foreach($campaigns as $campaign) {
                                $selected   = ($_POST['id_content'] == $campaign['id_content']) ? ' selected="selected" ' : '';
                                if($_POST['id_content'] == $campaign['id_content']) $currentCampaign   = $campaign;
                        ?>
                            <option value="<?php echo $campaign['id_content']; ?>" <?php echo $selected; ?>>
                                <?php echo $campaign['contentName']; ?>
                            </option>
                        <?php } ?>
                    </select>
                    <button class="btn btn-mini" type="submit"><?php echo _('Afficher les statistiques'); ?></button>
            <?php

                }else {
                    echo 'Aucune campagne trouvée sur cette période';
                }
            }else {
            ?>
            <button class="btn btn-mini" type="submit"><?php echo _('Filter'); ?></button>
            <?php
            }
            ?>

    </form>

    <div id="results">
        <?php
            if($_POST['id_content'] > 0) {
                $results    = array();
                $view       = 0;
                $click      = 0;

                $stats      = $app->dbMulti("SELECT * FROM k_contentadstats WHERE id_content='".$_POST['id_content']."'");
                foreach($stats as $s) {
                    $date = $s['year'].'-'.str_pad($s['month'], 2, "0", STR_PAD_LEFT).'-'.str_pad($s['day'], 2, "0", STR_PAD_LEFT);

                    if($date >= $filter['dateStart'] && $date <= $filter['dateEnd']) {
                        $results[$date] = array('view' => $s['view'], 'click' => $s['click']);
                        $view      += $s['view'];
                        $click     += $s['click'];
                    }
                }
                //$app->pre($currentCampaign);
        ?>
            <h2>
                Nombre d'affichages : <?php echo $view; ?><br />
                Nombre de clics : <?php echo $click; ?>
            </h2>
            <p>
                <?php
                    if($currentCampaign['adCode'] == NULL && sizeof($currentCampaign['contentMedia']['image']) > 0){
                        echo "<a href=\"/ad".$currentCampaign['id_content']."\" target=\"_blank\"><img src=\"".$currentCampaign['contentMedia']['image'][0]['url']."\"  /></a>";
                    }
                ?>
            </p>
        <?php

            }
        ?>
    </div>


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