<?php

	if(!defined('COREINC')) die('Direct access not allowed');

    # Suppression d'un carnet d'adresse
    #
    if($_GET['remove'] != NULL){

        $me = $app->apiLoad('user')->userAddressBookGet(array(
            'id_addressbook'    => $_GET['remove'],
            'id_user'           => $_REQUEST['id_user'],
            'debug'             => false
        ));

        if($me['addressbookIsProtected'] == 0){
            $app->dbQuery("DELETE FROM k_useraddressbook WHERE id_addressbook='".$_GET['remove']."' AND id_user=".$_REQUEST['id_user']);
        }
    }
    
    if($_POST['create'] || $_POST['update']){
            
        $do = true;
        
        // Nouvelle adresse ou modification
        $def['k_useraddressbook'] = array(
            'id_user'                       => array('value' => $_REQUEST['id_user'],                   'query' => 1),
            'addressbookTitle'              => array('value' => $_POST['addressbookTitle'],             'query' => 1,   'check' => '.'),
            'addressbookLastName'           => array('value' => $_POST['addressbookLastName'],          'query' => 1,   'check' => '.'),
            'addressbookFirstName'          => array('value' => $_POST['addressbookFirstName'],         'query' => 1,   'check' => '.'),
            'addressbookEmail'              => array('value' => $_POST['addressbookEmail'],             'query' => 1),
            'addressbookCompanyName'        => array('value' => $_POST['addressbookCompanyName'],       'query' => 1),
            'addressbookCompanyFonction'    => array('value' => $_POST['addressbookCompanyFunction'],   'query' => 1),
            'addressbookAddresse1'          => array('value' => $_POST['addressbookAddresse1'],         'query' => 1,   'check' => '.'),
            'addressbookAddresse2'          => array('value' => $_POST['addressbookAddresse2'],         'query' => 1),
            'addressbookAddresse3'          => array('value' => $_POST['addressbookAddresse3'],         'query' => 1),
            'addressbookCityCode'           => array('value' => $_POST['addressbookCityCode'],          'query' => 1,   'check' => '.'),
            'addressbookCityName'           => array('value' => $_POST['addressbookCityName'],          'query' => 1,   'check' => '.'),
            'addressbookCountryCode'        => array('value' => $_POST['addressbookCountryCode'],       'query' => 1),
            'addressbookStateName'          => array('value' => $_POST['addressbookStateName'],         'query' => 1),
            'addressbookPhone1'             => array('value' => $_POST['addressbookPhone1'],            'query' => 1),
            'addressbookPhone2'             => array('value' => $_POST['addressbookPhone2'],            'query' => 1),
            'addressbookTVAIntra'           => array('value' => $_POST['addressbookTVAIntra'],          'query' => 1)
        );

        
            //die($app->pre($_REQUEST));
        if(!$app->formValidation($def)) $do = false;
        if($do){
            //die($app->pre($_POST));
            $result = $app->apiLoad('user')->userAddressBookSet(array(
                'id_user'           => $_REQUEST['id_user'],
                'id_addressbook'    => $_REQUEST['id_addressbook'],
                'def'               => $def,
                'debug'             => false
            ));

            if($result){
                $ADDRESSBOOK_UPDATED = true;
            }else{
                $ADDRESSBOOK_ERROR = true;
            }
        }else{
            $ADDRESSBOOK_FILLED = true;
        }
    }

    if(sizeof($_POST) > 0){

        /*----------- Carnet d'adresses ------------*/
        
        
        // Livraison et facturation par défaut
        
        $books      = $app->apiLoad('user')->userAddressBookGet(array(
            'id_user'   => $_REQUEST['id_user']
        ));

        $delivery   = $_POST['addressbookIsDelivery'];
        if($delivery == NULL) $delivery = $books[0]['id_addressbook'];

        $billing    = $_POST['addressbookIsBilling'];
        if($billing == NULL) $billing = $books[0]['id_addressbook'];

        $app->dbQuery("UPDATE k_useraddressbook SET addressbookIsDelivery=0, addressbookIsBilling=0, addressbookIsProtected=0 WHERE id_user='".$_REQUEST['id_user']."'");
        $app->dbQuery("UPDATE k_useraddressbook SET addressbookIsDelivery=1,addressbookIsProtected=1  WHERE id_addressbook='".$delivery."'");
        $app->dbQuery("UPDATE k_useraddressbook SET addressbookIsBilling=1,addressbookIsProtected=1   WHERE id_addressbook='".$billing."'");
        
        
        
    }
    if($do === true)header('Location: ?id_user='.$_REQUEST['id_user']);

    # Recuperer les carnets d'addresse
    $myAddressBook = $app->apiLoad('user')->userAddressBookGet(array(
        'id_user'   => $_REQUEST['id_user'],
        'debug'     => false
    ));

    # Recuperer l'adresse à modifier
    $modAddressBook = $app->apiLoad('user')->userAddressBookGet(array(
        'id_user'           => $_REQUEST['id_user'],
        'id_addressbook'    => $_REQUEST['id_addressbook'],
        'debug'             => false
    ));
    
