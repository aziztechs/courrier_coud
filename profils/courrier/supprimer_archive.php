<?php
// Démarrer la session
session_start();

if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    session_destroy();
    exit();
}


// Inclure les fichiers nécessaires
require_once('../../traitement/fonction_archive.php');
require_once('../../traitement/fonction.php');

// Vérifier si l'ID de l'archive est passé en paramètre
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Identifiant d'archive invalide.";
    header('Location: liste_archive.php');
    exit();
}

$id_archive = (int)$_GET['id'];

// Récupérer les données de l'archive pour obtenir le chemin du fichier PDF
$archive = getArchiveById($id_archive);

if (!$archive) {
    $_SESSION['error_message'] = "Archive introuvable.";
    header('Location: liste_archive.php');
    exit();
}

// Traitement de la suppression
try {
    // Supprimer le fichier PDF associé s'il existe
    if (!empty($archive['pdf_archive'])) {
        $filePath = '../../uploads/' . $archive['pdf_archive'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    // Supprimer l'archive de la base de données
    if (supprimerArchive($id_archive)) {
        $_SESSION['success_message'] = "L'archive a été supprimée avec succès.";
    } else {
        throw new Exception("Erreur lors de la suppression de l'archive dans la base de données.");
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
}

header('Location: liste_archive.php');
exit();