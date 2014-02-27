<?php

if(!defined('COREINC')) die('Direct access not allowed');

// Filter
if(isset($_GET['cf'])){
    $app->filterSet('pubs', $_GET);
    $filter = array_merge($app->filterGet('pubs'), $_GET);
}else
    if(isset($_POST['filter'])){
        $_POST['filter']['date'] = ($_POST['filter']['date'] == 1) ? 1 : 0;
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

            <label class="control-label">Filtrer par période</label>
            <input type="checkbox" name="filter[date]"	value="1" <?php echo ($filter['date'] == '1') ? ' checked="checked" ' : ''; ?> />

            <label class="control-label"> Du</label>
            <input type="text" name="filter[dateStart]" class="datePicker" value="<?php echo $filter['dateStart']; ?>">

            <label class="control-label"> au</label>
            <input type="text" name="filter[dateEnd]" class="datePicker" value="<?php echo $filter['dateEnd']; ?>">

            <label class="control-label"><?php echo _('Category'); ?></label>
            <?php
            echo $app->apiLoad('category')->categorySelector(array(
                'name'		=> 'filter[id_category]',
                'value'		=> $filter['id_category'],
                'language'	=> 'fr',
                'one'		=> true,
                'empty'		=> true
            )); ?>


            <button class="btn btn-mini" type="submit"><?php echo _('Filter'); ?></button>
        </form>
    </div>


    <table border="0" cellpadding="0" cellspacing="0" class="listing">
        <thead>
        <tr>
            <th width="200" class="icone"><?php echo _('Ad location'); ?></th>
            <th><?php echo _('Ad'); ?></th>
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
                        'assoCategory'     => true,
                        'id_category'      => $filter['id_category'],
                        'categoryThrough'  => true,
                        'debug'     => false
                    );
                    if($filter['date'] == '1' && $filter['dateStart'] != '' && $filter['dateEnd'] != '') {
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
               <td>
                   <a href="data?id_type=<?php echo $types[0]['id_type']; ?>&id_adzone=<?php echo $z['id_adzone']; ?>">
                       <strong><?php echo $z['zoneName']; ?></strong>
                   </a>
               </td>
               <td align="left">
                   <?php if(sizeof($campaigns) > 0) { ?>
                   <table border="0" cellpadding="0" cellspacing="0" width="100%" >
                       <tr>
                           <th width="200" align="left" style="padding: 5px;border-bottom: 1px solid #ccc;"><?php echo _('Name'); ?></th>
                           <th width="170" align="left" style="padding: 5px;border-bottom: 1px solid #ccc;"><?php echo _('Period'); ?></th>
                           <th width="300" align="left" style="padding: 5px;border-bottom: 1px solid #ccc;"><?php echo _('Categories'); ?></th>
                           <th align="left" style="padding: 5px;border-bottom: 1px solid #ccc;"><?php echo _('Overview'); ?></th>
                       </tr>
                   <?php
                        foreach($campaigns as $campaign) {
                   ?>
                       <tr>
                           <td style="border-bottom: 1px solid #ccc;">
                               <a href="data?id_content=<?php echo $campaign['id_content']; ?>">
                                   <strong><?php echo $campaign['contentName']; ?></strong>
                               </a>
                           </td>
                           <td style="border-bottom: 1px solid #ccc;">
                               <a href="data?id_content=<?php echo $campaign['id_content']; ?>">
                               <?php
                                   if($campaign['contentDateStart'] != NULL) {
                                       echo _('From').' '.$app->helperDate($campaign['contentDateStart'], '%d %B %Y'). '<br />';
                                   }
                                   if($campaign['contentDateEnd'] != NULL) {
                                       echo _('To').' '.$app->helperDate($campaign['contentDateEnd'], '%d %B %Y').'';
                                   }
                               ?>
                               </a>
                           </td>
                           <td style="border-bottom: 1px solid #ccc;">
                               <?php
                                    if(is_array($campaign['id_category'])) {
                                        $cats = array();
                                        foreach($campaign['id_category'] as $idcat) {
                                            $category = $app->apiLoad('category')->categoryGet(array('id_category' => $idcat, 'language' => '??'));
                                            $cats[] = str_replace('<br />', ' ', $category['categoryName']);
                                        }
                                        echo implode(', ', $cats);
                                    }
                               ?>
                           </td>
                           <td style="border-bottom: 1px solid #ccc;">
                               <?php
                               if($campaign['adCode'] == NULL && sizeof($campaign['contentMedia']['image']) > 0){
                                   echo "<a href=\"/ad".$campaign['id_content']."\" target=\"_blank\"><img src=\"".$campaign['contentMedia']['image'][0]['url']."\" height=45 /></a>";
                               }
                               ?>
                           </td>
                       </tr>
                   <?php
                        }
                   ?>
                   </table>
                   <?php } ?>
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
