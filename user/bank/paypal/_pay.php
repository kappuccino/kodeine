<?php
	$apiSuccess 	= true;
	$apiOutput 		= 

	"<form action=\"https://www.paypal.com/fr/cgi-bin/webscr\" method=\"post\" target=\"_blank\">
		<input type=\"hidden\" name=\"cmd\" 			value=\"_xclick\" />
		<input type=\"hidden\" name=\"business\" 		value=\"________EMAIL_______\" />
		<input type=\"hidden\" name=\"item_name\" 		value=\"Commande Kappuccino #".$myCart['id_cart']."\" />

		<input type=\"hidden\" name=\"currency_code\" 	value=\"EUR\" />
		<input type=\"hidden\" name=\"amount\" 			value=\"".$myCart['cartTotalFinal']."\" />
		<input type=\"hidden\" name=\"no_shipping\" 	value=\"1\" />
		<input type=\"hidden\" name=\"no_note\" 		value=\"1\" />
	
		<input type=\"image\" src=\"http://www.paypal.com/fr_FR/i/btn/x-click-but01.gif\" name=\"submit\" alt=\"Effectuez vos paiements via PayPal : une solution rapide, gratuite et securisee\" />
	</form><br /><br />";

?>