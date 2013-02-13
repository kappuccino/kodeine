<?php if($_REQUEST['id_newsletter'] != NULL) { ?>

<div id="sub_steps"><div class="wrapper clearfix">

    <a href="/admin/newsletter/data-options?id_newsletter=<?php echo $_REQUEST['id_newsletter']; ?>" class="btn <?php echo isMe('/newsletter/data-options') ? 'active':'' ?>">
        Etape 1 : Infos générales
    </a>

    <?php if($data['is_designer'] != 1) { ?>

    <a href="/admin/newsletter/data-editor?id_newsletter=<?php echo $_REQUEST['id_newsletter']; ?>" class="btn <?php echo isMe('/newsletter/data-editor') ? 'active':'' ?>">
        Etape 2 : Editeur
    </a>

    <?php } else { ?>

    <a href="/admin/newsletter/data-designer?id_newsletter=<?php echo $_REQUEST['id_newsletter']; ?>" class="btn <?php echo isMe('/newsletter/data-designer') ? 'active':'' ?>">
        Etape 2 : Designer
    </a>

    <?php } ?>

    <a href="/admin/newsletter/data-list?id_newsletter=<?php echo $_REQUEST['id_newsletter']; ?>" class="btn <?php echo isMe('/newsletter/data-list') ? 'active':'' ?>">
        Etape 3 : Listes abonnés
    </a>

    <a href="<?php if($step == 'stats' || $data['newsletterSendDate'] != NULL) echo 'analytic?id_newsletter='.$_REQUEST['id_newsletter']; else echo '#'; ?>" class="btn <?php echo ($step == 'send' || $step == 'stats') ? 'active':'' ?>">
        <?php if($step == 'stats' || $data['newsletterSendDate'] != NULL) { ?>
            Etape 4 : Statistiques
        <?php }else { ?>
            Etape 4 : Confirmation
        <?php } ?>
    </a>


</div></div>

<?php } ?>
