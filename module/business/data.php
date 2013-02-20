<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	foreach($_POST as $k=>$v){
        $tab = explode('-',$k);
        if(sizeof($tab) == 3){
            $_POST[$tab[0]][$tab[1]][$tab[2]] = $v;
        }
    }
    # Data
    # On recupere le panier
    #
    $myCmd = $app->apiLoad('business')->businessCartGet(array(
        'is_cart'   => true,
        'create'    => true,
        'id_cart'   => $_REQUEST['id_cart'],
        'debug'     => false
    ));
	
    $create = false;
	
    if($_REQUEST['id_cart'] != $myCmd['id_cart']){
        $create = true;
        $def['k_businesscart'] = array(
            'is_admin' => array('value' => 1)
         );
        $q = $app->dbUpdate($def)." WHERE id_cart=".$myCmd['id_cart'];
        $app->dbQuery($q);
        $app->apiLoad('business')->businessCartUserSet($myCmd['id_cart'], $myCmd['id_user']);
    }
    
    $_REQUEST['id_cart'] = $myCmd['id_cart'];
    if($_REQUEST['id_cart'] == '')die("Pas de panier");
    
    if($create)header('Location: data?id_cart='.$_REQUEST['id_cart']);
    
	# On enregistre les changements
	if(sizeof($_POST) > 0){
        # Suppression Commande        
        if($_POST['cartremove']){
            $app->apiLoad('business')->businessCartRemove($_REQUEST['id_cart']);
            
            header('Location: index?id_cart='.$_REQUEST['id_cart']);
            exit();
        }
        
        
	    if($_REQUEST['id_cart'] > 0){
	        
            $coeff_tax = 1;
            if($_POST['cartCarriageTax'] > 0) $coeff_tax = 1+($_POST['cartCarriageTax']/100);

	        $def['k_businesscart'] = array(
                'is_admin'                          => array('value' => 1),
                'id_shop'                           => array('value' => $_POST['id_shop']),
                'cartName'                          => array('value' => $_POST['cartName']),
                'cartStatus'                        => array('value' => $_POST['cartStatus']),
                'cartPayment'                       => array('value' => $_POST['cartPayment']),
                'cartDeliveryStatus'                => array('value' => $_POST['cartDeliveryStatus']),
                'cartCarriage'                      => array('value' => ($_POST['cartCarriage'] > 0) ? number_format($_POST['cartCarriage'], 2, '.', ' ') : 0),
                'cartCarriageTax'                   => array('value' => ($_POST['cartCarriageTax'] > 0) ? number_format($_POST['cartCarriageTax'], 2, '.', ' ') : 0),
                'cartCarriageTotalTax'              => array('value' => ($coeff_tax * $_POST['cartCarriage'] > 0) ? number_format($coeff_tax * $_POST['cartCarriage'], 2, '.', ' ') : 0),
                'cartCarriageAccountNumber'         => array('value' => $_POST['cartCarriageAccountNumber'])
             );
             $q = $app->dbUpdate($def)." WHERE id_cart=".$_REQUEST['id_cart'];
             $app->dbQuery($q);
        }
        if($_POST['id_user'] > 0){
            $app->apiLoad('business')->businessCartUserSet($_REQUEST['id_cart'], $_POST['id_user']);
		}
		
        if($_POST['id_content'] > 0)
            $msg = $app->apiLoad('business')->businessCartLineSet(array('id_cart' => $_REQUEST['id_cart'], 'id_content' => $_POST['id_content'], 'contentQuantity' => 1, 'create' => true));

        
        if($_POST['newline'])
            $app->apiLoad('business')->businessCartLineSet(array('id_cart' => $_REQUEST['id_cart']));
            
        
        foreach($myCmd['line'] as $l){
            if(sizeof($_POST['line'][$l['id_cartline']]) > 0){
                $opt = array_merge(array('id_cart' => $_REQUEST['id_cart'],'id_cartline' => $l['id_cartline']),$_POST['line'][$l['id_cartline']]);
                $app->apiLoad('business')->businessCartLineSet($opt);
            }
        }
        
        # Finalisation Commande        
        if($_POST['cartfinalise'] == '1'){
            
            $app->apiLoad('business')->businessCmdNew(array('id_cart' => $_REQUEST['id_cart']));
            header('Location: index');
            exit();
        }
        
        if($msg == '')header('Location: data?id_cart='.$_REQUEST['id_cart']);
	}
	
    $app->apiLoad('business')->businessCartTaxJSON($_REQUEST['id_cart']);
	
	# Data
	# On recupere le panier
	#
	$myCmd = $app->apiLoad('business')->businessCartGet(array(
		'is_cart'		=> true,		
		'userAffect'	=> false,		
		'id_cart' 		=> $_REQUEST['id_cart'],
		'debug'			=> false
	));
	
    
    # Bases TVA - Totaux TVA
   /* $tva = array();
    
    foreach($myCmd['line'] as $l){
        if($l['contentTax'] > 0){
            $tva[$l['contentTax']]['total'] += $l['contentPriceQuantity'];
            $tva[$l['contentTax']]['base'] += $l['contentPriceTaxQuantity'] - $l['contentPriceQuantity'];
        }
    }
    # Tri par ordre croissant TVA
    ksort($tva);*/
    
    # Tri par ordre décroissant les lignes de commande
    //krsort($myCmd['line']);
	
	/*
	$myCmd = $app->apiLoad('business')->businessCartGet(array(
		'is_cmd'	=> true,
		'id_cart'	=> $_GET['id_cart'],
		'debug'		=> false
	));
	*/
	
	//$flag = $app->dbMulti("SELECT * FROM k_businesscartflag WHERE id_cart=".$myCmd['id_cart']);
