<!DOCTYPE html>
<head>
	<?php include(COREINC.'/head.php'); ?>
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/bootstrap3/css/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/flatui/css/flat-ui.css" />
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/dsnr-ui.css" />
</head>

<body>
<?php
	# Kodeine gets
	#
	$type = $app->apiLoad('type')->typeGet();
	$categories = $app->apiLoad('category')->categoryGet(array(
		'language' => 'fr',
		'thread' => true
	));

	function printCat($cat, $iterations=0) {
		foreach ($cat as $c) {
			$space = '';
			for($i = 0; $i < $iterations; $i++) {
				$space .= '&nbsp;&nbsp;&nbsp;&nbsp;';
			}
			echo '<option value="'. $c['id_category'] .'">'. $space.' '.$c['categoryName'] .'</option>';
			if (!empty($c['sub'])) {
				printCat($c['sub'], $iterations + 1);
			}
		}
	}

?>
<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php');
	include(__DIR__.'/ui/steps.php');
	?></header>

<div class="inject-subnav-right hide"></div>

<div id="app">

	<div class="alert alert-info alert-dismissable" style="margin: 20px 100px;">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		Afin d'agencer les éléments plus précisément, votre bloc est affiché en surimpression sur l'arrière plan de la newsletter. En cas de sauvegarde des modifications, seul le bloc sera enregistré.
		<br/><br/><a href="#" id="changelayout">Changer l'arrière plan de ma newsletter (les modifications apportées seront perdues)</a>
		<?php
			$layouts = $app->dbMulti('SELECT * FROM `@nlwrap`');
			#$app->pre($layouts);
			echo '<ul id="layoutlist" style="display: none">';
			foreach($layouts as $layout) {
				if (is_numeric($layout['id_wrap'])) {
					echo '<li><a href="dsnr?bloc='.$_GET['bloc'].'&layout='.$layout['id_wrap'].'">'.$layout['name'].'</a></li>';
				}
			}
			echo '</ul>'
		?>

	</div>

	<div class="col-lg-4">

		<h4 class="templatename"></h4>

		<div id="dsnr">
			<div id="panel-img"></div>
			<div id="panel-text"></div>
			<div id="panel-connector"></div>
		</div>
	</div>

	<div class="col-lg-8">
		<div id="dsnrframewrap">
			<iframe id="dsnrframe" style="height:100%; width: 100%" frameborder="0" scrolling="auto"></iframe>
		</div>
	</div>

</div>

<!-- - TEMPLATE PanelImg - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -->
<script type="text/template" id="tpl_ui_img">

	<h5>{{ nodename }}</h5>

	<div class="input-group">
		<span class="input-group-addon">URL</span>
		<input class="url form-control" type="text" class="form-control url" data-nodeattr="src" placeholder="Url de votre image">
	</div>

	<a href="#" class="btn btn-lg btn-inverse imgselect"><span class="fui-new"></span>Gestion des medias</a>

	<ul class="controls">
		<li><div class="lab">Largeur (px)</div>
			<div class="form-group has-success">
			<input type="text" value="{{ widthAttr }}" placeholder="Largeur" data-nodeattr="width" class="form-control">
		</div></li>
		<li><div class="lab">Hauteur (px)</div>
			<div class="form-group has-success">
			<input type="text" value="{{ heightAttr }}" placeholder="Hauteur" data-nodeattr="height" class="form-control">
		</div></li>
		<!--<li><a href="#" class="btn btn-lg btn-primary"><span class="fui-check"></span>Appliquer</a></li>-->
	</ul>

	<ul class="right">
		<li><a href="#" class="btn btn-lg btn-primary"><span class="fui-check"></span>Appliquer</a></li>
		<li><a href="#" class="btn btn-lg btn-danger"><span class="fui-cross"></span>Annuler</a></li>
	</ul>

</script>

<!-- - TEMPLATE PanelLink - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - --->
<script type="text/template" id="tpl_ui_link">

	<h5>{{ nodename }}</h5>

	<select name="selectfamily" value="family" class="select-block">
		<option value="0">Provenance des informations :</option>
		<option value="2">Contenu</option>
		<option value="3">User</option>
	</select>

	<div id="contentpanel">
	</div>

	<div id="userpanel">
	</div>

	<div id="categorypanel">
	</div>


	<!--<a href="#" class="btn btn-lg btn-primary"><span class="fui-check"></span>Appliquer</a>
	<a href="#" class="btn btn-lg btn-danger"><span class="fui-cross"></span>Annuler</a>-->

