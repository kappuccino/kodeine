<?php
$api	= $app->apiLoad('newsletter');
$pref	= $app->configGet('newsletter');

$apiConnector	= $app->apiLoad('newsletterMailChimp');

if($_REQUEST['id_newsletter'] != NULL){
    $data = $app->apiLoad('newsletter')->newsletterGet(array(
        'id_newsletter' 	=> $_REQUEST['id_newsletter']
    ));

    $title = $data['newsletterName'];
}else{
    $title = 'Nouvelle newsletter';
}

$mails	= $app->apiLoad('newsletter')->newsletterPoolPopulation($data['id_newsletter']);

?><!DOCTYPE html>
<head>
    <?php include(COREINC.'/head.php'); ?>

    <link rel="stylesheet" type="text/css" media="all" href="ui/css/newsletter.css" />

</head>

<body>

<header><?php
    include(COREINC.'/top.php');
    include(dirname(dirname(__DIR__)).'/ui/menu.php');
    include(dirname(dirname(__DIR__)).'/ui/steps.php');
    ?></header>

<div class="wrapper clearfix">

    <?php
    if($message == NULL && $_GET['message'] != NULL) $message = urldecode($_GET['message']);
    if($message != NULL){
        list($class, $message) = $app->helperMessage($message);
        echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
    }
    ?>

    <form action="data-list?id_newsletter=<?php echo $data['id_newsletter'] ?>" method="post" id="data" enctype="multipart/form-data">

        <input type="hidden" name="action" value="1" />
        <input type="hidden" name="id_newsletter" value="<?php echo $data['id_newsletter'] ?>" />
        <input type="hidden" name="do" id="do" value="" />


        <?php if($data['newsletterSendDate'] != NULL){ ?>

        <div class="message"><?php echo _('Campaign has been already sent on MailChimp'); ?></div>

        <?php } else { ?>

        <div class="clearfix"></div>
        <?php
        unset($api);
        //$app->pre($apis);
        $lists	= $apiConnector->listGet();
        //$app->pre($lists);

        ?>
        <br />
        Sélectionner une liste<br /><select name="id_newsletterListMailchimp" id="id_newsletterListMailchimp" style="width:250px;">
            <option value=""></option>
            <?php
            foreach($lists['data'] as $list) {
                ?>
                <option value="<?php echo $list['id']; ?>">
                    <?php echo $list['name'].' ('.$list['stats']['member_count'].')'; ?>
                </option>
                <?php
            }
            ?>



        </select>

            <div class="clearfix"></div>
        <?php if(is_array($mails) && sizeof($mails) > 0) { ?>
        <br />Nouveau segment (<?php echo sizeof($mails); ?> abonnés) : <br />
        <input type="text" id="segment" name="segment" size="30" value="<?php echo ($data['newsletterSendDate'] != '') ? $data['newsletterSendDate'] : date('Y-m-d').' : Newsletter '.$data['id_newsletter']; ?>">

        <?php } ?>
        <div class="clearfix"></div>

        <div id="groups"></div>


        <div id="bar" style="width:400px; background: #CCCCCC; 						-moz-border-radius:10px; -webkit-border-radius:10px;">
            <div id="progress" style="width:20px; height:20px; background:#058dc7;  -moz-border-radius:10px; -webkit-border-radius:10px;"></div>
        </div>

        <p><a href="#" id="send" onclick="return false;" class="btn btn-primary"><?php echo _('Send this campaign on MailChimp'); ?></a></p>


        <?php } ?>


</div>

<?php include(COREINC.'/end.php'); ?>

<script>
    $(document).ready(function() {

        $('#id_newsletterListMailchimp').change(function() {
            $.ajax({
                'url' : 'connector/mailchimp/get-groups',
                'data' : {'id' : $(this).val() }
            }).done(function(d) {
                        $('#groups').html(d);
                    });

        });

        $('#send').click( function() {
            if(confirm("Etes vous sur de vouloir envoyer cette newsletter sur MailChimp ?")) {
                var offset = 0;
                send(offset);

            }else {
                return false;
            }
        });
    });

    function send(offset) {
        console.log(offset);
        $.ajax({
            'url' : 'connector/mailchimp/push-ajax',
            'dataType' : 'json',
            'data' : {
                'id_newsletter' : <?php echo $data['id_newsletter'] ?>,
                'id_newsletterList' : $('#id_newsletterListMailchimp').val(),
                'listInterestGroupings' : $('#listInterestGroupings').val(),
                'segment' : $('#segment').val(),
                'offset' : offset}
        }).done(function(d) {
                    console.log(d);
            if(d.done == '1') {
                alert('finish !');
            }else {
                if(d.done == '') {
                    var done = true;
                }else {

                    $('#progress').css('width', (d.pourcent * 4)+'px');
                    send(offset + 1);
                }
            }
        });
    }
</script>
</body></html>