?><!DOCTYPE html>
<html lang="fr">
<head>
    <?php include(ADMINUI.'/head.php'); ?>

    <link rel="stylesheet" type="text/css" media="all" href="ressource/css/form.css" />
    <script type="text/javascript" src="ressource/plugin/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
    <script type="text/javascript" src="ressource/js/content.js"></script>

    <!-- Calendar -->
    <script src="ressource/plugin/calendar-eightysix/js/calendar-eightysix-v1.0.1.js"></script>
    <link type="text/css" media="screen" href="ressource/plugin/calendar-eightysix/css/calendar-eightysix-default.css" rel="stylesheet" />
</head>
<body>

<ul class="menu-icon clearfix">
    <li class=""><a href="user.picker.php"><img src="ressource/img/ico-list.png" height="32" width="32" /><br />Liste</a></li>
    
    <div style="float:right; margin:15px 10px 0px 0px;">
        <a href="user.picker.create.php" class="button colorButton rButton">Ajouter un utilisateur</a>
    </div>

</ul>

<div class="app">


<div style="padding:5px 0px 5px 5px">
    <a href="javascript:$('data').submit()" class="button rButton">Enregistrer</a>
    <a href="?create=1&id_user=<?php echo $_REQUEST['id_user']; ?>" class="button rButton">Nouvelle adresse</a>
    <?php if($_REQUEST['id_user'] > 0){ ?>
    <a href="user.picker.addressbook.php?id_user=<?php echo $_REQUEST['id_user'] ?>" class="button rButton">Recharger la page</a>
    <?php } ?>
    <?php if($_REQUEST['id_user'] > 0){ ?>
    <a href="user.picker.data.php?id_user=<?php echo $_REQUEST['id_user'] ?>"  class="button rButton">Revenir à la fiche utilisateur</a>
    <?php } ?>
    <a href="" class="button rButton" onclick="insertUser(<?php echo $_REQUEST['id_user']; ?>);return false;">Séléctionner</a>