</script>

<!-- - TEMPLATE panel CONNECTOR - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -->
<script type="text/template" id="tpl_panel_connector">

	<select name="selectconnector" value="family" class="select-block">
		<option value="1" data-url="ajax-dsnr-userconnector">Membre</option>
		<option value="2" data-url="ajax-dsnr-groupconnector">Groupe</option>
		<option value="3" data-url="ajax-dsnr-eventconnector">Event</option>
		<option value="4" data-url="ajax-dsnr-marqueconnector">Marque</option>
		<option value="5" data-url="ajax-dsnr-modeleconnector">Modèle</option>
	</select>

	<br/>

	<div class="input-group" style="clear:both">
		<span class="input-group-addon">Identifiant kodeine</span>
		<input class="url form-control" type="text" id="connectorid" class="form-control url" data-nodeattr="src" placeholder="42">
	</div>

	<br style="clear:both"/>

	<div class="clearfix">
		<a href="#" class="btn btn-lg btn-primary apply" style="color:white;"><span class="fui-check"></span>Appliquer</a>
		<a href="#" class="btn btn-lg btn-danger" style="color:white;"><span class="fui-cross"></span>Annuler</a>
	</div>

</script>

<!-- - TEMPLATE PanelText - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  -->
<script type="text/template" id="tpl_ui_text">

	<h5>{{ nodename }}</h5>

	{{#if textarea}}
		<form><textarea id="dsnr-textarea">{{contents}}</textarea></form>
	{{else}}
		<input type="text" id="dsnr-textinput" class="form-control" value="{{contents}}"/>
	{{/if}}

	<a href="#" class="btn btn-lg btn-primary"><span class="fui-check"></span>Appliquer</a>
	<a href="#" class="btn btn-lg btn-danger"><span class="fui-cross"></span>Annuler</a>

</script>

<!-- - TEMPLATE ToolbarView - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  -->
<script type="text/template" id="tpl_toolbar">

	<div class="dsnr-btn-group">
		<a class="btn btn-primary" data-toggle="edit"><i class="fui-new"></i></a>
	</div>

</script>

<?php include(COREINC.'/end.php'); ?>

<script type="text/javascript">var TEMPLATE_ID = <?php echo intval($_GET['bloc']); ?>;</script>
<script type="text/javascript">var LAYOUT_ID = <?php echo intval($_GET['layout']); ?>;</script>
<script type="text/javascript">var SERVER = "<?php echo $_SERVER['SERVER_NAME']; ?>";</script>
<script>
	function mediaInsert(id, data, method){
		console.log("Custom INSERT");
		console.log(id, data, method);

		var split = data.split('@@');
		if (split.length && split[0] == 'image') {
			Dsnr.PanelView.importMedia(split[1]);
		} else {
			alert('Ce fichier ne semble pas être une image :(');
		}

	}

	$(function() {
		$('#changelayout').on('click', function() {
			console.log('layout change ?')
			$('#layoutlist').css('display', 'block');
		});
	})
</script>


<script type="text/javascript" src="ui/vendors/underscore-min.js"></script>
<script type="text/javascript" src="ui/vendors/backbone.min.js"></script>
<script type="text/javascript" src="ui/vendors/underscore-min.js"></script>
<script type="text/javascript" src="ui/vendors/handlebars.js"></script>
<script type="text/javascript" src="ui/vendors/richtext/ckeditor.js"></script>
<script type="text/javascript" src="ui/vendors/richtext/adapters/jquery.js"></script>
<!--<script type="text/javascript" src="ui/vendors/wisihtml-rules.js"></script>-->
<!--<script type="text/javascript" src="ui/vendors/wisihtml.js"></script>-->

<script type="text/javascript" src="ui/js/dsnr/dsnr.js"></script>
<script type="text/javascript" src="ui/js/dsnr/dsnr-constructs.js"></script>
<script type="text/javascript" src="ui/js/dsnr/dsnr-inject-models.js"></script>
<script type="text/javascript" src="ui/js/dsnr/dsnr-inject-views.js"></script>
<script type="text/javascript" src="ui/js/dsnr/dsnr-panel-views.js"></script>

<!-- KODEINE CONNECTORS -->
<script type="text/javascript" src="ui/js/dsnr/connectors/dsnr-panel-user.js"></script>

<script type="text/javascript" src="ui/css/flatui/js/bootstrap-select.js"></script>
<script type="text/javascript" src="ui/css/flatui/js/bootstrap.min.js"></script>

</body></html>