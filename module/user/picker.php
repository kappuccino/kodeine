<?php

	if(!defined('COREINC')) die('Direct access not allowed');

    // Filter
    if(isset($_GET['cf'])){
        $app->filterSet('user.picker', $_GET);
        $filter = array_merge($app->filterGet('user.picker'), $_GET);  
    }else
    if(isset($_POST['filter'])){
        $app->filterSet('user.picker', $_POST['filter']);
        $filter = array_merge($app->filterGet('user.picker'), $_POST['filter']);   
    }else{
        $filter = $app->filterGet('user.picker');
    }

    $dir = ($filter['direction'] == 'ASC') ? 'DESC' : 'ASC';

    $users = $app->apiLoad('user')->userGet(array(
        'search'    => array(
                            array('searchField' => 'k_user.userMail', 'searchValue' => $filter['q'], 'searchMode' => 'CT'),
                            array('searchField' => 'k_useraddressbook.addressbookCompanyName', 'searchValue' => $filter['q'], 'searchMode' => 'CT'),
                            array('searchField' => 'k_useraddressbook.addressbookLastName', 'searchValue' => $filter['q'], 'searchMode' => 'CT'),
                            array('searchField' => 'k_useraddressbook.addressbookFirstName', 'searchValue' => $filter['q'], 'searchMode' => 'CT'),
                        ),
        'useField'  => false,
        'debug'     => false,
        'limit'     => $filter['limit'],
        'offset'    => $filter['offset'],
        'order'     => $filter['order'],
        'direction' => $filter['direction'],
        'sqlJoin'   => ' LEFT JOIN k_useraddressbook ON k_useraddressbook.id_user=k_user.id_user',
    ));

    $fields = $app->apiLoad('field')->fieldGet(array(
        'user'      => true,
        'debug'     => false
    ));

?><!DOCTYPE html>
<html lang="fr">
<head>
    <?php include(ADMINUI.'/head.php'); ?>
</head>
<body>

<ul class="menu-icon clearfix">
    <li class=""><a href="user.picker.php"><img src="ressource/img/ico-list.png" height="32" width="32" /><br /><?php echo _('Listing') ?></a></li>
    
    <div style="float:right; margin:15px 10px 0px 0px;">
        <a href="user.picker.create.php" class="button colorButton rButton"><?php echo _('New user') ?></a>
    </div>

</ul>
       

<div class="app">

<div class="quickForm clearfix">

    <div class="upper clearfix">
        <div class="label"><?php echo _('Options') ?></div>
    </div>

    <form action="user.picker.php" method="post" id="filter">
        <input type="hidden" name="filter[open]"    value="0" />
        <input type="hidden" name="filter[offset]"  value="0" />

        <?php echo _('Search') ?>
        <input type="text" name="filter[q]" value="<?php echo $filter['q'] ?>" />

	    <?php echo _('How many') ?>
        <input type="text" name="filter[limit]" value="<?php echo $filter['limit'] ?>" size="3" />


        <input type="submit" />
    </form>
</div>

