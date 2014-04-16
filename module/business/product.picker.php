<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	if($_REQUEST['id_type'] == NULL){
        $type = $app->apiLoad('type')->typeGet(array('profile' => true));
        foreach($type as $e){
            if($e['is_business']){
                header("Location: product.picker.php?id_type=".$e['id_type']);
                exit();
            }
        }
        die('Pas de produit');
    }

    if(isset($_REQUEST['duplicate'])){
        $app->apiLoad('content')->contentDuplicate($_REQUEST['duplicate']);
    }
    if(sizeof($_POST['see']) > 0){
        foreach($_POST['see'] as $e => $v){
            $app->dbQuery("UPDATE k_content SET contentSee=".$v." WHERE id_content=".$e);
        }
    }
    if(sizeof($_POST['remove']) > 0){
        foreach($_POST['remove'] as $e){
            $app->apiLoad('content')->contentRemove($_REQUEST['id_type'], $e, $_REQUEST['language']);
        }
    }

    // Type
    $type       = $app->apiLoad('type')->typeGet();
    $id_type    = $_REQUEST['id_type'];
    $cType      = $app->apiLoad('type')->typeGet(array('id_type' => $id_type));

    // Filter (verifier content / album)
    if($id_type == NULL)        die("APP : id_type IS NULL");
    if($cType['is_gallery'])    header("Location: content.gallery.index.php?id_type=".$cType['is_type']);

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

?>
<html lang="fr">
<head>
    <?php include(ADMINUI.'/head.php'); ?>
</head>
<body>
    
<ul class="menu-icon clearfix">
    <li class=""><a href="product.picker.php"><img src="ressource/img/ico-list.png" height="32" width="32" /><br />Liste</a></li>
    <div style="float:right; margin:15px 10px 0px 0px;">
        <a href="product.picker.data.php?id_type=<?php echo $_REQUEST['id_type']; ?>" class="button colorButton rButton">Ajouter un produit</a>
    </div>
</ul>

<div class="menu-inline"><?php
    foreach($app->apiLoad('type')->typeGet(array('profile' => true)) as $e){
        if($e['is_business']){
            echo "<div class=\"item ".($e['id_type'] == $_REQUEST['id_type'] ? 'me' : '')."\">";
            echo "<a href=\"product.picker.php?id_type=".$e['id_type']."\" class=\"text\">".$e['typeName']."</a>";
            echo "<a href=\"product.picker.data.php?id_type=".$e['id_type']."\" class=\"plus\"><img src=\"ressource/img/picto-add.png\" height=\"16\" width=\"16\" /></a>";
            echo "</div>";
        }
    }
?></div>

