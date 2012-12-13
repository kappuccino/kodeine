<?php

	$category = $app->apiLoad('category')->categoryGet(array(
		'mid_category'	=> $_GET['mid_category'],
		'language'		=> 'fr'
	));

	foreach($category as $e){
		$color = ($_REQUEST['id_category'] == $e['id_category']) ? '515151' : 'e1e1e1';

		echo "<li id=\"".$e['id_category']."\">";
			echo "<div class=\"holder clearfix\">";



				echo "<div class=\"toggle\" onclick=\"threadMe(this, ".$_GET['level'].", ".$e['id_category'].")\" style=\"visibility:".(($e['categoryHasChildren'] == '1') ? 'visible' : 'hidden')."\"></div>";

				echo "<div class=\"check\"><input type=\"checkbox\" name=\"del[]\" value=\"".$e['id_category']."\" /></div>";

				echo "<div class=\"handle\"></div>";

				echo "<div class=\"data\" style=\"margin-left:".($_GET['level'] * 20)."px;\"><a href=\"javascript:void();\" onclick=\"edit(".$e['id_category'].")\">".$e['categoryName']."</a></div>";




			echo "</div>";

			echo "<ul id=\"mid-".$e['id_category']."\"></ul>";

		echo "</li>";
	}


?>