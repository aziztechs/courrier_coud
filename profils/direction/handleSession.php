<?php
// Démarre une nouvelle session ou reprend une session existante
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courier_coud/');
    session_destroy();
    exit();
}

// Sélectionnez les options à partir de la base de données avec une pagination
include('../../traitement/fonction.php');


if (isset($_GET['id_courrier'])) {
    // Stockez l'ID du courrier dans la session
    $_SESSION['id_courrier'] = $_GET['id_courrier'];

    // Redirigez vers listeLits.php ou toute autre page où vous voulez aller ensuite
    header('Location: /courrier_coud/profils/direction/listeLits.php');
    exit; // Assurez-vous d'appeler exit après header pour arrêter le script
} else {
    // Gestion de l'erreur si l'ID du courrier n'est pas fourni
    echo "Aucun ID de courrier fourni.";
    // Optionnellement, redirigez vers une autre page ou affichez un message d'erreur
}

if (isset($_GET['id_courriers'])) {
    // Stocker l'ID du courrier dans la session
    $_SESSION['id_courriers'] = $_GET['id_courriers'];
    
    // Rediriger vers la page de suivi
    header('Location: suivi.php');
    exit();
} else {
    echo "Aucun courrier sélectionné.";
}