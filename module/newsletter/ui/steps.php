
<div id="sub_steps"><div class="wrapper clearfix">

    <a href="data-options?id_newsletter=<?php echo $_REQUEST['id_newsletter']; ?>" class="btn <?php echo isMe('/newsletter/data-options') ? 'active':'' ?>">
        Etape 1 : Infos générales
    </a>
    <a href="data-designer?id_newsletter=<?php echo $_REQUEST['id_newsletter']; ?>" class="btn <?php echo isMe('/newsletter/data-designer') ? 'active':'' ?>">
        Etape 2 : Designer
    </a>
    <a href="data-list?id_newsletter=<?php echo $_REQUEST['id_newsletter']; ?>" class="btn <?php echo isMe('/newsletter/data-list') ? 'active':'' ?>">
        Etape 3 : Listes abonnés
    </a>
    <a href="#" class="btn <?php echo isMe('/newsletter/data-confirm') ? 'active':'' ?>">
        Etape 4 : Confirmation
    </a>

</div></div>
