<?php
session_start();
require_once('../../traitement/fonction.php');



if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id_courrier'])) {
    $id = filter_input(INPUT_GET, 'id_courrier', FILTER_VALIDATE_INT);
    
    if ($id && supprimerCourrier($id)) {
        $_SESSION['message'] = "Le courrier a été supprimé avec succès";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Erreur lors de la suppression du courrier";
        $_SESSION['message_type'] = "error";
    }
    
    header('Location: liste_courrier.php');
    exit();
}
?>