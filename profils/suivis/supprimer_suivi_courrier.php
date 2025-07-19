<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}

require_once '../../traitement/suivi_courrier_fonctions.php';

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$suivi = supprimerSuivi($mysqli, $id_suivi);

// Récupérer l'ID du suivi à supprimer
$id_suivi = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_suivi) {
    $_SESSION['error_message'] = "ID de suivi invalide";
    header('Location: liste_suivi_courriers.php');
    exit();
}

// Vérifier que le suivi existe
$result = getSuivi($mysqli, $id_suivi);
if (!$result['success']) {
    $_SESSION['error_message'] = $result['error'];
    header('Location: liste_suivi_courriers.php');
    exit();
}

// Effectuer la suppression
$result = supprimerSuivi($mysqli, $id_suivi);
if ($result['success']) {
    $_SESSION['success_message'] = "Le suivi a été supprimé avec succès.";
} else {
    $_SESSION['error_message'] = $result['error'] ?? "Une erreur est survenue lors de la suppression";
}

header('Location: liste_suivi_courriers.php');
exit();
?>