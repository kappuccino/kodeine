<html>
<head>
    <meta charset="utf-8" />
    <title>jQuery UI Resizable - Default functionality</title>
    <style>
        #resizable { width: 150px; height: 150px; padding: 0.5em; }
        #resizable h3 { text-align: center; margin: 0; }
    </style>
</head>
<body>

<div id="resizable" class="ui-widget-content">
    <h3 class="ui-widget-header">Resizable</h3>
</div>



<script type="text/javascript" src="http://code.jquery.com/jquery-1.9.0.js"></script>
<script type="text/javascript" src="http://code.jquery.com/ui/1.10.0/jquery-ui.js"></script>

<script>
    $(function() {
        $( "#resizable" ).resizable();
    });
</script>

</body>
</html>