</div>

    <form action="user.picker.addressbook.php?id_user=<?php echo $_REQUEST['id_user']; ?>" method="post" id="data">
        <br />
        <?php
            if($ADDRESSBOOK_ERROR)      echo 'ADDRESSBOOK_ERROR';
            if($ADDRESSBOOK_FILLED)     echo 'ADDRESSBOOK_FILLED';
            if($ADDRESSBOOK_UPDATED)    echo 'ADDRESSBOOK_UPDATED';
        ?>
        <input type="hidden" name="create" id="create" value="<?php echo $_REQUEST['create']; ?>">
        <input type="hidden" name="update" id="update" value="<?php echo $_REQUEST['update']; ?>">
        <input type="hidden" name="id_addressbook" id="id_addressbook" value="<?php echo $_REQUEST['id_addressbook']; ?>">
        <div id="newaddress" style="display:<?php echo ($_REQUEST['id_addressbook'] > 0 || $_REQUEST['create'] || $do === false) ? 'block' : 'none'; ?>">                           
            <table width="100%" border="0">
                <tr>
                    <td width="150">Nom de l'adresse</td>
                    <td><input type="text" name="addressbookTitle" value="<?php echo $app->formValue($modAddressBook['addressbookTitle'], $_POST['addressbookTitle']) ?>" /></td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td>Nom</td>
                    <td><input type="text" name="addressbookLastName" value="<?php echo $app->formValue($modAddressBook['addressbookLastName'], $_POST['addressbookLastName']) ?>" /></td>
                </tr>
                <tr>
                    <td>Prénom</td>
                    <td><input type="text" name="addressbookFirstName" value="<?php echo $app->formValue($modAddressBook['addressbookFirstName'], $_POST['addressbookFirstName']) ?>" /></td>
                </tr>
                <tr>
                    <td>Raison sociale</td>
                    <td><input type="text" name="addressbookCompanyName" value="<?php echo $app->formValue($modAddressBook['addressbookCompanyName'], $_POST['addressbookCompanyName']) ?>" /></td>
                </tr>
                <tr>
                    <td>Addresse 1</td>
                    <td><input type="text" name="addressbookAddresse1" value="<?php echo $app->formValue($modAddressBook['addressbookAddresse1'], $_POST['addressbookAddresse1']) ?>" /></td>
                </tr>
                <tr>
                    <td>Addresse 2</td>
                    <td><input type="text" name="addressbookAddresse2" value="<?php echo $app->formValue($modAddressBook['addressbookAddresse2'], $_POST['addressbookAddresse2']) ?>" /></td>
                </tr>
                <tr>
                    <td>Addresse 3</td>
                    <td><input type="text" name="addressbookAddresse3" value="<?php echo $app->formValue($modAddressBook['addressbookAddresse3'], $_POST['addressbookAddresse3']) ?>" /></td>
                </tr>
                <tr>
                    <td>Code postal</td>
                    <td><input type="text" name="addressbookCityCode" value="<?php echo $app->formValue($modAddressBook['addressbookCityCode'], $_POST['addressbookCityCode']) ?>" /></td>
                </tr>
                <tr>
                    <td>Ville</td>
                    <td><input type="text" name="addressbookCityName" value="<?php echo $app->formValue($modAddressBook['addressbookCityName'], $_POST['addressbookCityName']) ?>" /></td>
                </tr>
                <tr>
                    <td>Pays</td>
                    <td><select name="addressbookCountryCode"><?php
                        foreach($app->countryGet() as $e){
                            $sel = ($e['iso'] == $app->formValue($modAddressBook['addressbookCountryCode'], $_POST['addressbookCountryCode'])) ? ' selected' : NULL;
                            echo "<option value=\"".$e['iso']."\"".$sel.">".$e['countryName']."</option>";
                        }               
                    ?></select>
                </tr>
                <tr>
                    <td>Etat</td>
                    <td><input type="text" name="addressbookStateName" value="<?php echo $app->formValue($modAddressBook['addressbookStateName'], $_POST['addressbookStateName']) ?>" /></td>
                </tr>
                <tr>
                    <td>Téléphone 1</td>
                    <td><input type="text" name="addressbookPhone1" value="<?php echo $app->formValue($modAddressBook['addressbookPhone1'], $_POST['addressbookPhone1']) ?>" /></td>
                </tr>
                <tr>
                    <td>Téléphone 2</td>
                    <td><input type="text" name="addressbookPhone2" value="<?php echo $app->formValue($modAddressBook['addressbookPhone2'], $_POST['addressbookPhone2']) ?>" /></td>
                </tr>
                <tr>
                    <td>TvA Intra</td>
                    <td><input type="text" name="addressbookTVAIntra" value="<?php echo $app->formValue($modAddressBook['addressbookTVAIntra'], $_POST['addressbookTVAIntra']) ?>" /></td>
                </tr>
            </table>
            
            <button type="submit" style="padding:4px;">Valider</button>
             <a href="" onclick="javascript:$('newaddress').setStyle('display', 'none');$('create').value='';$('update').value='';$('id_addressbook').value='';return false;">Annuler</a>
        </div>
        <table width="100%" border="1">
           <tr align="center">
                <td ><b>&nbsp;Adresse</b></td>
                <td width="100"><b>Livraison</b></td>
                <td width="100"><b>Facturation</b></td>
                <td width="100">Action</td>
            </tr>
            <?php foreach($myAddressBook as $idx => $e){ ?>
            <tr>
                <td><?php
                    echo "<b>".$e['addressbookTitle']."</b><br />";
                    echo $app->apiLoad('user')->userAddressBookFormat($e, array('name' => true, 'html' => true));
                ?></td>
                <td><input type="radio" name="addressbookIsDelivery" value="<?php echo $e['id_addressbook'] ?>" <?php if($e['addressbookIsDelivery']) echo "checked" ?> /></td>
                <td><input type="radio" name="addressbookIsBilling"  value="<?php echo $e['id_addressbook'] ?>" <?php if($e['addressbookIsBilling'])  echo "checked" ?> /></td>
                <td>
                    <a href="?update=1&id_addressbook=<?php echo $e['id_addressbook'] ?>&id_user=<?php echo $_REQUEST['id_user']; ?>">Modifier</a>
                    <?php if($e['addressbookIsProtected'] == 0){ ?>
                        <a href="?remove=<?php echo $e['id_addressbook']; ?>&id_user=<?php echo $_REQUEST['id_user']; ?>" onclick="return s()"> - Supprimer</a>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
        </table>
    </form>

    <script>
        function s(){
            return confirm("Souhaitez-vous supprimer cette adresse ?");
        }
        function insertUser(id_user){
            parent.opener.document.getElementById('id_user').value=id_user;
            parent.opener.document.getElementById('form-cart').submit();
            window.close();      
        }
		window.onbeforeunload = function() {
		    parent.opener.document.getElementById('form-cart').submit();
		} 
    </script>
            
</div></body></html>