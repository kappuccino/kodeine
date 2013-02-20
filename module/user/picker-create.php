<?php

	if(!defined('COREINC')) die('Direct access not allowed');

    if($_POST['action']){

        #$app->pre($app->db_query, $app->db_error);
        $do = true;

        /*--------------- Données USER ---------------*/
        $def['k_user'] = array(
            'id_group'          => array('value' => -1,         			'zero'  => true),
            'is_admin'          => array('value' => 0,     					'zero'  => true),
        	'is_active'         => array('value' => 0,        				'zero'  => true),
            'userMail'          => array('value' => $_POST['userMail'],		'check' => '.'),
            'userDateCreate'    => array('value' => date('Y-m-d H:i:s'),	'check' => '.'),
            'userDateUpdate'    => array('value' => date('Y-m-d H:i:s'),	'check' => '.')
        );
		
		//die($app->pre($def));

        if($do){
        	
	        // Nouvelle adresse
	        $addressbook['k_useraddressbook'] = array(
	            'addressbookTitle'              => array('value' => $_POST['addressbookTitle'],             'query' => 1,   'check' => '.'),
	            'addressbookLastName'           => array('value' => $_POST['addressbookLastName'],          'query' => 1,   'check' => '.'),
	            'addressbookFirstName'          => array('value' => $_POST['addressbookFirstName'],         'query' => 1,   'check' => '.'),
	            'addressbookEmail'              => array('value' => $_POST['userMail'],             		'query' => 1),
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
			
			//Création utilisateur
            $result = $app->apiLoad('user')->userSet(array(
                'id_user'       => $_POST['id_user'],
                'def'           => $def,
                'field'         => $_POST['field'],
                'addressbook'	=> $addressbook,
                'debug'			=> false
            ));
			
			
			$id_user = $app->apiLoad('user')->id_user;
			
	        $message = ($result) ? 'OK: Enregistrement en base' : 'KO: Erreur, APP : <br />'.$app->apiLoad('user')->db_query.' '.$app->apiLoad('user')->db_error;
            if($result) header("Location: user.picker.addressbook.php?id_user=".$id_user);
        }else{
            $message = 'WA: Merci de compléter les champs correctement';
        }
    }

    
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
</ul>

<div class="app">

<?php
    if($message != NULL){
        list($class, $message) = $app->helperMessage($message);
        echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
    }
?>

<form action="user.picker.create.php" method="post" id="data">
	<input type="hidden" name="action" value="1" />
	<input type="hidden" name="id_group" value="-1" />

    <br />
    <?php
        if($ADDRESSBOOK_ERROR)      echo 'ADDRESSBOOK_ERROR';
        if($ADDRESSBOOK_FILLED)     echo 'ADDRESSBOOK_FILLED';
        if($ADDRESSBOOK_UPDATED)    echo 'ADDRESSBOOK_UPDATED';
    ?>                        
    <table width="100%" border="0">
        <tr>
            <td width="150">Email / Identifiant</td>
            <td><input type="text" name="userMail" value="<?php echo $app->formValue('', $_POST['userMail']) ?>" /></td>
        </tr>
        <tr>
            <td width="150">Nom de l'adresse</td>
            <td><input type="text" name="addressbookTitle" value="<?php echo $app->formValue('Principale', $_POST['addressbookTitle']) ?>" /></td>
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
            <td>TVA Intra</td>
            <td><input type="text" name="addressbookTVAIntra" value="<?php echo $app->formValue($modAddressBook['addressbookTVAIntra'], $_POST['addressbookTVAIntra']) ?>" /></td>
        </tr>
    </table>
    
    <button type="submit" style="padding:4px;">Valider</button>
</form>


</div></body></html>