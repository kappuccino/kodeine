<?php
    // Verifie si la newsletter a deja ete envoyee

    // Si oui alors on redirige vers la page de stats
    if($_REQUEST['id_newsletter'] != NULL){
        $data = $app->apiLoad('newsletter')->newsletterGet(array(
            'id_newsletter' 	=> $_REQUEST['id_newsletter']
        ));
        if($data['newsletterSendDate'] == NULL){
            header('Location: analytic?id_newsletter='.$_REQUEST['id_newsletter']);
        }
    }
?>