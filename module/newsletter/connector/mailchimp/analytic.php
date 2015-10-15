<?php
$api  = $app->apiLoad('newsletter');
$pref = $app->configGet('newsletter');

$apiConnector	= $app->apiLoad('newsletterMailChimp');
# Remettre a ZERO le pool + click + stats
#
if($_POST['reset'] == 'do'){
    $rest = new newsletterREST($pref['auth'], $pref['passw']);
    $act = $rest->request('/controller.php', 'POST', array(
        'reset'			=> true,
        'id_newsletter' => $_POST['id_newsletter']
    ));

    $app->dbQuery("UPDATE k_newsletter SET newsletterSendDate=NULL WHERE id_newsletter=".$_POST['id_newsletter']);

    header("Location: data-options?id_newsletter=".$_POST['id_newsletter']);
}

$data = $api->newsletterGet(array(
    'id_newsletter' => $_REQUEST['id_newsletter']
));

$rest = new newsletterREST($pref['auth'], $pref['passw']);
$stat = $rest->request('/controller.php', 'POST', array(
    'analytic'		=> true,
    'id_newsletter' => $data['id_newsletter']
));
$stat = json_decode($stat, true);

#	$app->pre($stat);


# Merge & Percent
#
$total 			= $stat['campaign']['campaingSent'];
$notseen		= $total - $stat['campaign']['campaingOpened'] - $stat['campaign']['campaingBounced'];
$recipient		= $total - $stat['campaign']['campaingBounced'];

# Le pourcentage est par rapport au nombre total de mail envoye
#
$openRate		= number_format((@($stat['campaign']['campaingOpened']  / $total) * 100),	2, '.', '');
$clickRate		= number_format((@($stat['campaign']['campaingClicked'] / $total) * 100),	2, '.', '');
$bounceRate		= number_format((@($stat['campaign']['campaingBounced'] / $total) * 100),	2, '.', '');
$notseenRate	= number_format((@($notseen / $total) * 100), 								2, '.', '');

# Recip = le pourcentage est par rapport au nombre de personne qui ont normallement recu le mail (tous - bounce)
#
$recipOpenRate	= number_format((@($stat['campaign']['campaingOpened']  	 / $recipient) * 100),	2, '.', '');
$recipClickRate	= number_format((@($stat['campaign']['campaingClicked'] 	 / $recipient) * 100),	2, '.', '');
$recipUnsubRate	= number_format((@($stat['campaign']['campaingUnsubscribed'] / $recipient) * 100),	2, '.', '');
?><!DOCTYPE html>


<head>
    <?php include(COREINC.'/head.php'); ?>
    <link rel="stylesheet" type="text/css" media="all" href="ui/css/analytic.css" />
</head>

<body>

<header><?php
    include(COREINC.'/top.php');
    include(dirname(dirname(__DIR__)).'/ui/menu.php');
    $step = 'stats';
    include(dirname(dirname(__DIR__)).'/ui/steps.php');
    ?></header>

<div class="inject-subnav-right hide">
    <?php if($_REQUEST['id_newsletter'] > 0){ ?>
    <li><a href="preview?id_newsletter=<?php echo $_REQUEST['id_newsletter'] ?>" class="btn btn-small" target="_blank">Prévisualiser</a></li>
    <?php } ?>
</div>

