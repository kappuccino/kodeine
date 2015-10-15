<?php if($_REQUEST['id_newsletter'] != NULL) { ?>

<div id="sub_steps"><div class="wrapper clearfix">

    <a href="data-options?id_newsletter=<?php echo $_REQUEST['id_newsletter']; ?>" class="btn <?php echo isMe('/newsletter/data-options') ? 'active':'' ?>">
        Options de ma newsletter
    </a>

    <?php if($data['is_designer'] != 1) { ?>

    <!--<a href="data-editor?id_newsletter=<?php /*echo $_REQUEST['id_newsletter']; */?>" class="btn <?php /*echo isMe('/newsletter/data-editor') ? 'active':'' */?>">
        Etape 2 : Editeur
    </a>-->

    <?php } else { ?>

    <!--<a href="data-designer?id_newsletter=<?php /*echo $_REQUEST['id_newsletter']; */?>" class="btn <?php /*echo isMe('/newsletter/data-designer') ? 'active':'' */?>">
        Etape 2 : Designer
    </a>-->

    <?php } ?>

	<a href="data-editor?id_newsletter=<?php echo $_REQUEST['id_newsletter']; ?>" class="btn <?php echo isMe('/newsletter/data-editor') ? 'active':'' ?>">
		Selectionner un gabarit
	</a>

	<a target="_blank" href="preview?id_newsletter=<?php echo $_REQUEST['id_newsletter']; ?>" class="btn">
		Prévisualiser ma newsletter
	</a>

    <a href="data-list?id_newsletter=<?php echo $_REQUEST['id_newsletter']; ?>" class="btn <?php echo isMe('/newsletter/data-list') ? 'active':'' ?>">
        Envoyer avec Mailchimp
    </a>


</div></div>

<?php } ?>
