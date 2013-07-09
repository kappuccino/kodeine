<?php

	$forums = $app->apiLoad('socialForum')->socialForumGet(array(
		'debug'				=> false,
		'mid_socialforum'	=> $_GET['mid_socialforum']
	));

	foreach($forums as $e){
		$color = ($_REQUEST['id_socialforum'] == $e['id_socialforum']) ? '515151' : 'e1e1e1';

		echo "<li id=\"".$e['id_socialforum']."\" class=\"".$class."\">";
			echo "<div style=\"margin:1px 0px 0px 0px; clear:both;\" class=\"holder\">";
				echo "<div class=\"toggle\" onclick=\"threadMe(this, ".$_GET['level'].", ".$e['id_socialforum'].")\" style=\"visibility:".((sizeof($e['socialForumFlat']) > 0) ? 'visible' : 'hidden')."\"></div>";

				echo "<div class=\"check\"><input type=\"checkbox\" name=\"del[]\" value=\"".$e['id_socialforum']."\" /></div>";
				echo "<div class=\"handle\"></div>";
				echo "<div class=\"data\" style=\"margin-left:".($_GET['level'] * 20)."px;\"><a href=\"javascript:edit(".$e['id_socialforum'].")\" class=\"sniff\">".$e['socialForumName']."</a></div>";
				echo "<br style=\"clear:both;\" />";

			echo "</div>";

			echo "<ul id=\"mid-".$e['id_socialforum']."\"></ul>";

		echo "</li>";
	}


?>