<div id="app"><div class="wrapper">

    <div>

        <?php if(is_array($stat['campaign']) && $data['newsletterSendDate'] != NULL){ ?>

        <h3 class="campaignName"><?php echo $stat['campaign']['campaignName'] ?></h3>

        <p class="campaignNameCaption">
            Envoy&eacute; a <?php echo $total ?> destinataires le <?php echo $app->helperDate($data['newsletterSendDate'], '%e %B %Y &agrave; %Hh%M') ?>
        </p>

        <?php if($stat['campaign']['campaingPool'] > 0){ ?>
            <div class="alert">
                Il reste <?php echo $stat['campaign']['campaingPool'] ?> mail dans la liste en attente d'expedition
                (<?php echo $stat['campaign']['campaingSent'] ?> d&eacute;j&agrave; envoy&eacute;s) les statistiques sont
                par cons&eacute;quent incompl&eacute;tes
            </div>
            <?php } ?>

        <div class="legend clearfix">
            <div class="open"	><input type="checkbox" class="capt" id="cb_view"  value="view"  checked="checked" onclick="doPlot('right')" /><label for="cb_view">Ouverture</label></div>
            <div class="click"	><label for="cb_click">Click</label><input type="checkbox" class="capt" id="cb_click" value="click" checked="checked" onclick="doPlot('right')" /></div>
        </div>

        <div id="placeholder" style="width:900px; height:200px;"></div>

        <table width="840" border="0" cellpadding="0" cellspacing="0" style="margin:0 auto; margin-top:40px;">
            <tr valign="top">
                <td>
                    <div id="donut" style="width:180px; height:180px;">@</div>
                </td>
                <td width="30%" style="padding-top:20px;">
                    <div class="clearfix">
                        <img src="ui/img/analytic-vert.gif" alt="analytic-vert" width="10" height="10" style="float:left; margin:3px 10px 0px 0px" />
                        <div style="float:left">
                            <span class="big bold"><?php echo number_format($stat['campaign']['campaingOpened'], 0, '', ' ') ?></span>
                            <span class="medium"><a href="analytic-data?id_newsletter=<?php echo $data['id_newsletter'] ?>&campaingOpened">ouvertures uniques</a></span><br />
                            <div class="small-caption"><?php echo number_format($stat['campaign']['campaingTotalOpened'], 0, '', ' ') ?> ouvertures total</div>
                        </div>
                    </div>
                    <div class="clearfix" style="padding:20px 0px 20px 0px;">
                        <img src="ui/img/analytic-orange.gif" alt="analytic-vert" width="10" height="10" style="float:left; margin:3px 10px 0px 0px" />
                        <div style="float:left">
                            <span class="big bold"><?php echo number_format($stat['campaign']['campaingBounced'], 0, '', ' ')  ?></span>
                            <span class="medium"><a href="analytic-data?id_newsletter=<?php echo $data['id_newsletter'] ?>&campaingBounced">retour expediteur</a></span><br />
                            <div class="small-caption"><?php echo $bounceRate ?>% mail non d&eacute;livr&eacute;</div>
                        </div>
                    </div>
                    <div class="clearfix">
                        <img src="ui/img/analytic-blue.gif" alt="analytic-vert" width="10" height="10" style="float:left; margin:3px 10px 0px 0px" />
                        <div style="float:left">
                            <span class="big bold"><?php echo number_format($notseen, 0, '', ' ') ?></span>
                            <span class="medium"><a href="analytic-data?id_newsletter=<?php echo $data['id_newsletter'] ?>&notSeen">non ouvert</a></span><br />
                            <div class="small-caption">Ceci est une estimation *</div>
                        </div>
                    </div>
                </td>
                <td width="40%" style="padding-top:20px;">
                    <div style="padding-bottom:20px;">
                        <span class="big grey"><?php echo $recipOpenRate ?>%</span>
                        <span class="medium grey">des destinataires ont ouvert le mail</span>
                    </div>
                    <div style="padding-bottom:20px;">
                        <span class="big grey"><?php echo $recipClickRate ?>%</span>
                        <span class="medium grey"><a href="analytic-data?id_newsletter=<?php echo $data['id_newsletter'] ?>&clicked">ont click&eacute; sur un lien</a></span>
                    </div>
                    <div style="padding-bottom:20px;">
                        <span class="big grey"><?php echo $recipUnsubRate ?>%</span>
                        <span class="medium grey"><a href="analytic-data?id_newsletter=<?php echo $data['id_newsletter'] ?>&unsubscribed">se sont d&eacute;sinscrits</a></span>
                    </div>
                    <div style="padding-bottom:20px;">
                        <span class="big grey"><?php echo number_format($total, 0, '', ' ') ?></span>
                        <span class="medium grey">destinataires pour ce mailing</span>
                    </div>
                </td>
            </tr>
        </table>


        <?php }elseif($data['newsletterSendDate'] != NULL) { ?>

        <div style="font-weight:bold; font-size:14px; text-align:center; padding-top:50px; color:#808080;">
            Cette newsletter a bien été envoyée sur MailChimp
        </div>
        <?php
            $stats = $apiConnector->campaignStats(array('cid' => $data['newsletterConnectorId']));
            //$app->pre($stats);

        ?>
        <ul>
            <li>Nombre de clics : <?php echo $stats['unique_clicks']; ?></li>
            <li>Nombre d'ouvertures : <?php echo $stats['unique_opens']; ?> / <?php echo $stats['emails_sent']; ?></li>
        </ul>


        <?php } ?>

        <div style="margin-top:40px; padding:10px 0px 3px 0px; border-top:1px solid #808080;">
            <form method="post" action="analytic">
                <input type="hidden"	name="id_newsletter"	value="<?php echo $data['id_newsletter'] ?>" />
                <input type="checkbox"	name="reset"			value="do" />

                Réinitialiser et consid&eacute;rer cette newsletter comme non envoy&eacute;e<br />
                <input type="submit" class="btn btn-mini" value="Confirmer" />

                <br /><br />
                * Certains clients de messagerie n'affichant pas automatiquement les images, ce nombre peut &ecirc;tre en dessous de la r&eacute;alit&eacute;.
            </form>
        </div>

    </div>
