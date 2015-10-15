<?php

if(!defined('COREINC')) die('Direct access not allowed');

$user = $app->apiLoad('user')->userGet(array(
  'id_user' => $_GET['id_user']
));

$addresses = $app->apiLoad('user')->userAddressBookGet(array(
  'id_user'			=> $_GET['id_user'],
  'debug'				=> false
));

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
  <li><a onclick="filterToggle('user');" class="btn btn-small"><?php echo _('Display settings'); ?></a></li>
  <li><a href="data" class="btn btn-small btn-success"><?php echo _('New user'); ?></a></li>
</div>

<div id="app">
  <?php foreach($addresses as $address) { ?>
  <table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing">
    <thead>
      <tr>
        <th>Prénom</th>
        <th>Nom</th>
        <th>Email</th>
        <th>Adresse</th>
        <th>Code Postal</th>
        <th>Ville</th>
        <th>Code Pays</th>
        <th>Téléphone</th>
        <th>Remarques</th>
      </tr>
    </thead>

    <tbody>
      <tr>
        <td><?php echo $address['addressbookFirstName'] ?></td>
        <td><?php echo $address['addressbookLastName'] ?></td>
        <td><?php echo $address['addressbookEmail'] ?></td>
        <td><?php echo $address['addressbookAddresse1'].' '.$address['addressbookAddresse2'].' '.$address['addressbookAddresse3'] ?></td>
        <td><?php echo $address['addressbookCityCode'] ?></td>
        <td><?php echo $address['addressbookCityName'] ?></td>
        <td><?php echo $address['addressbookCountryCode'] ?></td>
        <td><?php echo $address['addressbookPhone1'].' / '.$address['addressbookPhone2'] ?></td>
        <td><?php echo $address['addressbookRemarque'] ?></td>
      </tr>
    </tbody>

  </table>
  <?php } ?>
</div>

<?php include(COREINC.'/end.php'); ?>

</body></html>