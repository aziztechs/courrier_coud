<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['password'])) {
    header('Location: /courrier_coud/');
    exit();
}

require_once('../../traitement/fonction_archive.php');

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        // Récupérer le numéro de correspondance avant suppression pour le message
        $archive = getArchiveById($id);
        $num = $archive['num_correspondance'] ?? 'inconnu';
        
        if (supprimerArchive($id)) {
            $_SESSION['delete_success'] = "L'archive #$num a été supprimée avec succès";
        } else {
            throw new Exception("Échec de la suppression de l'archive");
        }
    } catch (Exception $e) {
        $_SESSION['delete_error'] = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

// Redirection vers la liste avec le paramètre de succès
header('Location: liste_archive.php?delete_success=1');
exit();