</div>

<?php include(COREINC.'/end.php'); ?>
<script language="javascript" type="text/javascript" src="ui/_flot/jquery.flot.js"></script>
<script language="javascript" type="text/javascript" src="ui/_flot/jquery.flot.pie.min.js"></script>

<?php if(is_array($stat['campaign']) && $data['newsletterSendDate'] != NULL){

    #$app->pre($stat);

    $max = 0;

    if(sizeof($stat['analytic']['click']) > 0){
        foreach($stat['analytic']['click'] as $date => $e){
            $ts			= $app->helperDate($date, TIMESTAMP) * 1000;
            $click[]	= '['.$ts.','.$e.']';

            if($e > $max) $max = $e;
        }
    }else{
        $click = array();
    }

    if(sizeof($stat['analytic']['open']) > 0){
        foreach($stat['analytic']['open'] as $date => $e){
            $ts			= $app->helperDate($date, TIMESTAMP) * 1000;
            $open[]		= '['.$ts.','.$e.']';

            if($e > $max) $max = $e;
        }
    }else{
        $open = array();
    }

    ?>


<script type="text/javascript">

    datasets = {
        'view' : {
            label: 'View',
            color:	'rgb(80, 180, 50)', // vert
            data: 	[<?php echo implode(', ', $open) ?>],
            yaxis: 2
        },
        'click' : {
            label: 'Click',
            color:	'rgb(5, 141, 199)', // bleu
            data: 	[<?php echo implode(', ', $click) ?>],
            yaxis: 1
        }
    };

    function doPlot(position) {
        data = [];

        $('.capt').each(function(i, m){
            var me = (m.checked) ? datasets[m.value] : [];

            data.push(me);
        });

        $.plot($("#placeholder"), data, {
            series: {
                lines:  { show: true, lineWidth: 4 },
                points: { show: true, lineWidth: 1, radius: 4, fill:true },
                shadowSize: 0
            },
            xaxes: [
                {
                    mode: "time",
                    timeformat: "%d/%m %H:%M",
                    ticks:4
                }
            ],
            yaxes: [
                {
                    max: <?php echo ($max + 1) ?>,
                    min: 0,
                    alignTicksWithAxis: 1,
                    position: position,
                    tickDecimals:0
                },{
                    min: 0,
                    max: <?php echo ($max + 1) ?>,
                    tickDecimals:0
                }
            ],
            grid: {
                hoverable: true,
                clickable: false,
                borderWidth: 0,
                labelMargin:20
            },
            legend: {
                show: false,
                position: 'sw'
            }

        });
    }

    doPlot('right');

    function showTooltip(x, y, contents) {
        $("#tooltip").remove();
        $('<div id="tooltip">' + contents + '</div>').css({ top:(y+5), left:(x+5), opacity: 0.80}).appendTo("body").fadeIn(200);
    }

    var previousPoint = null;
    $("#placeholder").bind("plothover", function (event, pos, item) {

        if (item) {
            if (previousPoint != item.dataIndex) {
                previousPoint = item.dataIndex;

                var x = parseInt(item.datapoint[0].toFixed(0));
                var y = parseInt(item.datapoint[1].toFixed(0));

                var d = new Date(x);
                var v = d.getDate()+'/'+d.getMonth()+'/'+d.getFullYear()+ ' '+d.getHours()+'h'+d.getMinutes();

                var type = (item.seriesIndex == 1) ? 'Click: ' : 'Ouverture: ';
                var clas = (item.seriesIndex == 1) ? 'click'   : 'open';

                showTooltip(item.pageX, item.pageY, "<div class=\"d\">"+v + "</div><div class=\"t "+clas+"\"><span>" + type +'</span>'+ y +"</div>");
            }
        }else {
            $("#tooltip").remove();
            previousPoint = null;
        }
    });

    /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */

    $.plot($("#donut"), [
        {color: 'rgb(80, 180, 50)', data: <?php echo $openRate ?>}, 	// vert
        {color: 'rgb(5, 141, 199)', data: <?php echo $notseenRate ?>}, 	// bleu
        {color: 'rgb(249, 89,  7)', data: <?php echo $bounceRate ?>}	// orange
    ],{
        series: {
            pie: {
                innerRadius: 0.5,
                show: true
            }
        }
    });


    /*$(function () {

    });*/

</script>
    <?php } ?>

</body></html>