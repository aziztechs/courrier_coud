<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}
require_once('../../traitement/fonction_suivi_courrier.php');

if (!isset($_GET['id'])) {
    header("Location: liste_suivi_courrier.php");
    exit();
}

$id = $_GET['id'];

// Vérifier d'abord si l'enregistrement existe
$suivi = $connexion->query("SELECT id FROM suivi_courrier WHERE id = $id")->fetch_assoc();

if ($suivi) {
    $connexion->query("DELETE FROM suivi_courrier WHERE id = $id");
}

header("Location: liste_suivi_courrier.php");
exit();
?>