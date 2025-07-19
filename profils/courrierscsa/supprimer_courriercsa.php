<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}

// Vérification des permissions
if ($_SESSION['Fonction'] === 'secretariat_csa') {
    $_SESSION['error'] = "Vous n'avez pas les permissions nécessaires pour cette action";
    header('Location: liste_courrierscsa.php');
    exit();
}

require_once '../../traitement/courriercsa_fonctions.php';

// Vérification de l'ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = "ID de courrier invalide.";
    $_SESSION['message_type'] = "danger";
    header("Location: liste_courrierscsa.php");
    exit();
}

$id = (int)$_GET['id'];

// Vérification CSRF (optionnel mais recommandé)
if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['message'] = "Token de sécurité invalide.";
    $_SESSION['message_type'] = "danger";
    header("Location: liste_courrierscsa.php");
    exit();
}

// Récupération du courrier pour vérifier son existence
$courrier = getCourrierById($conn, $id);
if (!$courrier) {
    $_SESSION['message'] = "Le courrier demandé n'existe pas.";
    $_SESSION['message_type'] = "danger";
    header("Location: liste_courrierscsa.php");
    exit();
}

// Suppression du PDF associé si existant
if (!empty($courrier['pdf']) && file_exists($courrier['pdf'])) {
    unlink($courrier['pdf']);
}

// Suppression du courrier
if (supprimerCourrier($conn, $id)) {
    $_SESSION['message'] = "Le courrier a été supprimé avec succès.";
    $_SESSION['message_type'] = "success";
} else {
    $_SESSION['message'] = "Erreur lors de la suppression du courrier.";
    $_SESSION['message_type'] = "danger";
}

header("Location: liste_courrierscsa.php");
exit();
?>