<?php
    // Content
    $language   = ($filter['language'] != '') ? $filter['language'] : 'fr';
    $opt        = array(
        'debug'             => false,
        'id_type'           => $id_type,
        'useChapter'        => false,
        'useGroup'          => false,
        'contentSee'        => 'ALL',
    #   'assoUser'          => true,
        'language'          => $language,
        'id_category'       => $filter['id_category'],
        'categoryThrough'   => (($filter['categoryThrough'] && $filter['id_category'] != '') ? true : false),
        'limit'             => $filter['limit'],
        'offset'            => $filter['offset'],
        'search'            => $filter['q'],
        'order'             => $filter['order'],
        'direction'         => $filter['direction'],
        'id_search'         => $filter['id_search'],
    );
    
    if($filter['viewChildren']){
        $opt['id_parent'] = '0';
    }else{
        $opt['id_parent'] = '*';
    }
    
    if($filter['id_shop']) $opt['id_shop'] = $filter['id_shop'];

    $content= $app->apiLoad('content')->contentGet($opt);

    $total  = $app->apiLoad('content')->total;
    $limit  = $app->apiLoad('content')->limit;

    $fields = $app->apiLoad('field')->fieldGet(array('id_type' => $id_type, 'debug' => false));
    $lang   = $app->countryGet(array('is_used' => true));

    /* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

    function view($app, $cType, $filter, $e, $level=0){

        if(intval($e['id_content']) == 0) return false;
    
        $version = $app->apiLoad('content')->contentVersionGet(array(
            'id_content'    => $e['id_content'],
            'language'      => $e['language']
        ));

        foreach($app->dbMulti("SELECT language FROM k_contentdata WHERE id_content=".$e['id_content']) as $l){
            $languages .= "<a href=\"content.language.php?id_content=".$e['id_content']."&language=".$l['language']."\" class=\"button rButton miniButton\">".strtoupper($l['language'])."</a> ";
        }

        echo 
        "<tr>".
            "<td>";
        if($e['contentSee'])echo "<a href=\"\" title=\"S&eacute;l&eacute;ctionner\" onclick=\"insertProduct(".$e['id_content'].");\"><img src=\"ressource/img/add.png\" alt=\"S&eacute;l&eacute;ctionner\" width=\"20\"></a>";
        echo 
            "</td>".
            "<td><input type=\"checkbox\" name=\"remove[]\" value=\"".$e['id_content']."\" class=\"cb\" /></td>".
            "<td>".
                "<input type=\"hidden\"     name=\"see[".$e['id_content']."]\" value=\"0\" />".
                "<input type=\"checkbox\"   name=\"see[".$e['id_content']."]\" value=\"1\" class=\"cs\" ".(($e['contentSee']) ? "checked" : '')." />".
            "</td>".
            "<td class=\"icone\"><a href=\"javascript:duplicate(".$e['id_content'].");\"><img src=\"ressource/img/ico-duplicate.png\" height=\"18\" width=\"18\" /></a></td>".
            "<td style=\"padding-left:5px;\">".sizeof($version)."</td>".
            "<td><a href=\"content.comment.php?id_content=".$e['id_content']."\">".$e['contentCommentCount']."</a></td>".
            "<td>".$languages."</td>".
            "<td>".$e['id_content']."</td>".
            "<td class=\"dateTime\">".
                "<span class=\"date\">".$app->helperDate($e['contentDateCreation'], '%d.%m.%Y')."</span> ".
                "<span class=\"time\">".$app->helperDate($e['contentDateCreation'], '%Hh%M')."</span>".
            "</td>".
            "<td class=\"dateTime\">".
                "<span class=\"date\">".$app->helperDate($e['contentDateUpdate'], '%d.%m.%Y')."</span> ".
                "<span class=\"time\">".$app->helperDate($e['contentDateUpdate'], '%Hh%M')."</span>".
            "</td>".
            "<td style=\"padding-left:".($level * 15)."px;\"><a href=\"product.picker.data.php?id_content=".$e['id_content']."&language=".$e['language']."\">".$e['contentName']."</a></td>";
            if($cType['is_business']){
                echo "<td>".$e['contentRef']."</td>";
            }

        echo "</tr>";

        if($filter['viewChildren']){
            $subs = $app->dbMulti("SELECT id_content FROM k_content WHERE id_parent=".$e['id_content']." ORDER BY pos_parent ASC");

            foreach($subs as $sub){
                $sub = $app->apiLoad('content')->contentGet(array(
                    'debug'         => false,
                    'raw'           => true,
                    'language'      => $e['language'],
                    'id_content'    => $sub['id_content']
                ));

                view($app, $cType, $filter, $sub, $level+1);
            }
        }

    }

?>
<div class="app">

<div class="quickForm clearfix">

    <div class="upper clearfix">
        <div class="label"><a href="javascript:filterToggle('content<?php echo $id_type ?>');">OPTIONS</a></div>
    </div>

    <form action="product.picker.php" method="post" id="filter" style="<?php echo ($filter['open']) ? '' : 'display:none;' ?>">
    
        <input type="hidden" name="id_type"         value="<?php echo $id_type ?>" />
        <input type="hidden" name="filter[open]"    value="1" />
        <input type="hidden" name="filter[offset]"  value="0" />

        Recherche
        <input type="text" name="filter[q]" value="<?php echo $filter['q'] ?>" />

        Combien
        <input type="text" name="filter[limit]" value="<?php echo $filter['limit'] ?>" size="3" />
        
        Catégorie
        <?php echo $app->apiLoad('category')->categorySelector(array(
                'name'      => 'filter[id_category]',
                'value'     => $filter['id_category'],
                'language'  => 'fr',
                'one'       => true,
                'empty'     => true
        )); ?>
        
        <?php if($cType['is_business'] == '1'){ ?>
        Shop
        <select name="filter[id_shop]">
            <option></option><?php
            $shop = $app->apiLoad('shop')->shopGet();
            foreach($shop as $e){
                echo "<option value=\"".$e['id_shop']."\"".(($filter['id_shop'] == $e['id_shop']) ? ' selected' : '').">".$e['shopName']."</option>";
            }
        ?></select>
        <?php } ?>
        
        Héritage
        <input type="hidden" name="filter[categoryThrough]" value="0" />
        <input type="checkbox" name="filter[categoryThrough]" value="1" <?php if($filter['categoryThrough']) echo ' checked'; ?> />
        
        Langue
        <select name="filter[language]"><?php
            foreach($app->countryGet(array('is_used' => 1)) as $e){
                $sel = ($e['iso'] == $filter['language']) ? ' selected' : NULL;
                echo "<option value=\"".$e['iso']."\"".$sel.">".$e['countryLanguage']."</option>";
            }       
        ?></select>
        
        Tous
        <input type="radio" name="filter[viewChildren]" value="0" <?php if(!$filter['viewChildren']) echo ' checked'; ?> />

        Ordonner
        <input type="radio" name="filter[viewChildren]" value="1" <?php if($filter['viewChildren']) echo ' checked'; ?> />

        <input type="submit" class="" value="Filter les résultats" />

    </form>
</div>

<form method="post" action="product.picker.php" id="listing">
    <input type="hidden" name="id_type"     value="<?php echo $id_type ?>" />
    <input type="hidden" name="language"    value="<?php echo $language ?>" />
    
    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing">
        <thead>
            <tr>
                <th width="30"  class="icone"><img src="ressource/img/add.png" height="20" width="20" /></th>
                <th width="30" class="icone"><img src="ressource/img/ico-delete-th.png" height="20" width="20" /></th>
                <th width="30" class="icone order <?php if($filter['order'] == 'k_content.contentSee') echo 'order'.$dir; ?>"       onClick="document.location='product.picker.php?id_type=<?php echo $_REQUEST['id_type'] ?>&cf&order=k_content.contentSee&direction=<?php echo $dir ?>'"><span><img src="ressource/img/ico-see.png" height="11" width="18" /></span></th>
                <th width="30" class="icone"><img src="ressource/img/ico-duplicate.png" height="18" width="18" /></th>
                <th width="30" class="icone"><img src="ressource/img/ico-version.png" height="20" width="20" /></th>
                <th width="30" class="icone"><img src="ressource/img/ico-comment-th.png" height="20" width="20" /></th>
                <th width="<?php echo 20 + (sizeof($lang) * 25) ?>"class="icone"><img src="ressource/img/ico-flag.png" height="20" width="20" /></th>
                <th width="60"  class="order <?php if($filter['order'] == 'k_content.id_content') echo 'order'.$dir; ?>"            onClick="document.location='product.picker.php?id_type=<?php echo $_REQUEST['id_type'] ?>&cf&order=k_content.id_content&direction=<?php echo $dir ?>'"><span>#</span></th>
                <th width="100" class="order <?php if($filter['order'] == 'k_content.contentDateCreation') echo 'order'.$dir; ?>"   onClick="document.location='product.picker.php?id_type=<?php echo $_REQUEST['id_type'] ?>&cf&order=k_content.contentDateCreation&direction=<?php echo $dir ?>'"><span>Création</span></th>
                <th width="100" class="order <?php if($filter['order'] == 'k_content.contentDateUpdate') echo 'order'.$dir; ?>"     onClick="document.location='product.picker.php?id_type=<?php echo $_REQUEST['id_type'] ?>&cf&order=k_content.contentDateUpdate&direction=<?php echo $dir ?>'"><span>Mise à jour</span></th>
                <th             class="order <?php if($filter['order'] == 'k_contentdata.contentName') echo 'order'.$dir; ?>"       onClick="document.location='product.picker.php?id_type=<?php echo $_REQUEST['id_type'] ?>&cf&order=k_contentdata.contentName&direction=<?php echo $dir ?>'"><span>Nom</span></th>
                <?php if($cType['is_business']){ ?>
                <th width="200" class="order <?php if($filter['order'] == 'k_content.contentRef') echo 'order'.$dir; ?>"            onClick="document.location='product.picker.php?id_type=<?php echo $_REQUEST['id_type'] ?>&cf&order=k_content.contentRef&direction=<?php echo $dir ?>'"><span>R&eacute;f&eacute;rence</span></th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
        <?php if(sizeof($content) == 0){ ?>
            <tr>
                <td colspan="10" style="padding:40px 0px 40px 0px; text-align:center; font-weight:bold">Aucun contenu disponible<br /><br /><a href="product.picker.data.php?id_type=<?php echo $id_type ?>">Ajouter une page : <?php echo $cType['typeName'] ?></a></td>
            </tr>   
        <?php }else{
                foreach($content as $e){
                    view($app, $cType, $filter, $e);
                }
            }
        ?>
        </tbody>
        <?php if(sizeof($content) > 0){ ?>
        <tfoot>
            <tr>
                <td width="30"><input type="checkbox" onchange="$$('.cb').set('checked', this.checked);" /></td>
                <td width="30"><input type="checkbox" onchange="$$('.cs').set('checked', this.checked);" /></td>
                <td colspan="6" height="25"><a href="#" onClick="apply();" class="button rButton"><span>Effectuer les changement sur la selection</span></a></td>
                <td colspan="<?php echo $cType['is_business'] ? '3' : '2' ?>" class="pagination"><?php $app->pagination($total, $limit, $filter['offset'], 'product.picker.php?cf&id_type='.$id_type.'&offset=%s'); ?></td>
            </tr>
        </tfoot>
        <?php } ?>
    </table>
</form>

<script>

    function duplicate(id){
        if(confirm("DUPLIQUER ?")){
            document.location='product.picker.php?id_type=<?php echo $_REQUEST['id_type'] ?>&duplicate='+id;
        }
    }

    function apply(){
        if(confirm("Confirmez-vous les changements sur la selection ?")){
            $('listing').submit();
        }
    }

    function insertProduct(id_content){
        parent.opener.document.getElementById('id_content').value=id_content;
        parent.opener.document.getElementById('form-cart').submit();
        
        window.close();      
    }
</script>

</div>

</body></html>