<form method="post" action="user.picker.php" id="listing">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing">
    <thead>
        <tr>
            
            <th width="30"  class="icone"><img src="ressource/img/add.png" height="20" width="20" /></th>
            
            <th width="80"  class="order <?php if($filter['order'] == 'k_user.id_user')     echo 'order'.$dir; ?>" onClick="document.location='user.picker.php?cf&order=k_user.id_user&direction=<?php echo $dir ?>'"><span>#</span></th>
            <th width="110" class="order <?php if($filter['order'] == 'k_user.userDateCreate')  echo 'order'.$dir; ?>" onClick="document.location='user.picker.php?cf&order=k_user.userDateCreate&direction=<?php echo $dir ?>'"><span><?php echo _('Created') ?></span></th>
            <th width="110" class="order <?php if($filter['order'] == 'k_user.userDateUpdate')  echo 'order'.$dir; ?>" onClick="document.location='user.picker.php?cf&order=k_user.userDateUpdate&direction=<?php echo $dir ?>'"><span><?php echo _('Updated') ?></span></th>
            <th             class="order <?php if($filter['order'] == 'k_useraddressbook.addressbookCompanyName')        echo 'order'.$dir; ?>" onClick="document.location='user.picker.php?cf&order=k_useraddressbook.addressbookCompanyName&direction=<?php echo $dir ?>'"><span><?php echo _('Company name') ?></span></th>
            <th             class="order <?php if($filter['order'] == 'k_useraddressbook.addressbookLastName')        echo 'order'.$dir; ?>" onClick="document.location='user.picker.php?cf&order=k_useraddressbook.addressbookLastName&direction=<?php echo $dir ?>'"><span><?php echo _('Name') ?></span></th>
            <th             class="order <?php if($filter['order'] == 'k_useraddressbook.addressbookFirstName')        echo 'order'.$dir; ?>" onClick="document.location='user.picker.php?cf&order=k_useraddressbook.addressbookFirstName&direction=<?php echo $dir ?>'"><span><?php echo _('Last name') ?></span></th>
            <th             class="order <?php if($filter['order'] == 'k_user.userMail')        echo 'order'.$dir; ?>" onClick="document.location='user.picker.php?cf&order=k_user.userMail&direction=<?php echo $dir ?>'"><span><?php echo _('Email') ?></span></th>
            <?php
                $colspan = 1;

                if($filter['cola'] != ''){
                    $col = $app->apiLoad('field')->fieldGet(array('id_field' => $filter['cola']));
                    echo "<th width=\"180\" class=\"order ".(($filter['order'] == 'field'.$filter['cola']) ? 'order'.$dir : '')."\" onClick=\"document.location='user.picker.php?cf&order=field".$filter['cola']."&direction=".$dir."'\"><span>".$col['fieldName']."</span></th>";
                    $colspan++;
                }

                if($filter['colb'] != ''){
                    $col = $app->apiLoad('field')->fieldGet(array('id_field' => $filter['colb']));
                    echo "<th width=\"180\" class=\"order ".(($filter['order'] == 'field'.$filter['colb']) ? 'order'.$dir : '')."\" onClick=\"document.location='user.picker.php?cf&order=field".$filter['colb']."&direction=".$dir."'\"><span>".$col['fieldName']."</span></th>";
                    $colspan++;
                }
            ?>
        </tr>
    </thead>
    <tbody><?php
    if(sizeof($users) > 0){
        foreach($users as $e){
                $disabled = ($e['id_user'] == $app->user['id_user']) ? "disabled=\"disabled\"" : NULL;
                $a = $app->apiLoad('user')->userAddressBookGet(array('delivery' => true, 'id_user' => $e['id_user']));
        ?>
        <tr>
            <td><a href="#" onclick="insertUser(<?php echo $e['id_user']; ?>);"><img src="ressource/img/add.png" width="20"></a></td>
            <td><?php echo $e['id_user'] ?></td>
            <td class="dateTime">
                <span class="date"><?php echo $app->helperDate($e['userDateCreate'], '%d.%m.%Y')?></span>
                <span class="time"><?php echo $app->helperDate($e['userDateCreate'], '%Hh%M')    ?></span>
            </td>
            <td class="dateTime">
                <span class="date"><?php echo $app->helperDate($e['userDateUpdate'], '%d.%m.%Y')?></span>
                <span class="time"><?php echo $app->helperDate($e['userDateUpdate'], '%Hh%M')    ?></span>
            </td>
            <td><a href="user.picker.data.php?id_user=<?php echo $e['id_user']; ?>"><?php echo $a['addressbookCompanyName'] ?></a></td>
            <td><a href="user.picker.data.php?id_user=<?php echo $e['id_user']; ?>"><?php echo $a['addressbookLastName'] ?></a></td>
            <td><a href="user.picker.data.php?id_user=<?php echo $e['id_user']; ?>"><?php echo $a['addressbookFirstName'] ?></a></td>
            <td><a href="user.picker.data.php?id_user=<?php echo $e['id_user']; ?>"><?php echo $e['userMail'] ?></a></td>
            <?php
                if($filter['cola'] != '') echo "<td>".$e['field'.$filter['cola']]."</td>";
                if($filter['colb'] != '') echo "<td>".$e['field'.$filter['colb']]."</td>";
            ?>
        </tr>
        <?php }
    }else{ ?>
        <tr>
            <td colspan="<?php echo (7 + $colspan) ?>" style="text-align:center; font-weight:bold; padding:30px 0px 30px 0px;">
	            <?php echo _('No result'); ?>
            </td>
        </tr>
    <?php } ?>
    </tbody>
    <tfoot>
        <tr>
        <?php if(sizeof($users) > 0){ ?>
            <td height="25"></td>
            <td colspan="3"></td>
            <td colspan="<?php echo $colspan ?>" class="pagination"><?php $app->pagination($app->apiLoad('user')->total, $app->apiLoad('user')->limit, $filter['offset'], 'user.picker.php?cf&offset=%s'); ?></td>
        <?php }else{ ?>
            <td colspan="<?php echo (4 + $colspan) ?>">&nbsp;</td>
        <?php } ?>
        </tr>
    </tfoot>
</table>


</form>

</div>

<script>

	function insertUser(id_user){
	    parent.opener.document.getElementById('id_user').value=id_user;
	    parent.opener.document.getElementById('form-cart').submit();
	    window.close();
	}


	window.onbeforeunload = function() {
	    parent.opener.document.getElementById('form-cart').submit();
	}

</script>

</body></html>