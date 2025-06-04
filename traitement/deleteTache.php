<?php

require_once('fonction.php');
$connexion = connexionBD() ;


if ( isset($_GET['idsT']) ) {
    
        global $connexion;
    $idsT = $_GET['idsT' ];
    $fun = $_GET['func'];
    $role = $_GET['role'];


    $sql1= "DELETE FROM suivi WHERE id_suivi = $idsT";
    $query = $connexion -> prepare($sql1);
    $query -> execute();
    if ($query->execute()) {
        // Redirection après mise à jour réussie
       
       
                header('Location: /courrier_coud/profils/direction/accueil_direction.php');
                     exit();
     
    } else {
        echo "Erreur lors de la mise à jour.";
    }
}


