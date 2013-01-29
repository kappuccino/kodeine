<?php if($_REQUEST['id_newsletter'] != NULL) { ?>

<div id="sub_steps"><div class="wrapper clearfix">

    <?php if($data['is_designer'] != 1) { ?>

    <a href="data-infos?id_newsletter=<?php echo $_REQUEST['id_newsletter']; ?>" class="btn <?php echo isMe('/newsletter/data-infos') ? 'active':'' ?>">
        Etape 1 : Infos générales
    </a>
    <a href="data-editor?id_newsletter=<?php echo $_REQUEST['id_newsletter']; ?>" class="btn <?php echo isMe('/newsletter/data-editor') ? 'active':'' ?>">
        Etape 2 : Editeur
    </a>

    <?php } else { ?>

    <a href="data-options?id_newsletter=<?php echo $_REQUEST['id_newsletter']; ?>" class="btn <?php echo isMe('/newsletter/data-options') ? 'active':'' ?>">
        Etape 1 : Infos générales
    </a>
    <a href="data-designer?id_newsletter=<?php echo $_REQUEST['id_newsletter']; ?>" class="btn <?php echo isMe('/newsletter/data-designer') ? 'active':'' ?>">
        Etape 2 : Designer
    </a>

    <?php } ?>

    <a href="data-list?id_newsletter=<?php echo $_REQUEST['id_newsletter']; ?>" class="btn <?php echo isMe('/newsletter/data-list') ? 'active':'' ?>">
        Etape 3 : Listes abonnés
    </a>
    <a href="#" class="btn <?php echo isMe('/newsletter/data-confirm') ? 'active':'' ?>">
        Etape 4 : Confirmation
    </a>


</div></div>

<?php } ?>
