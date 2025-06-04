<?php
// Démarre une nouvelle session ou reprend une session existante
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courier_coud/');
    exit();
}

// Sélectionnez les options à partir de la base de données avec une pagination
include('../../traitement/fonction.php');
include('../../traitement/requete.php');


if (isset($_GET['id_courrier']) ) {
    // Stockez l'ID du courrier dans la session
    $_SESSION['id_courrier'] = $_GET['id_courrier'];
    header('Location: tache.php');
    exit; // Assurez-vous d'appeler exit après header pour arrêter le script
} else {
    // Gestion de l'erreur si l'ID du courrier n'est pas fourni
    echo "Aucun ID de courrier fourni.";
    // Optionnellement, redirigez vers une autre page ou affichez un message d'erreur
}

if (isset($_GET['instruction']) ) {
    // Stockez l'ID du courrier dans la session
    $_SESSION['instruction'] = $_GET['instruction'];    // Redirigez vers listeLits.php ou toute autre page où vous voulez aller ensuite
    header('Location: tache.php');
    exit; // Assurez-vous d'appeler exit après header pour arrêter le script
} else {
    // Gestion de l'erreur si l'ID du courrier n'est pas fourni
    echo "Aucune Instruction fournie.";
    // Optionnellement, redirigez vers une autre page ou affichez un message d'erreur
}