?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
<?php   #<script type="text/javascript" src="http://fbug.googlecode.com/svn/lite/branches/firebug1.3/content/firebug-lite-dev.js"></script> ?>

</head>
<body>
	
	<div class="pbg">
		
		<!-- BANDEAU TOP - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - --> 
		
		<div class="top">
			<div a href="../" class="logo">Logo</div>
			<div class="pathway clearfix">
				<h1>
					<a href="../business/">Business</a> &raquo;
					<a href="../business/data?id_cart=<?php echo $_REQUEST['id_cart'] ?>">Création de commande</a>
				</h1>
				<!--<div class="types clearfix">
					<div class="button button-blue"><a href="next.php">Lien #1</a></div>
					<div class="button button-blue"><a href="#">type</a></div>
					<div class="button button-blue selected"><a href="#">type</a></div>
					<div class="button button-blue"><a href="#">type</a></div>
				</div>-->
			</div>
		</div>
	</div>
	
<?php
    if($msg != '')echo '<script>alert("'.addslashes($msg).'");</script>';
?>
<?php #include('ressource/ui/menu.business.php'); ?>

<br />


<div class="bocontainer">
	<div class="row-fluid">
	<?php include('lib/menu.php'); ?>
		
	<div class="app">
	
	<div style="padding:10px 10px 10px 0px">
	    <a onclick="cartremove()" class="button button-red">Supprimer la commande</a>
	    <a onclick="finalise()" class="button button-blue">Finaliser la commande</a>
	    <a href="data-index" class="button button-blue">Commandes en cours de création</a>
	</div>
	
	<br />
	<br style="clear:both;" />
	
		<form action="?id_cart=<?php echo $_REQUEST['id_cart'] ?>" id="form-cart" method="post">
		    
	        <input name="id_cart" type="hidden" value="<?php echo $_REQUEST['id_cart']; ?>">
	        <input id="cartfinalise" name="cartfinalise" type="hidden">
	        <input id="cartremove" name="cartremove" type="hidden">
	
			<div class="center">
				<div class="control-group">
					<label class="control-label" for="cartName"><h3>Création de commande</h3></label>
					<div class="controls">
						<input class="input-xlarge" id="cartName" onchange="save()" name="cartName" type="text" value="<?php echo $myCmd['cartName']; ?>" placeholder="Nom de la commande">
					</div>
				</div>
				
	            <div>
	                <a class="button button-blue" onclick="userPicker(0)">S&eacute;lectionner un client</a>
	            </div>  
	            <div>
	                <a class="button button-blue" onclick="userPicker(<?php echo $myCmd['id_user']; ?>)">Editer le client</a>
	            </div>  	
				
				<br /><br />
				
				<table width="100%" border="1">
					<tr valign="top">
						<td width="40%">
						
							<input id="id_user" name="id_user" type="hidden" value="<?php echo $myCmd['id_user']; ?>">
							<p>Commande numero 	: </p>
							<p>Code client 		: <?php echo $myCmd['id_user'] ?></p>
							<p>Date				: <span id="cartDateCreate"><?php echo $myCmd['cartDateCreate'] ?></span></p>
							<p>Statut			: <select name="cartStatus" onchange="save()">
													<?php
														foreach($app->apiLoad('business')->businessConfigGet(array('configField' => 'cartStatus')) as $e){
															$sel = ($myCmd['cartStatus'] == $e['configKey']) ? ' selected' : NULL;
															echo "<option value=\"".$e['configKey']."\"".$sel.">".$e['configCustom']."</option>";
														}
													?>
												   </select></p>
							<p>Mode de paiement	: <select name="cartPayment" onchange="save()">
	                                                <?php
	                                                    foreach($app->apiLoad('business')->businessConfigGet(array('configField' => 'cartPayment')) as $e){
	                                                        $sel = ($myCmd['cartPayment'] == $e['configKey']) ? ' selected' : NULL;
	                                                        echo "<option value=\"".$e['configKey']."\"".$sel.">".$e['configCustom']."</option>";
	                                                    }
	                                                ?>
												   </select></p>
	                        <p>Statut livraison : <select name="cartDeliveryStatus" onchange="save()">
	                                                <?php
	                                                    foreach($app->apiLoad('business')->businessConfigGet(array('configField' => 'cartDeliveryStatus')) as $e){
	                                                        $sel = ($myCmd['cartDeliveryStatus'] == $e['configKey']) ? ' selected' : NULL;
	                                                        echo "<option value=\"".$e['configKey']."\"".$sel.">".$e['configCustom']."</option>";
	                                                    }
	                                                ?>
	                                               </select></p>
	                        <p>Shop : <?php echo $app->apiLoad('shop')->shopSelector(array(
	                                        'name'      => 'id_shop',
	                                        'value'     => $myCmd['id_shop'],
	                                        'language'  => 'fr',
	                                        'one'       => true,
	                                        'empty'     => true
	                                )); ?>
						</td>
						<td width="30%">
							<p></p><b>Adresse de livraison</b></p>
	                        <span id="cartDelivery"><b><?php echo str_replace("\n", '<br />', $myCmd['cartDeliveryName']) ?></b><br /><?php echo str_replace("\n", '<br />', $myCmd['cartDeliveryAddress']); ?></span>
						</td>
						<td width="30%">
							<p><b>Adresse de facturation</b></p>
							<span id="cartBilling"><b><?php echo str_replace("\n", '<br />', $myCmd['cartBillingName']) ?></b><br /><?php echo str_replace("\n", '<br />', $myCmd['cartBillingAddress']); ?></span>
						</td>
					</tr>
				</table>
				
				<p>&nbsp;</p>
				
	            <input id="id_content" name="id_content" type="hidden">
	            <input id="newline" name="newline" type="hidden">
	                        
				<table width="100%" border="1" id="table-cart">
					<tr>
						<td width="100">Ref</td>
						<td>Libellé Produit</td>
	
						<td width="90">P.U. HT</td>
						<td width="90">Remise</td>
	                    <td width="90">TVA</td>
						<td width="90">P.U. TTC</td>
						<td width="90">Compte</td>
	                    <td width="90">Qt&eacute;.</td>
	                    <td width="90">Stock</td>
						<td width="90">Total HT</td>
	                    <td width="90">Total TTC</td>
	                    <td width="90">&nbsp;</td>
					</tr>
					<?php
					    foreach($myCmd['line'] as $l){
					        
					    // Récupération du stock
					    $stock = "-";
					    if($l['id_content'] > 0) {
					        $qstock = $app->dbOne("SELECT contentStock,contentStockNeg FROM k_content WHERE id_content='".$l['id_content']."'");
	                        if($qstock['contentStockNeg'] >= 0) $stock = $qstock['contentStock'];           
				        }
					?>
					<tr>
						<td>
						    <input name="line-<?php echo $l['id_cartline'] ?>-id_content" value="<?php echo $l['id_content'] ?>" type="hidden">
						    <?php if($l['id_content'] > 0) { ?>
						        <?php echo $l['contentRef'] ?>
	                        <?php }else { ?>
	                            <input name="line-<?php echo $l['id_cartline'] ?>-contentRef" value="<?php echo $l['contentRef'] ?>" onchange="save()">
	                        <?php } ?>			        
					    </td>
						<td>
	                        <input name="line-<?php echo $l['id_cartline'] ?>-contentName" value="<?php echo $l['contentName'] ?>" onchange="save()">                      
	                    </td>
	
						<td><input name="line-<?php echo $l['id_cartline'] ?>-contentPrice" value="<?php echo $l['contentPrice'] ?>" size="4" onchange="save()"></td>
						<td><input name="line-<?php echo $l['id_cartline'] ?>-contentPriceDiscount" value="<?php echo $l['contentPriceDiscount'] ?>" size="4" onchange="save()">
							<select name="line-<?php echo $l['id_cartline'] ?>-contentPriceDiscountMode" onchange="save()">
								<option value="PERCENT" <?php if($l['contentPriceDiscountMode'] == "PERCENT")echo "selected"; ?>>%</option>
								<option value="FIXE" <?php if($l['contentPriceDiscountMode'] == "FIXE")echo "selected"; ?>>Fixe</option>
							</select>
						</td>
	                    <td><?php echo $app->apiLoad('business')->businessTaxSelector(array('one' => true, 'empty' => true, 'events' => 'onchange="save();"', 'name' => 'line-'.$l['id_cartline'].'-contentTax','value' => $l['contentTax'])); ?></td>
						<td><span id="line-<?php echo $l['id_cartline'] ?>-contentPriceTax"><?php echo $l['contentPriceTax'] ?></span></td>
	                    <td><?php echo $app->apiLoad('business')->businessAccountSelector(array('one' => true, 'empty' => true, 'events' => 'onchange="save();"', 'name' => 'line-'.$l['id_cartline'].'-accountNumber','value' => $l['accountNumber'])); ?></td>
						<td><input name="line-<?php echo $l['id_cartline'] ?>-contentQuantity" value="<?php echo $l['contentQuantity'] ?>" size="4" onchange="save()"></td>
	                    <td><span id="line-<?php echo $l['id_cartline'] ?>-stock"><?php echo $stock ?></span></td>
	                    <td><span id="line-<?php echo $l['id_cartline'] ?>-contentPriceQuantity"><?php echo $l['contentPriceQuantity'] ?></span></td>
	                    <td><span id="line-<?php echo $l['id_cartline'] ?>-contentPriceTaxQuantity"><?php echo $l['contentPriceTaxQuantity'] ?></span></td>
	                    <td>
	                        <input name="line-<?php echo $l['id_cartline'] ?>-remove" id="remove-<?php echo $l['id_cartline'] ?>" type="hidden">
	                        <img src="ressource/img/ico-delete-th.png" height="20" width="20" border="0" onclick='del(<?php echo $l['id_cartline'] ?>);'>
	                    </td>
					</tr>
					<?php } ?>
					<tr>
	                    <td colspan="12">
	                        <div style="float:left; margin:10px;">
	                            <a class="button button-blue" onclick="productPicker(0)">Sélectionner un produit</a>
	                        </div>
	                        <div style="float:left; margin:10px;">
	                            <a class="button button-blue" onclick="addCartLine();">Ajouter un produit manuellement</a>
	                        </div>
	                    </td>
						<!--<td><span id="cartTotal"></span><?php //echo $myCmd['cartTotal'] ?></td>
						<td><span id="cartTotalTax"></span><?php //echo $myCmd['cartTotalTax'] ?></td>
	                    <td colspan="2"></td>-->
					</tr>
				</table>
				
				<p>&nbsp;</p>
				
				<table border="1" width="250" align="right">
				    <?php foreach($myCmd['cartTax'] as $t=>$v){if($v['total'] > 0){ ?>
	                <tr class="totaltva">
	                    <td>Total HT <?php echo $t ?> %</td>
	                    <td width="50%" align="right"><?php echo $v['total'] ?></td>
	                </tr>			        
			        <?php }} ?>
					<tr id="trTotalHT">
						<td><b>Total Général HT</b></td>
						<td width="50%" align="right"><b><span id="cartTotal"><?php echo $myCmd['cartTotal'] ?></span></b></td>
					</tr>
	                <?php foreach($myCmd['cartTax'] as $t=>$v){if($v['base'] > 0){  ?>
	                <tr class="basetva">
	                    <td>TVA à <?php echo $t ?> %</td>
	                    <td width="50%" align="right"><?php echo $v['base'] ?></td>
	                </tr>                   
	                <?php }} ?>
					<tr id="trTotalTVA">
						<td><b>Total TVA</td>
						<td align="right"><b><span id="totalTVA"><?php echo number_format(($myCmd['cartTotalTax'] - $myCmd['cartTotal']), 2, '.', ' ') ?></span></b></td>
					</tr>
					<tr>
						<td><b>Total TTC</b></td>
						<td align="right"><b><span id="cartTotalTax"><?php echo $myCmd['cartTotalTax'] ?></span></b></td>
					</tr>
	                <tr >
	                    <td>
	                        Frais de port<br />
	                        <?php echo $app->apiLoad('business')->businessAccountSelector(array('one' => true, 'empty' => true, 'events' => 'onchange="save();"', 'name' => 'cartCarriageAccountNumber','value' => $myCmd['cartCarriageAccountNumber'])); ?>
	                    </td>
	                    <td align="right"><input name="cartCarriage" value="<?php echo $myCmd['cartCarriage'] ?>" size="4" onchange="save()"></td>
	                </tr>
	                <tr >
	                    <td>TVA <?php echo $app->apiLoad('business')->businessTaxSelector(array('one' => true, 'empty' => true, 'events' => 'onchange="save();"', 'name' => 'cartCarriageTax','value' => $myCmd['cartCarriageTax'])); ?></td>
	                    <td align="right"><span id="cartCarriageTVA"><?php echo number_format($myCmd['cartCarriageTotalTax']-$myCmd['cartCarriage'], 2, '.', ' '); ?></span></td>
	                </tr>
					<tr>
						<td><b>Total commande</b></td>
						<td align="right"><b><span id="cartTotalFinal"><?php echo $myCmd['cartTotalFinal'] ?></span></b></td>
					</tr>
				</table>
			  <br clear="both"
	            <p>&nbsp;</p>
	            <p>&nbsp;</p>
			
				<!--<table border="1" width="250">
				<?php //foreach($flag as $e){ ?>
					<tr>
						<td width="50%"><?php echo $e['cartFlagName'] ?></td>
						<td><?php echo $e['cartFlagValue'] ?></td>
					</tr>
				<?php //} ?>
				</table>-->
			</div>
		</form>
		
	<?php include(COREINC.'/end.php'); ?>
	<script>
	    // Suppression de ligne
	    function del(id_line){
	        if(confirm('Etes vous sur de vouloir supprimer la ligne ?')){
	            $("#remove-" + id_line).val("1");
	            $("#form-cart").submit();
	        }
	        return false;
	    }
	    
	    // Ajout de ligne manuelle
	    function addCartLine(){
	        $("#newline").val("1");
	        $("#form-cart").submit();
	    }
	    
	    // Suppression de la commande !
	    function cartremove(){
	        if(confirm('Etes vous sur de vouloir supprimer la commande ?')){
	            $("#cartremove").val("1");
	            $("#form-cart").submit();
	        }
	        return false;
	    }
	    
	    // Finalisation de la commande !
	    function finalise(){
	        if(confirm('Etes vous sur de vouloir finaliser la commande ?\nLa commande ne pourra plus être modifiée')){
	            $("#cartfinalise").val("1");
	            $("#form-cart").submit();
	        }
	        return false;
	    }
	    
	    // On sauve et on met à jour les champs du formulaire
	    function save(){
	        //$("#form-cart").submit();
	       
	        
	        var postData = new Object(); 
	        //var postData = new Array(); 
	        $("input").each(
	              function() {
	                postData[this.name] = $(this).val();
	                //alert(eval(this.name));
	              });
	        $("select").each(
	              function() {
	                postData[this.name] = $(this).val();
	              });
	
	        //alert(postData);
	        var postDataJson = JSON.stringify(postData);
	        //alert(postDataJson);
	        $.post("lib/data.ajax",{data:postDataJson},function(data){
	            $("#cartTotalFinal").html(data.cartTotalFinal);
	            $("input").each(
	                  function() {
	                    if($(this).val() != data[this.name])$(this).val(data[this.name]);
	                  });
	            $("select").each(
	                  function() {
	                  	if($(this).val() != data[this.name])$(this).val(data[this.name]);
	                  });
	            $("span").each(
	                  function() {
	                    if($(this).html() != data[this.id])$(this).html(data[this.id]);
	                  });
	                  
	            $('.basetva').remove();
	            $('.totaltva').remove();
	            for(taux in data.cartTax){
	                total = data.cartTax[taux].total;
	                base = data.cartTax[taux].base;
	                $("#trTotalHT").before("<tr class=\"totaltva\"><td>Total HT " + taux + " %</td><td width=\"50%\" align=\"right\">" + total + "</td></tr>");
	                $("#trTotalTVA").before("<tr class=\"basetva\"><td>TVA à " + taux + " %</td><td width=\"50%\" align=\"right\">" + base + "</td></tr>");
	            }
	            if(data.msg)alert(data.msg);   
	            
	        }, "json");
	        
	    }
	
	</script>
	<?php //$app->pre($_POST); ?>
	<?php //$app->pre($myCmd); ?>
	</div>
</div>
</div>
</body></html>